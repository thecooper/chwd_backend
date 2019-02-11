<?php

namespace App\DataSources\Ballotpedia;

use App\DataLayer\Election\ElectionFragment;
use App\DataLayer\DataSource\DataSource;
use App\DataLayer\DataSource\DatasourceDTO;

use App\DataSources\IDataSource;
use App\DataSources\DataSourceConfig;
use App\DataSources\FieldMapper;
use App\DataSources\FileDataSourceConfig;
use App\DataSources\DataSourceImportResult;
use App\DataSources\DirectoryScanner;

use App\BusinessLogic\Models\Candidate;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\Repositories\CandidateRepository;
use App\BusinessLogic\ElectionLoader;
use App\BusinessLogic\CandidateLoader;

class Ballotpedia_CSV_File_Source implements IDataSource
{
    public static $column_mapping = [
        'state' => 0,
        'name' => 1,
        'first_name' => 2,
        'last_name' => 3,
        'ballotpedia_url' => 4,
        'candidates_id' => 5,
        'party_affiliation' => 6,
        'race_id' => 7,
        'general_election_date' => 8,
        'general_runoff_election_date' => 9,
        'office_district_id' => 10,
        'district_name' => 11,
        'district_type' => 12,
        'office_level' => 13,
        'office' => 14,
        'is_incumbent' => 15,
        'general_election_status' => 16,
        'website_url' => 17,
        'facebook_profile' => 18,
        'twitter_handle' => 19
    ];

    private $field_mapper;
    private $data_source_id;
    private $election_repository;
    private $candidate_repository;

    public function __construct(FieldMapper $field_mapper, ElectionRepository $election_repository, CandidateRepository $candidate_repository)
    {
        $this->field_mapper = $field_mapper;
        $this->election_repository = $election_repository;
        $this->candidate_repository = $candidate_repository;
    }

    public function Process(DataSourceConfig $config)
    {
        $input_directory = "";
        $import_limit = -1;
        $debugging = env("APP_DEBUG", false);

        if($config instanceof FileDataSourceConfig) {
            $input_directory = $config->input_directory;
            $import_limit = $config->import_limit;
        } else {
            throw new \Exception("Ballotpedia_csv_file_source does not currently support FileDataSourceConfig configuration type");
        }

        if($import_limit === -1 && $debugging) {
            echo "Running the entire import for the Ballotpedia file\n";
        }
        
        $result = new DataSourceImportResult();

        $ballotpedia_data_source = DataSource::where('name', 'ballotpedia')->first();
        
        if($ballotpedia_data_source == null) {
            throw new \Exception('No datasource has been established for Ballotpedia');
        }

        $this->data_source_id = $ballotpedia_data_source->id;

        $result->processed_line_count = 0;

        $processed_election_ids = array();

        // TODO: refactor this to have another class (or abstract class) simply provide lines for this class to process
        foreach (DirectoryScanner::getFileHandles($config->input_directory) as $file_handle) {
            if ($file_handle != false) {
                $header_read = false;
               
                while ($fields = fgetcsv($file_handle)) {
                    $result->processed_line_count++;

                    if (!$header_read) {
                        $header_read = true;
                        continue;
                    }

                    if($import_limit !== -1) {
                        if($result->processed_line_count >= $import_limit) { break; }
                    }
                    
                    $this->field_mapper->load_fields($fields);

                    $line_fields = $this->field_mapper->get_fields();
                    
                    $election_state = $line_fields['state'];
                    $election_g_election_date = $line_fields['general_election_date'];
                    $election_r_election_date = $line_fields['general_runoff_election_date'];

                    $election_pre_hash = $election_state.$election_g_election_date.$election_r_election_date;
                    $election_hash = hash("md5", $election_pre_hash);

                    if(!array_key_exists($election_hash, $processed_election_ids)) {
                        $new_election_id = $this->save_election($line_fields, $ballotpedia_data_source);
                        if($debugging) { print_r("Election ID for value $election_pre_hash: $new_election_id\n"); }
                        $processed_election_ids[$election_hash] = $new_election_id;
                    }

                    $translated_election_id = null;
                    
                    try {
                        $translated_eleciton_id = $processed_election_ids[$election_hash];
                    } catch (\Exception $ex) {
                        throw new \Exception("There was a problem setting the translated_election_id for value $election_pre_hash on line " . $result->processed_line_count);
                    }

                    $this->save_candidate($line_fields, $translated_eleciton_id);
                }
            }
        }

        // TODO: do file cleanup here. Create "processed" folder if it doesn't exist, then move file there
        // TODO: Also, create new file with rows that could not be processed for analysis

        // TODO: keep track of IDs that were processed so we can remove the ones that are no longer imported
        // TODO: Also, use processed IDs to re-run consolidation (or let that be another process that runs at a scheduled time)

        return $result;
    }

    public function CanProcess()
    {
        return true;
    }
    
    private function save_election($fields, $datasource_model) {
        $election_fields = array();

        $election_fields['id'] = null;
        $election_fields['election_id'] = null;
        $election_fields['name'] = $this->election_name_generator($fields);
        $election_fields['state_abbreviation'] = $fields['state'];
        $election_fields['primary_election_date'] = null;
        $election_fields['general_election_date'] = $this->set_null_if_empty($fields['general_election_date']);
        $election_fields['runoff_election_date'] = $this->set_null_if_empty($fields['general_runoff_election_date']);
        $election_fields['data_source_id'] = $this->data_source_id;

        // For now we need to translate a DataSource Eloquent model into a BusinessLogic model since it takes the whole thing, not just the ID
        $datasource = DatasourceDTO::create($datasource_model);
        
        $updated_or_created_election = $this->election_repository
          ->save(ElectionLoader::create($election_fields), $datasource);

        return $updated_or_created_election->id;
    }

    private function election_name_generator($fields) {
        // TODO: factor this out to separate static method on another class for easier unit testing
        $district_name = StateLookup::lookup($fields['state']);

        $general_election_date = null;
        $general_election_date_value = $fields['general_election_date'];

        try {
            $general_election_date = new \DateTime($general_election_date_value);
        } catch (\Exception $ex) {
            throw new \Exception('Unable to parse date for field general_election_date: ' . $general_election_date_value);
        }

        $election_year = date_format($general_election_date, 'Y');

        $election_name = "{$district_name} General Election {$election_year}";

        return $election_name;
    }

    private function save_candidate($fields, $election_id) {
        $candidate_fields = $fields;

        $candidate_fields['name'] = $fields['name'];
        $candidate_fields['election_id'] = $election_id;
        $candidate_fields['party_affiliation'] = $fields['party_affiliation'];
        $candidate_fields['election_status'] = $fields['general_election_status'];
        $candidate_fields['office'] = $fields['office'];
        $candidate_fields['office_level'] = $fields['office_level'];
        $candidate_fields['is_incumbent'] = (strtolower($fields['is_incumbent']) == 'yes');
        $candidate_fields['district_type'] = $fields['district_type'];
        $candidate_fields['district'] = $fields['district_name'];
        $candidate_fields['district_identifier'] = $this->derive_district_identifier($fields['district_name']);
        $candidate_fields['website_url'] = $this->set_null_if_empty($fields['website_url']);
        $candidate_fields['donate_url'] = null;
        $candidate_fields['facebook_profile'] = $this->set_null_if_empty($fields['facebook_profile']);
        $candidate_fields['twitter_handle'] = $this->set_null_if_empty($fields['twitter_handle']);
        $candidate_fields['data_source_id'] = $this->data_source_id;

        $candidate = new Candidate();
        CandidateLoader::load($candidate, $candidate_fields);
        
        $updated_or_created_candidate = $this->candidate_repository->save($candidate, $this->data_source_id);
        
        return $updated_or_created_candidate->consolidated_candidate_id;
    }

    private function derive_district_identifier($district_name) {
        // TODO: refactor district identifier into more extensible code
        $regex = '';

        if(strpos($district_name, "Alaska State Senate") !== false) {
            $regex = '/District ([a-zA-Z])/';
        } else {
            $regex = '/District ([\d]+)|Circuit Place ([\d]+)/';
        }
        
        $match_count = preg_match_all($regex, $district_name, $matches, PREG_SET_ORDER);
        
        if($match_count == 0 || $match_count == false) {
            return null;
        }

        $id = $matches[0][1];

        return $id;
    }

    private function set_null_if_empty($input) {
        return $input == '' ? null : $input;
    }
}

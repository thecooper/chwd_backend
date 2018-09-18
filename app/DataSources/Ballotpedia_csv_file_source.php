<?php

namespace App\DataSources;

use App\Models\Candidate\Candidate;
use App\Models\Election\ConsolidatedElection;
use App\Models\Election\Election;
use App;

use \Exception;

class Ballotpedia_CSV_File_Source implements IDataSource
{
    public static $column_mapping = array(
        'state' => 0,
        'name' => 1,
        'first_name' => 2,
        'last_name' => 3,
        'url' => 4,
        'candidates_id' => 5,
        'party_affiliation' => 6,
        'race_id' => 7,
        'election_date_id' => 8,
        'election_dates_district_id' => 9,
        'election_dates_district_name' => 10,
        'general_election_date' => 11,
        'general_runoff_election' => 12,
        'general_runoff_election_date' => 13,
        'office_district_id' => 14,
        'district_name' => 15,
        'district_type' => 16,
        'office_level' => 17,
        'office' => 18,
        'is_incumbent' => 19,
        'general_election_status' => 20,
        'website_url' => 21,
        'facebook_profile' => 22,
        'twitter_handle' => 23
    );

    private $field_mapper;
    private $data_source_id;

    public function __construct(FieldMapper $field_mapper)
    {
        $this->field_mapper = $field_mapper;
    }

    public function Process(DataSourceConfig $data_source_config)
    {
        $result = new DataSourceImportResult();

        $ballotpedia_data_source = App\DataSource::where('name', 'ballotpedia')->first();
        
        if($ballotpedia_data_source == null) {
            throw new \Exception('No datasource has been established for Ballotpedia');
        }

        $this->data_source_id = $ballotpedia_data_source->id;

        $result->processed_line_count = 0;

        $processed_election_ids = array();

        // TODO: refactor this to have another class (or abstract class) simply provide lines for this class to process
        foreach (DirectoryScanner::getFileHandles($data_source_config->input_directory) as $file_handle) {
            if ($file_handle != false) {
                $header_read = false;
               
                while ($fields = fgetcsv($file_handle)) {
                    $result->processed_line_count++;

                    if (!$header_read) {
                        $header_read = true;
                        continue;
                    }

                    // if($result->processed_line_count >= 1000) { break; }
                    
                    $this->field_mapper->load_fields($fields);

                    $election_date_id = $this->field_mapper->get_value('election_date_id');
                    
                    if($election_date_id == '' || $election_date_id == null) {
                        continue;
                    }

                    $line_fields = $this->field_mapper->get_fields();
                    
                    if(false == array_search($election_date_id, $processed_election_ids)) {
                        $new_election_id = $this->save_election($line_fields);
                        $processed_election_ids[$election_date_id] = $new_election_id;
                    }
                    
                    $translated_election_id = null;
                    
                    try {
                        $translated_eleciton_id = $processed_election_ids[$election_date_id];
                    } catch (Exception $ex) {
                        throw new Exception("There was a problem setting the translated_election_id using value " . $election_date_id . " on line " . $this->line_count);
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
    
    private function save_election($fields) {
        $election_fields = array();

        $election_fields['consolidated_election_id'] = null;
        $election_fields['name'] = $this->election_name_generator($fields);
        $election_fields['state_abbreviation'] = $fields['state'];
        $election_fields['primary_election_date'] = null;
        $election_fields['general_election_date'] = $this->set_null_if_empty($fields['general_election_date']);
        $election_fields['runoff_election_date'] = $this->set_null_if_empty($fields['general_runoff_election_date']);
        $election_fields['data_source_id'] = $this->data_source_id;

        $updated_or_created_election = Election::createOrUpdate($election_fields);

        return $updated_or_created_election->consolidated_election_id;
    }

    private function election_name_generator($fields) {
        // TODO: factor this out to separate static method on another class for easier unit testing
        $district_name = $fields['election_dates_district_name'];

        $general_election_date = null;
        $general_election_date_value = $fields['general_election_date'];

        try {
            $general_election_date = new \DateTime($general_election_date_value);
        } catch (Exception $ex) {
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
        
        $updated_or_created_candidate = Candidate::createOrUpdate($candidate_fields);
        
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

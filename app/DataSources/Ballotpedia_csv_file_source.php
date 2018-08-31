<?php

namespace App\DataSources;

use App\Models\Candidate\Candidate;
use App\Models\Election\ElectionConsolidator;
use App\Models\Election\ConsolidatedElection;
use App\Models\Election\Election;
use App\DataSource;
use \Exception;

class Ballotpedia_CSV_File_Source implements IDataSource
{
    private static $column_mapping = array(
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
    private $election_consolidator;
    private $data_source_id;
    private $import_dir;

    private $line_count = 0;

    public function __construct(ElectionConsolidator $election_consolidator)
    {
        $this->field_mapper = new FieldMapper(Ballotpedia_CSV_File_Source::$column_mapping);
        $this->election_consolidator = $election_consolidator;

        $ballotpedia_data_source = DataSource::where('name', 'Ballotpedia')->first();
        // TODO: Some null checking for ballotpedia_data_source

        if($ballotpedia_data_source == null) {
            throw new \Exception('No datasource has been established for Ballotpedia');
        }
        
        $this->data_source_id = $ballotpedia_data_source->id;

        $this->import_dir = env('APP_BALLOTPEDIA_IMPORT_DIR', 'C:\\temp');
    }

    public function Process()
    {
        $this->line_count = 0;

        $processed_election_ids = array();

        foreach (DirectoryScanner::getFileHandles($this->import_dir) as $file_handle) {
            if ($file_handle != false) {                
                $header_read = false;
               
                while ($fields = fgetcsv($file_handle)) {
                    $this->line_count++;

                    if (!$header_read) {
                        $header_read = true;
                        continue;
                    }

                    if($this->line_count >= 10) { break; }
                    
                    // $fields = explode(',', $line);

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
        
        return $this->line_count;
    }

    public function CanProcess()
    {
        return true;
    }
    
    private function save_election($fields) {
        $election_fields = array();

        $election_fields['name'] = $this->election_name_generator($fields);
        $election_fields['consolidated_election_id'] = null;
        $election_fields['state_abbreviation'] = $fields['state'];
        $election_fields['election_date'] = '';
        $election_fields['is_runoff'] = false;

        // TODO : store both election and runoff dates separately, both can be provided
        if(array_key_exists('general_election_date', $fields)) {
            $election_fields['election_date'] = $fields['general_election_date'];
        } else if(array_key_exists('general_runoff_election_date', $fields)) {
            $election_fields['election_date'] = $fields['general_runoff_election_date'];
            $election_fields['is_runoff'] = true;
        }
        
        $election_fields['is_special'] = false;
        $election_fields['data_source_id'] = $this->data_source_id;

        $updated_or_created_election = Election::createOrUpdate($election_fields);

        return $updated_or_created_election->consolidated_election_id;
    }

    private function election_name_generator($fields) {
        $election_name_segments = array(
            $fields['election_dates_district_name']
        );
        
        $general_election_date = null;
        $general_runoff_election_date = null;
        
        $general_election_date_value = $fields['general_election_date'];
        $general_runoff_election_date_value = $fields['general_runoff_election_date'];

        try {
            $general_election_date = new \DateTime($general_election_date_value);
        } catch (Exception $ex) {
            var_dump($this->field_mapper->get_fields());
            throw new \Exception('Unable to parse date for field general_election_date: ' . $general_election_date_value . ' on line ' . $this->line_count);
        }

        try {
            $general_runoff_election_date = new \DateTime($general_runoff_election_date_value);
        } catch (Exception $ex) {
            throw new \Exception('Unable to parse date for field general_runoff_election_date: ' . $general_runoff_election_date_value);
        }

        $election_year = '';

        if($general_election_date != null && $general_election_date != '') {
            array_push($election_name_segments, 'General');
            $election_year = date_format($general_election_date, 'Y');
        } elseif ($general_runoff_election_date != null && $general_runoff_election_date != '') {
            array_push($general_runoff_election_date, 'General Runoff');
            $election_year = date_format($general_election_date, 'Y');
        } else {
            throw new \Exception('Unable to determine election year'); //TODO: to better error messaging here to be able to diagnose what rows aren't parsable.
        }

        array_push($election_name_segments, 'Election');
        array_push($election_name_segments, $election_year);

        return implode(' ', $election_name_segments);
    }

    private function save_candidate($fields, $election_id) {
        $candidate_fields = $fields;

        $candidate_fields['name'] = $fields['name'];
        $candidate_fields['election_id'] = $election_id;
        $candidate_fields['party_affiliation'] = $fields['party_affiliation'];
        $candidate_fields['website_url'] = $fields['website_url'];
        $candidate_fields['donate_url'] = null;
        $candidate_fields['facebook_profile'] = $fields['facebook_profile'];
        $candidate_fields['twitter_handle'] = $fields['twitter_handle'];
        $candidate_fields['election_status'] = $fields['general_election_status'];
        $candidate_fields['election_office'] = $fields['office'];
        $candidate_fields['is_incumbent'] = (strtolower($fields['is_incumbent']) == 'yes');
        $candidate_fields['data_source_id'] = $this->data_source_id;
        $candidate_fields['district_type'] = $fields['district_type'];
        $candidate_fields['district'] = $fields['district_name'];
        $candidate_fields['district_number'] = $this->derive_district_identifier($fields['district_name']);
        $candidate_fields['office_level'] = $fields['office_level'];

        $updated_or_created_candidate = Candidate::createOrUpdate($candidate_fields);

        return $updated_or_created_candidate->consolidated_candidate_id;
    }

    private function derive_district_identifier($district_name) {
        // TODO: refactor district identifier into more extensible code
        $match_count = preg_match_all('/District ([\d\w]+)|Circuit Place ([\d]+)/', $district_name, $matches, PREG_SET_ORDER);
        
        if($match_count == 0 || $match_count == false) {
            return null;
        }

        $id = $matches[0][1];

        return $id;
    }
}

class FieldMapper {
    private $column_mapping;
    private $field_set = null;

    function __construct($column_mapping) {
        $this->column_mapping = $column_mapping;
    }

    public function load_fields($fields) {
        $this->field_set = $fields;
    }

    public function get_value($field_name) {
        if(!$this->has_value($field_name)) {
            return null;
        }
        
        return $this->field_set[$this->column_mapping[$field_name]];
    }

    public function get_fields() {
        $translated_fields = array();

        foreach($this->column_mapping as $field_name => $translated_field_index) {
            $translated_fields[$field_name] = $this->field_set[$translated_field_index];
        }

        return $translated_fields;
    }

    public function has_value($field_name) {
        if($this->field_set == null) {
            throw new \Exception('FieldMapper::get_value() - fields have not yet been set');
        }
        
        if(!array_key_exists($field_name, $this->column_mapping)) {
            return false;
        }

        $field_index = $this->column_mapping[$field_name];
        
        if(!array_key_exists($field_index, $this->field_set)) {
            return false;
        }

        return true;
    }
}

class DirectoryScanner
{

    public static function getFileHandles($directory)
    {
        $handles = array();

        if (!file_exists($directory)) {
            throw new \Exception('Invalid configuration: data source directory not found: ' . $directory);
        }

        $file_or_directories = scandir($directory);
        $files_found = count($file_or_directories);

        print_r('Found {$files_found} potential files to process<br/>');

        if ($file_or_directories != false) {
            foreach ($file_or_directories as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $full_file_path = join('/', [$directory, $file]);
                print_r($full_file_path . '<br/>');

                if (is_file($full_file_path)) {
                    print_r('Found file to process: {$file}<br/>');
                    $handle = fopen($full_file_path, 'r');

                    yield $handle;

                    fclose($handle);
                }
            }
        } else {
            throw new \Exception('Unable to find files in configured directory');
        }
    }
}

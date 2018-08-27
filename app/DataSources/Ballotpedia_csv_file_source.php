<?php

namespace App\DataSources;

use App\Candidate;
use App\Models\Election\ElectionConsolidator;
use App\Models\Election\ConsolidatedElection;
use App\Models\Election\Election;
use App\DataSource;

class Ballotpedia_CSV_File_Source implements IDataSource
{
    private static $column_mapping = array(
        "state" => 0,
        "name" => 1,
        "first_name" => 2,
        "last_name" => 3,
        "url" => 4,
        "candidates_id" => 5,
        "party" => 6,
        "race_id" => 5,
        "election_date_id" => 6,
        "election_dates_district_id" => 7,
        "election_dates_district_name" => 8,
        "general_election_date" => 9,
        "general_runoff_election" => 10,
        "general_runoff_election_date" => 11,
        "office_district_id" => 12,
        "district_name" => 13,
        "district_type" => 14,
        "office_level" => 15,
        "office" => 16,
        "is_incumbent" => 17,
        "general_election_status" => 18,
        "website_url" => 19,
        "facebook_profile" => 20,
        "twitter_handle" => 21
    );

    private $field_mapper;
    private $election_consolidator;
    private $data_source_id;

    function __construct(ElectionConsolidator $election_consolidator)
    {
        $this->field_mapper = new FieldMapper(Ballotpedia_CSV_File_Source::$column_mapping);
        $this->election_consolidator = $election_consolidator;

        $ballotpedia_data_source = DataSource::where('name', 'Ballotpedia')->firstOrFail();
        // TODO: Some null checking for ballotpedia_data_source
        $this->data_source_id = $ballotpedia_data_source->id;
    }

    public function Process()
    {
        $data_source_directory = "C:\\temp";
        $file_processed_count = 0;

        $processed_election_ids = array();

        foreach (DirectoryScanner::getFileHandles($data_source_directory) as $file_handle) {
            if ($file_handle != false) {
                $file_processed_count++;
                $header_read = false;
               
                while ($line = fgets($file_handle)) {
                    if (!$header_read) {
                        $header_read = true;
                        continue;
                    }

                    $fields = explode(",", $line);

                    $this->field_mapper->load_fields($fields);

                    $election_date_id = $this->field_mapper->get_value("election_date_id");
                    
                    if(false == array_search($election_date_id, $processed_election_ids)) {
                        $new_election_id = $this->save_election($this->field_mapper->get_fields());
                        $processed_election_ids[$election_date_id] = $new_election_id;
                    }
                    
                    $translated_eleciton_id = $processed_election_ids[$election_date_id];

                    $this->persist_candidate($translated_eleciton_id);
                }
            }
        }

        return $file_processed_count;
    }

    public function CanProcess()
    {
        return true;
    }
    
    private function save_election($fields) {
        $election_fields = array();

        $election_fields['name'] = $this->election_name_generator();
        $election_fields['consolidated_election_id'] = null;
        $election_fields['state_abbreviation'] = $fields['state'];
        $election_fields['election_date'] = "";
        $election_fields['is_runoff'] = false;

        if(array_key_exists("general_election_date", $fields)) {
            $election_fields['election_date'] = $fields["general_election_date"];
        } else if(array_key_exists("general_runoff_election_date", $fields)) {
            $election_fields['election_date'] = $fields["general_runoff_election_date"];
            $election_fields['is_runoff'] = true;
        }
        
        $election_fields['is_special'] = false;
        $election_fields['data_source_id'] = $this->data_source_id;
        $election_fields['election_type'] = ($is_runoff ? "runoff" : "general");

        $updated_or_created_election = Election::createOrUpdate($election_fields);

        return $updated_or_created_election->id;
    }

    private function election_name_generator() {
        $election_name_segments = array(
            $this->field_mapper->get_value("election_dates_district_name")
        );
        
        $general_election_date = null;
        $general_runoff_election_date = null;
        
        $general_election_date_value = $this->field_mapper->get_value("general_election_date");
        $general_runoff_election_date_value = $this->field_mapper->get_value("general_runoff_election_date");

        try {
            $general_election_date = new \DateTime($general_election_date_value);
        } catch (Exception $ex) {
            print_r("Here");
            throw new \Exception("Unable to parse date for field general_election_date: " . $general_election_date_value);
        }

        try {
            $general_runoff_election_date = new \DateTime($general_runoff_election_date_value);
        } catch (Exception $ex) {
            throw new \Exception("Unable to parse date for field general_runoff_election_date: " . $general_runoff_election_date_value);
        }

        $election_year = "";

        if($general_election_date != null && $general_election_date != "") {
            array_push($election_name_segments, "General");
            $election_year = date_format($general_election_date, "Y");
        } elseif ($general_runoff_election_date != null && $general_runoff_election_date != "") {
            array_push($general_runoff_election_date, "General Runoff");
            $election_year = date_format($general_election_date, "Y");
        } else {
            throw new \Exception("Unable to determine election year"); //TODO: to better error messaging here to be able to diagnose what rows aren't parsable.
        }

        array_push($election_name_segments, "Election");
        array_push($election_name_segments, $election_year);

        return implode(" ", $election_name_segments);
    }

    private function persist_candidate($linking_election_id) {
        $candidate_name = $this->field_mapper->get_value("name");
        $candidate_office_level = $this->field_mapper->get_value("office_level");
        $candidate_district = $this->field_mapper->get_value("district_name");
        $candidate_district_type = $this->field_mapper->get_value("district_type");
        
        $state = $this->field_mapper->get_value("state");

        $candidate = Candidate::where('name', $candidate_name)
            ->where('office_level', $candidate_office_level)
            ->where('district', $candidate_district)
            ->where('district_type', $candidate_district_type)
            ->first();

        $district_identifier = $this->derive_district_identifier($candidate_district);
            
        if($candidate == null) { $candidate = new Candidate(); }
        
        $candidate->name = $this->field_mapper->get_value("name");
        $candidate->party_affiliation = $this->field_mapper->get_value("party");
        $candidate->website_url = $this->field_mapper->get_value("website_url");
        $candidate->election_id = $linking_election_id;
        $candidate->donate_url = "";
        $candidate->facebook_profile = $this->field_mapper->get_value("facebook_profile");
        $candidate->twitter_handle = $this->field_mapper->get_value("twitter_handle");
        $candidate->gender = "?";
        $candidate->election_office = $this->field_mapper->get_value("office");
        $candidate->election_status = $this->field_mapper->get_value("general_election_status");
        $candidate->birthdate = date("Y-m-d");
        $candidate->data_source_id = $this->data_source_id;
        
        $is_incumbent = false;
        if($this->field_mapper->has_value("is_incumbent")) {
            $is_incumbent = strtolower($this->field_mapper->get_value("is_incumbent")) == "yes" ? true : false;
        }

        $candidate->is_incumbent = (strtolower($is_incumbent) == "yes" ? true : false);
        $candidate->name = $candidate_name;
        $candidate->office_level = $candidate_office_level;
        $candidate->district = $candidate_district;
        $candidate->district_number = $district_identifier;
        $candidate->district_type = $candidate_district_type;

        try {
            if (!$candidate->save()) {
                // Log the error here
            }
        } catch (Exception $ex) {
            // Log error here and fail gracefully
            throw $ex;
        }
    }

    private function derive_district_identifier($district_name) {
        // TODO: refactor district identifier into more extensible code
        $match_count = preg_match_all("/District ([\d\w]+)|Circuit Place ([\d]+)/", $district_name, $matches, PREG_SET_ORDER);
        
        if($match_count == 0 || $match_count == false) {
            return null;
        }

        $id = $matches[0][1];

        var_dump($id);

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
            throw new \Exception("FieldMapper::get_value() - fields have not yet been set");
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
            throw new Exception("Invalid configuration: data source directory not found");
        }

        $file_or_directories = scandir($directory);
        $files_found = count($file_or_directories);

        print_r("Found {$files_found} potential files to process<br/>");

        if ($file_or_directories != false) {
            foreach ($file_or_directories as $file) {
                if ($file == "." || $file == "..") {
                    continue;
                }

                $full_file_path = join("\\", [$directory, $file]);
                print_r($full_file_path . "<br/>");

                if (is_file($full_file_path)) {
                    print_r("Found file to process: {$file}<br/>");
                    $handle = fopen($full_file_path, "r");

                    yield $handle;

                    fclose($handle);
                }
            }
        } else {
            throw new Exception("Unable to find files in configured directory");
        }
    }
}

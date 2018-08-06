<?php

namespace App\DataSources;

use App\Candidate;

class Ballotpedia_CSV_File_Source implements IDataSource
{
    public function __constructor()
    {

    }

    public function Process()
    {
        $data_source_directory = "C:\\temp";
        $file_processed_count = 0;

        foreach (DirectoryScanner::getFileHandles($data_source_directory) as $file_handle) {
            if ($file_handle != false) {
                $file_processed_count++;
                $header_read = false;

                while ($line = fgets($file_handle)) {
                    if (!$header_read) {
                        $header_read = true;
                        continue;
                    }

                    $segments = explode(",", $line);

                    list($state_abbreviation, $name, , $candidate_id, $party, $race_id, $office, $office_level, $district_type,
                        $is_incumbent, $election_status, $website_url, $facebook_profile, $twitter_handle) = $segments;

                    $candidate = new Candidate();

                    $candidate->name = $name;
                    $candidate->party_affiliation = $party;
                    $candidate->website_url = $website_url;
                    $candidate->election_id = null;
                    $candidate->donate_url = "";
                    $candidate->facebook_profile = $facebook_profile;
                    $candidate->twitter_handle = $twitter_handle;
                    $candidate->gender = "?";
                    $candidate->election_office = $office;
                    $candidate->election_status = $election_status;
                    $candidate->birthdate = date("Y-m-d");
                    $candidate->data_source_id = 1;
                    $candidate->is_incumbent = (strtolower($is_incumbent) == "yes" ? true : false);

                    try {
                        if (!$candidate->save()) {
                            // Log the error here
                        }
                    } catch (Exception $ex) {
                        // Log error here and fail gracefully
                        throw $ex;
                    }
                }
            }
        }

        return $file_processed_count;
    }

    public function CanProcess()
    {
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

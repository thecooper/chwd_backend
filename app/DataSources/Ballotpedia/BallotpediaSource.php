<?php

namespace App\DataSources\Ballotpedia;

use Illuminate\Support\Facades\Log;

use App\DataSources\DirectoryScanner;
use App\DataSources\DataSourceImportResult;
use App\DataSources\FileDataSourceConfig;

use App\DataLayer\DataSource\DataSource;

class BallotpediaSource {

  private $field_generator;
  private $directory_scanner;
  private $data_processor;
  
  public function __construct(DirectoryScanner $directory_scanner, 
                              CSVFieldGenerator $field_generator,
                              BallotpediaDataProcessor $data_processor) {
    $this->field_generator = $field_generator;
    $this->directory_scanner = $directory_scanner;
    $this->data_processor = $data_processor;
  }
  
  public function import(FileDataSourceConfig $config) {
    Log::channel('import')->info("Starting import for Ballotpedia datasource");
    
    $result = new DataSourceImportResult();
    $result->start_import();

    $input_directory = $config->input_directory;
    $import_limit = $config->import_limit;
    $debugging = env("APP_DEBUG", false);

    $regexp = '/Ballotpedia_data_for_CHWD_([\d]{4}\-[\d]{2}\-[\d]{2})/';

    $match_predicate = function($file) use ($regexp) {
      return preg_match($regexp, $file) === 1;      
    };

    $file_sort = function($fileA, $fileB) use ($regexp) {
      $file_A_matches = [];
      $file_B_matches = [];
      
      preg_match($regexp, $fileA, $file_A_matches);
      preg_match($regexp, $fileB, $file_B_matches);

      $file_A_date = strtotime($file_A_matches[1]);
      $file_B_date = strtotime($file_B_matches[1]);

      if($file_A_date > $file_B_date) {
        return 1;
      } elseif ($file_A_date < $file_B_date) {
        return -1;
      } else {
        return 0;
      }
    };
    
    $files = $this->directory_scanner->getFiles($input_directory, $match_predicate, $file_sort);
    
    $ballotpedia_data_source = DataSource::where('name', 'ballotpedia')->first();
        
    if($ballotpedia_data_source == null) {
      Log::channel('import')->info("No datasource has been established for Ballotpedia");
      throw new \Exception('No datasource has been established for Ballotpedia');
    }
    
    $this->data_processor->initialize($ballotpedia_data_source->id);
    
    foreach($files as $file) {
      if(strpos($file, 'Ballotpedia') === false) {
        continue;
      }

      Log::channel('import')->info("Processing file $file...");
      $file_contents = file_get_contents($file);
      $lines = explode(PHP_EOL, $file_contents);
      // $fieldList = $this->field_generator->generate_fields($file);

      $first_line = true;
      $current_line_count = 0;

      foreach($lines as $line) {
        $fields = str_getcsv($line);

        // Skip the first line
        if($first_line) {
          $this->data_processor->load_headers($fields);
          $first_line = false;
          continue;
        }

        if($result->processed_line_count == $import_limit) {
          break;
        }

        if($result->processed_line_count % 1000 === 0) {
          Log::channel('import')->debug("Processing line {$result->processed_line_count}");
        }
        
        try {
          $this->data_processor->process_fields($fields);
        } catch (\Exception $ex) {
          Log::channel('import')->error("{$ex->getMessage()} -- $file:$current_line_count\n");
          $result->failed_line_count++;

          if($result->failed_line_count > 100) {
            Log::channel('import')->error("Error threshold (100) exceeded");
            break;
          }
        }

        $current_line_count++;
        $result->processed_line_count++;
      }

      $result->processed_file_count++;
      $current_line_count = 0;

      if($result->processed_line_count == $import_limit) {
        break;
      }
    }

    $result->finish_import();
    Log::channel('import')->info("Processed {$result->processed_line_count} lines across {$result->processed_file_count} files");
    Log::channel('import')->info("{$result->failed_line_count} lines skipped");
    Log::channel('import')->info("Total execution time: {$result->execution_time()}");

    return $result;
  }
}
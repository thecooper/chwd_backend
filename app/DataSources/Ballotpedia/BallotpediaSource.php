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
  
  private $result;
  
  public function __construct(DirectoryScanner $directory_scanner, 
                              CSVFieldGenerator $field_generator,
                              BallotpediaDataProcessor $data_processor) {
    $this->field_generator = $field_generator;
    $this->directory_scanner = $directory_scanner;
    $this->data_processor = $data_processor;

    $this->result = new DataSourceImportResult();
  }

  public function import(FileDataSourceConfig $config) {
    $this->result->start_import();

    $input_directory = $config->input_directory;
    $import_limit = $config->import_limit;
    $debugging = env("APP_DEBUG", false);

    $files = $this->directory_scanner->getFiles($input_directory);

    $ballotpedia_data_source = DataSource::where('name', 'ballotpedia')->first();
        
    if($ballotpedia_data_source == null) {
        throw new \Exception('No datasource has been established for Ballotpedia');
    }
    
    $this->data_processor->initialize($ballotpedia_data_source->id);
    
    foreach($files as $file) {
      $fieldList = $this->field_generator->generate_fields($file);

      $first_line = true;
      $current_line_count = 0;

      foreach($fieldList as $fields) {
        // Skip the first line
        if($first_line) {
          $first_line = false;
          continue;
        }

        if($this->result->processed_line_count == $import_limit) {
          break;
        }
        
        try {
          $this->data_processor->process($fields);
        } catch (\Exception $ex) {
          Log::error("{$ex->getMessage()} -- $file:$current_line_count\n");
          echo "{$ex->getMessage()} -- $file:$current_line_count\n";
          $this->result->failed_line_count++;
        }

        $current_line_count++;
        $this->result->processed_line_count++;
      }

      $this->result->processed_file_count++;
      $current_line_count = 0;

      if($this->result->processed_line_count == $import_limit) {
        break;
      }
    }

    $this->result->finish_import();

    return $this->result;
  }
}
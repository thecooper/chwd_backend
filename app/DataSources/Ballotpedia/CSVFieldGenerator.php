<?php

namespace App\DataSources\Ballotpedia;

use Illuminate\Support\Facades\Log;

class CSVFieldGenerator {

  public function __construct() {
    
  }

  public function generate_fields($file_path) {
    if(!file_exists($file_path)) {
      Log::warning("Cannot generate fields for file {$file_path} because it does not exist");
      return [];
    }

    $fields = [];
    $file_handle = fopen($file_path, 'r');
    
    while ($fields = fgetcsv($file_handle)) {
      yield $fields;
    }

    fclose($file_handle);
  }
}
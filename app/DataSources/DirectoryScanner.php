<?php

namespace App\DataSources;

use Illuminate\Support\Facades\Log;

class DirectoryScanner
{
    public function getFiles($directory, callable $match_predicate, callable $sort_predicate)
    {
      $files = $this->get_file_list($directory, $match_predicate);
      $sorted_files = $this->sort_files_by_predicate($files, $sort_predicate);
      
      foreach($sorted_files as $file) {
        yield $this->get_full_file_path($directory, $file);
      }
    }

    private function get_file_list($directory, $match_predicate) {
      $handles = array();

      if (!file_exists($directory)) {
        Log::error("Invalid configuration - data source directory not found: $directory");
        throw new \Exception("Invalid configuration - data source directory not found: $directory");
      }

      $file_or_directories = scandir($directory);
      $files_found = count($file_or_directories);

      if ($file_or_directories != false) {
        $files_array = [];
        
        foreach ($file_or_directories as $file) {
          if ($file == '.' || $file == '..') {
            continue;
          }

          if (is_file($this->get_full_file_path($directory, $file)) && $match_predicate($file)) {
            array_push($files_array, $file);
          }
        }

        return $files_array;
      } else {
          throw new \Exception('Unable to find files in configured directory');
      }
    }

    private function sort_files_by_predicate(array $files, callable $sort_predicate) {
      if($files === null) {
        return [];
      }

      if($sort_predicate === null) {
        return $files;
      }

      usort($files, $sort_predicate);

      return $files;
    }

    private function get_full_file_path($directory, $file) {
      return join('/', [$directory, $file]);
    }
}
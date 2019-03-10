<?php

namespace App\DataSources;

class DirectoryScanner
{
    public function getFiles($directory)
    {
        $handles = array();

        if (!file_exists($directory)) {
          Log::error("Invalid configuration - data source directory not found: $directory");
          throw new \Exception("Invalid configuration - data source directory not found: $directory");
        }

        $file_or_directories = scandir($directory);
        $files_found = count($file_or_directories);

        if ($file_or_directories != false) {
            foreach ($file_or_directories as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $full_file_path = join('/', [$directory, $file]);
                // print_r($full_file_path . '<br/>');
                // print_r("Processing file: " . $full_file_path . "\n");

                if (is_file($full_file_path)) {
                    yield $full_file_path;
                }
            }
        } else {
            throw new \Exception('Unable to find files in configured directory');
        }
    }
}
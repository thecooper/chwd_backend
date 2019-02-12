<?php

namespace App\DataSources;

class DirectoryScanner
{

    public function getFiles($directory)
    {
        $handles = array();

        if (!file_exists($directory)) {
          // throw new \Exception('Invalid configuration: data source directory not found: ' . $directory);
          // TODO: Do some logging here
          return [];
        }

        $file_or_directories = scandir($directory);
        $files_found = count($file_or_directories);

        // print_r('Found {$files_found} potential files to process<br/>');

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
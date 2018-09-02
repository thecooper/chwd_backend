<?php 

namespace App\DataSources;

class FileDataSourceConfig extends DataSourceConfig {
    public $input_directory;

    public function __construct($directory_path) {
        $this->input_directory = $directory_path;
    }
}
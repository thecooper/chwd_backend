<?php

namespace App\DataSources;

use \DateTime;

class DataSourceImportResult {
    public $processed_line_count;
    public $failed_line_count;
    public $processed_file_count;
    
    protected $import_start_timestamp;
    protected $import_end_timestamp;

    public function __construct() {
        $this->processed_line_count = 0;
        $this->failed_line_count = 0;
        $this->processed_file_count = 0;
    }

    public function start_import() {
        $this->import_start_timestamp = new DateTime();
    }

    public function finish_import() {
        $this->import_end_timestamp = new DateTime();
    }

    /**
     * @return string formatted string based on the difference between the start and end timestamp properties (hours:minutes:seconds)
     */
    public function execution_time() {
        $date_interval = $this->import_end_timestamp->diff($this->import_start_timestamp);
        return $date_interval->format('%h:%i:%s');
    }
}
<?php

namespace App\DataSources;

class DataSourceImportResult {
    public $processed_line_count;

    public $warning;
    public $error;
    public $information;
    public $failed_rows;
    protected $import_start_timestamp;
    protected $import_end_timestamp;

    public function __construct() {
        $this->warning = array();
        $this->error = array();
        $this->information = array();
        $this->failed_rows = array();
    }

    public function start_import() {
        $this->import_start_timestamp = new DateTime();
    }

    public function finish_import() {
        $this->import_end_timepstamp = new DateTime();
    }

    /**
     * @return string formatted string based on the difference between the start and end timestamp properties (hours:minutes:seconds)
     */
    public function execution_time() {
        $date_interval = $this->import_end_timestamp->diff($this->import_start_timestamp);
        return $date_interval->format('h:i:s');
    }
}
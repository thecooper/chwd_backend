<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DataSources;
use App\DataSources\FileDataSourceConfig as FileDataSourceConfig;

class import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $ballotpedia_importer;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $field_mapper = new \App\DataSources\FieldMapper(\App\DataSources\Ballotpedia_CSV_File_Source::$column_mapping);
        $this->ballotpedia_importer = new \App\DataSources\Ballotpedia_CSV_File_Source($field_mapper);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->ballotpedia_importer->CanProcess()) {
            $config = new FileDataSourceConfig(env('APP_BALLOTPEDIA_IMPORT_DIR'));
            $import_result = $this->ballotpedia_importer->Process($config);
            $line_count = $import_result->processed_line_count;
            // echo `Processed {$line_count} lines`;
        }
    }
}

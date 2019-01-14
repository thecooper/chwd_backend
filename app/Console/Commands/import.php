<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\DataSources\FileDataSourceConfig;
use App\DataSources\Ballotpedia_CSV_File_Source;
use App\DataSources\FieldMapper;

use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\ElectionFragmentCombiner;

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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $field_mapper = new FieldMapper(Ballotpedia_CSV_File_Source::$column_mapping);
        $election_fragment_combiner = new ElectionFragmentCombiner();
        $election_repository = new ElectionRepository($election_fragment_combiner);
        $ballotpedia_importer = new Ballotpedia_CSV_File_Source($field_mapper, $election_repository);

        if($ballotpedia_importer->CanProcess()) {
            $config = new FileDataSourceConfig();
            $config->input_directory = env('BALLOTPEDIA.IMPORT_DIR');
            $config->import_limit = env('BALLOTPEDIA.IMPORT_LIMIT', -1);
            
            $import_result = $ballotpedia_importer->Process($config);
            $line_count = $import_result->processed_line_count;
            echo "Processed $line_count lines";
        }
    }
}

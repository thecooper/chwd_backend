<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\DataSources\FileDataSourceConfig;
use App\DataSources\Ballotpedia\BallotpediaSource;
use App\DataSources\FieldMapper;

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
    public function handle(BallotpediaSource $ballotpedia_source)
    {
      $config = new FileDataSourceConfig();
      $config->input_directory = env('BALLOTPEDIA_IMPORT_DIR');
      $config->import_limit = env('BALLOTPEDIA_IMPORT_LIMIT', -1);
      
      try {
        $import_result = $ballotpedia_source->import($config);
      } catch (Exception $ex) {
        Log::channel('import')->error("There was a critical error: {$ex->getMessage()}");
      }
    }
}

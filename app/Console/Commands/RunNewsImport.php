<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SelectCandidatesToProcessNews;

class RunNewsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:RunNewsImport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches a job to run the news import job';

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
        SelectCandidatesToProcessNews::dispatch();
    }
}

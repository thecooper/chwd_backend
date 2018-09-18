<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Election\ConsolidatedElection;
use App\DataSources\NewsAPIDataSource;
use App\News;

class ProcessElectionNews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $election;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ConsolidatedElection $election)
    {
        $this->election = $election;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(NewsAPIDataSource $news_data_source)
    {
        print_r("Starting job to process news for election: {$this->election->name}");
        
        foreach($this->election->candidates as $candidate) {
            $query = "\"{$candidate->name}\" {$this->election->state_abbreviation}";

            $articles = $news_data_source->get_articles($query);

            News::save_articles($articles, $candidate->id);
        }
    }
}

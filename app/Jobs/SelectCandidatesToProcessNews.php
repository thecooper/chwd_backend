<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

use App\DataLayer\News;
use App\DataSources\NewsAPIDataSource;
use App\DataLayer\Candidate\Candidate;
use App\DataLayer\Election\ConsolidatedElection;

use \DateTime;
use \DateInterval;
use \Exception;

class SelectCandidatesToProcessNews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $candidate_update_id_query = "select consolidated_candidates.id from consolidated_candidates left join candidate_news_imports on consolidated_candidates.id = candidate_news_imports.candidate_id where last_updated_timestamp <= ? OR last_updated_timestamp is null limit ?";
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(NewsAPIDataSource $news_data_source)
    {
        $current_date = new DateTime();
        $import_count_per_day = env("ELECTION_NEWS_IMPORT_COUNT_PER_DAY", 0);
        $import_dry_run = (boolean)env("ELECTION_NEWS_DRY_RUN", false);

        $import_count = 0;
        $a_week_ago = new DateInterval("P7D");
        $a_week_ago->invert = 1;

        $minimum_refresh_timestamp = date_add(new DateTime(), $a_week_ago);

        $candidates = $this->get_candidates_for_news_import($import_count_per_day, $minimum_refresh_timestamp);

        $election_ids = $candidates->pluck('election_id')->unique();

        $elections_abbreviations = ConsolidatedElection::whereIn('id', $election_ids)->get()->mapWithKeys(function($election) {
            return [$election->id => $election->state_abbreviation];
        })->toArray();

        foreach($candidates as $candidate) {
            try {
                if(!$import_dry_run) {
                    $this->retrieve_and_save_news_for_candidate($news_data_source, $candidate, $elections_abbreviations[$candidate->election_id]);
                } else {
                    echo "Doing dry run of news import for candidate id: {$candidate->id}\n";
                }
                
                if(!$import_dry_run) {
                    $candidate->set_last_news_update_timestamp(new DateTime());
                }
            } catch (Exception $ex) {
                echo "An exception was encountered trying to import news for candidate {$candidate->id}\n";
                // Do some error logging here
            }
            
        }

        if($import_dry_run) {
            echo "Attempted to import $import_count_per_day news endpoint calls\n";
        }
    }

    private function get_candidates_for_news_import($import_limit, $refresh_before_timestamp) {
        if($import_limit == -1) {
            return Candidate::all();
        } else {
            $candidate_ids_to_process = collect(DB::select($this->candidate_update_id_query, [$refresh_before_timestamp, $import_limit]))
            ->map(function($val) { return $val->id; })->toArray();
        
            return Candidate::whereIn('id', $candidate_ids_to_process)->get();
        }
    }

    private function retrieve_and_save_news_for_candidate($news_data_source, $candidate, $election_state_abbreviation) {
        $query = "\"{$candidate->name}\" $election_state_abbreviation";
        $articles = $news_data_source->get_articles($query);
        $article_count = count($articles);
        if($article_count > 0) {
            echo "For candidate {$candidate->id} Found {$article_count} article(s) to load\n";
        }
        News::save_articles($articles, $candidate->id);
    }
}

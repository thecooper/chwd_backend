<?php

namespace App\BusinessLogic;

use App\DataSources\NewsAPIDataSource;

class BallotNewsAggregator {

    private $news_data_source;

    public function __construct(NewsAPIDataSource $news_data_source) {
        $this->news_data_source = $news_data_source;
    }

    public function get_election_news(ConsolidatedElection $election) {
        $candidates = $election->candidates;

        $query = $this->_build_candidate_name_query($candidates, $election->state_abbreviation);

        $articles = $news_data_source->get_articles($query);
    }

    private function _build_candidate_name_query($candidates, $state) {
        $candidate_names = $candidates->pluck('name')->map(function($name) {
            return `\"$name\"`;
        });

        return implode(" OR ", $candidate_names) . " AND $state";
    }
}
<?php

namespace App\DataLayer\Candidate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Candidate extends Model
{
    protected $primaryKey = 'id';

    public function election() {
        return $this->belongsTo('App\DataLayer\Election\ConsolidatedElection');
    }

    public function news() {
        return $this->hasMany('App\DataLayer\News', 'candidate_id');
    }

    /**
     * @return int of last updated
     */
    public function last_news_update_timestamp() {
        if($this->id == null) {
            return null;
        }

        $results =  DB::select('select last_updated_timestamp from candidate_news_imports where candidate_id = :id', ["id" => $this->id]);

        if(count($results) == 0) {
            return null;
        }

        return $results[0]->last_updated_timestamp;
    }

    public function set_last_news_update_timestamp($timestamp) {
        $last_updated_timestamp = $this->last_news_update_timestamp();

        if($last_updated_timestamp == null) {
            DB::insert('insert into candidate_news_imports (candidate_id, last_updated_timestamp) values (?, ?)', [$this->id, $timestamp]);
        } else {
            DB::update('update candidate_news_imports set last_updated_timestamp = ? where candidate_id = ?', [$timestamp, $this->id]);
        }
    }
}

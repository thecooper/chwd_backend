<?php

namespace App\DataLayer;

use Illuminate\Database\Eloquent\Model;
use App\BusinessLayer\NewsArticle;

class News extends Model
{

  public function consolidated_candidate() {
    return $this->belongsTo('App\DataLayer\Candidate\Candidate', 'candidate_id');
  }
    //
    public static function save_articles($articles, $candidate_id) {
        foreach($articles as $article) {
            $existing_article = News::where('url', $article->url)
                ->where('candidate_id', $candidate_id)
                ->first();
    
            if($existing_article == null) {
                $existing_article = new News();
            }
    
            $existing_article->url = $article->url;
            $existing_article->thumbnail_url = $article->thumbnail_url ?? '';
            $existing_article->title = $article->title;
            $existing_article->description = $article->description ?? '';
            $existing_article->candidate_id = $candidate_id;
            $existing_article->publish_date = (new \DateTime($article->publish_date))->format('Y-m-d H:i:s');
            $existing_article->save();
        }
    }
}

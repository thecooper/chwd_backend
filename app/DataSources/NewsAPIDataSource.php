<?php 

namespace App\DataSources;

use \Exception;
use App\Models\NewsArticle;

class NewsAPIDataSource implements INewsDataSource {
    private $api_key;
    private $api_base_url;

    public function __construct() {
        $this->api_key = env('NEWS_API_KEY', null);

        if($this->api_key == null) {
            throw new Exception('News API api key not provided in configuration');
        }

        $source_filter = "&sources=abc-news,associated-press,axios,cbs-news,cnn,fox-news,google-news,msnbc,national-review,nbc-news,newsweek,politico,reuters,the-american-conservative,the-hill,the-huffington-post,the-wall-street-journal,the-washington-post,the-washington-times";
        $this->api_base_url = "https://newsapi.org/v2/everything?apiKey=" . $this->api_key . $source_filter;
    }
    
    public function get_articles($query) {
        $url_encoded_query = urlencode($query);

        $api_url = $this->api_base_url . "&q=" . $url_encoded_query;

        $api_result = file_get_contents($api_url);

        return $this->process_api_result($api_result);
    }

    /**
     * @return array(NewsArticle)
     */
    private function process_api_result($result) {
        $result_json_decoded = json_decode($result);

        $result_count = $result_json_decoded->totalResults;

        $articles = $result_json_decoded->articles;

        $processed_articles = array();
        
        
        foreach($articles as $article) {
            $new_article = new NewsArticle();
            $new_article->url = $article->url;
            $new_article->thumbnail_url = $article->urlToImage;
            $new_article->title = $article->title;
            $new_article->description = $article->description;
            $new_article->publish_date = $article->publishedAt;

            array_push($processed_articles, $new_article);
        }

        return $processed_articles;
    }
}
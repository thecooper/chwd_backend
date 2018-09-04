<?php 

namespace App\DataSources;

interface INewsDataSource {

    /**
     * @param string $query string that represents query used to retreive news articles
     * @return App\Models\NewsArticle
     */
    function get_articles($query);
}
<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Model;

class ConsolidatedElection extends Model
{
    // protected $table = "consolidated_elections";

    public static $fields = [
        "id" => ["primary"],
        "name" => ["primary"],
        "state_abbreviation" => ["max:2"],
        "county" => ["nullable"],
        "district" => ["nullable"],
        "primary_date" => ["nullable"],
        "general_date" => ["nullable"],
        "is_special" => ["nullable"],
        "is_runoff" => ["nullable"],
    ];
}

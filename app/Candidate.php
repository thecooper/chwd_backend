<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    public $timestamps = false;

    //
    public function IsValid()
    {
        return true;
    }

    public function data_source()
    {
        return $this->hasOne('App\DataSource');
    }
}

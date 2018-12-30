<?php

namespace App\DataLayer\DataSource;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    //
    public function priorities()
    {
        return $this->hasMany('App\DataLayer\DataSourcePriority');
    }
}

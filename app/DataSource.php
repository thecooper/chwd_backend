<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    //
    public function priorities()
    {
        return $this->hasMany('App\DataSourcePriority');
    }
}

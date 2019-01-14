<?php

namespace App\DataLayer\DataSource;

use App\BusinessLogic\Models\Datasource;

class DatasourceDTO {

  public static function convert($from, $to) {
    $to->id = $from->id;
    $to->name = $from->name;
  }

  public static function create($from) {
    $datasource = new Datasource();
    DatasourceDTO::convert($from, $datasource);
    return $datasource;
  }
}
<?php

namespace App\DataSources;

require '../../vendor/autoload.php';
require_once '../../bootstrap/app.php';

// require_once './FieldMapper.php';
// require_once './DataSourceConfig.php';
// require_once './Ballotpedia_csv_file_source.php';

$import_ds_config = new FileDataSourceConfig("C:\\temp");

$field_mapper = new FieldMapper(Ballotpedia_CSV_File_Source::$column_mapping);
$ballotpedia_csv_file_source = new Ballotpedia_CSV_File_Source($field_mapper);
$ballotpedia_csv_file_source->Process($import_ds_config);

?>
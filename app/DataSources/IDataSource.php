<?php

namespace App\DataSources;

interface IDataSource
{
    function Process(DataSourceConfig $config);

    function CanProcess();
}

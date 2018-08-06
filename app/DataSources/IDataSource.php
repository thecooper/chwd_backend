<?php

namespace App\DataSources;

interface IDataSource
{
    function Process();

    function CanProcess();
}

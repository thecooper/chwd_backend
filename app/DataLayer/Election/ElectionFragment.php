<?php

namespace App\DataLayer\Election;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\DataLayer\EloquentModelTransferManager;

class ElectionFragment extends Model
{
    public $incrementing = true;
    protected $primaryKey = "id";
}

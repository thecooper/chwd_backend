<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataSources\Ballotpedia_CSV_File_Source;
use App\Models\Election\ElectionConsolidator;
use App\Models\EloquentModelTransferManager;

class ImportController extends Controller
{
    private $source = null;
    
    public function __construct(Ballotpedia_CSV_File_Source $source) {
        $this->source = $source;
    }

    public function show() {
        if ($this->source->CanProcess()) {
            $file_count = $this->source->Process();
        }
    
        return "Processed {$file_count} files";
    }
}

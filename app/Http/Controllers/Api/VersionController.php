<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VersionController extends Controller
{
    public function show()
    {
        return response()->json([
            'version' => config('site.software_version'),
            'date' => date('Y-m-d'),
        ]);
    }
}

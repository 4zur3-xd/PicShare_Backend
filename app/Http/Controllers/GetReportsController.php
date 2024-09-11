<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetReportsController extends Controller
{
    public function getReports(Request $request)
    {
        return $request->user();
    }
}

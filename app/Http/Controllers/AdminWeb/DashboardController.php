<?php

namespace App\Http\Controllers\AdminWeb;

use App\Models\Post;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $lineChartData = [];
            $pieChartData = [];
            $headData = [];

            // Get data for line chart
            $temp = User::whereDate('created_at', date('Y-m-d', strtotime('-4 days')))->count();
            array_push($lineChartData, $temp);
            $temp = User::whereDate('created_at', date('Y-m-d', strtotime('-3 days')))->count();
            array_push($lineChartData, $temp);
            $temp = User::whereDate('created_at', date('Y-m-d', strtotime('-2 days')))->count();
            array_push($lineChartData, $temp);
            $temp = User::whereDate('created_at', date('Y-m-d', strtotime('-1 days')))->count();
            array_push($lineChartData, $temp);
            $temp = User::whereDate('created_at', date('Y-m-d'))->count();
            array_push($lineChartData, $temp);

            // Get data dor pie chart
            $temp = User::whereNotNull('email_verified_at')->count();
            array_push($pieChartData, $temp);
            $temp = User::whereNull('email_verified_at')->count();
            array_push($pieChartData, $temp);

            // Get 4 header data
            $headData['total_users'] = User::count();
            $todayDate = date('Y-m-d H:i:s');
            $weekAgoDate = date('Y-m-d H:i:s', strtotime('-7 days'));
            $headData['new_users'] = User::whereBetween('created_at', [$weekAgoDate, $todayDate])->count();
            $headData['total_posts'] = Post::count();
            $headData['total_reports'] = Report::count();

            return view('dashboard')->with('lineChartData', $lineChartData)
            ->with('pieChartData', $pieChartData)
            ->with('headData', $headData);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }
}

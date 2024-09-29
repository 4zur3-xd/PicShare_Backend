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

    public function userManage($page = 1)
    {
        try {
            $headData = [];

            // Get 4 header data
            $headData['total_users'] = User::count();
            $todayDate = date('Y-m-d H:i:s');
            $weekAgoDate = date('Y-m-d H:i:s', strtotime('-7 days'));
            $headData['new_users'] = User::whereBetween('created_at', [$weekAgoDate, $todayDate])->count();
            $headData['veri_users'] = User::whereNotNull('email_verified_at')->count().' - '.User::whereNull('email_verified_at')->count();
            $headData['banned_users'] = User::where('status', 0)->count();

            // Users and pagination
            $perPage = 10;
            $allUser = User::get();
            $userNum = User::count();
            $pageNum = ceil($userNum/$perPage);
            $usersData = []; // Returning data
            $usersData['data'] = [];

            if(empty($_GET['search'])){
                if($userNum == 0){
                    $usersData['user_num'] = 0;
                    $usersData['msg'] = 'No user found! (Kinda impossible to happen)';
                }
    
                if($pageNum == 1){
                    $usersData['user_num'] = $userNum;
                    $usersData['page'] = $page;
                    $usersData['total_pages'] = $pageNum;
                    $usersData['data'] = $allUser;
                }
    
                if($pageNum > 1){
                    if($page*$perPage > $userNum){
                        for($i = ($page - 1)*$perPage; $i < $userNum; $i++){
                            array_push($usersData['data'], $allUser[$i]);
                        }
                    }else{
                        for($i = ($page - 1)*$perPage; $i < $page*$perPage; $i++){
                            array_push($usersData['data'], $allUser[$i]);
                        }
                    }
    
                    $usersData['user_num'] = $userNum;
                    $usersData['page'] = $page;
                    $usersData['total_pages'] = $pageNum;
                }
            }else{
                $usersData['data'] = User::whereLike('name', '%'.$_GET['search'].'%')->orWhereLike('email', '%'.$_GET['search'].'%')->distinct()->get();
                $usersData['user_num'] = User::whereLike('name', '%'.$_GET['search'].'%')->orWhereLike('email', '%'.$_GET['search'].'%')->distinct()->count();
            }

            return view('users')->with('headData', $headData)->with('usersData', $usersData);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }

    public function reportManage($page = 1)
    {
        try {
            // Reports and pagination
            $reportPerPage = 10;
            $data = Report::all();
            $reportNum = $data->count();
            $pageNum = ceil($reportNum/$reportPerPage);
            $reportsData = []; // Returning data
            $reportsData['data'] = [];

            // Get posts' data and users' data
            $temp = Report::distinct()->pluck('post_id');
            $reportsData['post_data'] = Post::whereIn('id', $temp)->get();

            $temp = Report::distinct()->pluck('reported_user');
            $reportsData['reported_user_data'] = User::whereIn('id', $temp)->get();

            $temp = Report::distinct()->pluck('user_reporting');
            $reportsData['reporting_user_data'] = User::whereIn('id', $temp)->get();

            // Pagination
            if($reportNum == 0){
                $reportsData['rp_num'] = 0;
                $reportsData['msg'] = 'No reports found!';
            }

            if($pageNum == 1){
                $reportsData['rp_num'] = $reportNum;
                $reportsData['page'] = $page;
                $reportsData['total_pages'] = $pageNum;
                $reportsData['data'] = $data;
            }

            if($pageNum > 1){
                if($page*$reportPerPage > $reportNum){
                    for($i = ($page - 1)*$reportPerPage; $i < $reportNum; $i++){
                        array_push($reportsData['data'], $data[$i]);
                    }
                }else{
                    for($i = ($page - 1)*$reportPerPage; $i < $page*$reportPerPage; $i++){
                        array_push($reportsData['data'], $data[$i]);
                    }
                }

                $reportsData['rp_num'] = $reportNum;
                $reportsData['page'] = $page;
                $reportsData['total_pages'] = $pageNum;
            }

            return view('reports')->with('reportsData', $reportsData);
            // return $reportsData;
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }

    public function userBan()
    {
        try {
            $target = User::where('id', $_POST['user_id'])->first();
            $target->status = 0;
            $target->save();

            return redirect()->back();
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }
}

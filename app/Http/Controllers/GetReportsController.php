<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Report;
use Illuminate\Http\Request;

class GetReportsController extends Controller
{
    public function getReports($page = 1)
    {
        try {
            $reportPerPage = 10;
            $data = Report::all();
            $reportNum = $data->count();
            $pageNum = ceil($reportNum/$reportPerPage);

            if($reportNum == 0){
                $msg = 'No report found.';
                return ResponseHelper::success(message: $msg, data: ['report_num' => $reportNum]);
            }

            if($pageNum == 1){
                return ResponseHelper::success(data: [
                    'current_page' => $page,
                    'page_num' => $pageNum,
                    'report_num' => $reportNum,
                    'reports' => $data,
                ]);
            }

            if($pageNum > 1){
                $reports = [];
                if($page*$reportPerPage > $reportNum){
                    for($i = ($page - 1)*$reportPerPage; $i < $reportNum; $i++){
                        array_push($reports, $data[$i]);
                    }
                }else{
                    for($i = ($page - 1)*$reportPerPage; $i < $page*$reportPerPage; $i++){
                        array_push($reports, $data[$i]);
                    }
                }

                return ResponseHelper::success(data: [
                    'current_page' => $page,
                    'page_num' => $pageNum,
                    'report_num' => $reportNum,
                    'reports' => $reports,
                ]);
            }

            return $data; // return all data just in case i mess up pagination lol
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function getReportByPost($id = null)
    {
        try {
            if($id == null){
                $msg = 'No post id delivered.';
                return ResponseHelper::error(message: $msg);
            }

            $data = Report::where('post_id', $id);
            $reportNum = $data->count();

            if($reportNum == 0){
                $msg = 'No report found.';
                return ResponseHelper::success(message: $msg, data: ['report_num' => $reportNum]);
            }else{
                return ResponseHelper::success(data: [
                    'report_num' => $reportNum,
                    'reports' => $data->get(),
                ]);
            }
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function getReportByUserSent($id = null)
    {
        try {
            if($id == null){
                $msg = 'No user id delivered.';
                return ResponseHelper::error(message: $msg);
            }

            $data = Report::where('user_reporting', $id);
            $reportNum = $data->count();

            if($reportNum == 0){
                $msg = 'No report found.';
                return ResponseHelper::success(message: $msg, data: ['report_num' => $reportNum]);
            }else{
                return ResponseHelper::success(data: [
                    'report_num' => $reportNum,
                    'reports' => $data->get(),
                ]);
            }
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function getReportByUser($id = null)
    {
        try {
            if($id == null){
                $msg = 'No user id delivered.';
                return ResponseHelper::error(message: $msg);
            }

            $data = Report::where('reported_user', $id);
            $reportNum = $data->count();

            if($reportNum == 0){
                $msg = 'No report found.';
                return ResponseHelper::success(message: $msg, data: ['report_num' => $reportNum]);
            }else{
                return ResponseHelper::success(data: [
                    'report_num' => $reportNum,
                    'reports' => $data->get(),
                ]);
            }
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}

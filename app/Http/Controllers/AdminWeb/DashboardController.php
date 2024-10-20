<?php

namespace App\Http\Controllers\AdminWeb;

use App\Enum\FriendType;
use App\Enum\NotificationPayloadType;
use App\Enum\NotificationType;
use App\Helper\LinkToHelper;
use App\Helper\NotificationHelper;
use App\Models\Post;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebasePushController;
use App\Http\Controllers\NotificationController;
use App\Http\Requests\StoreNotificationRequest;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{

    protected $firebasePushController;
    protected $notificationController;

    public function __construct(FirebasePushController $firebasePushController, NotificationController $notificationController)
    {
        $this->firebasePushController = $firebasePushController;
        $this->notificationController = $notificationController;
    }
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
            Post::where('user_id', $_POST['user_id'])->delete();

            return redirect()->back();
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }

    public function postDelete()
    {
        try {
            $imgUrl = Post::where('id', operator: $_POST['post_id'])->first()->url_image;
            $postId=$_POST['post_id'];
            $post=Post::findOrFail($postId);
            Post::where('id', $postId)->delete();

            $imgUrl = str_replace('/storage/', '', $imgUrl);
            $delete = Storage::disk('public')->delete($imgUrl);

            if(!$delete){
                $msg = 'Something wrong when deleting image!';
                return view('errors.500')->with('error_info', $msg);
            }

            // push notification and store in notification table
            $message="Your post has been deleted by Admin";
            $title="Post Deleted";
            $userId=$post->user_id;
            $this->sendNotification($userId, $message, $title,$post);

            return redirect()->back();
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }



    private function sendNotification($userId, $message, $title,$post)
    {
        $currentUser = auth()->user();
        $friendUser = User::find($userId);
        if (!$friendUser) {
            return;
        }

        $content = $message;
        $fcmToken = $friendUser->fcm_token;
        $avatar = $currentUser->url_avatar;

        // Create notification record
        $linkTo = LinkToHelper::createLinkTo(NotificationPayloadType::DELETION, null,$post->id,postCaption: $post->caption,postImage: $post->url_image,postCreatedTime: $post->created_at,postLikeCount : $post->like_count,postCmtCount:$post->cmt_count );
        $request = new StoreNotificationRequest([
            'title' => $title,
            'user_id' => $friendUser->id,
            'content' => $content,
            'link_to' => $linkTo,
            'notification_type' => NotificationType::SYSTEM,
        ]);
        $notification = $this->notificationController->store($request);
        $notificationId = $notification ? $notification->id : null;
        if ($fcmToken) {
            $notificationData = $this->prepareNotificationData($fcmToken, $title, $content, $avatar,  $notificationId);
            $this->firebasePushController->sendNotification(new Request($notificationData));
        }

    }

    private function prepareNotificationData($fcmToken, $title, $body, $imageUrl, $notificationId)
    {
        return NotificationHelper::createNotificationData(
            fcmToken: $fcmToken,
            title: $title,
            body: $body,
            imageUrl: $imageUrl,
            postId: null,
            commentId: null,
            replyId: null,
            friendType: null,
            type: NotificationPayloadType::DELETION,
            notificationId: $notificationId,
            conversationId: null,
        );
    }
}

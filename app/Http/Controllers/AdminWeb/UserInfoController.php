<?php

namespace App\Http\Controllers\AdminWeb;

use App\Models\Post;
use App\Models\User;
use App\Models\Report;
use App\Helper\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserInfoController extends Controller
{
    public function index ($id = null)
    {
        try {
            if($id == null){
                $msg = 'Please provide user id!';
                return view('errors.500')->with('error_info', $msg);
            }

            $headData = [];

            $userData = User::where('id', $id)->first();

            if(!$userData){
                $msg = 'User not found!';
                return view('errors.500')->with('error_info', $msg);
            }else{
                $headData['total_posts'] = Post::where('user_id', $id)->count();
                $headData['total_rp_sent'] = Report::where('user_reporting', $id)->count();
                $headData['total_rp_recv'] = Report::where('reported_user', $id)->count();
            }

            return view('userinfo')->with('userData', $userData)->with('headData', $headData);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }

    public function edit (Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'name' => ['string', 'max:255']
            ]);

            if($validation->fails()){
                $failMSG = $validation->messages();
                return redirect()->back()->with('failMSG', $failMSG->toArray()['name'][0]);
            }

            $user = User::where('id', $request->user()->id)->first();
            $user->name = $request->name;
            $user->save();

            $successMSG = 'Success!';
            return redirect()->back()->with('successMSG', $successMSG);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }

    public function editPassword (Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'old_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if($validation->fails()){
                $failMSG = array_values($validation->messages()->toArray())[0][0];
                return redirect()->back()->with('failMSG', $failMSG);
            }

            $user = User::where('id', $request->user()->id)->first();
            $user->password = $request->password;
            $user->save();

            $successMSG = 'Success!';
            return redirect()->back()->with('successMSG', $successMSG);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }

    public function editAvatar (Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'url_avatar' => ['image', 'max:2048']
            ]);

            if($validation->fails()){
                $failMSG = $validation->messages()->toArray();
                return redirect()->back()->with('failMSG', $failMSG['url_avatar'][0]);
            }

            $imageFile = $request->file('url_avatar');
            $fullUrl = ImageHelper::saveAndGenerateUrl($imageFile, 'public/images');

            $user = User::where('id', $request->user()->id)->first();
            $user->url_avatar = $fullUrl;
            $user->save();

            $successMSG = 'Success!';
            return redirect()->back()->with('successMSG', $successMSG);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }

    public function deleteAvatar (Request $request)
    {
        try {
            $url_avatar = str_replace('/storage/', '', $request->user()->url_avatar);
            $delete = Storage::disk('public')->delete($url_avatar);

            if(!$delete){
                $msg = 'Something wrong when deleting image!';
                return redirect()->back()->with('failMSG', $msg);
            }

            $user = User::where('id', $request->user()->id)->first();
            $user->url_avatar = null;
            $user->save();

            $successMSG = 'Success!';
            return redirect()->back()->with('successMSG', $successMSG);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }
}

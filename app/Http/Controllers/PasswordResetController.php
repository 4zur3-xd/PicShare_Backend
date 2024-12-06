<?php

namespace App\Http\Controllers;

use App\Helper\JwtHelper;
use App\Helper\ResponseHelper;
use App\Mail\OtpResetEmail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{

    private $otp;
    private $validity;
    private array $validationMessages;

    public function __construct()
    {
        $this->otp = new Otp();
        $this->validity =  (int) env('OTP_VALIDITY_MINUTES', 15);
        $this->validationMessages = [
            'email.required' => __('emailIsRequire'),
            'email.email' => __('invalidEmail'),
            'email.unique' => __('alreadyTakenEmail'),
            'password.required' => __('passwordIsRequired'),
            'password.confirmed' => __('passwordNotMatch'),
            'otp.required' => __('otpIsRequired'),
        ];
    }
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $otp = $this->otp->generate($request->email, 'numeric', 6, $this->validity);

        Mail::to($request->email)->send(new OtpResetEmail($otp, $this->validity));

        return ResponseHelper::success(message: __('otpSentToYourEmail'), data: ['validity' => $this->validity]);
    }

    public function resetPassword(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|numeric',
                'password' => 'required|confirmed',
            ], $this->validationMessages);

            $validate = $this->otp->validate($request->email, $request->otp);

            if (!$validate->status) {
                return ResponseHelper::error(message: __('invalidOTP'));
            }

            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->password = Hash::make($request->password);
                JwtHelper::deleteAllRefreshTokenOfUser($user->id);
                $user->save();
            }
            DB::commit();

            return ResponseHelper::success(message: __('passwordResetSuccessfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Enum\Language;
use App\Helper\ImageHelper;
use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class ApiUserController extends Controller
{
    public function destroy(Request $request)
    {
        try {
            auth()->user()->tokens()->delete();

            $request->user()->delete();

            $msg = __('deleteUserSuccessfully');

            return ResponseHelper::success(message: $msg);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the request
            $validation = Validator::make($request->all(), [
                'name' => ['string', 'max:255'],
                'url_avatar' => ['nullable', 'image', 'max:2048'],
                'language' => ['string', 'max:255'],
            ]);
    
            if ($validation->fails()) {
                return ResponseHelper::error(message: __('failToValidation') .  $validation->errors());
            }
    
            // Handle the image file if it is present
            $imageFile = $request->file('url_avatar');
            $fullUrl = ImageHelper::saveAndGenerateUrl($imageFile, 'public/images');
    
            // Update the user with new data
            $user = $request->user();
            $dataToUpdate = $request->all();
    
            // If the image URL was updated, replace it in the data array
            if ($fullUrl) {
                $dataToUpdate['url_avatar'] = $fullUrl;
            }

            if(isset($dataToUpdate['language'])){
                $locale = $dataToUpdate['language'];
                if (!in_array($locale, Language::getValues())) {
                    $locale = Language::EN;
                }
                App::setLocale($locale);
            }
    
            // Update user attributes
            $user->fill($dataToUpdate);
            $user->save();
            // user->update = user->fill + user->save
            return ResponseHelper::success(message: __('updateSuccessfully'), data: $user);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:__('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
    public function changePassword(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'old_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if ($validation->fails()) {
                return ResponseHelper::error(message: __('failToValidation') .  $validation->errors());
            }

            $request->user()->fill($request->all());
            $request->user()->save();

            $msg = __('updatePassSuccessfully');

            return ResponseHelper::success(message: $msg, data: $request->user());
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
}

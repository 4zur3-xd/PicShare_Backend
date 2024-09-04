<?php

namespace App\Http\Controllers;

use App\Helper\ImageHelper;
use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
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

            $msg = "User deleted.";

            return ResponseHelper::success(message: $msg);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
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
                return ResponseHelper::error(message: 'Validation fails.');
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
    
            // Update user attributes
            $user->fill($dataToUpdate);
            $user->save();
            // user->update = user->fill + user->save
            return ResponseHelper::success(message: 'User updated.', data: $user);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
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
                return ResponseHelper::error(message: $validation->errors());
            }

            $request->user()->fill($request->all());
            $request->user()->save();

            $msg = 'Password updated.';

            return ResponseHelper::success(message: $msg, data: $request->user());
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}

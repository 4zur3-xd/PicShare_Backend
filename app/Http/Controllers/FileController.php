<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    //

    public function getPrivateFile($filePath)
    {
        // Step 1: Check if the file exists in the private storage
        if (!Storage::exists('private/images/' . $filePath)) {
            abort(404, 'File not found.');
        }

        // Step 2: Get the current authenticated user
        $user = auth()->user();

        // Step 4: If everything is fine, return the file for download
        $response = response()->file(storage_path('app/private/images/' . $filePath));
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Keep-Alive', 'timeout=40, max=200');
        return $response;
    }
}

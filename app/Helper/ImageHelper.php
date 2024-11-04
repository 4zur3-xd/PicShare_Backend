<?php

namespace App\Helper;

use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Generate a public URL for the given image path.
     *
     * @param string $imagePath Relative path to the image (e.g., 'public/images/your-image.jpg')
     * @return string Public URL to access the image
     */
    public static function generateImageUrl(string $imagePath): string
    {
        return Storage::url($imagePath);
    }

    public static function saveAndGenerateUrl($imageFile, $folder = 'public/images'): ?string
    {
        if ($imageFile) {
            // Save the file to the specified folder and get the relative path
            $fileName = time() . '_' . $imageFile->getClientOriginalName();
            $path = $imageFile->storeAs($folder, $fileName); 
    
            // Generate the public URL from the stored path
            $publicPath = str_replace('public/', '', $path); // Remove 'public/' from the path
            return Storage::url($publicPath);
        }

        return null;
    }

    /**
     * Delete an image from storage.
     *
     * @param string $imagePath
     * @return bool
     */
    public static function deleteImage(string $imagePath): bool
    {
        return Storage::delete($imagePath);
    }

    // Add more image-related helper methods as needed
}

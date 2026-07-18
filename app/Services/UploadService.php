<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    /**
     * Upload images to the MinIO storage (S3 standard)
     * Returns a public URL so the frontend can display it.
     */
    public function uploadImage(UploadedFile $file, $folder = 'images')
    {
        // Save the file to drive S3 (MinIO) in public mode.
        $path = Storage::disk('s3')->put($folder, $file, 'public');
        
        if (!$path) {
            throw new \Exception("Tải ảnh lên MinIO thất bại! Vui lòng kiểm tra lại kết nối MinIO hoặc dung lượng ổ đĩa.");
        }

        // Get the full URL
        return Storage::disk('s3')->url($path);
    }

    public function deleteImage($url)
    {
        if (!$url) return;

        try {
            // Extract the original path from the full URL.
            $bucket = env('AWS_BUCKET', 'dolabakery');
            $path = parse_url($url, PHP_URL_PATH);
            
            // Remove the /bucket/ prefix to the correct string format (do not use ltrim because ltrim is a character mask).
            $prefix = "/$bucket/";
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            } else {
                $path = ltrim($path, '/');
            }

            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
        } catch (\Exception $e) {
            // Ignore any image deletion errors (e.g., old photos not available on MinIO) to avoid blocking product deletion.
            \Illuminate\Support\Facades\Log::warning("Không thể xóa ảnh trên MinIO: " . $e->getMessage());
        }
    }
}

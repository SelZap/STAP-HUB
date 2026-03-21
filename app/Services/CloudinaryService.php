<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $config = new Configuration();
        $config->cloud->cloudName  = config('cloudinary.cloud_name');
        $config->cloud->apiKey     = config('cloudinary.api_key');
        $config->cloud->apiSecret  = config('cloudinary.api_secret');
        $config->url->secure       = true;

        $this->cloudinary = new Cloudinary($config);
    }

    public function uploadImage(string $base64Image, string $folder = 'stap-hub/images'): ?string
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($base64Image, [
                'folder'        => $folder,
                'resource_type' => 'image',
            ]);

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Cloudinary image upload failed: ' . $e->getMessage());
            return null;
        }
    }

    public function uploadVideo(string $base64Video, string $folder = 'stap-hub/videos'): ?string
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($base64Video, [
                'folder'        => $folder,
                'resource_type' => 'video',
            ]);

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Cloudinary video upload failed: ' . $e->getMessage());
            return null;
        }
    }

    public function delete(string $publicId, string $resourceType = 'image'): bool
    {
        try {
            $this->cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => $resourceType,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Cloudinary delete failed: ' . $e->getMessage());
            return false;
        }
    }
}
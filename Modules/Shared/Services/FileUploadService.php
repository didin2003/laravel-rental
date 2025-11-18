<?php

namespace Modules\Shared\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadToStorage(UploadedFile $file, string $folder = 'uploads'): string
    {
        return $file->store("{$folder}", 'public');
    }

    public function uploadToPublic(UploadedFile $file, string $folder = 'uploads'): string
    {
        $publicPath = public_path("assets/images/{$folder}");
        if (! File::exists($publicPath)) {
            File::makeDirectory($publicPath, 0755, true, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($publicPath, $filename);

        return $filename;
    }

    public function uploadToSS(UploadedFile $file, string $folder = 'uploads'): string
    {
        // Create unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Full S3 path: uploads/yourfile.jpg
        $filePath = "{$folder}/{$filename}";

        // Upload file to S3
        Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

        // Return only filename like your old function
        return $filename;
    }

    /**
     * Upload multiple files to public directory
     *
     * @param  array<UploadedFile>  $files
     * @return array<string> Filenames
     */
    public function uploadMultipleToPublic(array $files, string $folder = 'uploads'): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $uploadedFiles[] = $this->uploadToPublic($file, $folder);
            }
        }

        return $uploadedFiles;
    }

    public function removeImageFromDirectory($image_path)   // Full path => 'assets/images/kjgdfggkldfg.png'
    {if (File::exists($image_path)) {
        File::delete($image_path);
    }
    }
}

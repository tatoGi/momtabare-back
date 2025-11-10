<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Upload and convert image to WebP format
     *
     * @param  int  $quality  Quality for WebP conversion (0-100)
     * @param  array  $sizes  Optional array of sizes ['thumbnail' => 150, 'medium' => 600]
     * @return string Path to the uploaded WebP image
     */
    public function uploadAsWebP(UploadedFile $file, string $directory, int $quality = 80, array $sizes = []): string
    {
        // Generate unique filename
        $filename = Str::random(40);
        $webpFilename = $filename.'.webp';

        // Ensure directory exists
        $fullPath = storage_path('app/public/'.$directory);
        if (! file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Read and convert image to WebP
        $image = Image::read($file);

        // Resize large images to prevent memory issues
        $width = $image->width();
        $height = $image->height();
        $maxDimension = 2000; // Max 2000px on longest side

        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width > $height) {
                $image->scale(width: $maxDimension);
            } else {
                $image->scale(height: $maxDimension);
            }
        }

        // Save main image
        $mainPath = $fullPath.'/'.$webpFilename;
        $image->toWebp($quality)->save($mainPath);

        // Free memory
        unset($image);

        // Generate additional sizes if requested
        if (! empty($sizes)) {
            foreach ($sizes as $sizeName => $width) {
                $sizeFilename = $filename.'_'.$sizeName.'.webp';
                $sizePath = $fullPath.'/'.$sizeFilename;

                $resizedImage = Image::read($file);
                $resizedImage->scale(width: $width)->toWebp($quality)->save($sizePath);
                unset($resizedImage);
            }
        }

        return $directory.'/'.$webpFilename;
    }

    /**
     * Upload and convert image to WebP with custom filename
     *
     * @return string Path to the uploaded WebP image
     */
    public function uploadAsWebPWithName(UploadedFile $file, string $directory, string $customName, int $quality = 80): string
    {
        // Generate WebP filename from custom name
        $webpFilename = pathinfo($customName, PATHINFO_FILENAME).'.webp';

        // Ensure directory exists
        $fullPath = storage_path('app/public/'.$directory);
        if (! file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Read and convert image to WebP
        $image = Image::read($file);

        // Save image
        $mainPath = $fullPath.'/'.$webpFilename;
        $image->toWebp($quality)->save($mainPath);

        return $directory.'/'.$webpFilename;
    }

    /**
     * Delete image and its variations
     *
     * @param  string  $path  Path to the image
     * @param  array  $variations  Optional array of variation suffixes ['_thumbnail', '_medium']
     */
    public function deleteImage(string $path, array $variations = []): bool
    {
        $deleted = Storage::disk('public')->delete($path);

        if (! empty($variations)) {
            $pathInfo = pathinfo($path);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];

            foreach ($variations as $variation) {
                $variationPath = $directory.'/'.$filename.$variation.'.'.$extension;
                Storage::disk('public')->delete($variationPath);
            }
        }

        return $deleted;
    }

    /**
     * Update image - delete old and upload new as WebP
     */
    public function updateImage(UploadedFile $newFile, ?string $oldPath, string $directory, int $quality = 80, array $sizes = []): string
    {
        // Delete old image if exists
        if ($oldPath) {
            $this->deleteImage($oldPath);
        }

        // Upload new image
        return $this->uploadAsWebP($newFile, $directory, $quality, $sizes);
    }

    /**
     * Convert existing image to WebP and replace original
     *
     * @param  string  $existingPath  Path to existing image in public storage
     * @param  bool  $deleteOriginal  Delete original file after conversion
     * @return string Path to the new WebP image
     */
    public function convertExistingToWebP(string $existingPath, int $quality = 80, bool $deleteOriginal = true): string
    {
        $fullPath = storage_path('app/public/'.$existingPath);

        if (! file_exists($fullPath)) {
            throw new \Exception("Image not found: {$existingPath}");
        }

        // Check file size and skip if too large for memory
        $fileSize = filesize($fullPath);
        $maxSize = 10 * 1024 * 1024; // 10MB

        if ($fileSize > $maxSize) {
            throw new \Exception("Image too large ({$fileSize} bytes). Skipping to prevent memory exhaustion.");
        }

        // Read existing image
        $image = Image::read($fullPath);

        // Resize large images to prevent memory issues
        $width = $image->width();
        $height = $image->height();
        $maxDimension = 2000; // Max 2000px on longest side

        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width > $height) {
                $image->scale(width: $maxDimension);
            } else {
                $image->scale(height: $maxDimension);
            }
        }

        // Generate WebP filename
        $pathInfo = pathinfo($existingPath);
        $webpFilename = $pathInfo['filename'].'.webp';
        $webpPath = $pathInfo['dirname'].'/'.$webpFilename;
        $webpFullPath = storage_path('app/public/'.$webpPath);

        // Save as WebP
        $image->toWebp($quality)->save($webpFullPath);

        // Free memory
        unset($image);
        gc_collect_cycles();

        // Delete original file if requested
        if ($deleteOriginal && $existingPath !== $webpPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $webpPath;
    }

    /**
     * Get optimized quality based on image size
     */
    public function getOptimalQuality(UploadedFile $file): int
    {
        $sizeInMB = $file->getSize() / 1024 / 1024;

        if ($sizeInMB > 5) {
            return 70;
        } elseif ($sizeInMB > 2) {
            return 75;
        }

        return 80;
    }
}

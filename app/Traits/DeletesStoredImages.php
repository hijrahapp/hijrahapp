<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait DeletesStoredImages
{
    /**
     * Override in the model to declare which attributes contain image URLs.
     *
     * @return array<int, string>
     */
    protected function imageUrlAttributes(): array
    {
        return [];
    }

    /**
     * Boot the trait and register model events.
     */
    public static function bootDeletesStoredImages(): void
    {
        static::deleting(function ($model) {
            if (!method_exists($model, 'imageUrlAttributes')) {
                return;
            }

            foreach ($model->imageUrlAttributes() as $attribute) {
                $url = $model->{$attribute} ?? null;
                if (!$url) {
                    continue;
                }

                self::deleteImageIfLocal($url);
            }
        });

        static::updating(function ($model) {
            if (!method_exists($model, 'imageUrlAttributes')) {
                return;
            }

            foreach ($model->imageUrlAttributes() as $attribute) {
                if (!$model->isDirty($attribute)) {
                    continue;
                }

                $original = $model->getOriginal($attribute);
                $new = $model->{$attribute};
                if ($original && $original !== $new) {
                    self::deleteImageIfLocal($original);
                }
            }
        });
    }

    /**
     * Delete an image from the public disk if the URL points to local storage.
     */
    protected static function deleteImageIfLocal(string $url): void
    {
        $publicPrefix = '/storage/';
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? $url;

        $pos = strpos($path, $publicPrefix);
        if ($pos === false) {
            return;
        }

        $relativePathOnDisk = ltrim(substr($path, $pos + strlen($publicPrefix)), '/');
        if (!$relativePathOnDisk) {
            return;
        }

        // Try deleting via public disk first
        if (!Storage::disk('public')->delete($relativePathOnDisk)) {
            // If public/storage is not symlinked to storage/app/public, also attempt direct unlink under public/storage
            $publicFile = public_path('storage/' . $relativePathOnDisk);
            if (file_exists($publicFile)) {
                @unlink($publicFile);
            }
        }
    }
}



<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileUrlValidation implements ValidationRule
{
    protected array $allowedTypes;

    protected array $allowedServices;

    public function __construct(array $allowedTypes = [], array $allowedServices = [])
    {
        $this->allowedTypes = $allowedTypes;
        $this->allowedServices = $allowedServices;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $originalUrl = trim($value);
        $url = strtolower($originalUrl);

        // First, validate that it's a proper URL structure
        if (! $this->isValidUrl($originalUrl)) {
            $fail('The URL format is invalid. Please provide a valid URL.');

            return;
        }

        // Check if URL has a valid file extension
        $hasValidExtension = $this->hasValidFileExtension($url);

        // Check if URL is from approved streaming services
        $isApprovedService = $this->isFromApprovedService($url);

        if (! $hasValidExtension && ! $isApprovedService) {
            $allowedExtensions = ! empty($this->allowedTypes) ? '.'.implode(', .', $this->allowedTypes) : '';
            $allowedServicesText = ! empty($this->allowedServices) ? implode(', ', $this->allowedServices) : '';

            $message = 'URL must either have a valid file extension';
            if ($allowedExtensions) {
                $message .= ' ('.$allowedExtensions.')';
            }
            if ($allowedServicesText) {
                $message .= ' or be from an approved service ('.$allowedServicesText.')';
            }

            $fail($message);
        }
    }

    /**
     * Validate if the given string is a proper URL
     */
    protected function isValidUrl(string $url): bool
    {
        // Use PHP's filter_var with FILTER_VALIDATE_URL
        $isValid = filter_var($url, FILTER_VALIDATE_URL) !== false;

        if (! $isValid) {
            return false;
        }

        // Additional checks for URL structure
        $parsedUrl = parse_url($url);

        // Must have scheme and host
        if (! isset($parsedUrl['scheme']) || ! isset($parsedUrl['host'])) {
            return false;
        }

        // Scheme should be http or https
        if (! in_array(strtolower($parsedUrl['scheme']), ['http', 'https'])) {
            return false;
        }

        // Host should be valid
        if (empty($parsedUrl['host']) || ! $this->isValidHost($parsedUrl['host'])) {
            return false;
        }

        return true;
    }

    /**
     * Validate if the host is valid
     */
    protected function isValidHost(string $host): bool
    {
        // Check if it's a valid domain or IP
        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false
            || filter_var($host, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if URL has valid file extension
     */
    protected function hasValidFileExtension(string $url): bool
    {
        if (empty($this->allowedTypes)) {
            return false;
        }

        // Parse URL to get the path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Extract file extension from path
        $pathInfo = pathinfo($path);
        $extension = strtolower($pathInfo['extension'] ?? '');

        return in_array($extension, $this->allowedTypes);
    }

    /**
     * Check if URL is from approved service
     */
    protected function isFromApprovedService(string $url): bool
    {
        if (empty($this->allowedServices)) {
            return false;
        }

        foreach ($this->allowedServices as $service) {
            if (str_contains($url, strtolower($service))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create validation rule for video files
     */
    public static function video(): self
    {
        return new self(
            ['mp4', 'mov', 'avi'],
            ['youtube.com', 'vimeo.com', 'dailymotion.com', 'peertube', 'drive.google.com']
        );
    }

    /**
     * Create validation rule for audio files
     */
    public static function audio(): self
    {
        return new self(
            ['mp3', 'wav', 'aac'],
            ['audiomack.com', 'mixcloud.com', 'podbean.com', 'drive.google.com']
        );
    }

    /**
     * Create validation rule for book files
     */
    public static function book(): self
    {
        return new self(
            ['pdf'],
            ['drive.google.com']
        );
    }
}

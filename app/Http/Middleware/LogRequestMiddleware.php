<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $requestContext = $this->buildRequestContext($request);

        Log::channel('requests')->info('Incoming request', $requestContext);

        $response = null;

        try {
            $response = $next($request);
            return $response;
        } catch (\Throwable $exception) {
            Log::channel('requests')->error('Request failed', array_merge($requestContext, [
                'exception' => get_class($exception),
                'exception_message' => $exception->getMessage(),
            ]));

            throw $exception;
        } finally {
            $durationMs = (microtime(true) - $startTime) * 1000;

            Log::channel('requests')->info('Request completed', array_merge($requestContext, [
                'response_status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'duration_ms' => round($durationMs, 2),
            ]));
        }
    }

    private function buildRequestContext(Request $request): array
    {
        $redactedHeaders = $this->redactHeaders($request->headers->all());
        $sanitizedBody = $this->sanitizeBody($request);

        return [
            'method' => $request->getMethod(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'path' => $request->getPathInfo(),
            'full_url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route_name' => optional($request->route())->getName(),
            'route_action' => optional($request->route())->getActionName(),
            'headers' => $redactedHeaders,
            'query' => $request->query(),
            'body' => $sanitizedBody,
            'authenticated_user_id' => optional($request->user())->id ?? null,
        ];
    }

    private function redactHeaders(array $headers): array
    {
        $sensitiveHeaderNames = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-xsrf-token',
            'x-csrf-token',
        ];

        $result = [];
        foreach ($headers as $name => $values) {
            $lower = strtolower($name);
            if (in_array($lower, $sensitiveHeaderNames, true)) {
                $result[$name] = ['[redacted]'];
            } else {
                $result[$name] = $values;
            }
        }

        return $result;
    }

    private function sanitizeBody(Request $request): array|string|null
    {
        // Only attempt to log body for common content types
        $contentType = strtolower((string) $request->headers->get('Content-Type'));

        $data = [];
        if (str_contains($contentType, 'application/json')) {
            $data = $request->json()->all() ?? [];
        } elseif (str_contains($contentType, 'application/x-www-form-urlencoded') || str_contains($contentType, 'multipart/form-data')) {
            $data = $request->request->all();
        } else {
            // For other content types (e.g., file downloads), skip body logging
            return null;
        }

        $sensitiveKeys = [
            'password',
            'current_password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'secret',
        ];

        $sanitized = $this->deepRedact($data, $sensitiveKeys);

        $json = json_encode($sanitized, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return '[unserializable body]';
        }

        // Cap very large bodies
        $maxLength = 5000; // characters
        return strlen($json) > $maxLength ? substr($json, 0, $maxLength) . '... [truncated]' : $sanitized;
    }

    private function deepRedact(mixed $value, array $sensitiveKeys): mixed
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                if (is_string($key) && in_array(strtolower($key), $sensitiveKeys, true)) {
                    $result[$key] = '[redacted]';
                } else {
                    $result[$key] = $this->deepRedact($item, $sensitiveKeys);
                }
            }
            return $result;
        }

        return $value;
    }
}



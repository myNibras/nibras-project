<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\UploadedFile;

class ParseMultipartFormDataForPut
{
    /**
     * Handle an incoming request.
     * Parse multipart/form-data for PUT requests since PHP doesn't do it automatically
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process PUT requests with multipart/form-data
        if ($request->method() === 'PUT' && str_contains($request->header('Content-Type', ''), 'multipart/form-data')) {
            $this->parseMultipartData($request);
        }

        return $next($request);
    }

    /**
     * Parse multipart/form-data and populate request
     */
    private function parseMultipartData(Request $request): void
    {
        $content = $request->getContent();
        $contentType = $request->header('Content-Type', '');
        
        // Extract boundary
        if (!preg_match('/boundary=([^;]+)/i', $contentType, $matches)) {
            return;
        }
        
        $boundary = trim($matches[1], '"\' ');
        if (empty($boundary) || empty($content)) {
            return;
        }

        // Split by boundary
        $parts = preg_split('/--' . preg_quote($boundary, '/') . '(?:--)?\r?\n?/', $content);
        $data = [];
        $files = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part) || $part === '--') {
                continue;
            }

            // Split headers and body
            $headerEnd = strpos($part, "\r\n\r\n");
            if ($headerEnd === false) {
                $headerEnd = strpos($part, "\n\n");
            }
            
            if ($headerEnd === false) {
                continue;
            }

            $headers = substr($part, 0, $headerEnd);
            $body = substr($part, $headerEnd + 4);
            $body = rtrim($body, "\r\n");

            // Parse Content-Disposition header
            if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"(?:;\s*filename="([^"]+)")?/i', $headers, $matches)) {
                $fieldName = $matches[1];
                $filename = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : null;

                if ($filename) {
                    // It's a file - create temporary file
                    $tempPath = tempnam(sys_get_temp_dir(), 'laravel_upload_');
                    file_put_contents($tempPath, $body);
                    
                    // Get Content-Type from headers if available
                    $mimeType = 'application/octet-stream';
                    if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $mimeMatches)) {
                        $mimeType = trim($mimeMatches[1]);
                    } else {
                        $mimeType = mime_content_type($tempPath) ?: 'application/octet-stream';
                    }
                    
                    $files[$fieldName] = new UploadedFile(
                        $tempPath,
                        $filename,
                        $mimeType,
                        null,
                        true
                    );
                } else {
                    // It's a regular field
                    $data[$fieldName] = $body;
                }
            }
        }

        // Merge data and files into request
        if (!empty($data)) {
            $request->merge($data);
        }
        foreach ($files as $key => $file) {
            $request->files->set($key, $file);
        }
    }
}

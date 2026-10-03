<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponseMiddleware
{
    /**
     * Handle an incoming request and optimize response size & caching headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Do not compress binary files or streamed responses
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        // Add standard performance & SEO headers
        $response->headers->set('Vary', 'Accept-Encoding, User-Agent');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Set caching headers for static content or cached views
        if ($request->is('css/*', 'js/*', 'images/*', 'fonts/*', 'build/*', 'favicon.ico')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            return $response;
        }

        // Apply Gzip compression if supported by the client browser
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (function_exists('gzencode') && str_contains($acceptEncoding, 'gzip') && !headers_sent()) {
            $content = $response->getContent();
            
            // Only compress responses larger than 1KB (1024 bytes) to avoid compression overhead on tiny payloads
            if ($content && strlen($content) > 1024 && !$response->headers->has('Content-Encoding')) {
                $compressed = gzencode($content, 6);
                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', (string) strlen($compressed));
                }
            }
        }

        return $response;
    }
}

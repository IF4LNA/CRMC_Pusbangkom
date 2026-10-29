<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking. Dokumen privat (preview) perlu di-embed
        // di iframe pada halaman yang sama, jadi untuk endpoint berkas
        // pakai SAMEORIGIN; halaman lain tetap DENY.
        if (preg_match('#^crmc/lampiran/.+/berkas#', $request->path())) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        } else {
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Remove server signature if present
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // HSTS only if using HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy - compatible with current setup
        // (Tailwind CDN, Lucide, Leaflet/OpenStreetMap, document preview iframe)
        if (!$response->headers->has('Content-Security-Policy')) {
            $isBerkas = (bool) preg_match('#^crmc/lampiran/.+/berkas#', $request->path());

            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://unpkg.com https://cdn.jsdelivr.net https://cdn.sheetjs.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: blob: https:",
                "connect-src 'self' https://*.tile.openstreetmap.org https://*.openstreetmap.org https://cdn.jsdelivr.net https://cdn.sheetjs.com",
                "worker-src 'self' blob:",
                "frame-src 'self' https://www.google.com https://maps.google.com https://maps.googleapis.com",
                "frame-ancestors " . ($isBerkas ? "'self'" : "'none'"),
                "object-src 'self'",
                "media-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
            ];
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
}

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

        // Content Security Policy.
        // Aset aplikasi (Tailwind, Lucide, font) disajikan sendiri lewat
        // build Vite. Domain eksternal yang masih diizinkan hanya untuk
        // fitur opsional: peta Leaflet (unpkg), pratinjau dokumen
        // (jsdelivr/SheetJS), dan widget Cloudflare Turnstile.
        // Turnstile wajib diizinkan di script-src, frame-src, style-src,
        // dan connect-src dari https://challenges.cloudflare.com — tanpa
        // itu widget tidak mau render dan pengguna tidak bisa login.
        if (! $response->headers->has('Content-Security-Policy')) {
            $isBerkas = (bool) preg_match('#^crmc/lampiran/.+/berkas#', $request->path());

            // Server pengembangan Vite memuat skrip/gaya dari origin-nya
            // sendiri dan memakai WebSocket untuk hot reload.
            $viteDev = app()->environment('local')
                ? ' http://localhost:* http://127.0.0.1:* http://[::1]:* ws://localhost:* ws://127.0.0.1:* ws://[::1]:*'
                : '';

            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net https://cdn.sheetjs.com https://challenges.cloudflare.com".$viteDev,
                "style-src 'self' 'unsafe-inline' https://unpkg.com https://challenges.cloudflare.com".$viteDev,
                "font-src 'self' data:".$viteDev,
                "img-src 'self' data: blob: https:",
                "connect-src 'self' https://*.tile.openstreetmap.org https://*.openstreetmap.org https://cdn.jsdelivr.net https://cdn.sheetjs.com https://challenges.cloudflare.com".$viteDev,
                "worker-src 'self' blob:",
                "manifest-src 'self'",
                "frame-src 'self' https://www.google.com https://maps.google.com https://maps.googleapis.com https://challenges.cloudflare.com",
                'frame-ancestors '.($isBerkas ? "'self'" : "'none'"),
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

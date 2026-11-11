<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Uji header keamanan, terutama deteksi HTTPS di balik reverse proxy.
 *
 * Di produksi TLS biasanya diakhiri Nginx/load balancer, sehingga Laravel
 * hanya tahu request memakai HTTPS dari header X-Forwarded-Proto. Header
 * itu baru dipercaya bila alamat proxy terdaftar di TRUSTED_PROXIES.
 */
class SecurityHeadersTest extends TestCase
{
    public function test_request_https_dari_proxy_tepercaya_mendapat_hsts(): void
    {
        config(['app.trusted_proxies' => '*']);

        $this->withHeader('X-Forwarded-Proto', 'https')
            ->get('/login')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_header_forwarded_diabaikan_bila_tidak_ada_proxy_tepercaya(): void
    {
        config(['app.trusted_proxies' => null]);

        $this->withHeader('X-Forwarded-Proto', 'https')
            ->get('/login')
            ->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_halaman_biasa_tidak_bisa_di_frame(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as BaseTrustProxies;

/**
 * Menentukan proxy tepercaya dari konfigurasi aplikasi.
 *
 * Middleware bawaan Laravel membaca daftar proxy dari properti kelas yang
 * diisi lewat bootstrap. Nilai dari `.env` tidak tersedia lagi saat
 * konfigurasi di-cache, jadi daftar proxy disimpan di `config/app.php`
 * (yang ikut di-cache) dan dibaca di sini pada setiap request.
 */
class TrustProxies extends BaseTrustProxies
{
    /**
     * @return array<int, string>|string|null
     */
    protected function proxies(): array|string|null
    {
        $proxies = config('app.trusted_proxies');

        if (! is_string($proxies)) {
            return $proxies;
        }

        $proxies = trim($proxies);

        if ($proxies === '') {
            return null;
        }

        if ($proxies === '*') {
            return '*';
        }

        return array_values(array_filter(array_map('trim', explode(',', $proxies))));
    }
}

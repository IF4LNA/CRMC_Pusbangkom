<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Verifikasi tantangan Cloudflare Turnstile pada formulir login.
 *
 * Widget Turnstile di halaman login menghasilkan token sekali pakai
 * (field "cf-turnstile-response"). Token itu belum bisa dipercaya sebelum
 * diverifikasi sisi server ke endpoint siteverify Cloudflare dengan
 * secret key — siapa pun bisa memalsukan field form, tetapi tidak bisa
 * memalsukan jawaban endpoint resminya.
 *
 * Verifikasi gagal tertutup (fail closed): bila Cloudflare tidak bisa
 * dihubungi atau menjawab tidak sah, login ditolak. Menembus captcha
 * tidak boleh semudah "menunggu server verifikasi error".
 *
 * Pemanggilan rule hanya dilakukan bila secret key terpasang, sehingga
 * lingkungan tanpa kunci (mis. pengembangan lokal) tetap bisa login.
 */
class TurnstileCaptcha implements ValidationRule
{
    /** Endpoint verifikasi sisi server milik Cloudflare. */
    private const ENDPOINT_VERIFIKASI = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $token = is_string($value) ? trim($value) : '';

        if ($token === '') {
            $fail('Verifikasi keamanan belum selesai. Silakan selesaikan verifikasi lalu coba lagi.');

            return;
        }

        try {
            $respon = Http::asForm()
                ->connectTimeout(3)
                ->timeout(8)
                ->post(self::ENDPOINT_VERIFIKASI, [
                    'secret' => (string) config('services.turnstile.secret'),
                    'response' => $token,
                    'remoteip' => request()?->ip(),
                ]);
        } catch (ConnectionException $e) {
            // Koneksi ke Cloudflare gagal: laporkan untuk diperiksa admin,
            // tetapi tetap tolak login supaya verifikasi tidak bisa dilewati.
            report($e);

            $fail('Verifikasi keamanan tidak dapat diproses. Silakan coba lagi beberapa saat.');

            return;
        } catch (Throwable $e) {
            report($e);

            $fail('Verifikasi keamanan tidak dapat diproses. Silakan coba lagi beberapa saat.');

            return;
        }

        if (! $respon->successful() || ! $respon->json('success')) {
            $fail('Verifikasi keamanan tidak lolos. Silakan muat ulang halaman dan coba lagi.');
        }
    }
}

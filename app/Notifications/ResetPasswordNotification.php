<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Buat notifikasi atur ulang kata sandi.
     */
    public function __construct(public string $token)
    {
        //
    }

    /**
     * Kanal pengiriman notifikasi.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Susun isi email atur ulang kata sandi.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $menit = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi CRMC PUSBANGKOM')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun CRMC Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line("Tautan ini hanya berlaku selama {$menit} menit.")
            ->line('Jika Anda tidak merasa meminta perubahan kata sandi, abaikan email ini. Kata sandi Anda tidak akan berubah.');
    }

    /**
     * Representasi notifikasi dalam bentuk array.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public int $expiryMinutes;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, int $expiryMinutes = 4)
    {
        $this->otp = $otp;
        $this->expiryMinutes = $expiryMinutes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address') ?: 'no-reply@donganh.gov.vn';
        $fromName = config('mail.from.name') ?: 'DongAnh Discovery';

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
            ],
            subject: 'Mã xác thực OTP thay đổi mật khẩu - DongAnh Discovery',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-otp',
        );
    }
}

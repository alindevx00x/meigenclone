<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class WeeklyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public $prompts,
        public string $subscriberEmail
    ) {
    }

    public function envelope(): Envelope
    {
        $siteName = config('app.name', 'PromptImage');
        return new Envelope(
            subject: "🎨 Weekly Top 10 AI Art Prompts — {$siteName}",
        );
    }

    public function content(): Content
    {
        $unsubscribeUrl = URL::signedRoute('newsletter.unsubscribe', ['email' => $this->subscriberEmail]);

        return new Content(
            view: 'emails.weekly-digest',
            with: [
                'prompts' => $this->prompts,
                'subscriberEmail' => $this->subscriberEmail,
                'unsubscribeUrl' => $unsubscribeUrl,
                'siteName' => config('app.name', 'PromptImage'),
                'siteTagline' => 'Discover the best community AI art prompts & references',
            ],
        );
    }
}

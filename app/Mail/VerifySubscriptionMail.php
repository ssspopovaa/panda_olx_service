<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class VerifySubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Subscription $subscription
    ) {
    }

    public function build()
    {
        $url = config('app.url') . '/verify?token=' . $this->subscription->verification_token;
        return $this->subject('Please verify your subscription')
            ->view('emails.verify')
            ->with(['verifyUrl' => $url, 'email' => $this->subscription->email]);
    }
}

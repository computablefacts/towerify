<?php

namespace App\Modules\AdversaryMeter\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HoneypotRequested extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $emailSubject;
    private $emailBody;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $subject, array $body)
    {
        $this->user = $user;
        $this->emailSubject = $subject;
        $this->emailBody = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from($this->user->email, $this->user->name)
            ->subject("AdversaryMeter - {$this->emailSubject}")
            ->markdown('email.honeypot-requested', [
                'params' => $this->emailBody
            ]);
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SwitchBusinessMailer extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @param array $details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('🔐 Email Verification OTP - Switch Business')
                    ->from(env('MAIL_FROM_ADDRESS'), 'FixHR')
                    ->view('emails.switch-business-verify-mail');  // Your template name
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCustomMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    public $filePath;

    public function __construct($details, $filePath = null)
    {
        $this->details = $details;
        $this->filePath = $filePath;
    }

    public function build()
    {
        $email = $this->subject($this->details['subject'] ?? 'Notification from Company')
                      ->view('emails.custom')
                      ->with('details', $this->details);

        if ($this->filePath && file_exists($this->filePath)) {
            $email->attach($this->filePath, [
                'as' => $this->details['attachment_name'] ?? 'SalarySlip.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}

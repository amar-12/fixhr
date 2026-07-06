<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {

        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data['subject'],
        );
    }

    public function build()
    {
        $bladeContent = 'approval-mail';
        if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'CLAIM_APPROVAL_REQUEST'){
            $bladeContent = 'claim-request-approval-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'TOUR_APPROVAL_REQUEST'){
            $bladeContent = 'travel-request-approval-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'MISPUNCH_APPROVAL_REQUEST'){
            $bladeContent = 'mispunch-request-approval-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'GATEPASS_APPROVAL_REQUEST'){
            $bladeContent = 'gatepass-request-approval-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'TOUR_APPROVAL_REQUEST_REJECT'){
            $bladeContent = 'travel-request-reject-requester-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'TOUR_APPROVAL_REJECT'){
            $bladeContent = 'travel-request-reject-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'MISPUNCH_APPROVAL_REJECT'){
            $bladeContent = 'mispunch-request-reject-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'GATEPASS_APPROVAL_REJECT'){
            $bladeContent = 'gatepass-request-reject-mail';
        }elseif(isset($this->data['mail_type']) && $this->data['mail_type'] == 'CLAIM_REQUEST_REQUESTER_REJECT'){
            $bladeContent = 'claim-request-reject-requester';
        }elseif(isset($this->data['mail_type']) && $this->data['mail_type'] == 'CLAIM_REQUEST_REJECT'){
            $bladeContent = 'claim-request-reject';
        } elseif(isset($this->data['mail_type']) && $this->data['mail_type'] == 'CLAIM_DEDUCTION_ACCEPTED'){
            $bladeContent = 'claim-deduction-acceptance-mail';
        } elseif(isset($this->data['mail_type']) && $this->data['mail_type'] == 'CLAIM_DEDUCTION_DECLINED'){
            $bladeContent = 'claim-deduction-decline-mail';
        } elseif(isset($this->data['mail_type']) && $this->data['mail_type'] == 'CLAIM_APPROVED_WITH_DEDUCTION'){
            $bladeContent = 'claim-approved-with-deduction-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'MISPUNCH_APPROVAL_REQUEST_REJECT'){
            $bladeContent = 'mispunch-approval-request-reject-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'GATEPASS_APPROVAL_REQUEST_REJECT'){
            $bladeContent = 'gatepass-approval-request-reject-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'LEAVE_APPROVAL_REQUEST_REJECT'){
            $bladeContent = 'leave-request-reject-requester-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'LEAVE_APPROVAL_REJECT'){
            $bladeContent = 'leave-request-reject-mail';
        }else if(isset($this->data['mail_type']) && $this->data['mail_type'] == 'LEAVE_APPROVAL_REQUEST'){
                $bladeContent = 'leave-request-approval-mail';
        }

        return $this->subject($this->data['subject'].' - '.env('MAIL_FROM_NAME'))
            ->view('emails.'.$bladeContent);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

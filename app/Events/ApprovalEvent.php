<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ApprovalEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request_id;
    // public $current_approver_id;
    // public $next_approver_id;
    // public function __construct($request_id, $current_approver_id, $next_approver_id)
    public function __construct($request_id)
    {
        $this->request_id = $request_id;
        // $this->current_approver_id = $current_approver_id;
        // $this->next_approver_id = $next_approver_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('claim-approval.' . $this->request_id),
        ];
    }

    // public function broadcastWith()
    // {
    //     return [
    //         'request_id' => $this->request_id,
    //         'current_approver_id' => $this->current_approver_id,
    //         'next_approver_id' => $this->next_approver_id,
    //     ];
    // }

    // public function broadcastWhen(): bool
    // {
    //     return $this->request_id == 218;
    // }

    public function broadcastWhen(): bool
    {
        return true;
        // return Auth::user()->emp_id === $this->current_approver_id || Auth::user()->emp_id === $this->next_approver_id;
    }
}

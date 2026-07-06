<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class TravelApprovalEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $travel_request_id;

    public function __construct($travel_request_id)
    {
        $this->travel_request_id = $travel_request_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('travel-approval.' . $this->travel_request_id),
        ];
    }

    public function broadcastWhen(): bool
    {
        return true; // Customize as needed
    }
}

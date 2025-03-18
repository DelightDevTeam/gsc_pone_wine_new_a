<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositNotifyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */


    public $deposit;

    /**
     * Create a new event instance.
     */
    public function __construct($deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('agent.' . $this->deposit->agent_id);
    }



    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'deposit.notify';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'player_name' => $this->deposit->user->user_name,
            'amount' => $this->deposit->amount,
            'refrence_no' => $this->deposit->refrence_no,
            'message' => "Player {$this->deposit->user->user_name} has deposited {$this->deposit->amount}.",
        ];
    }
}
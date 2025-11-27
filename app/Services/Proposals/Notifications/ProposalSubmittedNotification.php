<?php

namespace App\Services\Proposals\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProposalSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public Proposal $proposal) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'proposal_submitted',
            'proposal_id' => $this->proposal->id,
            'title' => $this->proposal->title,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}

<?php

namespace App\Services\Proposals\Notifications;

use App\Models\Proposal;
use App\Models\ProposalReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProposalReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Proposal       $proposal,
        public ProposalReview $review
    )
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'proposal_reviewed',
            'proposal_id' => $this->proposal->id,
            'title' => $this->proposal->title,

            'review_id' => $this->review->id,
            'reviewer_id' => $this->review->reviewer_id,
            'rating' => (int)$this->review->rating,
            'comment' => $this->review->comment,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}

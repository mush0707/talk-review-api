<?php

namespace App\Services\Proposals;

use App\Models\Proposal;
use App\Models\ProposalReview;
use App\Models\User;
use App\Services\Proposals\Notifications\ProposalReviewedNotification;
use App\Services\Proposals\Notifications\ProposalStatusChangedNotification;
use App\Services\Proposals\Notifications\ProposalSubmittedNotification;
use App\Services\Users\UserRepository;

class ProposalNotificationService
{
    public function __construct(
        private UserRepository $userRepository
    )
    {
    }

    public function proposalSubmitted(Proposal $proposal): void
    {
        $reviewersAndAdmins = $this->userRepository->reviewersAndAdmins();
        foreach ($reviewersAndAdmins as $u) {
            $u->notify(new ProposalSubmittedNotification($proposal));
        }
    }

    public function proposalReviewed(Proposal $proposal, ProposalReview $review): void
    {
        $proposal->loadMissing('speaker');

        $proposal->speaker?->notify(new ProposalReviewedNotification($proposal, $review));
        $admins = $this->userRepository->admins();
        foreach ($admins as $admin) {
            $admin->notify(new ProposalReviewedNotification($proposal, $review));
        }
    }

    public function statusChanged(Proposal $proposal): void
    {
        $proposal->loadMissing(['speaker', 'reviews']);

        $notification = new ProposalStatusChangedNotification($proposal);

        // owner
        $proposal->speaker?->notify($notification);

        // reviewers who reviewed
        $reviewerIds = $proposal->reviews->pluck('reviewer_id')->unique()->values()->all();
        if (!empty($reviewerIds)) {
            $users = $this->userRepository->getByIds($reviewerIds);
            foreach ($users as $u) {
                $u->notify($notification);
            }
        }
    }
}

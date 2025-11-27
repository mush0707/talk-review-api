<?php

namespace App\Services\Proposals\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('proposals.read.any') || $user->can('proposals.read.own');
    }

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->can('proposals.read.any')) return true;

        return $user->can('proposals.read.own') && (int)$proposal->speaker_id === (int)$user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('proposals.create');
    }

    public function changeStatus(User $user, Proposal $proposal): bool
    {
        return $user->can('proposals.status.change');
    }

    public function viewReviews(User $user, Proposal $proposal): bool
    {
        if ($user->can('reviews.read.any')) return true;

        return $user->can('reviews.read.own_proposal') && (int)$proposal->speaker_id === (int)$user->id;
    }

    public function upsertReview(User $user, Proposal $proposal): bool
    {
        return $user->can('reviews.upsert');
    }
}

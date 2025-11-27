<?php

namespace App\Services\Proposals;

use App\Models\Proposal;
use App\Models\ProposalReview;
use App\Models\User;
use App\Services\Proposals\Data\ReviewSearchData;
use App\Services\Proposals\Data\ReviewUpsertData;
use Elastic\ScoutDriverPlus\Support\Query;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProposalReviewService
{
    public function __construct(
        private ProposalNotificationService $notify
    ) {}

    public function upsert(User $reviewer, Proposal $proposal, ReviewUpsertData $data): ProposalReview
    {
        if (!$reviewer->hasRole('reviewer')) {
            abort(403);
        }

        $review = ProposalReview::query()->updateOrCreate(
            [
                'proposal_id' => $proposal->id,
                'reviewer_id' => $reviewer->id,
            ],
            [
                'rating' => $data->rating,
                'comment' => $data->comment,
            ]
        );

        // Index review in ES (separate index)
        $review->searchable();

        // Notify owner + admins
        $this->notify->proposalReviewed($proposal, $review);

        return $review;
    }

    public function searchForProposal(Proposal $proposal, ReviewSearchData $data): LengthAwarePaginator
    {
        $bool = Query::bool();

        // hard scope to proposal
        $bool->filter(Query::term()->field('proposal_id')->value($proposal->id));

        if ($data->search && trim($data->search) !== '') {
            $bool->must(
                Query::match()
                    ->field('comment')
                    ->query($data->search)
            );
        } else {
            $bool->must(Query::matchAll());
        }

        if ($data->rating_min !== null || $data->rating_max !== null) {
            $range = Query::range()->field('rating');
            if ($data->rating_min !== null) $range->gte($data->rating_min);
            if ($data->rating_max !== null) $range->lte($data->rating_max);
            $bool->filter($range);
        }

        return ProposalReview::searchQuery($bool)
            ->sort('created_at', 'desc')
            ->paginate($data->per_page, 'page', $data->page);
    }
}

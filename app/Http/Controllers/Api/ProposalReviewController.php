<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use App\Models\Proposal;
use App\Services\Proposals\Data\ReviewSearchData;
use App\Services\Proposals\Data\ReviewUpsertData;
use App\Services\Proposals\ProposalReviewService;
use Illuminate\Http\JsonResponse;

class ProposalReviewController extends BaseApiController
{
    public function __construct(
        private ProposalReviewService $service
    )
    {
    }

    public function index(Proposal $proposal): JsonResponse
    {
        $user = request()->user();

        // speaker can read only reviews of own proposal
        if ($user->hasRole('speaker') && $proposal->speaker_id !== $user->id) {
            abort(403);
        }

        $data = ReviewSearchData::from(request());
        return $this->paginated($this->service->searchForProposal($proposal, $data));
    }

    public function upsert(Proposal $proposal, ProposalReviewService $service): JsonResponse
    {
        $data = ReviewUpsertData::from(request());
        $review = $service->upsert(request()->user(), $proposal, $data);

        return $this->created($review);
    }
}

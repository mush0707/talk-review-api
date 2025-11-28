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
        $data = ReviewSearchData::from(request());
        return $this->success($this->service->search($proposal, $data));
    }

    public function upsert(Proposal $proposal): JsonResponse
    {
        $data = ReviewUpsertData::from(request());
        $review = $this->service->upsert(request()->user(), $proposal, $data);

        return $this->created($review);
    }
}

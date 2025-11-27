<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use App\Models\Proposal;
use App\Services\Proposals\Data\ProposalCreateData;
use App\Services\Proposals\Data\ProposalSearchData;
use App\Services\Proposals\Data\ProposalStatusChangeData;
use App\Services\Proposals\ProposalService;
use Illuminate\Http\JsonResponse;

class ProposalController extends BaseApiController
{
    public function __construct(
        private ProposalService $service,
    )
    {
    }

    public function index(): JsonResponse
    {
        $data = ProposalSearchData::from(request());
        return $this->paginated($this->service->search(request()->user(), $data));
    }

    public function store(): JsonResponse
    {
        $data = ProposalCreateData::from(request());
        $proposal = $this->service->create(request()->user(), $data);
        return $this->created($proposal);
    }

    public function show(Proposal $proposal): JsonResponse
    {
        $proposal->load('tags')->loadCount('reviews');
        return $this->success($proposal);
    }

    public function changeStatus(Proposal $proposal): JsonResponse
    {
        $data = ProposalStatusChangeData::from(request());
        $this->service->changeStatus(request()->user(), $proposal, $data);
        return $this->noContent();
    }
}

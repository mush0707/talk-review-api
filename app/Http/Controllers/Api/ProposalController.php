<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use App\Models\Proposal;
use App\Services\Files\FileStorageService;
use App\Services\Proposals\Data\ProposalCreateData;
use App\Services\Proposals\Data\ProposalSearchData;
use App\Services\Proposals\Data\ProposalStatusChangeData;
use App\Services\Proposals\ProposalService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ProposalController extends BaseApiController
{
    public function __construct(
        private ProposalService $service,
        private FileStorageService $fileStorageService
    )
    {
    }

    public function index(): JsonResponse
    {
        $data = ProposalSearchData::from(request());
        return $this->success($this->service->search(request()->user(), $data));
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

        $attachment = null;

        if ($proposal->attachment_path) {
            $attachment = $this->fileStorageService->temporarySignedDownloadUrl(
                'proposals.attachment.download',
                ['proposal' => $proposal->id],
                now()->addMinutes(10),
            );
        }

        return $this->success([
            'proposal' => $proposal->makeHidden(['attachment_path']),
            'attachment' => $attachment,
        ]);
    }

    public function downloadAttachment(Proposal $proposal): StreamedResponse
    {
        if (!$proposal->attachment_path) {
            abort(404, 'Attachment not found');
        }

        // optional: friendly filename
        $name = 'proposal-' . $proposal->id . '.pdf';

        return $this->downloadFromDisk('local', $proposal->attachment_path, $name);
    }

    public function changeStatus(Proposal $proposal): JsonResponse
    {
        $data = ProposalStatusChangeData::from(request());
        $this->service->changeStatus(request()->user(), $proposal, $data);
        return $this->noContent();
    }
}

<?php

namespace App\Services\Proposals;

use App\Models\Proposal;
use App\Models\User;
use App\Services\Files\FileStorageService;
use App\Services\Proposals\Data\ProposalCreateData;
use App\Services\Proposals\Data\ProposalSearchData;
use App\Services\Proposals\Data\ProposalStatusChangeData;
use App\Services\Proposals\Enums\ProposalStatus;
use App\Services\Tags\TagLibraryService;
use Elastic\ScoutDriverPlus\Support\Query;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProposalService
{
    public function __construct(
        private TagLibraryService $tagService,
        private FileStorageService $fileStorageService,
        private ProposalNotificationService $notify
    ) {}

    public function create(User $speaker, ProposalCreateData $data): Proposal
    {
        return DB::transaction(function () use ($speaker, $data) {
            $attachmentPath = null;

            if ($data->file) {
                $attachmentPath = $this->fileStorageService->store($data->file, 'proposals');
            }

            $proposal = Proposal::query()->create([
                'speaker_id' => $speaker->id,
                'title' => $data->title,
                'description' => $data->description,
                'attachment_path' => $attachmentPath,
                'status' => ProposalStatus::Pending,
            ]);

            $tagIds = $data->tag_ids ?? [];

            if (!empty($data->tag_names)) {
                $createdTags = $this->tagService->firstOrCreateByNames($data->tag_names);
                $createdIds = collect($createdTags)->pluck('id')->all();
                $tagIds = array_values(array_unique(array_merge($tagIds, $createdIds)));
            }

            if (!empty($tagIds)) {
                $proposal->tags()->sync($tagIds);
            }

            $proposal->load(['tags', 'speaker']);

            // index in ES
            $proposal->searchable();

            // notify all reviewers + admins
            $this->notify->proposalSubmitted($proposal);

            return $proposal;
        });
    }

    public function search(User $user, ProposalSearchData $data): LengthAwarePaginator
    {
        // speaker must see only own proposals
        $scopeToSpeaker = $user->hasRole('speaker') && !$user->hasAnyRole(['reviewer', 'admin']);

        $bool = Query::bool();

        if ($data->search && trim($data->search) !== '') {
            $bool->must(
                Query::multiMatch()
                    ->query($data->search)
                    ->fields([
                        'title^3',
                        'description',
                        'tag_names',
                    ])
                    ->fuzziness('AUTO')
            );
        } else {
            $bool->must(Query::matchAll());
        }

        if (!empty($data->tag_ids)) {
            $bool->filter(Query::terms()->field('tag_ids')->values($data->tag_ids));
        }

        if ($data->status) {
            $bool->filter(Query::term()->field('status')->value($data->status));
        }

        if ($scopeToSpeaker) {
            $bool->filter(Query::term()->field('speaker_id')->value($user->id));
        }

        // paginate via Scout
        return Proposal::searchQuery($bool)
            ->sort('created_at', 'desc')
            ->paginate($data->per_page, 'page', $data->page);
    }

    public function changeStatus(User $admin, Proposal $proposal, ProposalStatusChangeData $data): Proposal
    {
        if (!$admin->hasRole('admin')) {
            abort(403);
        }

        $proposal->status = ProposalStatus::from($data->status);
        $proposal->save();

        $proposal->load(['tags', 'speaker', 'reviews']);

        // refresh ES index
        $proposal->searchable();

        // notify owner + reviewers who reviewed this proposal
        $this->notify->statusChanged($proposal);

        return $proposal;
    }
}

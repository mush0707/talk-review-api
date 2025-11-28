<?php

namespace App\Models;

use App\Services\Proposals\Enums\ProposalStatus;
use Elastic\ScoutDriverPlus\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    use Searchable;

    protected $fillable = [
        'speaker_id',
        'title',
        'description',
        'attachment_path',
        'status',
    ];

    protected $casts = [
        'status' => ProposalStatus::class,
    ];

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'speaker_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'proposal_tags');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProposalReview::class);
    }

    public function searchableAs(): string
    {
        return 'proposals';
    }

    public function searchableWith(): array
    {
        return ['tags'];
    }

    public function toSearchableArray(): array
    {
        $tagIds = $this->tags->pluck('id')->values()->all();
        $tagNames = $this->tags->pluck('name')->values()->all();
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'speaker_id' => $this->speaker_id,
            'tag_ids' => $tagIds,
            'tag_names' => $tagNames,
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}

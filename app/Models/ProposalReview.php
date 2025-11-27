<?php

namespace App\Models;

use Elastic\ScoutDriverPlus\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalReview extends Model
{
    use Searchable;

    protected $fillable = ['proposal_id', 'reviewer_id', 'rating', 'comment'];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function searchableAs(): string
    {
        return 'proposal_reviews';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'proposal_id' => $this->proposal_id,
            'reviewer_id' => $this->reviewer_id,
            'rating' => (int) $this->rating,
            'comment' => (string) ($this->comment ?? ''),
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}

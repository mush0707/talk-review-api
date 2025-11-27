<?php

namespace App\Services\Proposals\Enums;

enum ProposalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

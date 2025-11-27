<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Collection;

class UserRepository
{
    public function getById(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    /**
     * @param int[] $ids
     * @return Collection
     */
    public function getByIds(array $ids): Collection
    {
        return User::query()->whereIn('id', $ids)->get();
    }

    public function reviewersAndAdmins()
    {
        return User::role(['reviewer', 'admin'])->get();
    }

    public function admins()
    {
        return User::role(['admin'])->get();
    }
}

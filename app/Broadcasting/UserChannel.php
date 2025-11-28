<?php

namespace App\Broadcasting;

class UserChannel
{
    public function join($user, $id): bool
    {
        return (int) $user->id === (int) $id;
    }
}

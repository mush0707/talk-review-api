<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    channel: 'App.Models.User.{id}',
    callback: function ($user, $id): bool {
        return (int) $user->id === (int) $id;
    }
);

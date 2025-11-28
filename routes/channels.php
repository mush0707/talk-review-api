<?php

use Illuminate\Support\Facades\Broadcast;
Broadcast::routes(['middleware' => ['auth:sanctum']]);
Broadcast::channel(
    channel: 'App.Models.User.{id}',
    callback: \App\Broadcasting\UserChannel::class
);

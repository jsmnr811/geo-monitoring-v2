<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('sync.{userId}', function ($user, $userId) {
    Log::info('Channel auth for sync.{userId}', ['user' => $user ? $user->id : null, 'userId' => $userId, 'authenticated' => $user && (int) $user->id === (int) $userId]);

    return (int) $user->id === (int) $userId;
});

<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);
Broadcast::channel('message.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

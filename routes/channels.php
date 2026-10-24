<?php

use App\Models\PortalUser;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('portal-user.{id}', function ($user, $id) {
    return $user instanceof PortalUser && (int) $user->id === (int) $id;
});

Broadcast::channel('role.{role}', function ($user, $role) {
    $userRole = $user instanceof PortalUser ? 'user' : 'admin';

    return $userRole === $role;
});

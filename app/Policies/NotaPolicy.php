<?php

namespace App\Policies;

use App\Models\Nota;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotaPolicy
{
    public function view(User $user, Nota $nota): bool
    {
        return $user->id === $nota->user_id;
    }

    public function update(User $user, Nota $nota): bool
    {
        return $user->id === $nota->user_id;
    }

    public function delete(User $user, Nota $nota): bool
    {
        return $user->id === $nota->user_id;
    }
}

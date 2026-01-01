<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperadmin() || $user->isAdmin();
    }

    public function view(User $user, Feedback $feedback): bool
    {
        return $user->isSuperadmin() || $user->isAdmin();
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->isSuperadmin();
    }
}
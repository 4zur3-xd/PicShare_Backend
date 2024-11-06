<?php

namespace App\Policies;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FriendPolicy
{
    public function modify(User $user, Friend $friend)
    {
        return $user->id === $friend->user_id || $user->id === $friend->friend_id ? Response::allow() : Response::deny(__('notAllowToModifyThisFriend'));
    }
}

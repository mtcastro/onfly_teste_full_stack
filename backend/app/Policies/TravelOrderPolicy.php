<?php

namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }
    public function view(User $user, TravelOrder $order): bool
    {
        return $user->id === $order->user_id || $user->role === 'admin';
    }
    public function create(User $user): bool
    {
        return true;
    }
    public function update(User $user, TravelOrder $order): bool
    {
        // edição de conteúdo só pelo dono, não cobre status
        return $user->id === $order->user_id || $user->role === 'admin';
    }
    public function updateStatus(User $user, TravelOrder $order): bool
    {
        // Somente admin pode alterar status
        return $user->role === 'admin';
    }

    public function delete(User $user, TravelOrder $order): bool
    {
        return $user->role === 'user' && $user->id === $order->user_id && $order->status === 'requested';
    }
}

<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PropertyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Property $property)
    {
        return $user->id === $property->user_id;
    }

    public function update(User $user, Property $property)
    {
        return $user->id === $property->user_id;
    }

    public function delete(User $user, Property $property)
    {
        return $user->id === $property->user_id;
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SubscriptionPrice;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPricePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubscriptionPrice');
    }

    public function view(AuthUser $authUser, SubscriptionPrice $subscriptionPrice): bool
    {
        return $authUser->can('View:SubscriptionPrice');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubscriptionPrice');
    }

    public function update(AuthUser $authUser, SubscriptionPrice $subscriptionPrice): bool
    {
        return $authUser->can('Update:SubscriptionPrice');
    }

    public function delete(AuthUser $authUser, SubscriptionPrice $subscriptionPrice): bool
    {
        return $authUser->can('Delete:SubscriptionPrice');
    }

    public function restore(AuthUser $authUser, SubscriptionPrice $subscriptionPrice): bool
    {
        return $authUser->can('Restore:SubscriptionPrice');
    }

    public function forceDelete(AuthUser $authUser, SubscriptionPrice $subscriptionPrice): bool
    {
        return $authUser->can('ForceDelete:SubscriptionPrice');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubscriptionPrice');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubscriptionPrice');
    }

    public function replicate(AuthUser $authUser, SubscriptionPrice $subscriptionPrice): bool
    {
        return $authUser->can('Replicate:SubscriptionPrice');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubscriptionPrice');
    }

}
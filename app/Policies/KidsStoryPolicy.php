<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KidsStory;
use Illuminate\Auth\Access\HandlesAuthorization;

class KidsStoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KidsStory');
    }

    public function view(AuthUser $authUser, KidsStory $kidsStory): bool
    {
        return $authUser->can('View:KidsStory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KidsStory');
    }

    public function update(AuthUser $authUser, KidsStory $kidsStory): bool
    {
        return $authUser->can('Update:KidsStory');
    }

    public function delete(AuthUser $authUser, KidsStory $kidsStory): bool
    {
        return $authUser->can('Delete:KidsStory');
    }

    public function restore(AuthUser $authUser, KidsStory $kidsStory): bool
    {
        return $authUser->can('Restore:KidsStory');
    }

    public function forceDelete(AuthUser $authUser, KidsStory $kidsStory): bool
    {
        return $authUser->can('ForceDelete:KidsStory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KidsStory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KidsStory');
    }

    public function replicate(AuthUser $authUser, KidsStory $kidsStory): bool
    {
        return $authUser->can('Replicate:KidsStory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KidsStory');
    }

}
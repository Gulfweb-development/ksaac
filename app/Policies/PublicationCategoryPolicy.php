<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PublicationCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PublicationCategory');
    }

    public function view(AuthUser $authUser, PublicationCategory $publicationCategory): bool
    {
        return $authUser->can('View:PublicationCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PublicationCategory');
    }

    public function update(AuthUser $authUser, PublicationCategory $publicationCategory): bool
    {
        return $authUser->can('Update:PublicationCategory');
    }

    public function delete(AuthUser $authUser, PublicationCategory $publicationCategory): bool
    {
        return $authUser->can('Delete:PublicationCategory');
    }

    public function restore(AuthUser $authUser, PublicationCategory $publicationCategory): bool
    {
        return $authUser->can('Restore:PublicationCategory');
    }

    public function forceDelete(AuthUser $authUser, PublicationCategory $publicationCategory): bool
    {
        return $authUser->can('ForceDelete:PublicationCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PublicationCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PublicationCategory');
    }

    public function replicate(AuthUser $authUser, PublicationCategory $publicationCategory): bool
    {
        return $authUser->can('Replicate:PublicationCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PublicationCategory');
    }

}
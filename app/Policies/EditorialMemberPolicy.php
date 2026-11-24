<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EditorialMember;
use Illuminate\Auth\Access\HandlesAuthorization;

class EditorialMemberPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EditorialMember');
    }

    public function view(AuthUser $authUser, EditorialMember $editorialMember): bool
    {
        return $authUser->can('View:EditorialMember');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EditorialMember');
    }

    public function update(AuthUser $authUser, EditorialMember $editorialMember): bool
    {
        return $authUser->can('Update:EditorialMember');
    }

    public function delete(AuthUser $authUser, EditorialMember $editorialMember): bool
    {
        return $authUser->can('Delete:EditorialMember');
    }

    public function restore(AuthUser $authUser, EditorialMember $editorialMember): bool
    {
        return $authUser->can('Restore:EditorialMember');
    }

    public function forceDelete(AuthUser $authUser, EditorialMember $editorialMember): bool
    {
        return $authUser->can('ForceDelete:EditorialMember');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EditorialMember');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EditorialMember');
    }

    public function replicate(AuthUser $authUser, EditorialMember $editorialMember): bool
    {
        return $authUser->can('Replicate:EditorialMember');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EditorialMember');
    }

}
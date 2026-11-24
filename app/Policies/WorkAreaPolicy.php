<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WorkArea;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkAreaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkArea');
    }

    public function view(AuthUser $authUser, WorkArea $workArea): bool
    {
        return $authUser->can('View:WorkArea');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkArea');
    }

    public function update(AuthUser $authUser, WorkArea $workArea): bool
    {
        return $authUser->can('Update:WorkArea');
    }

    public function delete(AuthUser $authUser, WorkArea $workArea): bool
    {
        return $authUser->can('Delete:WorkArea');
    }

    public function restore(AuthUser $authUser, WorkArea $workArea): bool
    {
        return $authUser->can('Restore:WorkArea');
    }

    public function forceDelete(AuthUser $authUser, WorkArea $workArea): bool
    {
        return $authUser->can('ForceDelete:WorkArea');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkArea');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkArea');
    }

    public function replicate(AuthUser $authUser, WorkArea $workArea): bool
    {
        return $authUser->can('Replicate:WorkArea');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkArea');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JournalNews;
use Illuminate\Auth\Access\HandlesAuthorization;

class JournalNewsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JournalNews');
    }

    public function view(AuthUser $authUser, JournalNews $journalNews): bool
    {
        return $authUser->can('View:JournalNews');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JournalNews');
    }

    public function update(AuthUser $authUser, JournalNews $journalNews): bool
    {
        return $authUser->can('Update:JournalNews');
    }

    public function delete(AuthUser $authUser, JournalNews $journalNews): bool
    {
        return $authUser->can('Delete:JournalNews');
    }

    public function restore(AuthUser $authUser, JournalNews $journalNews): bool
    {
        return $authUser->can('Restore:JournalNews');
    }

    public function forceDelete(AuthUser $authUser, JournalNews $journalNews): bool
    {
        return $authUser->can('ForceDelete:JournalNews');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JournalNews');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JournalNews');
    }

    public function replicate(AuthUser $authUser, JournalNews $journalNews): bool
    {
        return $authUser->can('Replicate:JournalNews');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JournalNews');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SubmissionReview;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionReviewPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubmissionReview');
    }

    // Reviewers reach only their own assignments; editors (who manage submissions) reach all.
    public function view(AuthUser $authUser, SubmissionReview $submissionReview): bool
    {
        return $authUser->can('View:SubmissionReview') && $this->ownsOrEdits($authUser, $submissionReview);
    }

    private function ownsOrEdits(AuthUser $authUser, SubmissionReview $review): bool
    {
        return $review->reviewer_id === $authUser->getKey() || $authUser->can('Update:Submission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubmissionReview');
    }

    public function update(AuthUser $authUser, SubmissionReview $submissionReview): bool
    {
        return $authUser->can('Update:SubmissionReview') && $this->ownsOrEdits($authUser, $submissionReview);
    }

    public function delete(AuthUser $authUser, SubmissionReview $submissionReview): bool
    {
        return $authUser->can('Delete:SubmissionReview');
    }

    public function restore(AuthUser $authUser, SubmissionReview $submissionReview): bool
    {
        return $authUser->can('Restore:SubmissionReview');
    }

    public function forceDelete(AuthUser $authUser, SubmissionReview $submissionReview): bool
    {
        return $authUser->can('ForceDelete:SubmissionReview');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubmissionReview');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubmissionReview');
    }

    public function replicate(AuthUser $authUser, SubmissionReview $submissionReview): bool
    {
        return $authUser->can('Replicate:SubmissionReview');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubmissionReview');
    }

}
<?php

namespace Database\Seeders;

use App\Services\Journal\PeerReview;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Working roles beside super_admin. Run after `php artisan shield:generate` so the permissions exist;
 * re-running re-syncs each role to exactly the lists below.
 * php artisan db:seed --class=EditorRolesSeeder
 */
class EditorRolesSeeder extends Seeder
{
    /** Shield subjects (the part after "Action:") each role fully manages. */
    public const ROLES = [
        'ksaac_editor' => [
            'Homepage', 'KsaacSite', 'HeroSlide', 'Stat', 'WorkArea', 'News', 'Publication', 'PublicationCategory',
            'Conference', 'Project', 'KidsStory', 'Partner', 'BoardMember',
            'Page', 'MenuItem', 'SocialLink', 'NewsletterSubscriber', 'ContactMessage',
        ],
        'journal_editor' => [
            'JacSite', 'HeroSlide', 'Award', 'Volume', 'Issue', 'Article', 'EditorialMember', 'JournalNews', 'Video', 'SubscriptionPrice',
            'Subscription', 'Submission', 'SubmissionReview', 'Page',
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role => $subjects) {
            Role::findOrCreate($role, 'admin')->syncPermissions($this->permissionsFor($subjects));
        }

        // Reviewers open and fill in their own assignments only (see SubmissionReviewPolicy).
        Role::findOrCreate(PeerReview::REVIEWER_ROLE, 'admin')->syncPermissions(
            Permission::where('guard_name', 'admin')->whereIn('name', ['ViewAny:SubmissionReview', 'View:SubmissionReview', 'Update:SubmissionReview'])->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function permissionsFor(array $subjects)
    {
        return Permission::where('guard_name', 'admin')->get()
            ->filter(fn (Permission $p) => in_array(str($p->name)->after(':')->toString(), $subjects, true))
            ->values();
    }
}

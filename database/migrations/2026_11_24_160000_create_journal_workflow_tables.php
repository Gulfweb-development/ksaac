<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Journal subscriptions (bank-transfer requests and their approval) and the research peer-review workflow. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_renewal')->default(false);
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 40);
            $table->text('address')->nullable();
            $table->string('plan', 30);
            $table->string('region', 20);
            $table->decimal('amount', 8, 3);
            $table->string('currency', 3);
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable()->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 500);
            $table->string('language', 2)->default('ar');
            $table->text('abstract');
            $table->string('keywords', 500)->nullable();
            $table->string('theme')->nullable();
            $table->json('co_authors')->nullable();
            $table->string('status', 30)->default('received')->index();
            $table->unsignedTinyInteger('round')->default(1);
            $table->foreignId('editor_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('reward_amount', 8, 3)->nullable();
            $table->date('reward_paid_on')->nullable();
            $table->timestamps();
        });

        Schema::create('submission_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('admins')->cascadeOnDelete();
            $table->unsignedTinyInteger('round')->default(1);
            $table->string('status', 20)->default('invited')->index();
            $table->date('due_on')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('scores')->nullable();
            $table->string('recommendation', 30)->nullable();
            $table->text('comments_to_author')->nullable();
            $table->text('comments_to_editor')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'reviewer_id', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_reviews');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('subscriptions');
    }
};

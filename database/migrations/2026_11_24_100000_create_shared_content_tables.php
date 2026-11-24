<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('site', 20)->index();
            $table->string('email');
            $table->string('locale', 5)->default('ar');
            $table->timestamp('unsubscribed_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['site', 'email']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('site', 20)->index();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('site', 20)->index();
            $table->string('platform', 30);
            $table->string('url');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('site', 20)->index();
            $table->json('title');
            $table->string('slug');
            $table->json('content')->nullable();
            $table->json('meta_description')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();

            $table->unique(['site', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('newsletter_subscribers');
    }
};

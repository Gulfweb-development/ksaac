<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Arab Childhood Journal (jac-kw.org). Translatable columns are JSON {ar, en}; PDFs and covers live in the media table. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->json('title');
            $table->unsignedSmallInteger('year')->nullable();
            $table->timestamps();
        });

        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volume_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number')->unique();
            $table->json('title');
            $table->json('theme')->nullable();
            $table->date('published_on')->nullable()->index();
            $table->boolean('is_current')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->string('language', 2)->default('ar');
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('authors')->nullable();
            $table->json('abstract')->nullable();
            $table->json('keywords')->nullable();
            $table->unsignedSmallInteger('page_from')->nullable();
            $table->unsignedSmallInteger('page_to')->nullable();
            $table->string('doi')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('editorial_members', function (Blueprint $table) {
            $table->id();
            $table->string('group', 20)->index();
            $table->json('name');
            $table->json('role')->nullable();
            $table->boolean('is_deceased')->default(false);
            $table->date('deceased_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('journal_news', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('youtube_url');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('subscription_prices', function (Blueprint $table) {
            $table->id();
            $table->string('plan', 30);
            $table->string('region', 20);
            $table->decimal('amount', 8, 3);
            $table->string('currency', 3);
            $table->timestamps();

            $table->unique(['plan', 'region']);
        });
    }

    public function down(): void
    {
        foreach (['subscription_prices', 'videos', 'journal_news', 'editorial_members', 'articles', 'issues', 'volumes'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

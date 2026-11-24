<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** KSAAC website content. Translatable columns are JSON {ar, en}; images/files live in the media table. */
return new class extends Migration
{
    public function up(): void
    {
        // Shared by both sites: top items, mega-menu column headings, and their links (max 3 levels).
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('site', 20)->index();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->json('label');
            $table->string('url')->nullable();
            $table->json('badge')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->json('kicker')->nullable();
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->json('button_label')->nullable();
            $table->string('button_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('value');
            $table->string('suffix', 10)->nullable();
            $table->json('label');
            $table->boolean('animate')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('work_areas', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('publication_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('author')->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->json('description')->nullable();
            $table->string('purchase_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->date('starts_on')->nullable()->index();
            $table->date('ends_on')->nullable();
            $table->json('venue')->nullable();
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->date('started_on')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('kids_stories', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('board_members', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('position')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['board_members', 'partners', 'kids_stories', 'projects', 'conferences', 'publications', 'publication_categories', 'news', 'work_areas', 'stats', 'hero_slides', 'menu_items'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('meta_title_id')->nullable();
            $t->string('meta_title_en')->nullable();
            $t->text('meta_description_id')->nullable();
            $t->text('meta_description_en')->nullable();
            $t->string('banner_title_id')->nullable();
            $t->string('banner_title_en')->nullable();
            $t->string('banner_subtitle_id')->nullable();
            $t->string('banner_subtitle_en')->nullable();
            $t->string('banner_image')->nullable();
            $t->timestamps();
        });

        Schema::create('articles', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title_id');
            $t->string('title_en')->nullable();
            $t->text('excerpt_id')->nullable();
            $t->text('excerpt_en')->nullable();
            $t->longText('body_id')->nullable();
            $t->longText('body_en')->nullable();
            $t->string('category')->default('edukasi_gaya_hidup');
            $t->string('cover_image')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->boolean('is_featured')->default(false);
            $t->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('name_id');
            $t->string('name_en')->nullable();
            $t->text('description_id')->nullable();
            $t->text('description_en')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_category_id')->constrained()->cascadeOnDelete();
            $t->string('slug')->unique();
            $t->string('name_id');
            $t->string('name_en')->nullable();
            $t->text('description_id')->nullable();
            $t->text('description_en')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('people', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('role_id')->nullable();
            $t->string('role_en')->nullable();
            $t->string('group')->default('directors');
            $t->string('photo')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('awards', function (Blueprint $t) {
            $t->id();
            $t->string('title_id');
            $t->string('title_en')->nullable();
            $t->integer('year')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('milestones', function (Blueprint $t) {
            $t->id();
            $t->string('year');
            $t->text('caption_id')->nullable();
            $t->text('caption_en')->nullable();
            $t->string('photo')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('impact_programs', function (Blueprint $t) {
            $t->id();
            $t->string('title_id');
            $t->string('title_en')->nullable();
            $t->text('body_id')->nullable();
            $t->text('body_en')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('csr_programs', function (Blueprint $t) {
            $t->id();
            $t->string('category')->default('esg');
            $t->string('title_id');
            $t->string('title_en')->nullable();
            $t->longText('body_id')->nullable();
            $t->longText('body_en')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('investor_documents', function (Blueprint $t) {
            $t->id();
            $t->string('category')->default('annual_report');
            $t->string('title_id');
            $t->string('title_en')->nullable();
            $t->integer('year')->nullable();
            $t->string('file_id')->nullable();
            $t->string('file_en')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('job_vacancies', function (Blueprint $t) {
            $t->id();
            $t->string('title_id');
            $t->string('title_en')->nullable();
            $t->string('department_id')->nullable();
            $t->string('department_en')->nullable();
            $t->string('location')->nullable();
            $t->longText('description_id')->nullable();
            $t->longText('description_en')->nullable();
            $t->boolean('is_open')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('global_sites', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('region')->nullable();
            $t->text('address_id')->nullable();
            $t->text('address_en')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('online_shops', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('url')->nullable();
            $t->string('logo')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('subject')->nullable();
            $t->text('message');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'contact_messages', 'online_shops', 'global_sites', 'job_vacancies',
            'investor_documents', 'csr_programs', 'impact_programs', 'milestones',
            'awards', 'people', 'products', 'product_categories', 'articles', 'pages',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('image_url', 500);
            $table->string('short_description', 500);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();

        DB::table('services')->insert([
            [
                'title' => 'Cyber Security',
                'image_url' => 'img/carousel-2.jpg',
                'short_description' => 'Security reviews, hardening, monitoring, and incident readiness for modern web systems.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Data Analytics',
                'image_url' => 'img/feature.jpg',
                'short_description' => 'Dashboards and reporting workflows that turn business data into decisions.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Web Development',
                'image_url' => 'img/carousel-1.jpg',
                'short_description' => 'Responsive websites and Laravel applications built for speed, clarity, and maintainability.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'App Development',
                'image_url' => 'img/about.jpg',
                'short_description' => 'Cross-platform app planning and development for customer-facing and internal workflows.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'SEO Optimization',
                'image_url' => 'img/carousel-2.jpg',
                'short_description' => 'Technical SEO, page speed, and content structure improvements for better discovery.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        $this->updateServiceImages([
            'Cyber Security' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHdQMfI64FjTnA-If740MENnG9X9B-cmM2Jg&s',
            'Data Analytics' => 'img/feature.jpg',
            'Web Development' => 'img/carousel-2.jpg',
            'App Development' => 'img/about.jpg',
            'SEO Optimization' => 'img/carousel-1.jpg',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        $this->updateServiceImages([
            'Cyber Security' => 'img/carousel-2.jpg',
            'Data Analytics' => 'img/feature.jpg',
            'Web Development' => 'img/carousel-1.jpg',
            'App Development' => 'img/about.jpg',
            'SEO Optimization' => 'img/carousel-2.jpg',
        ]);
    }

    /**
     * @param  array<string, string>  $imagesByTitle
     */
    private function updateServiceImages(array $imagesByTitle): void
    {
        $now = now();

        foreach ($imagesByTitle as $title => $imageUrl) {
            DB::table('services')
                ->where('title', $title)
                ->update([
                    'image_url' => $imageUrl,
                    'updated_at' => $now,
                ]);
        }
    }
};

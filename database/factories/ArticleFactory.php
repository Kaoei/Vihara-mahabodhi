<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category' => fake()->randomElement(['Berita Kegiatan', 'Renungan Dharma', 'Pengumuman']),
            'author' => 'Tim Dharma Wihara Mahabodhi',
            'excerpt' => fake()->sentence(15),
            'content' => fake()->paragraphs(3, true),
            'cover_image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1000&q=80',
            'published_at' => now(),
            'is_published' => true,
            'is_featured' => false,
        ];
    }
}

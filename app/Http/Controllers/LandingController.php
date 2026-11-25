<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\ViharaProfile;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    /**
     * Get the shared Wihara Mahabodhi profile configuration from the database.
     *
     * @return array<string, mixed>
     */
    private function getViharaProfile(): array
    {
        return ViharaProfile::current()->toLandingArray();
    }

    /**
     * Display the public landing page for Wihara Mahabodhi Buddhist Centre.
     */
    public function index(): View
    {
        $viharaProfile = $this->getViharaProfile();

        $schedules = Schedule::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $scheduleCategories = $schedules->pluck('category')->unique()->values();

        $galleries = Gallery::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get();

        $articles = Article::query()
            ->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('landing', [
            'isArticleDetail' => false,
            'vihara' => $viharaProfile,
            'schedules' => $schedules,
            'scheduleCategories' => $scheduleCategories,
            'galleries' => $galleries,
            'articles' => $articles,
        ]);
    }

    /**
     * Display a dedicated page for a single Article / Berita / Renungan Dharma.
     */
    public function showArticle(Article $article): View
    {
        $relatedArticles = Article::query()
            ->where('is_published', true)
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('landing', [
            'isArticleDetail' => true,
            'vihara' => $this->getViharaProfile(),
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\ProductCategory;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class PostCategoryController extends FontEndController
{
    /**
     * Specific Category Post Listing
     */
    public function index(PostCategory $category, $slug)
    {
        $category = Cache::remember('post_category_' . $slug, $this->cacheTime, function () use ($slug) {
            return PostCategory::where(["slug" => $slug, "status" => 1])->first();
        });

        if (!$category) return abort(404);

        $page = LengthAwarePaginator::resolveCurrentPage();

        // lấy bài viết nổi bật
        $featuredArticle = Cache::remember('featured_article_' . $slug, $this->cacheTime, function () use ($category) {
            return Post::where('status', 1)
                ->take(5)
                ->orderBy('view', 'DESC')
                ->get();
        });

        // tạo cache
        $posts = Cache::remember('posts_for_category_' . $slug . '_page_' . $page, $this->cacheTime, function () use ($category) {
            $postsForCategory = $category->posts()->where('status', 1)->get();

            $paginatedData = new LengthAwarePaginator(
                $postsForCategory->forPage(LengthAwarePaginator::resolveCurrentPage(), 6),
                $postsForCategory->count(),
                6,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => Route('web_list_post', $category->slug)]
            );
            $paginatedData->setPath(Route('web_list_post', $category->slug));

            return $paginatedData;
        });

        // lấy tất cả các danh mục bài viết
        $categories = Cache::remember('categories', $this->cacheTime, function () {
            return PostCategory::where('status', 1)->get();
        });

        return $this->render('web.post_category.index', compact(
            'category',
            'posts',
            'featuredArticle',
            'categories'
        ));
    }

    /**
     * All Posts / News Directory (/posts and /news)
     */
    public function allPosts(Request $request)
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        $featuredArticle = Cache::remember('featured_articles_all', $this->cacheTime, function () {
            return Post::where('status', 1)
                ->take(5)
                ->orderBy('view', 'DESC')
                ->get();
        });

        $posts = Cache::remember('all_posts_page_' . $page, $this->cacheTime, function () {
            return Post::where('status', 1)->orderBy('id', 'DESC')->paginate(6);
        });

        $categories = Cache::remember('categories', $this->cacheTime, function () {
            return PostCategory::where('status', 1)->get();
        });

        // Synthetic category object for "Tất Cả Bài Viết"
        $category = new PostCategory();
        $category->slug = 'posts';
        $category->name = ['vi' => 'Tất Cả Tin Tức & Mẹo Sức Khỏe', 'en' => 'All News & Health Articles'];

        return $this->render('web.post_category.index', compact(
            'category',
            'posts',
            'featuredArticle',
            'categories'
        ));
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostController extends FontEndController
{
    public function index(Request $req)
    {
        $slug = $req->slug;
        $post = Cache::remember('post_' . $slug, $this->cacheTime, function () use ($slug) {
            return Post::where(['slug' => $slug, 'status' => 1])->first();
        });

        if (empty($post)) return abort(404);

        $featuredArticle = Cache::remember('featured_article_' . $slug, $this->cacheTime, function () use ($post) {
            return Post::where('id', '!=', $post->id)
                ->where('status', 1)
                ->take(5)
                ->orderBy('view','DESC')
                ->get();
        });

        $categories = $post->categories()->select('post_categories.id')->pluck('post_categories.id');

        $relatedPosts = Post::whereHas('categories', function ($query) use ($categories) {
            $query->whereIn('post_category_relation.category_id', $categories);
        })
        ->where('posts.id', '!=', $post->id) // Loại trừ bài viết ban đầu
        ->get();

        $prevPost = Post::where('id', '<', $post->id)->where('status', 1)->orderBy('id', 'desc')->first();
        $nextPost = Post::where('id', '>', $post->id)->where('status', 1)->orderBy('id', 'asc')->first();
        $firstCategory = $post->categories->first();

        // $this->header = [
        //     'canonical' => Route('web_post_detail', $post->slug).'/',
        //     'robots' => $post->robots ?? 'index,follow',
        //     'title' => $post->meta_title,
        //     'description' => $post->meta_description ?? "Interesting and latest news about showbit and celebrity movies" . $post->name,
        //     'keywords' => $post->meta_keywords ?? "Interesting, latest news,movies,showbiz," . $post->name,
        //     'og_image' => $post->image,
        //     'og_url' => Route('web_post_detail', $post->slug),
        // ];

        return $this->render('web.post.index', compact(
            'post',
            'featuredArticle',
            'relatedPosts',
            'prevPost',
            'nextPost',
            'firstCategory'
        ));
    }
}

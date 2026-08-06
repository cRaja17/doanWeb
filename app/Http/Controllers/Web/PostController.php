<?php

namespace App\Http\Controllers\Web;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Support\FrontendCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostController extends FontEndController
{
    public function index(Request $req)
    {
        $slug = $req->slug;
        $post = Cache::remember(FrontendCache::key('post_' . $slug), $this->cacheTime, function () use ($slug) {
            return Post::where(['slug' => $slug, 'status' => 1])->first();
        });

        if (empty($post)) return abort(404);

        $featuredArticle = Cache::remember(FrontendCache::key('featured_article_' . $slug), $this->cacheTime, function () use ($post) {
            return Post::where('id', '!=', $post->id)
                ->where('status', 1)
                ->take(5)
                ->orderBy('view','DESC')
                ->get();
        });

        // Bài viết được cache nên `view` đọc ra là số cũ; tăng thẳng bằng query
        // builder để (a) không đi qua model events - tránh xoá sạch cache trang
        // khách mỗi lượt xem, và (b) không ghi đè dữ liệu cũ đang giữ trong cache.
        DB::table('posts')->where('id', $post->id)->increment('view');

        $categories = $post->categories()->select('post_categories.id')->pluck('post_categories.id');

        // Trước đây không giới hạn: một danh mục nhiều bài là truy vấn đổ về
        // toàn bộ bài viết chỉ để hiển thị vài cái.
        $relatedPosts = Post::whereHas('categories', function ($query) use ($categories) {
            $query->whereIn('post_category_relation.category_id', $categories);
        })
        ->where('posts.id', '!=', $post->id) // Loại trừ bài viết ban đầu
        ->where('posts.status', 1)
        ->latest('posts.id')
        ->take(6)
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

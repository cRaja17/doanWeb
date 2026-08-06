<?php

namespace App\Http\Controllers\Web;

use App\Models\Config;
use App\Models\Post;
use App\Models\Language;
use App\Models\PostCategory;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use App\Support\FrontendCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Spatie\TranslationLoader\LanguageLine;
use App\Http\Controllers\Web\FontEndController;
use Illuminate\Support\Facades\Cookie;

class PageController extends FontEndController
{

    /**
     * @Description : Trang HomePage
     *
     * @throws 	: NotFoundException
     * @param 	: int data
     * @return 	: array
     * @Author 	: DRaja
     */

    public function index(Request $request)
    {
        $lang = app()->getLocale();
        $languages = Language::where('active', '=', 1)->get();


        $featuredArticle = Cache::remember(FrontendCache::key('featured_article_home_' . $lang), $this->cacheTime, function () use ($lang) {
            return Post::where('status', 1)
                ->take(4)
                ->orderBy('view', 'DESC')
                ->get();
        });

        $getSlides = Cache::remember(FrontendCache::key('slides_home_' . $lang), $this->cacheTime, function () use ($lang) {
            $config = Config::where('name', 'slides')->first();
            $query = $config ? $config->value : '[]';

            $data = [];
            $decoded = json_decode($query);
            if (is_array($decoded) || is_object($decoded)) {
                foreach ($decoded as $key => $slide) {
                    $data[$key] = $slide;
                }
            }
            return $data;
        });

        $getImages = Cache::remember(FrontendCache::key('images_home_' . $lang), $this->cacheTime, function () use ($lang) {
            $config = Config::where('name', 'images')->first();
            $query = $config ? $config->value : '[]';

            $data = [];
            $decoded = json_decode($query);
            if (is_array($decoded) || is_object($decoded)) {
                foreach ($decoded as $key => $image) {
                    $data[$key] = $image;
                }
            }
            return $data;
        });


        $getPostCategories = Cache::remember(FrontendCache::key('post_categories_home_' . $lang), $this->cacheTime, function () use ($lang) {
            return PostCategory::where('status', 1)
                ->get();
        });

        $featuredProducts = Cache::remember(FrontendCache::key('featured_products_home_' . $lang), $this->cacheTime, function () use ($lang) {
            return \App\Models\Product::where('status', 1)
                ->take(8)
                ->get();
        });

        $productCategories = Cache::remember(FrontendCache::key('product_categories_home_' . $lang), $this->cacheTime, function () use ($lang) {
            return \App\Models\ProductCategory::where('status', 1)
                ->get();
        });

        $this->header = [
            'canonical' => route('web_home'),
            'robots' => 'index,follow',
            'title' => translate('home.meta_title'),
            'description' => translate('home.meta_description'),
            'keywords' => translate('home.meta_keyword'),
            'og_image' => '',
            'og_url' => route('web_home'),
        ];

        return $this->render('web.page.index', compact(
            'lang',
            'languages',
            'featuredArticle',
            'getSlides',
            'getImages',
            'getPostCategories',
            'featuredProducts',
            'productCategories'
        ));
    }
}

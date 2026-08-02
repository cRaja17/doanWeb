<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Information;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InformationController extends FontEndController
{
    public function index(Information $information, $slug)
    {
        $locale = app()->getLocale();

        $information = Cache::remember('information_' . $slug . '-' . $locale, $this->cacheTime, function () use ($slug) {
            return Information::where(["slug" => $slug, "status" => 1])->first();
        });

        if (!$information) return abort(404);

        // lấy tất cả các danh mục bài viết

        $manyInformation =  Cache::remember('manyInformation_' . $locale, $this->cacheTime, function () use ($information) {
            return Information::where('status', 1)->get();
        });

        $this->header = [
            'canonical' => Route('web.information', $information->slug),
            'robots' => $information->robots,
            'title' => $information->name,
            'description' => $information->meta_description,
            'keywords' => $information->meta_keywords,
            'og_image' => $information->image ?? null,
            'og_url' => Route('web.information', $information->slug),
        ];
        return $this->render('web.information.index', compact(
            'information',
            'manyInformation'
        ));
    }
}

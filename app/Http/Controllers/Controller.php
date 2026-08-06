<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public $layout = null;

    public $config = [];

    public $header = [];

    private $language = 'vi';
    /**
     * @Description : Hàm render 
     *
     * @throws 	: NotFoundException
     * @param 	: string $view , array $data
     * @return 	: view + layout
     * @Author 	: DRaja
     */


    public function render($view, $data = [])
    {

        // check URL 
        // if (!empty($this->header['canonical'])) {
        //     if (request()->root() . parse_url(request()->getRequestUri(), PHP_URL_PATH) != $this->header['canonical']) {
        //         return abort(404);
        //     }
        // }
        return view($this->layout, $this->config, $this->header)->with('content', view(
            $view,
            $data,
            [
                'language' => $this->language,
            ]
        ));
    }


    /**
     * @Description : send message to email address 
     *
     * @throws 	: NotFoundException
     * @param 	: $e : address , $subject : title , $content : content in HTML
     * @return 	: array json
     * @Author 	: DRaja
     */

    public function sendEmail($e, $subject, $content)
    {
        try {
            if (empty($e)) {
                throw new \RuntimeException('Thiếu địa chỉ email người nhận.');
            }

            Mail::to($e)->send(new \App\Mail\OrderNotification($subject, $content));

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error('SMTP Error: ' . $ex->getMessage());
            return response($ex->getMessage(), 500);
        }
    }
}

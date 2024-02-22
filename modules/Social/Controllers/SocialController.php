<?php
namespace Modules\Social\Controllers;

use Modules\FrontendController;
use Modules\Social\Models\Forum;
use Modules\Social\Models\Post;

class SocialController extends FrontendController
{
    public function index(){
        $data = [
            'rows'=>Post::search(['feed'=>1])->with(['user'])->paginate(20),
            'forums'=>Forum::query()->where('status','publish')->get()
        ];
        return view('Social::frontend.index',$data);
    }
}

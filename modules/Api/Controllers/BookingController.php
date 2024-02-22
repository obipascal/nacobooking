<?php
namespace Modules\Api\Controllers;
use App\Http\Controllers\Controller;
use Modules\Template\Models\Template;

class BookingController extends Controller
{

    public function getTypes(){
        $types = get_bookable_services();

        $res = [];
        foreach ($types as $type=>$class) {
            $obj = new $class();
            $res[$type] = [
                'icon'=>call_user_func([$obj,'getServiceIconFeatured']),
                'name'=>call_user_func([$obj,'getModelName']),
                'search_fields'=>[

                ],
            ];
        }
        return $res;
    }

    public function getConfigs(){
        $languages = \Modules\Language\Models\Language::getActive();
        $template = Template::find(setting_item('api_app_layout'));
        $res = [
            'languages'=>$languages->map(function($lang){
                return $lang->only(['locale','name']);
            }),
            'booking_types'=>$this->getTypes(),
            'translations'=>[

            ],
            'app_layout'=>$template? json_decode($template->content,true) : []
        ];
        return $this->sendSuccess($res);
    }
}

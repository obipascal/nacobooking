<?php
namespace Modules\Booking\Gateways;

use Illuminate\Http\Request;

abstract class BaseGateway
{
    protected $id;
    public    $name;
    public $is_offline = false;

    public function __construct($id = false)
    {
        if ($id)
            $this->id = $id;
    }

    public function isAvailable()
    {
        return $this->getOption('enable');
    }

    public function getHtml()
    {

    }

    /**
     * @param Request $request
     * @param \Modules\Booking\Models\Booking $booking
     * @param \Modules\Booking\Models\Bookable $service
     */
    public function process(Request $request, $booking, $service)
    {

    }

    public function cancelPayment(Request $request)
    {

    }

    public function confirmPayment(Request $request)
    {

    }

    public function getOptionsConfigs()
    {
        return [];
    }

    public function getOptionsConfigsFormatted()
    {
        $languages = \Modules\Language\Models\Language::getActive();
        $options = $this->getOptionsConfigs();
        if (!empty($options)) {
            foreach ($options as &$option) {
                $option['value'] = $this->getOption($option['id'], $option['std'] ?? '');
                if( !empty($option['multi_lang']) && !empty($languages) && setting_item('site_enable_multi_lang') && setting_item('site_locale')){
                    foreach($languages as $language){
                        if( setting_item('site_locale') == $language->locale) continue;
                        $option["value_".$language->locale] = $this->getOption($option['id']."_".$language->locale, '');
                    }
                }
                $option['id'] = 'g_' . $this->id . '_' . $option['id'];
            }
        }
        return $options;
    }

    public function getOption($key, $default = '')
    {
        return setting_item('g_' . $this->id . '_' . $key, $default);
    }

    public function getDisplayName()
    {
        $locale = app()->getLocale();
        if(!empty($locale)){
            $title = $this->getOption("name_".$locale);
        }
        if(empty($title)){
            $title = $this->getOption("name" , $this->name);
        }
        return $title;
    }

    public function getDisplayHtml()
    {
        $locale = app()->getLocale();
        if(!empty($locale)){
            $html = $this->getOption("html_".$locale);
        }
        if(empty($html)){
            $html = $this->getOption("html");
        }
        return $html;
    }

    public function getReturnUrl()
    {
        return url(app_get_locale(false,false,"/").config('booking.booking_route_prefix') . '/confirm/' . $this->id);
    }

    public function getCancelUrl()
    {
        return url(app_get_locale(false,false,"/").config('booking.booking_route_prefix') . '/cancel/' . $this->id);
    }

    public function getDisplayLogo()
    {
        $logo_id = $this->getOption('logo_id');
        return get_file_url($logo_id);
    }
}

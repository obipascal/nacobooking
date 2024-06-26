<?php
namespace Modules\Booking\Controllers;

use DebugBar\DebugBar;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
//use Modules\Booking\Events\VendorLogPayment;
use Modules\Booking\Events\EnquirySendEvent;
use Modules\Tour\Models\TourDate;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Enquiry;
use Modules\Booking\Gateways\PaystackGateway;
use App\Helpers\ReCaptchaEngine;
use Modules\Booking\Emails\NewBookingEmail;

class BookingController extends \App\Http\Controllers\Controller
{
    use AuthorizesRequests;
    protected $booking;
    protected $enquiryClass;

    public function __construct()
    {
        $this->booking = Booking::class;
        $this->enquiryClass = Enquiry::class;
    }

    public function checkout($code)
    {
        if(!is_enable_guest_checkout() and !Auth::check()){
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }

        $booking = $this->booking::where('code', $code)->first();

        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }

        if($booking->status != 'draft'){
            return redirect('/');
        }
        $data = [
            'page_title' => __('Checkout'),
            'booking'    => $booking,
            'service'    => $booking->service,
            'gateways'   => $this->getGateways(),
            'user'       => Auth::user()
        ];

        return view('Booking::frontend/checkout', $data);
    }
    
    public function completeCheckout($code) {
        if(!is_enable_guest_checkout() and !Auth::check()){
            
            return redirect(url('/login?redirect=/booking/'. $code . '/complete-reservation'));
            
        }

        $booking = $this->booking::where('code', $code)->first();

        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }

        if($booking->status != 'draft'){
            return redirect('/');
        }
        $data = [
            'page_title' => __('Checkout'),
            'booking'    => $booking,
            'service'    => $booking->service,
            'gateways'   => $this->getGateways(),
            'user'       => Auth::user()
        ];

        return view('Booking::frontend/completeCheckout', $data);
    }

    public function checkStatusCheckout($code)
    {
        $booking = $this->booking::where('code', $code)->first();
        $data = [
            'error'    => false,
            'message'  => '',
            'redirect' => ''
        ];
        if (empty($booking)) {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        if ($booking->customer_id != Auth::id()) {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        if ($booking->status != 'draft') {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        return response()->json($data, 200);
    }

    public function doCheckout(Request $request)
    {

        

        if(!is_enable_guest_checkout() and !Auth::check()){
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }
        /**
         * @param Booking $booking
         */
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
      
      
        $code = $request->input('code');
        $booking = $this->booking::where('code', $code)->first();
      
      
        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        if ($booking->status != 'draft') {
            return $this->sendError('',[
                'url'=>$booking->getDetailUrl()
            ]);
        }
        
        $service = $booking->service;
    
    
        if (empty($service)) {
            return $this->sendError(__("Service not found"));
        }
        /**
         * Google ReCapcha
         */
        if(ReCaptchaEngine::isEnable() and setting_item("booking_enable_recaptcha")){
            $codeCapcha = $request->input('g-recaptcha-response');
            if(!$codeCapcha or !ReCaptchaEngine::verify($codeCapcha)){
                return $this->sendError(__("Please verify the captcha"));
            }
        }
        $rules = [
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|string|email|max:255',
            'phone'           => 'required|string|max:255',
            'country' => 'required',
            'payment_gateway' => 'required',
            'term_conditions' => 'required',
        ];
       
       
        $how_to_pay = $request->input('how_to_pay');
        $rules = $service->filterCheckoutValidate($request, $rules);
       
       
        if (!empty($rules)) {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->sendError('', ['errors' => $validator->errors()]);
            }
        }
       
       
        if (!empty($rules['payment_gateway'])) {
            
            $payment_gateway = $request->input('payment_gateway');
            
            if($payment_gateway !== 'stripe'){
            
                $gateways = get_payment_gateways();
                if (empty($gateways[$payment_gateway]) or !class_exists($gateways[$payment_gateway])) {
                    return $this->sendError(__("Payment gateway not found"));
                }
                
                $gatewayObj = new $gateways[$payment_gateway]($payment_gateway);
                if (!$gatewayObj->isAvailable()) {
                    return $this->sendError(__("Payment gateway is not available"));
                }
            }else{
                $gatewayObj = new PaystackGateway();
                
            }
        }
        
        $service->beforeCheckout($request, $booking);
        // Normal Checkout
        $booking->first_name = $request->input('first_name');
        $booking->last_name = $request->input('last_name');
        $booking->email = $request->input('email');
        $booking->phone = $request->input('phone');
        $booking->address = $request->input('address_line_1');
        $booking->address2 = $request->input('address_line_2');
        $booking->city = $request->input('city');
        $booking->state = $request->input('state');
        $booking->zip_code = $request->input('zip_code');
        $booking->country = $request->input('country');
        $booking->customer_notes = $request->input('customer_notes');
        $booking->gateway = $payment_gateway;
        $booking->pay_now = $booking->deposit;

        if($how_to_pay != 'deposit'){
            $booking->deposit = 0;
            $booking->pay_now = $booking->total;
        }
        $booking->save();
        

//        event(new VendorLogPayment($booking));

        if(Auth::check()) {
            $user = Auth::user();
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->phone = $request->input('phone');
            $user->address = $request->input('address_line_1');
            $user->address2 = $request->input('address_line_2');
            $user->city = $request->input('city');
            $user->state = $request->input('state');
            $user->zip_code = $request->input('zip_code');
            $user->country = $request->input('country');
            $user->save();
        }

        $booking->addMeta('locale',app()->getLocale());
        $booking->addMeta('how_to_pay',$how_to_pay);

        $service->afterCheckout($request, $booking);
        try {
             //return $this->sendError(__("Payment gateway"));
            
            $gatewayObj->process($request, $booking, $service);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }
    
    public function doBookingReservation(Request $request)
    {

        

        if(!is_enable_guest_checkout() and !Auth::check()){
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }
        /**
         * @param Booking $booking
         */
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
      
      
        $code = $request->input('code');
        $booking = $this->booking::where('code', $code)->first();
      
      
        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        if ($booking->status != 'draft') {
            return $this->sendError('',[
                'url'=>$booking->getDetailUrl()
            ]);
        }
        
        $service = $booking->service;
    
    
        if (empty($service)) {
            return $this->sendError(__("Service not found"));
        }
        /**
         * Google ReCapcha
         */
        if(ReCaptchaEngine::isEnable() and setting_item("booking_enable_recaptcha")){
            $codeCapcha = $request->input('g-recaptcha-response');
            if(!$codeCapcha or !ReCaptchaEngine::verify($codeCapcha)){
                return $this->sendError(__("Please verify the captcha"));
            }
        }
        $rules = [
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|string|email|max:255',
            'phone'           => 'required|string|max:255',
            'country' => 'required',
            'term_conditions' => 'required',
        ];
       
       
        $how_to_pay = $request->input('how_to_pay');
        $rules = $service->filterCheckoutValidate($request, $rules);
       
       
        if (!empty($rules)) {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->sendError('', ['errors' => $validator->errors()]);
            }
        }
       
     
        
        $service->beforeCheckout($request, $booking);
        // Normal Checkout
        $booking->first_name = $request->input('first_name');
        $booking->last_name = $request->input('last_name');
        $booking->email = $request->input('email');
        $booking->phone = $request->input('phone');
        $booking->address = $request->input('address_line_1');
        $booking->address2 = $request->input('address_line_2');
        $booking->city = $request->input('city');
        $booking->state = $request->input('state');
        $booking->zip_code = $request->input('zip_code');
        $booking->country = $request->input('country');
        $booking->customer_notes = $request->input('customer_notes');
        $booking->gateway = '';
        $booking->pay_now = $booking->deposit;
        

        if($how_to_pay != 'deposit'){
            $booking->deposit = 0;
            $booking->pay_now = $booking->total;
        }
        $booking->save();
        

//        event(new VendorLogPayment($booking));

        if(Auth::check()) {
            $user = Auth::user();
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->phone = $request->input('phone');
            $user->address = $request->input('address_line_1');
            $user->address2 = $request->input('address_line_2');
            $user->city = $request->input('city');
            $user->state = $request->input('state');
            $user->zip_code = $request->input('zip_code');
            $user->country = $request->input('country');
            $user->save();
        }


        // send booking emails
        try{
            $booking = $this->booking::where('code', $code)->first();
            $booking->sendNewBookingEmailsToVendorAndAdmin();
        } catch(\Exception $e)
        {
            return $this->sendError('', ['errors' => [$e->getMessage()]]);
        }

         
        
        return response()->json([
                    'url' => $booking->getDetailUrl() . '/reserved',
                    'status' => true
                ]);
    }
    
    // handle booking approval
    public function approveBookingReservation(Request $request, $code)
    {

        
       
       
        if(!is_enable_guest_checkout() and !Auth::check()){
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }
        
        /**
         * @param Booking $booking
         */
        $validator = Validator::make(['code' => $code], [
            'code' => 'required',
        ]);
        
        if ($validator->fails()) {
           
    
           return back()->with('message', implode(', ', $validator->errors()->messages));
           
            
        }
      
      
         
        $booking = $this->booking::where('code', $code)->first();
      
      
        if (empty($booking)) {
            
            abort(404);
        }
        
        
       
        
        if ($booking->status != 'draft' && $booking->booking_status !== 'pending') {
           
            return back()->with('message',  "Booking already approved");
            
        }
        
         $booking->booking_status = 'approved';
         $booking->save();
        
         
        // send booking emails
        try{
            $booking = $this->booking::where('code', $code)->first();
           
            $booking->sendBookingPaymentLink();
        } catch(\Exception $e)
        {
            return $this->sendError('', ['errors' => [$e->getMessage()]]);
        }

         
        
        return redirect()->back()->with('message', 'Booking reservation has been approved and an email has been forwarded to customer with payment link.');
    }
    
    public function mailTester(Request $request, $code) {
        
         try{
            $booking = $this->booking::where('code', $code)->first();
           
            //   return (new NewBookingEmail($booking, 'customer', $booking->getPaymentLink()))->render();
         
          
            $send = $booking->sendBookingPaymentLink();
            
            dd($send);
        } catch(\Exception $e)
        {
            return $this->sendError('', ['errors' => [$e->getMessage()]]);
        }
    }
    

    public function confirmPayment(Request $request, $gateway)
    {

        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        return $gatewayObj->confirmPayment($request);
    }

    public function cancelPayment(Request $request, $gateway)
    {

        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        return $gatewayObj->cancelPayment($request);
    }

    /**
     * @todo Handle Add To Cart Validate
     *
     * @param Request $request
     * @return string json
     */
    public function addToCart(Request $request)
    {
        if(!is_enable_guest_checkout() and !Auth::check()){
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }

        $validator = Validator::make($request->all(), [
            'service_id'   => 'required|integer',
            'service_type' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();
        if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$service_type];
        $service = $module::find($service_id);
        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            return $this->sendError(__('Service not found'));
        }
        if (!$service->isBookable()) {
            return $this->sendError(__('Service is not bookable'));
        }

        if(Auth::id() == $service->create_user){
            return $this->sendError(__('You cannot book your own service'));
        }

        return $service->addToCart($request);
    }

    protected function getGateways()
    {

        $all = get_payment_gateways();
        $res = [];
        foreach ($all as $k => $item) {
            if (class_exists($item)) {
                $obj = new $item($k);
                if ($obj->isAvailable()) {
                    $res[$k] = $obj;
                }
            }
        }
        return $res;
    }

    public function detail(Request $request, $code)
    {
        if(!is_enable_guest_checkout() and !Auth::check()){
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }

        $booking = Booking::where('code', $code)->first();
        if (empty($booking)) {
            abort(404);
        }

        if ($booking->status == 'draft') {
            return redirect($booking->getCheckoutUrl());
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        $data = [
            'page_title' => __('Booking Details'),
            'booking'    => $booking,
            'service'    => $booking->service,
        ];
        if ($booking->gateway) {
            $data['gateway'] = get_payment_gateway_obj($booking->gateway);
        }
        return view('Booking::frontend/detail', $data);
    }
    
     public function bookingReserved(Request $request, $code)
    {
        if(!is_enable_guest_checkout() and !Auth::check()){
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }

        $booking = Booking::where('code', $code)->first();
        if (empty($booking)) {
            abort(404);
        }

        // if ($booking->status == 'draft') {
        //     return redirect($booking->getCheckoutUrl());
        // }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        $data = [
            'page_title' => __('Booking Details'),
            'booking'    => $booking,
            'service'    => $booking->service,
        ];
        if ($booking->gateway) {
            $data['gateway'] = get_payment_gateway_obj($booking->gateway);
        }
        return view('Booking::frontend/booking-reserved', $data);
    }
    
	public function exportIcal($service_type = 'tour', $id)
	{
		\Debugbar::disable();
		$allServices = get_bookable_services();
		$allServices['room']='Modules\Hotel\Models\HotelRoom';
		if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
		}
		$module = $allServices[$service_type];

		$path ='/ical/';
		$fileName = 'booking_' . $service_type . '_' . $id . '.ics';
		$fullPath = $path.$fileName;

		$content  = $this->booking::getContentCalendarIcal($service_type,$id,$module);
		Storage::disk('uploads')->put($fullPath, $content);
		$file = Storage::disk('uploads')->get($fullPath);

		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');

		echo $file;
	}

	public function addEnquiry(Request $request){
        $rules =  [
            'service_id'   => 'required|integer',
            'service_type' => 'required',
            'enquiry_name' => 'required',
            'enquiry_email' => [
                'required',
                'email',
                'max:255',
            ],
        ];

        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }

        if(setting_item('booking_enquiry_enable_recaptcha')){
            $codeCapcha = trim($request->input('g-recaptcha-response'));
            if(empty($codeCapcha) or !ReCaptchaEngine::verify($codeCapcha)){
                return $this->sendError(__("Please verify the captcha"));
            }
        }

        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();
        if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$service_type];
        $service = $module::find($service_id);
        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            return $this->sendError(__('Service not found'));
        }
        $row = new $this->enquiryClass();
        $row->fill([
            'name'=>$request->input('enquiry_name'),
            'email'=>$request->input('enquiry_email'),
            'phone'=>$request->input('enquiry_phone'),
            'note'=>$request->input('enquiry_note'),
        ]);
        $row->object_id = $request->input("service_id");
        $row->object_model = $request->input("service_type");
        $row->status = "pending";
        $row->vendor_id = $service->create_user;
        $row->save();
        event(new EnquirySendEvent($row));
        return $this->sendSuccess([
            'message' => __("Thank you for contacting us! We will be in contact shortly.")
        ]);
    }
}

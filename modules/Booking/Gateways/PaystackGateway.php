<?php
namespace Modules\Booking\Gateways;

use Illuminate\Http\Request;
use Mockery\Exception;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Payment;
use Modules\Booking\Gateways\Paystack;
use Omnipay\Omnipay;
use Omnipay\Stripe\Gateway;
use PHPUnit\Framework\Error\Warning;
use Validator;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Illuminate\Support\Facades\Log;

use App\Helpers\Assets;

class PaystackGateway extends BaseGateway
{
    protected $id = 'stripe';

    public $name = 'Paystack Checkout';

    protected $gateway;

    public function getOptionsConfigs()
    {
        return [
            [
                'type'  => 'checkbox',
                'id'    => 'enable',
                'label' => __('Enable Stripe Standard?')
            ],
            [
                'type'  => 'input',
                'id'    => 'name',
                'label' => __('Custom Name'),
                'std'   => __("Stripe"),
                'multi_lang' => "1"
            ],
            [
                'type'  => 'upload',
                'id'    => 'logo_id',
                'label' => __('Custom Logo'),
            ],
            [
                'type'  => 'editor',
                'id'    => 'html',
                'label' => __('Custom HTML Description'),
                'multi_lang' => "1"
            ],
            [
                'type'       => 'input',
                'id'        => 'stripe_secret_key',
                'label'     => __('Secret Key'),
            ],
            [
                'type'       => 'input',
                'id'        => 'stripe_publishable_key',
                'label'     => __('Publishable Key'),
            ],
            [
                'type'       => 'checkbox',
                'id'        => 'stripe_enable_sandbox',
                'label'     => __('Enable Sandbox Mode'),
            ],
            [
                'type'       => 'input',
                'id'        => 'stripe_test_secret_key',
                'label'     => __('Test Secret Key'),
            ],
            [
                'type'       => 'input',
                'id'        => 'stripe_test_publishable_key',
                'label'     => __('Test Publishable Key'),
            ]
        ];
    }

    public function process(Request $request, $booking, $service)
    {
        
        if (in_array($booking->status, [
            $booking::PAID,
            $booking::COMPLETED,
            $booking::CANCELLED
        ])) {

            throw new Exception(__("Booking status doesn't need to be paid"));
        }
        if (!$booking->total) {
            throw new Exception(__("Booking total is zero. Can not process payment gateway!"));
        }
        $rules = [
            'reference'  => ['required'],
        ];
        $messages = [
            'reference.required'  => __('invalid reference!'),
        ];
        
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors'   => $validator->errors() ], 200)->send();
        }
        
        //$this->getGateway();
        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->payment_gateway = $this->id;
        $data = $this->handlePurchaseData([
            'amount'        => (float)$booking->pay_now,
            'transactionId' => $booking->code . '.' . time()
        ], $booking, $request);
        try{
             
            $paystack = new Paystack();
            
            $response = $paystack->verifyTransaction($request->input("reference"));
            if ($response->data->status == 'success' || $response->data->status == 'pending' ) {
                
                
                $payment->status = 'completed';
                $payment->logs = \GuzzleHttp\json_encode($response);
                $payment->save();
                $booking->payment_id = $payment->id;
                $booking->status = $booking::PAID;
                $booking->paid += $data['amount'];
                $booking->save();
                try{
                    $booking->sendNewBookingEmails();
                    event(new BookingCreatedEvent($booking));

                } catch(\Swift_TransportException $e){
                    Log::warning($e->getMessage());
                }
                response()->json([
                    'url' => $booking->getDetailUrl()
                ])->send();
            } else {
                $payment->status = 'fail';
                $payment->logs = \GuzzleHttp\json_encode($response->getData());
                $payment->save();
                throw new Exception($response->getMessage());
            }
        }
        catch(Exception | InvalidCreditCardException $e){
            $payment->status = 'fail';
            $payment->save();
            throw new Exception('Paystack Gateway: ' . $e->getMessage());
        }
    }

    public function getGateway()
    {
        $this->gateway = Omnipay::create('paystack')->setCredentials('pk_test_d236cd0f648a7429521cad6c3018cd4d920f49e0','sk_test_1a9869113f2ce83341eaa21b3cac176dadd689e6');
       // if ($this->getOption('stripe_enable_sandbox')) {
         //   $this->gateway->setApiKey($this->getOption('stripe_test_secret_key'));
        //}
    }

    public function handlePurchaseData($data, $booking, $request)
    {
        $data['currency'] = setting_item('currency_main');
        $data['token'] = $request->input("token");
        $data['description'] = setting_item("site_title")." - #".$booking->id;
        return $data;
    }

    public function getDisplayHtml()
    {

        $script_inline = "
        var bookingCore_gateways_stripe = {
                stripe_publishable_key:'{$this->getOption('stripe_publishable_key')}',
                stripe_test_publishable_key:'{$this->getOption('stripe_test_publishable_key')}',
                stripe_enable_sandbox:'{$this->getOption('stripe_enable_sandbox')}',
            };";
        Assets::registerJs("https://js.stripe.com/v3/",true);
        Assets::registerJs($script_inline,true,10,false,true);
        Assets::registerJs( asset('module/booking/gateways/stripe.js') ,true);
        $data = [
            'html' => $this->getOption('html', ''),
        ];
        return view("Booking::frontend.gateways.stripe",$data);
    }
}

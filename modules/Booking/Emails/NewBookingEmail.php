<?php
namespace Modules\Booking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class NewBookingEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $booking;
    public $paymentLink;
    protected $email_type;

    public function __construct(Booking $booking,$to = 'admin', $paymentLink = null)
    {
        $this->booking = $booking;
        $this->paymentLink = $paymentLink;
        $this->email_type = $to;
    }

    public function build()
    {

        $subject = '';
        switch ($this->email_type){
            case "admin":
                $subject = __('[:site_name] New booking has been made',['site_name'=>setting_item('site_title')]);
            break;

            case "vendor":
                $subject = __('[:site_name] Your service got new booking',['site_name'=>setting_item('site_title')]);

            break;

            case "customer":
                $subject = __('Thank you for booking with us',['site_name'=>setting_item('site_title')]);
            break;

        }
        return $this->subject($subject)->from(config('mail.from.address'), "Naco Booking")->view('Booking::emails.new-booking')->with([
            'booking' => $this->booking,
            'service' => $this->booking->service,
            'payment_link' => strtolower($this->booking->status) === 'draft' ? $this->paymentLink : "" ,
            'to'=>$this->email_type
        ]);
    }
}

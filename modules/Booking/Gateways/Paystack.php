<?php
namespace Modules\Booking\Gateways;


class Paystack {
    protected $secret_key;
    protected $public_key;

    public function __construct() {
       
        $this->secret_key = 'sk_live_d7f264f74279454ee6fa61951ec567dced309d6e';
        $this->public_key = 'pk_live_5c2a4ad2d65ccf37194fc12fcd1e80c85dc2413f';
        
        
        // $this->secret_key = 'sk_test_1a9869113f2ce83341eaa21b3cac176dadd689e6';
        // $this->public_key = 'pk_live_5c2a4ad2d65ccf37194fc12fcd1e80c85dc2413f';
    }

    private function curl($url, $use_post, $post_data=[]){
        $curl = curl_init();
        
        $headers = [
            "Authorization: Bearer {$this->secret_key}",
            'Content-Type: application/json'
        ];

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        
        if($use_post){
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        }

        //Modify this two lines to suit your needs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);

        $response = curl_exec($curl);

        curl_close($curl);
        
        return $response;
    }
    
    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param string $ref Transaction Reference
     * @param int $amount_in_kobo Amount to be paid (in kobo)
     * @param string $email Customer's email address
     * @param array $metadata_arr An array of metadata to add to transaction
     * @param string $callback_url URL to call in case you want to overwrite the callback_url set on your paystack dashboard
     * @param boolean $return_obj Whether to return the whole Object or just the authorisation URL
     * @return boolean
     */
    public function init($ref, $amount_in_kobo, $email, $metadata_arr=[], $callback_url="", $return_obj=false){        
        if($ref && $amount_in_kobo && $email){
            //https://api.paystack.co/transaction/initialize
            $url = "https://api.paystack.co/transaction/initialize/";
            $post_data = [
                'reference'=>$ref,
                'amount'=>$amount_in_kobo,
                'email'=>$email,
                'metadata'=>json_encode($metadata_arr),
                'callback_url'=>$callback_url
            ];
            //curl($url, $use_post, $post_data=[])
            $response = $this->curl($url, TRUE, $post_data);
            if($response){                
                //return the whole Object if $return_obj is true, otherwise return just the authorization_url
                return $return_obj ? json_decode($response) : json_decode($response)->data->authorization_url;
            }
            
            //api request failed
            return FALSE;
        }
        
        return FALSE;
    }
    
    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param int $amount_in_kobo Amount to be paid (in kobo)
     * @param string $email Customer's email address
     * @param string $plan Plan to subscribe user to
     * @param array $metadata_arr An array of metadata to add to transaction
     * @param string $callback_url URL to call in case you want to overwrite the callback_url set on your paystack dashboard
     * @param boolean $return_obj Whether to return the whole Object or just the authorisation URL
     */
    public function initSubscription($amount_in_kobo, $email, $plan, $metadata_arr=[], $callback_url="", $return_obj=false){        
        if($amount_in_kobo && $email && $plan){
            //https://api.paystack.co/transaction/initialize
            $url = "https://api.paystack.co/transaction/initialize/";
                
            $post_data = [
                'amount'=>$amount_in_kobo,
                'email'=>$email,
                'plan'=>$plan,
                'metadata'=>json_encode($metadata_arr),
                'callback_url'=>$callback_url
            ];

            //curl($url, $use_post, $post_data=[])
            $response = $this->curl($url, TRUE, $post_data);
            
            if($response){                
                //return the whole decoded object if $return_obj is true, otherwise return just the authorization_url
                return $return_obj ? json_decode($response) : json_decode($response);
            }
            //api request failed
            return FALSE;
        }
        
        return FALSE;
    }	
	
    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param type $transaction_reference
     * @return array
     */
    public function verifyTransaction($transaction_reference){
        //https://api.paystack.co/transaction/verify/:reference
        $url = "https://api.paystack.co/transaction/verify/".$transaction_reference;
        
        //curl($url, $use_post, $post_data=[])
        return json_decode($this->curl($url, FALSE));
    }
    
    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    public function chargeReturningCustomer($auth_code, $amount_in_kobo, $email, $ref="", $metadata_arr=[]){
        
        if($auth_code && $amount_in_kobo && $email){
            //https://api.paystack.co/transaction/charge_authorization
            $url = "https://api.paystack.co/transaction/charge_authorization/";
                
            $post_data = [
                'authorization_code'=>$auth_code,
                'amount'=>$amount_in_kobo,
                'email'=>$email,
                'reference'=>$ref,
                'metadata'=>json_encode($metadata_arr)
            ];

            //curl($url, $use_post, $post_data=[])
            $response = $this->curl($url, TRUE, $post_data);
            
            if($response){                
                //return the whole json decoded object 
                return json_decode($response);
            }
            
            //api request failed
            return FALSE;
        }
        
        //required fields are not set
        return FALSE;
    }
    
    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @param string $phone
     * @param Array $meta
     * @return boolean
     */
    public function createCustomer($email, $first_name='', $last_name='', $phone='', $meta=[]){
        //https://api.paystack.co/customer
        $url = "https://api.paystack.co/customer";
        
        if($email && $url){
            $post_data = [
                'email'=>$email,
                'first_name'=>$first_name,
                'last_name'=>$last_name,
                'phone'=>$phone,
                'metadata'=>json_encode($meta)
            ];
            
            //curl($url, $use_post, $post_data=[])
            $response = $this->curl($url, TRUE, $post_data);
            
            //decode the response
            $data = json_decode($response);
            
            if($data && $data->status){                
                //return customer_code and ID
                return ['customer_id'=>$data->data->id, 'customer_code'=>$data->data->customer_code];
            }
            
            //api request failed
            return FALSE;
        }
        
        //required fields are not set
        return FALSE;
    }
}

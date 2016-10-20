<?php
/**
 * MOLPay OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author MOLPay Technical Team <technical@molpay.com>
 * @version 1.5.0
 */

class ControllerPaymentMolpayseamless extends Controller {
    
    protected function index() {
		$this->load->model('checkout/order');

		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	
		$this->data['action'] = $this->url->link('payment/molpayseamless/process_order', '', 'SSL');
		
		$this->data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$this->data['orderid'] = $this->session->data['order_id'];
		$this->data['bill_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
		$this->data['bill_email'] = $order_info['email'];
		$this->data['bill_mobile'] = $order_info['telephone'];
		$this->data['country'] = $order_info['payment_iso_code_2'];
		$this->data['currency'] = $order_info['currency_code'];
		$this->data['vcode'] = md5($this->data['amount'].$this->config->get('molpay_merchantid').$this->data['orderid'].$this->config->get('molpay_verifykey'));
		
		$products = $this->cart->getProducts();
		foreach ($products as $product) {
			$this->data['prod_desc'][]= $product['name']." x ".$product['quantity'];
		}

		$this->data['order_id'] = $this->session->data['order_id'];
		
		$this->data['lang'] = $this->session->data['language'];

		$this->data['error_warning'] = '';

		$this->data['molpay_set_timer'] = $this->config->get('molpay_set_timer');
		
		$this->data['merchant_channels'] = $this->getMerchantListChannel();

		if ($this->config->get('config_checkout_ids')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
	
			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = 'By clicking the Pay Online button, you agree to the <a target="_blank" href="http://molpay.com/v2/terms-of-services">Terms of Service</a> &amp; <a target="_blank" tabindex="25" href="http://molpay.com/v2/privacy-policy">Privacy Policy</a>.';
			}
		} else {
			$this->data['text_agree'] = 'By clicking the Pay Online button, you agree to the <a target="_blank" href="http://molpay.com/v2/terms-of-services">Terms of Service</a> &amp; <a target="_blank" tabindex="25" href="http://molpay.com/v2/privacy-policy">Privacy Policy</a>.';
		}

		if (isset($this->session->data['agree'])) { 
			$this->data['agree'] = $this->session->data['agree'];
		} else {
			$this->data['agree'] = '';
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/molpayseamless.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/molpayseamless.tpl';
		} else {
				$this->template = 'default/template/payment/molpayseamless.tpl';
		}	
	
		$this->render();
    }
 
    public function process_order(){
        /*** Start Processing Submitted Form Above ***/
        if( isset($this->request->post['payment_options']) && $this->request->post['payment_options'] != "" ) {

            $merchantid = $this->config->get('molpay_merchantid');// Change to your merchant ID
            $vkey = $this->config->get('molpay_verifykey');  // Change to your verify key
            
             // Put your own code/process HERE. (Eg: Insert data to DB)
             $your_orderid = $this->request->post['orderid'];
             $your_process_status = true;

             //Add fees on amount
             $fees_opt =  $this->config->get('mpsc_'.$this->request->post['payment_options'].'_feesopt');
             $fees = $this->config->get('mpsc_'.$this->request->post['payment_options'].'_fees');

             $fees_amt = ($fees_opt == 'amt')? $fees : $this->request->post['amount']*($fees/100);

             $grand_amt = $this->request->post['amount'] + $fees_amt;
             
			 if( isset($this->request->post['molpay_set_timer']) ) $settimer = (int)$this->request->post['molpay_set_timer'];
			 else $settimer = 0;
			 
                if( $your_process_status === true ) {
                    $params = array(
                        'status'          => true,  // Set True to proceed with MOLPay
                        'mpsmerchantid'   => $merchantid,
                        'mpschannel'      => $this->request->post['payment_options'],
                        'mpsamount'       => $grand_amt,
                        'mpsorderid'      => $your_orderid,
                        'mpsbill_name'    => $this->request->post['bill_name'],
                        'mpsbill_email'   => $this->request->post['bill_email'],
                        'mpsbill_mobile'  => $this->request->post['bill_mobile'],
                        'mpsbill_desc'    => $this->request->post['bill_desc'],
                        'mpscountry'      => $this->request->post['country'],
                        'mpsvcode'        => md5($grand_amt.$merchantid.$your_orderid.$vkey),
                        'mpscurrency'     => $this->request->post['currency'],
                        'mpslangcode'     => $this->session->data['language'],
						'mpstimer'	  	  => $settimer,
                        'mpstimerbox'     => "#counter",
                        'mpscancelurl'    => $this->url->link('payment/molpayseamless/cancel_order', '', 'SSL'),
                        'mpsreturnurl'    => $this->url->link('payment/molpayseamless/return_ipn', '', 'SSL')
                    );
                } elseif( $your_process_status === false ) {
                    $params = array(
                        'status'          => false,      // Set False to show an error message.
                        'error_code'      => "Your Error Code (Eg: 500)",
                        'error_desc'      => "Your Error Description (Eg: Internal Server Error)",
                        'failureurl'      => $this->url->link('payment/molpayseamless/fail_order', '', 'SSL'),
                    );
                }
            }
            else
            {
                $params = array(
                    'status'          => false,      // Set False to show an error message.
                    'error_code'      => "500",
                    'error_desc'      => "Internal Server Error",
                    'failureurl'      => $this->url->link('payment/molpayseamless/fail_order', '', 'SSL'),
                );
            }
            $this->response->setOutput(json_encode($params));    
    }
    
     public function validate_payment(){
                $this->language->load('checkout/checkout');
                $json = array();

                if ($this->config->get('config_checkout_id')) {
                    $this->load->model('catalog/information');

                    $information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

                    if ($information_info && $this->request->post['agree'] == "false") {
                        $json['error_warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
                    }
		    else{
 			$json['error_warning'] = "";
		    }
                }

            $this->response->setOutput(json_encode($json));
    }
    
    
    public function fail_order(){
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/molpayseamless_fail.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/molpayseamless_fail.tpl';
        } else {
            $this->template = 'default/template/payment/molpayseamless_fail.tpl';
        }
        $this->response->setOutput($this->render());
    }
	
    public function pending_order(){
	if ( file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/molpayseamless_pending.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/molpayseamless_pending.tpl';
	} else {
	    $this->template = 'default/template/payment/molpayseamless_pending.tpl';
	}
	$this->response->setOutput($this->render());
    }
	
	public function cancel_order(){
		$this->data['continue'] = $this->url->link('checkout/cart');
        $this->fail_order();
    }
	
	public function return_ipn(){
		$this->load->model('checkout/order');
		
		$vkey = $this->config->get('molpay_verifykey');

		$_POST['treq']	= 1; // Additional parameter that Merchant need to add.
		
		/********************************
		*Don't change below parameters
		********************************/
		$tranID     =    $_POST['tranID'];
		$orderid    =    $_POST['orderid'];
		$status     =    $_POST['status'];
		$domain     =    $_POST['domain'];
		$amount     =    $_POST['amount'];
		$currency   =    $_POST['currency'];
		$appcode    =    $_POST['appcode'];
		$paydate    =    $_POST['paydate'];
		$skey       =    $_POST['skey'];
		
		/* Snippet code below is the enhancement required
		 * by Merchant to add into their return script in order to
		 * implement backend acknowledge method
		************************************************************/
			while ( list($k,$v) = each($_POST) ) {
				$postData[]= $k."=".$v;
			}

			$postdata = implode("&",$postData);
			$url = "https://www.onlinepayment.com.my/MOLPay/API/chkstat/returnipn.php";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST , 1 );
			curl_setopt($ch, CURLOPT_POSTFIELDS , $postdata );
			curl_setopt($ch, CURLOPT_URL , $url );
			curl_setopt($ch, CURLOPT_HEADER , 1 );
			curl_setopt($ch, CURLINFO_HEADER_OUT , TRUE );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1 );
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE );
			//curl_setopt($ch, CURLOPT_SSLVERSION , CURL_SSLVERSION_TLSv1 );
			$result = curl_exec( $ch );
			curl_close( $ch );
		
		/***********************************************************
		* To verify the data integrity sending by MOLPay
		************************************************************/
		$key0 = md5( $tranID.$orderid.$status.$domain.$amount.$currency );
		$key1 = md5( $paydate.$domain.$key0.$appcode.$vkey );

		if( $skey != $key1 ) $status= -1; // Invalid transaction. 
		// Merchant might issue a requery to MOLPay to double check payment status with MOLPay.

		if ( $status == "00" ) { //Success
			
			$order_status_id = $this->config->get('molpay_success_status_id');
			$this->model_checkout_order->confirm($orderid, $order_status_id);
		
			$this->data['continue'] = $this->url->link('checkout/success');
			$this->pending_order();
			
		} elseif( $status == "22") { //Pending 
		
			$order_status_id = $this->config->get('molpay_pending_status_id');
			$this->model_checkout_order->confirm($orderid, $order_status_id);
			
			$this->data['continue'] = $this->url->link('checkout/success');
			$this->pending_order();
					
			
		} elseif( $status == "11" || $status == "-1" ) { //Failed
		
			$order_status_id = $this->config->get('molpay_failed_status_id');
			$this->model_checkout_order->confirm($orderid, $order_status_id);
			
			$this->data['continue'] = $this->url->link('checkout/cart');
			$this->fail_order();
		}
	} 
	
	public function callback_ipn(){
		$this->load->model('checkout/order');
		
		$vkey = $this->config->get('molpay_verifykey');

		/********************************
		*Don't change below parameters
		********************************/

		$nbcb 		= $_POST['nbcb'];
		$tranID 	= $_POST['tranID'];
		$orderid 	= $_POST['orderid'];
		$status 	= $_POST['status'];
		$domain 	= $_POST['domain'];
		$amount 	= $_POST['amount'];
		$currency 	= $_POST['currency'];
		$appcode 	= $_POST['appcode'];
		$paydate 	= $_POST['paydate'];
		$skey 		= $_POST['skey'];
		$err_code	= $_POST['err_code'];
		$err_desc	= $_POST['err_desc'];
		
		/***********************************************************
		* To verify the data integrity sending by MOLPay
		************************************************************/

		$key0 = md5( $tranID.$orderid.$status.$domain.$amount.$currency );
		$key1 = md5( $paydate.$domain.$key0.$appcode.$vkey );
		if( $skey != $key1 ) $status= -1; // Invalid transaction
		
		if ( $status == "00" ) { //Success
			
			$order_status_id = $this->config->get('molpay_success_status_id');
			$this->model_checkout_order->confirm($orderid, $order_status_id);
													
		} else { //Failed
		
			$order_status_id = $this->config->get('molpay_failed_status_id');
			$this->model_checkout_order->confirm($orderid, $order_status_id);
			
		}

		if ( $nbcb==1 ) {
			//IPN feedback to notified MOLPay
			echo "CBTOKEN:MPSTATOK";
		}
	}

    protected function getMerchantListChannel(){
        //payment-xxx.jpg - please replace img file which available
        $arr_channel = array(
        		array	(   "affinonline"	    ,   "Affin Bank(Affin Online) "								, "payment-affin.jpg" ),
				array	(   "amb"		        ,   "Am Bank (Am Online)" 									, "payment-amonline.jpg" ),
				array	(   "bankislam"		    ,   "Bank Islam" 				    						, "payment-bank-islam.jpg" ),
				array	(   "cimbclicks"	    ,   "CIMB Bank(CIMB Clicks)"								, "payment-cimb.jpg" ),
				array	(   "hlb"		        ,   "Hong Leong Bank(HLB Connect)"							, "payment-hlb.jpg" ),
				array	(   "maybank2u"		    ,   "Maybank(Maybank2u)" 									, "payment-m2u.jpg" ),
				array	(   "pbb"		    	,   "PublicBank (PBB Online)"								, "payment-pbe.jpg" ),
				array	(   "rhb"		    	,   "RHB Bank(RHB Now)" 									, "payment-rhb.jpg" ),
				array	(   "fpx"		    	,   "MyClear FPX"											, "payment-fpx.jpg" ),
				array	(   "fpx_amb"		    ,   "FPX Am Bank (Am Online)" 								, "payment-amonline.jpg" ),
				array	(   "fpx_bimb"		    ,   "FPX Bank Islam" 										, "payment-bank-islam.jpg" ),
				array	(   "fpx_cimbclicks"    ,   "FPX CIMB Bank(CIMB Clicks)"	 						, "payment-cimb.jpg" ),
				array	(   "fpx_hlb"		    ,   "FPX Hong Leong Bank(HLB Connect)" 						, "payment-hlb.jpg" ),
				array	(   "fpx_mb2u"		    ,   "FPX Maybank(Maybank2u)" 								, "payment-m2u.jpg" ),
				array	(   "fpx_pbb"		    ,   "FPX PublicBank (PBB Online)" 							, "payment-pbe.jpg" ),
				array	(   "fpx_rhb"		    ,   "FPX RHB Bank(RHB Now)" 								, "payment-rhb.jpg" ),
				array	(   "molwallet"		    ,   "MOLWallet" 											, "payment-molwallet.jpg" ),
				array	(   "cash-71"		    ,   "7-Eleven(MOLPay Cash)" 								, "payment-7e.jpg" ),
				array	(   "credit"		    ,   "Credit Card/ Debit Card" 								, "payment-credit.jpg" ),
				array	(   "ATMVA"		    	,   "ATM Transfer via Permata Bank" 						, "payment-ATMVA.jpg" ),
				array	(   "dragonpay"		    ,   "Dragonpay" 											, "payment-dragonpay.jpg" ),
				array	(   "paysbuy"		    ,   "PaysBuy" 												, "payment-paysbuy.jpg" ),
				array	(   "Point-BCard"	    ,   "Bcard points" 									 		, "payment-bcard.jpg" ),
				array	(   "credit3"		    ,   "Credit Card/ Debit Card" 								, "payment-credit.jpg" ),
				array	(   "NGANLUONG"		    ,   "NGANLUONG" 											, "payment-nganluong.jpg" ),
				array	(   "crossborder"	    ,   "Credit Card/ Debit Card" 								, "payment-credit.jpg" ),
				array	(   "paypal"		    ,   "PayPal" 								        		, "payment-xxx.jpg" ),
				array	(   "enetsD"		    ,   "eNETS" 												, "payment-xxx.jpg" ),
				array	(   "UPOP"		    	,   "China Union pay" 										, "payment-xxx.jpg" ),
				array	(   "alipay"		    ,   "Alipay.com"  											, "payment-xxx.jpg" ),
				array	(   "polipayment"	    ,   "POLi Payment" 											, "payment-xxx.jpg" ),
				array	(   "cash-epay"		    ,   "e-pay" 												, "payment-xxx.jpg" ),
				array	(   "WEBCASH"		    ,   "WEBCASH "												, "payment-webcash.jpg" ),
				array	(   "PEXPLUS"		    ,   "PEx+"													, "payment-pex.jpg" ),
				array	(   "jompay"		    ,   "JOMPay" 												, "payment-jompay.jpg" ),
				array	(   "Cash-Esapay"	    ,   "Cash Esapay" 											, "payment-esapay.jpg" ),
				array	(   "TH_PB_SCBPN"	    ,   "Paysbuy SCBPN" 								        , "payment-paysbuy.jpg" ),
				array	(   "TH_PB_KTBPN"	    ,   "Paysbuy KTBPN" 										, "payment-paysbuy.jpg" ),
				array	(   "TH_PB_BBLPN"	    ,   "Paysbuy BBLPN" 										, "payment-paysbuy.jpg" ),
				array	(   "TH_PB_BAYPN"	    ,   "Paysbuy BAYPN" 										, "payment-paysbuy.jpg" ),
				array	(   "TH_PB_CASH"	    ,   "Paysbuy CASH" 											, "payment-paysbuy.jpg" ),
				array	(   "FPX_OCBC"		    ,   "FPX OCBC Bank"											, "payment-ocbc.jpg" ),
				array	(   "FPX_SCB"		    ,   "FPX Standard Chartered Bank" 						    , "payment-standard-chartered.jpg" ),
				array	(   "FPX_ABB"		    ,   "FPX Affin Bank Berhad" 								, "payment-affin.jpg" )
        );

        $ch_info = array();
        foreach( $arr_channel as $kp => $vp ){
            $code_channel = strtolower($vp[0]);
            if( $this->config->get('mpsc_'.$code_channel) == 'on'){
                $ch_info[$code_channel] = array(
                    $vp[1],
                    '', // - use to have values need to be passed. currently not use
                    '', // - have to do this to avoid error array offset
                    '', // 
                    '', // 
                    $vp[2]
                );
            }
        }

        return $ch_info;
    }
}
?>

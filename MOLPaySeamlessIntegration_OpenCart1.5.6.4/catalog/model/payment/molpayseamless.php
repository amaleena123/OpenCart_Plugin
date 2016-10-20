<?php 
/**
 * MOLPay OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author MOLPay Technical Team <technical@molpay.com>
 * @version 1.5.0
 */

class ModelPaymentMolpayseamless extends Model {
    
    public function getMethod($address) {
        $this->load->language('payment/molpayseamless');

        $status = $this->config->get('molpayseamless_status');
		
        $method_data = array();
	
        if ($status) {  
            $method_data = array( 
                'code'       => 'molpayseamless',
                'title'      => $this->language->get('text_title'),
                'sort_order' => $this->config->get('molpay_sort_order')
                );
    	}
   
    	return $method_data;
    }
}
?>
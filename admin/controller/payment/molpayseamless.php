<?php
/**
 * MOLPay OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author MOLPay Technical Team <technical@molpay.com>
 * @version 1.5.0
 */
 
class ControllerPaymentMolpayseamless extends Controller {
    
    private $error = array(); 

    public function index() {
        $this->load->language('payment/molpayseamless');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_setting_setting->editSetting('molpayseamless', $this->request->post);				
            $this->session->data['success'] = $this->language->get('text_success');
            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
            
        }
        
        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_all_zones'] = $this->language->get('text_all_zones');
        $this->data['text_yes'] = $this->language->get('text_yes');
        $this->data['text_no'] = $this->language->get('text_no');

        $this->data['entry_merchantid'] = $this->language->get('entry_merchantid');
        $this->data['entry_verifykey'] = $this->language->get('entry_verifykey');
        $this->data['entry_order_status'] = $this->language->get('entry_order_status');
        $this->data['entry_pending_status'] = $this->language->get('entry_pending_status');
        $this->data['entry_success_status'] = $this->language->get('entry_success_status');
        $this->data['entry_failed_status'] = $this->language->get('entry_failed_status');	
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_set_timer'] = $this->language->get('entry_set_timer');
        $this->data['entry_channel'] = $this->language->get('entry_channel');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        $this->data['tab_general'] = $this->language->get('tab_general');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['account'])) {
            $this->data['error_merchantid'] = $this->error['account'];
        } else {
            $this->data['error_merchantid'] = '';
        }	

        if (isset($this->error['secret'])) {
            $this->data['error_verifykey'] = $this->error['secret'];
        } else {
            $this->data['error_verifykey'] = '';
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/molpayseamless', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/molpayseamless', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['molpay_merchantid'])) {
            $this->data['molpay_merchantid'] = $this->request->post['molpay_merchantid'];
        } else {
            $this->data['molpay_merchantid'] = $this->config->get('molpay_merchantid');
        }

        if (isset($this->request->post['molpay_verifykey'])) {
            $this->data['molpay_verifykey'] = $this->request->post['molpay_verifykey'];
        } else {
            $this->data['molpay_verifykey'] = $this->config->get('molpay_verifykey');
        }

        if (isset($this->request->post['molpay_order_status_id'])) {
            $this->data['molpay_order_status_id'] = $this->request->post['molpay_order_status_id'];
        } else {
            $this->data['molpay_order_status_id'] = $this->config->get('molpay_order_status_id'); 
        }

        if (isset($this->request->post['molpay_pending_status_id'])) {
            $this->data['molpay_pending_status_id'] = $this->request->post['molpay_pending_status_id'];
        } else {
            $this->data['molpay_pending_status_id'] = $this->config->get('molpay_pending_status_id');
        }

        if (isset($this->request->post['molpay_success_status_id'])) {
            $this->data['molpay_success_status_id'] = $this->request->post['molpay_success_status_id'];
        } else {
            $this->data['molpay_success_status_id'] = $this->config->get('molpay_success_status_id');
        }

        if (isset($this->request->post['molpay_failed_status_id'])) {
            $this->data['molpay_failed_status_id'] = $this->request->post['molpay_failed_status_id'];
        } else {
            $this->data['molpay_failed_status_id'] = $this->config->get('molpay_failed_status_id');
        }

        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['molpayseamless_status'])) {
            $this->data['molpayseamless_status'] = $this->request->post['molpayseamless_status'];
        } else {
            $this->data['molpayseamless_status'] = $this->config->get('molpayseamless_status');
        }

        if (isset($this->request->post['molpay_sort_order'])) {
            $this->data['molpay_sort_order'] = $this->request->post['molpay_sort_order'];
        } else {
            $this->data['molpay_sort_order'] = $this->config->get('molpay_sort_order');
        }
		
		if (isset($this->request->post['molpay_set_timer'])) {
            $this->data['molpay_set_timer'] = $this->request->post['molpay_set_timer'];
        } else {
            $this->data['molpay_set_timer'] = $this->config->get('molpay_set_timer');
        }

        #list out the channel at admin > payment
        $this->data['molpay_seamless_channels'] = $this->getListChannel();
        //mpsc_channelname_min, mpsc_channelname_max, mpsc_channelname_fee, mpsc_channelname_perc, mpsc_channelname_amt
        $count_channel = count($this->getListChannel());
        $arr_chnl = $this->getListChannel();
        #get value back according to form
        for($ai=0; $ai<$count_channel; $ai++){
            $chnlname = strtolower($arr_chnl[$ai][0]);
            if (isset($this->request->post['mpsc_'.$chnlname])) {
                $this->data['mpsc_'.$chnlname] = $this->request->post['mpsc_'.$chnlname];
            } else {
                $this->data['mpsc_'.$chnlname] = $this->config->get('mpsc_'.$chnlname);
            }
        }

        $this->layout = 'common/layout';
        $this->template = 'payment/molpayseamless.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    protected function getListChannel(){
	$arr_channel = array(
				array("affinonline" 	, "Affin Bank(Affin Online)"),
				array("amb"         	, "Am Bank (Am Online)"),
				array("bankislam"   	, "Bank Islam"),
				array("cimbclicks"  	, "CIMB Bank(CIMB Clicks)"),
				array("hlb"         	, "Hong Leong Bank(HLB Connect)"),
				array("maybank2u"   	, "Maybank(Maybank2u)"),
				array("pbb"         	, "PublicBank (PBB Online)"),
				array("rhb"         	, "RHB Bank(RHB Now)"),
				array("fpx"         	, "MyClear FPX (Maybank2u, CIMB Clicks, HLB Connect, RHB Now, PBB Online, Bank Islam)" ),
				array("fpx_amb"     	, "FPX Am Bank (Am Online)"),
				array("fpx_bimb"    	, "FPX Bank Islam"),
				array("fpx_cimbclicks" 	, "FPX CIMB Bank(CIMB Clicks)"),
				array("fpx_hlb" 	, "FPX Hong Leong Bank(HLB Connect)"),
				array("fpx_mb2u" 	, "FPX Maybank(Maybank2u)"),
				array("fpx_pbb" 	, "FPX PublicBank (PBB Online)"),
				array("fpx_rhb" 	, "FPX RHB Bank(RHB Now)"),
				array("FPX_OCBC"	, "FPX OCBC Bank"),
				array("FPX_SCB"		, "FPX Standard Chartered"),
				array("FPX_ABB"		, "FPX Affin Bank Berhad"),
				array("molwallet" 	, "MOLWallet"),
				array("cash-711" 	, "7-Eleven(MOLPay Cash)"),
				array("credit" 		, "Credit Card/ Debit Card"),
				array("ATMVA" 		, "ATM Transfer via Permata Bank"),
				array("dragonpay" 	, "Dragonpay"),
				array("paysbuy" 	, "PaysBuy"),
				array("Point-BCard" 	, "Bcard points"),
				array("credit3" 	, "Credit Card/ Debit Card Multi Currency" ),
				array("NGANLUONG" 	, "NGANLUONG"),
				array("crossborder" 	, "Credit Card/ Debit Card Multi Currency"),
				array("paypal" 		, "PayPal"),
				array("enetsD" 		, "eNETS"),
				array("UPOP" 		, "China Union pay"),
				array("alipay" 		, "Alipay.com"),
				array("polipayment" 	, "POLi Payment"),
				array("cash-epay" 	, "e-pay"),
				array("WEBCASH" 	, "WEBCASH"),
				array("PEXPLUS" 	, "PEx+"),
				array("jompay" 		, "JOMPay"),
				array("Cash-Esapay" 	, "Cash Esapay"),
				array("TH_PB_SCBPN" 	, "Paysbuy SCBPN"),
				array("TH_PB_KTBPN" 	, "Paysbuy KTBPN"),
				array("TH_PB_BBLPN" 	, "Paysbuy BBLPN"),
				array("TH_PB_BAYPN" 	, "Paysbuy BAYPN"),
				array("TH_PB_CASH" 	, "Paysbuy CASH")
	);        

        return $arr_channel;

    }
}
?>

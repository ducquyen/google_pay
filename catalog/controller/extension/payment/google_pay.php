<?php
class ControllerExtensionPaymentGooglePay extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('extension/payment/google_pay');

		$this->load->model('checkout/order');
		$this->load->model('extension/payment/google_pay');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['total_price'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$data['currency_code'] = $order_info['currency_code'];

		$_config = new Config();
		$_config->load('google_pay');
		$config_setting = ($_config->get('payment_google_pay_setting')) ? $_config->get('payment_google_pay_setting') : array();
		
		$data['api_version_major'] = $config_setting['api_version_major'];
		$data['api_version_minor'] = $config_setting['api_version_minor'];
		
		$data['merchant_id'] = $this->config->get('payment_google_pay_merchant_id');
		$data['merchant_name'] = $this->config->get('payment_google_pay_merchant_name');
		$data['environment'] = strtoupper($this->config->get('payment_google_pay_environment'));
		$data['debug'] = $this->config->get('payment_google_pay_debug');
		$data['accept_prepay_cards'] = $this->config->get('payment_google_pay_accept_prepay_cards');
		$data['button_color'] = $this->config->get('payment_google_pay_button_color');
		$data['button_type'] = $this->config->get('payment_google_pay_button_type');
		
		$merchant_gateway_code = $this->config->get('payment_google_pay_merchant_gateway_code');
		$merchant_gateway = $this->config->get('payment_google_pay_merchant_gateway');
		$card_networks_code = $this->config->get('payment_google_pay_card_networks_code');
		$auth_methods_code = $this->config->get('payment_google_pay_auth_methods_code');
		
		$parameters = array();
		
		if ($merchant_gateway_code == 'example_gateway') {
			$parameters = array(
				'gateway' => 'example',
				'gatewayMerchantId' => $merchant_gateway[$merchant_gateway_code]['field']['example_gateway_merchant_id']
			);
		} elseif ($merchant_gateway_code == 'braintree') {
			$parameters = array(
				'gateway' => 'braintree',
				'braintree:apiVersion' => $merchant_gateway[$merchant_gateway_code]['field']['braintree_api_version'],
				'braintree:sdkVersion' => $merchant_gateway[$merchant_gateway_code]['field']['braintree_sdk_version'],
				'braintree:merchantId' => $merchant_gateway[$merchant_gateway_code]['field']['braintree_api_version'],
				'braintree:clientKey' => $merchant_gateway[$merchant_gateway_code]['field']['braintree_api_version']
			);
		} elseif ($merchant_gateway_code == 'globalpayments') {
			$parameters = array(
				'gateway' => 'globalpayments',
				'gatewayMerchantId' => $merchant_gateway[$merchant_gateway_code]['field']['globalpayments_merchant_id']
			);
		} elseif ($merchant_gateway_code == 'worldpay') {
			$parameters = array(
				'gateway' => 'worldpay',
				'gatewayMerchantId' => $merchant_gateway[$merchant_gateway_code]['field']['worldpay_merchant_id']
			);
		}
		
		$data['tokenization_specification'] = array(
			'type' => 'PAYMENT_GATEWAY',
			'parameters' => $parameters
		);
	
		$data['allowed_card_networks'] = array();
		
		foreach ($card_networks_code as $card_network_code) {
			$data['allowed_card_networks'][] = strtoupper($card_network_code);
		}
				
		$data['allowed_card_auth_methods'] = array();
		
		foreach ($auth_methods_code as $auth_method_code) {
			$data['allowed_card_auth_methods'][] = strtoupper($auth_method_code);
		}
		
		return $this->load->view('extension/payment/google_pay', $data);
	}
	
	public function send() {
		$this->load->language('extension/payment/google_pay');
		
		$this->load->model('checkout/order');
		
		if (isset($this->request->post['data'])) {
			$json_data = json_decode(htmlspecialchars_decode($this->request->post['data']), true);
		}
		
		print_R($json_data);
		
		/*if (utf8_strlen($this->request->post['cc_number']) < 1) {
			$this->error['warning'] = $this->language->get('error_warning');
			$this->error['cc_number'] = $this->language->get('error_cc_number');
		}
		
		if (utf8_strlen($this->request->post['cc_cvv2']) < 1) {
			$this->error['warning'] = $this->language->get('error_warning');
			$this->error['cc_cvv2'] = $this->language->get('error_cc_cvv2');
		}*/
		
		if (!$this->error) {						
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_pp_pro_order_status_id'), $message, false);
		}
				
		if (!$this->error) {
			$data['success'] = $this->url->link('checkout/success');
		}
		
		if ($this->error) {
			$data['confirm'] = true;
		}
		
		$data['error'] = $this->error;
				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class ControllerExtensionPaymentOctifi extends Controller {
	public function index() {
	   	$this->load->model('extension/payment/octifi');
		$this->model_extension_payment_octifi->createtable();
		$this->load->model('account/customer');
		$data['store_name']=$this->config->get('config_name');
		$data['display_name']=$this->session->data['shipping_address']['lastname']." ".$this->session->data['shipping_address']['firstname'];
		$data['shiping_fristname']=$this->session->data['shipping_address']['firstname'];
		$data['shiping_lastname']=$this->session->data['shipping_address']['lastname'];
		$data['bill_fristname']=$this->session->data['payment_address']['firstname'];
		$data['bill_lastname']=$this->session->data['payment_address']['lastname'];
		$data['order_id']=$this->session->data['order_id'];
		$data['currency']=$this->session->data['currency'];
		$data['public_api_key']=$this->config->get('payment_octifi_public_key');
		//print_r($this->config->get('payment_octifi_test_mode'));die();
		if(isset($this->session->data['guest']))
		{
		  $data["email"]=$this->session->data['guest']['email'];
		$data['telephone']=$this->session->data['guest']['telephone'];
		   // echo"<pre>"; print_r($data);echo"</pre>";die();
		}
		else{
		 $customer_info=$this->model_account_customer->getCustomer($this->session->data['customer_id']);
		$data["email"]=$customer_info['email'];
		$data['telephone']=$customer_info['telephone'];
		}

		$data['callbackurl']=$this->url->link('extension/payment/octifi/callback');
		
					// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;
			
			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$data['totals'] = array();

			foreach ($totals as $total) {
				if($total['title']=="Total")
				{
				$data['totals']=$total['value'];
				}
			}
			//echo "<pre>"; print_r($data['totals']); echo "</pre>";die();

		


		//$this->session->data
		
		$data['header'] = $this->load->controller('common/header');
		return $this->load->view('extension/payment/octifi', $data);
	}

	public function callback() {
		// Totals
		$testmode=$this->config->get('payment_octifi_test_mode');
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;
			
			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$data['totals'] = array();

			foreach ($totals as $total) {
				if($total['title']=="Total")
				{
				$data['totals']=$total['value'];
				}
			}
			

		$returendata=base64_decode($this->request->get['data']);
		$php_formate=json_decode($returendata);

		$fild1=$php_formate->checkout_token;
		if($this->config->get('payment_octifi_payment_action') == 1)
		{
			$capture=false;
		}
		else
		{
			$capture=true;
		}
		if($testmode==1)
		{
			$url="https://k2.octifi.me/api/v1/charge/create/".$fild1."/";
		}
		else
		{
			$url="https://k2.octifi.com/api/v1/charge/create/".$fild1."/";
		}
		
		$fild=array('bill_total_amount' => $data['totals'],
					 "bill_tax_amount"=> 0,
					'bill_currency' => "SGD",
					"is_capture"=> $capture,

	);


$payload = json_encode($fild);
	
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Set HTTP Header for POST request 
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
"Content-Type: application/json",
     'Accept: application/json',
	"Authorization: Api-Key " . $this->config->get('payment_octifi_private_key'),

)
);

// Submit the POST request
$result = curl_exec($ch);

$input=json_decode($result);
//echo "<pre>"; print_r($input); echo "</pre>";die();
$statues_code=$input->status_code;
//$error=$input->errors->non_field_errors;
		if(isset($input->errors->non_field_errors))
		{
			$adddata=array('order_id' => $this->session->data['order_id'],
						'txn_number' =>  "",
						'result'    => "",
						'message'   =>  "",
						'charge_id'  =>"",
						'checkout_token' =>$fild1,
						'payment_action' =>$this->config->get('payment_octifi_payment_action'),
			);
			$this->load->model('extension/payment/octifi');

			$this->model_extension_payment_octifi->addDatabase($adddata);
			$json = array();
			if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'octifi') {
				$this->load->model('checkout/order');
				$this->session->data['error_octifi']=$error;
				$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 7);
				$this->response->redirect($this->url->link('checkout/success', '', true));
				
			}
		}
		elseif($php_formate->statuscode==200 AND $statues_code==200 )
		{
			$adddata=array('order_id' => $this->session->data['order_id'],
						'txn_number' =>  $input->data->order_payment_details->data->txn_number,
						'result'    => $input->data->order_payment_details->result,
						'message'   =>  $input->data->order_payment_details->message,
						'charge_id'  => $input->data->charge_id,
						'checkout_token' =>$fild1,
						'payment_action' =>$this->config->get('payment_octifi_payment_action'),
			);
			$this->load->model('extension/payment/octifi');

			$this->model_extension_payment_octifi->addDatabase($adddata);
			$json = array();
			if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'octifi') {
				$this->load->model('checkout/order');

				$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_octifi_order_status_id'));
		
				//$json['redirect'] = $this->url->link('checkout/success');
				$this->response->redirect($this->url->link('checkout/success', '', true));
				//echo "<pre>"; print_r($this->session->data['payment_method']['code']); echo "</pre>";die("pp");
			}

		}
		else{
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
			//$json['redirect'] = $this->url->link('checkout/checkout');
		}
	}
}

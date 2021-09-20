<?php
class ControllerExtensionPaymentOctifi extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/octifi');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			//echo "<pre>"; print_r($this->request->post); echo "</pre>";die();
			$this->model_setting_setting->editSetting('payment_octifi', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/cod', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/octifi', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		//echo "<pre>"; print_r($this->config->get('payment_octifi_test_mode')); echo "</pre>";die();
		if (isset($this->request->post['payment_octifi_payment_action'])) {
			$data['payment_octifi_payment_action'] = $this->request->post['payment_octifi_payment_action'];
		} else {
			$data['payment_octifi_payment_action'] = $this->config->get('payment_octifi_payment_action');
		}
		if (isset($this->request->post['payment_octifi_instalment_text'])) {
			$data['payment_octifi_instalment_text'] = $this->request->post['payment_octifi_instalment_text'];
		} else {
			$data['payment_octifi_instalment_text'] = $this->config->get('payment_octifi_instalment_text');
		}
		if (isset($this->request->post['payment_octifi_test_mode'])) {
			$data['payment_octifi_test_mode'] = $this->request->post['payment_octifi_test_mode'];
		} else {
			$data['payment_octifi_test_mode'] = $this->config->get('payment_octifi_test_mode');
		}
		//echo "<pre>"; print_r($data['payment_octifi_test_mode']); echo "</pre>";die("111");
		if (isset($this->request->post['payment_octifi_total'])) {
			$data['payment_octifi_total'] = $this->request->post['payment_octifi_total'];
		} else {
			$data['payment_octifi_total'] = $this->config->get('payment_octifi_total');
		}

		if (isset($this->request->post['payment_octifi_order_status_id'])) {
			$data['payment_octifi_order_status_id'] = $this->request->post['payment_octifi_order_status_id'];
		} else {
			$data['payment_octifi_order_status_id'] = $this->config->get('payment_octifi_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_octifi_geo_zone_id'])) {
			$data['payment_octifi_geo_zone_id'] = $this->request->post['payment_octifi_geo_zone_id'];
		} else {
			$data['payment_octifi_geo_zone_id'] = $this->config->get('payment_octifi_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_octifi_status'])) {
			$data['payment_octifi_status'] = $this->request->post['payment_octifi_status'];
		} else {
			$data['payment_octifi_status'] = $this->config->get('payment_octifi_status');
		}
		if (isset($this->request->post['payment_octifi_private_key'])) {
			$data['payment_octifi_private_key'] = $this->request->post['payment_octifi_private_key'];
		} else {
			$data['payment_octifi_private_key'] = $this->config->get('payment_octifi_private_key');
		}
		if (isset($this->request->post['payment_octifi_public_key'])) {
			$data['payment_octifi_public_key'] = $this->request->post['payment_octifi_public_key'];
		} else {
			$data['payment_octifi_public_key'] = $this->config->get('payment_octifi_public_key');
		}

		if (isset($this->request->post['payment_octifi_sort_order'])) {
			$data['payment_octifi_sort_order'] = $this->request->post['payment_octifi_sort_order'];
		} else {
			$data['payment_octifi_sort_order'] = $this->config->get('payment_octifi_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/octifi', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/cod')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}

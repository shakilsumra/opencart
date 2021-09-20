<?php
class ModelExtensionPaymentOctifi extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/octifi');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_octifi_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payment_octifi_total') > 0 && $this->config->get('payment_octifi_total') > $total) {
			$status = false;
		} elseif (!$this->cart->hasShipping()) {
			$status = false;
		} elseif (!$this->config->get('payment_octifi_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'octifi',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_octifi_sort_order')
			);
		}

		return $method_data;
	}
	public function addDatabase($data)
	{
		$this->db->query("INSERT INTO " . DB_PREFIX . "octifi SET order_id = '" . $this->db->escape($data['order_id']) . "', txn_number = '" . $this->db->escape($data['txn_number']) . "', result = '" . $this->db->escape($data['result']) . "', message = '" . $this->db->escape($data['message']) . "', charge_id = '" . $this->db->escape($data['charge_id']) . "', checkout_tocken = '" . $this->db->escape($data['checkout_token']) . "', payment_action = '" . $this->db->escape($data['payment_action']) . "'");
	}
	public function createtable()
	{

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "octifi` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL,
	`txn_number` varchar(255) COLLATE utf8_bin NOT NULL,
	`result` int(11) NOT NULL,
	`message` varchar(255) COLLATE utf8_bin NOT NULL,
	`charge_id` varchar(255) COLLATE utf8_bin NOT NULL,
	`checkout_tocken` varchar(255) COLLATE utf8_bin NOT NULL,
	`payment_action` int(11) NOT NULL,
	PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
	}
}



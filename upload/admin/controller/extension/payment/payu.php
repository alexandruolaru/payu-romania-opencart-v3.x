<?php
class ControllerExtensionPaymentPayu extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('extension/payment/payu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_payu', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['entry_merchant_code'] = $this->language->get('entry_merchant_code');
		$data['entry_secret_key'] = $this->language->get('entry_secret_key');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_sale'] = $this->language->get('text_sale');

		$data['entry_signature'] = $this->language->get('entry_signature');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['help_total'] = $this->language->get('help_total');

		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_order_status_authorized'] = $this->language->get('entry_order_status_authorized');
		$data['entry_order_status_received'] = $this->language->get('entry_order_status_received');
		$data['entry_order_status_complete'] = $this->language->get('entry_order_status_complete');


		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_payment_title'] = $this->language->get('entry_payment_title');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant_code'])) {
			$data['error_merchant_code'] = $this->error['merchant_code'];
		} else {
			$data['error_merchant_code'] = '';
		}

		if (isset($this->error['secret_key'])) {
			$data['error_secret_key'] = $this->error['secret_key'];
		} else {
			$data['error_secret_key'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/payu', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['action'] = $this->url->link('extension/payment/payu', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_payu_merchant_code'])) {
			$data['payment_payu_merchant_code'] = $this->request->post['payment_payu_merchant_code'];
		} else {
			$data['payment_payu_merchant_code'] = $this->config->get('payment_payu_merchant_code');
		}

		if (isset($this->request->post['payment_payu_secret_key'])) {
			$data['payment_payu_secret_key'] = $this->request->post['payment_payu_secret_key'];
		} else {
			$data['payment_payu_secret_key'] = $this->config->get('payment_payu_secret_key');
		}

		if (isset($this->request->post['payment_payu_total'])) {
			$data['payment_payu_total'] = $this->request->post['payment_payu_total'];
		} else {
			$data['payment_payu_total'] = $this->config->get('payment_payu_total');
		}

		if (isset($this->request->post['payment_payu_test'])) {
			$data['payment_payu_test'] = $this->request->post['payment_payu_test'];
		} else {
			$data['payment_payu_test'] = $this->config->get('payment_payu_test');
		}

		if (isset($this->request->post['payment_payu_order_status_authorized_id'])) {
			$data['payment_payu_order_status_authorized_id'] = $this->request->post['payment_payu_order_status_authorized_id'];
		} else {
			$data['payment_payu_order_status_authorized_id'] = $this->config->get('payment_payu_order_status_authorized_id');
		}

		if (isset($this->request->post['payment_payu_order_status_received_id'])) {
			$data['payment_payu_order_status_received_id'] = $this->request->post['payment_payu_order_status_received_id'];
		} else {
			$data['payment_payu_order_status_received_id'] = $this->config->get('payment_payu_order_status_received_id');
		}
		if (isset($this->request->post['payment_payu_order_status_complete_id'])) {
			$data['payment_payu_order_status_complete_id'] = $this->request->post['payment_payu_order_status_complete_id'];
		} else {
			$data['payment_payu_order_status_complete_id'] = $this->config->get('payment_payu_order_status_complete_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_payu_geo_zone_id'])) {
			$data['payment_payu_geo_zone_id'] = $this->request->post['payment_payu_geo_zone_id'];
		} else {
			$data['payment_payu_geo_zone_id'] = $this->config->get('payment_payu_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_payu_status'])) {
			$data['payment_payu_status'] = $this->request->post['payment_payu_status'];
		} else {
			$data['payment_payu_status'] = $this->config->get('payment_payu_status');
		}

		if (isset($this->request->post['payment_payu_sort_order'])) {
			$data['payment_payu_sort_order'] = $this->request->post['payment_payu_sort_order'];
		} else {
			$data['payment_payu_sort_order'] = $this->config->get('payment_payu_sort_order');
		}
		if (isset($this->request->post['payment_payu_title'])) {
			$data['payment_payu_title'] = $this->request->post['payment_payu_title'];
		} elseif (!empty($this->config->get('payment_payu_title'))) {
			$data['payment_payu_title'] = $this->config->get('payment_payu_title');
		} else {
			$data['payment_payu_title'] = $this->language->get('entry_text_title_payment');
		}
		$ipnURL = HTTPS_CATALOG  . 'index.php?route=extension/payment/payu/ipn';
		$data['ipn_url'] = $ipnURL;
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/payu', $data));
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/payment/payu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_payu_merchant_code']) {
			$this->error['merchant_code'] = $this->language->get('error_merchant_code');
		}

		if (!$this->request->post['payment_payu_secret_key']) {
			$this->error['secret_key'] = $this->language->get('error_secret_key');
		}


		return !$this->error;
	}
}

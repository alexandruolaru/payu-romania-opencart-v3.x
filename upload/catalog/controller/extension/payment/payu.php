<?php

require_once DIR_APPLICATION . 'Payu/LiveUpdate.php';

class ControllerExtensionPaymentPayu extends Controller
{
	public function index()
	{

		if (isset($this->session->data['order_id'])) {
			$data['button_confirm'] = $this->language->get('button_confirm');
			if ($this->config->get('payment_payu_test') == 'live') {
				$sandboxMode = false;
			} else {
				$sandboxMode = true;
			}
			$myLiveUpdate = new LiveUpdate();
			$myMerchantCode = $this->config->get('payment_payu_merchant_code'); // actual merchant ID
			$mySecretKey = htmlspecialchars_decode($this->config->get('payment_payu_secret_key')); // actual secret key
			$payMethod = 'CCVISAMC'; // see the official documentation
			$callbackUrl = $this->url->link('checkout/success'); // The URL where the customer will be redirected after the payment
			$refUrl = $this->url->link('checkout/failure'); // The URL where the customer will be redirected if they cancel the payment
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			if ($order_info) {
				$orderRef = $order_info['order_id'];  // set the order's reference number
				$myOrderDate = $order_info['date_added']; // order date
				$myOrderCurrency = $order_info['currency_code']; // currency code
				$orderLanguage = strtolower($order_info['language_code']);
				$destinationCity = $order_info['shipping_city'];
				$destinationCountryCode = $order_info['shipping_iso_code_2'];
				$billingInfo = array(
					'billFName'         => $order_info['payment_firstname'],
					'billLName'         => $order_info['payment_lastname'],
					'billCISerial'      => '',
					'billCINumber'      => '',
					'billCIIssuer'      => '',
					'billCNP'           => '',
					'billCompany'       => $order_info['payment_company'],
					'billFiscalCode'    => '',
					'billRegNumber'     => '',
					'billBank'          => '',
					'billBankAccount'   => '',
					'billEmail'         => $order_info['email'],
					'billPhone'         => $order_info['telephone'],
					'billFax'           => '',
					'billAddress1'      => $order_info['payment_address_1'],
					'billAddress2'      => $order_info['payment_address_2'],
					'billZipCode'       => $order_info['payment_postcode'],
					'billCity'          => $order_info['payment_city'],
					'billState'         => '',
					'billCountryCode'   => $order_info['payment_iso_code_2']
				);

				$deliveryInfo = array(
					'deliveryFName'         => $order_info['shipping_firstname'],
					'deliveryLName'         => $order_info['shipping_lastname'],
					'deliveryCompany'       => $order_info['shipping_company'],
					'deliveryPhone'         => $order_info['telephone'],
					'deliveryAddress1'      => $order_info['shipping_address_1'],
					'deliveryAddress2'      => $order_info['shipping_address_2'],
					'deliveryZipCode'       => $order_info['shipping_postcode'],
					'deliveryCity'          => $order_info['shipping_city'],
					'deliveryState'         => '',
					'deliveryCountryCode'   => $order_info['shipping_iso_code_2']
				);

				// order products
				$order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);
				$products = array();
				foreach ($order_products as $product) {
					$vat_percent = $product['tax'];
					$vat = ($vat_percent / 100);
					$product_data = [
						'code' => $product['product_id'],
						'name' => $product['name'],
						'price' => $product['price'],
						'priceType' => 'NET',
						'qty' => $product['quantity'],
						'groupId' => '',
						'vat' => $vat
					];

					$products[] = $product_data;
				}
				// shipping cost
				$order_totals = $this->model_checkout_order->getOrderTotals($order_info['order_id']);
				$shipping = 0;
				foreach ($order_totals as $order_total) {
					if ($order_total['code'] === 'shipping') {
						$shipping = $order_total['value'];
						break;
					}
				}
				try {
					$myLiveUpdate
						->setSecretKey($mySecretKey)
						->setMerchant($myMerchantCode)
						->setOrderRef($orderRef)
						->setOrderDate($myOrderDate)
						->setPricesCurrency($myOrderCurrency)
						->setLanguage($orderLanguage)
						->setDestinationCity($destinationCity)
						->setDestinationCountry($destinationCountryCode)
						->setPayMethod($payMethod)
						->setCallbackUrl($callbackUrl)
						->setRefUrl($refUrl);

					foreach ($products as $product) {
						$myLiveUpdate->addProduct($product['code'], $product['name'], $product['price'], $product['qty'], $product['vat'], null, $product['priceType']);
					}

					$myLiveUpdate
						->setOrderShipping($shipping)
						->setBilling($billingInfo)
						->setDelivery($deliveryInfo)
						->setTestMode($sandboxMode);
				} catch (InvalidArgumentException $e) {
					die('<pre>An error has occurred: ' . $e . '</pre>');
				}
				$data['action'] = $myLiveUpdate->getUpdateURL($sandboxMode);
				$data['form'] = $myLiveUpdate->getLiveUpdateHTML();
				return $this->load->view('extension/payment/payu', $data);
			} else {
				die('Order ID not found!');
			}
		}
	}
	public function ipn()
	{
		ini_set("mbstring.func_overload", 0);
		if (ini_get("mbstring.func_overload") > 2) { 
			echo "WARNING: mbstring.func_overload is set to overload strings and might cause problems\n";
		}
		$this->load->model('checkout/order');
		$pass        = htmlspecialchars_decode($this->config->get('payment_payu_secret_key'));
		$result        = "";                
		$return        = "";                
		$signature    = @$_POST["HASH"];   
		$status        = @$_POST["ORDERSTATUS"];
		$reforderno   = @$_POST["REFNOEXT"];
		$body        = "";
		ob_start();
		foreach ($_POST as $key => $val) {
			$$key = $val;
			if ($key != "HASH") {
				if (is_array($val)) {
					$result .= $this->ArrayExpand($val);
				} else {
					$size = strlen(stripslashes($val));
					$result .= $size . stripslashes($val);
				}
			}
		}

		$body = ob_get_contents();
		ob_end_flush();

		$date_return = date("YmdGis");

		$return = strlen(@$_POST["IPN_PID"][0]) . @$_POST["IPN_PID"][0] . strlen(@$_POST["IPN_PNAME"][0]) . @$_POST["IPN_PNAME"][0];
		$return .= strlen(@$_POST["IPN_DATE"]) . @$_POST["IPN_DATE"] . strlen($date_return) . $date_return;

		$hash =  $this->hmac($pass, $result); 

		$body .= $result . "\r\n\r\nHash: " . $hash . "\r\n\r\nSignature: " . $signature . "\r\n\r\nReturnSTR: " . $return;

		if ($hash == $signature) {
			echo "Verified OK!";

			$result_hash =  $this->hmac($pass, $return);
			echo "<EPAYMENT>" . $date_return . "|" . $result_hash . "</EPAYMENT>";
			switch ($status) {
				case "PAYMENT_AUTHORIZED":
					$this->model_checkout_order->addOrderHistory($reforderno, $this->config->get('payment_payu_order_status_authorized_id'));
					break;
				case "PAYMENT_RECEIVED":
					$this->model_checkout_order->addOrderHistory($reforderno, $this->config->get('payment_payu_order_status_received_id'));
					break;
				case "COMPLETE":
					$this->model_checkout_order->addOrderHistory($reforderno, $this->config->get('payment_payu_order_status_complete_id'));
					break;
				default:
			}
		}
	}
	public function status()
	{
		$this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
	}

	private function ArrayExpand($array)
	{
		$retval = "";
		for ($i = 0; $i < sizeof($array); $i++) {
			$size     = strlen(StripSlashes($array[$i]));
			$retval    .= $size . StripSlashes($array[$i]);
		}
		return $retval;
	}

	private function hmac($key, $data)
	{
		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*", md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;
		return md5($k_opad  . pack("H*", md5($k_ipad . $data)));
	}
}

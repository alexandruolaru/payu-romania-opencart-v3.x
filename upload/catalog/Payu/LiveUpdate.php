<?php

/**
 * Class LiveUpdate
 *
 *  * This class generates HTML HTTP POST code for PayU Live Update implementation
 *
 * Last update: March 2018, 9
 * More info: www.payu.ro
 *
 * This class is distributed to PayU partners as an example implementation only.
 * The example is provided "AS IS" and without warranty, express or implied. In no
 * event will GECAD ePayment be liable for any damages, including but not limited to
 * any lost profits, lost savings or any incidental or consequential damages, whether
 * resulting from impaired or lost data, software or computer failure or any other
 * cause, or for any other claim by the user or for any third party claim.
 *
 * You can freely modify the code to fit your needs for implementation.
 *
 * This class will generate the HTML CODE for the HTTP POST request. It will
 * not generate the entire HTML CODE. The <form></form> tags are not included,
 * allowing you to customize your form layout.
 * The class can be modified to include also the form tags. Please use the
 * $liveUpdateURL member for the action property of the form tag.
 *
 * The class has the default behaviour for most situations.
 *
 * The class contains a number of functions to set different variables: setLiveUpdateURL,
 * setTestMode, setLanguage, setSecretKey, setMerchant, setOrderRef, setOrderDate etc.
 * After calling any of these functions please check the return value to see if
 * the function succeeded. The functions will return an error if invalid data types
 * or values are received as parameters. If any of these functions will fail, the method
 * getLiveUpdateHTML might return an error. In case of an error, this method will
 * output an error as an HTML comment like the one below:
 * <!-- PayU ERROR: [error-message] -->
 *
 */
class LiveUpdate
{
    /**
     * @var string This is the URL address where HTTP POST should be sent (Live Update URL)
     */
    private $liveUpdateURL = 'https://secure.payu.ro/order/lu.php';
    private $sandboxUpdateURL = 'https://sandbox.payu.ro/order/lu.php';

    /**
     * @var boolean If "true", it enables the Test Mode
     */
    private $testMode = false;

    /**
     * @var string 2-letters language code. Default: ro
     */
    private $language = 'ro';

    /**
     * @var string Merchant's secret key (used at signature computing)
     */
    private $secretKey;

    /**
     * @var string Merchant identifier (merchant code)
     */
    private $merchant;

    /**
     * @var string Merchant's order reference number (identifier)
     */
    private $orderRef;

    /**
     * @var string The date when order was placed
     */
    private $orderDate;

    /**
     * @var array List of products' names
     */
    private $orderPName = [];

    /**
     * @var array List of products' groups
     */
    private $orderPGroup = [];

    /**
     * @var array List of products' codes
     */
    private $orderPCode = [];

    /**
     * @var array List of products' additional information
     */
    private $orderPInfo = [];

    /**
     * @var array List of products' prices (WITH NO VAT/TAXES if product's price type is not specified or it's NET)
     */
    private $orderPrice = [];

    /**
     * type of price, GROSS OR NET
     * @var array List of products' price types ("GROSS" or "NET")
     */
    private $orderPriceType = [];

    /**
     * @var array List of products' quantities
     */
    private $orderQty = [];

    /**
     * @var array List of products' VAX/Tax
     */
    private $orderVAT = [];

    /**
     * @var float Value of shipping cost
     */
    private $orderShipping = 0.0;

    /**
     * @var string The order's currency
     */
    private $pricesCurrency;

    /**
     * @var float Order's discount
     */
    private $discount = 0.0;

    /**
     * @var string Delivery destination city
     */
    private $destinationCity;

    /**
     * @var string Delivery destination state
     */
    private $destinationState;

    /**
     * @var integer Delivery destination country code
     */
    private $destinationCountry;

    /**
     * @var string PayU's payment method used
     */
    private $payMethod;

    /**
     * @var int Number of instalments (if any) used in the order
     */
    private $selectedInstallmentsNo;

    /**
     * @var bool Whether to include billing information when placing the order or not (see "billing" below)
     */
    private $billingSet = false;

    private $callbackUrl;
    
    private $refUrl;
    /**
     * @var array Order billing information to be used when placing the order
     */
    public $billing = [
        'billFName' => '',
        'billLName' => '',
        'billCISerial' => '',
        'billCINumber' => '',
        'billCIIssuer' => '',
        'billCNP' => '',
        'billCompany' => '',
        'billFiscalCode' => '',
        'billRegNumber' => '',
        'billBank' => '',
        'billBankAccount' => '',
        'billEmail' => '',
        'billPhone' => '',
        'billFax' => '',
        'billAddress1' => '',
        'billAddress2' => '',
        'billZipCode' => '',
        'billCity' => '',
        'billState' => '',
        'billCountryCode' => ''
    ];

    /**
     * @var bool Whether to include delivery information when placing the order or not (see "delivery" below)
     */
    private $deliverySet = false;

    /**
     * @var array Order delivery information to be used when placing the order
     */
    private $delivery = [
        'deliveryFName' => '',
        'deliveryLName' => '',
        'deliveryCompany' => '',
        'deliveryPhone' => '',
        'deliveryAddress1' => '',
        'deliveryAddress2' => '',
        'deliveryZipCode' => '',
        'deliveryCity' => '',
        'deliveryState' => '',
        'deliveryCountryCode' => ''
    ];

    /**
     * @var array Parameter to be used by airline services operators
     */
    private $airlineInfo;

    /**
     * @return string Live Update URL class property value
     */
    public function getUpdateURL($sandboxMode)
    {
        if ($sandboxMode) {
            return $this->sandboxUpdateURL;
        } else {
            return $this->liveUpdateURL;
        }
    }

    /**
     * Sets the PayU's Live Update URL
     *
     * @param string $liveUpdateURL PayU's Live Update URL
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the provided Live Update URL is invalid
     */
    public function setLiveUpdateURL($liveUpdateURL)
    {
        if (!is_string($liveUpdateURL)) {
            throw new InvalidArgumentException('Live Update URL must be a string');
        }

        if (empty($liveUpdateURL)) {
            throw new InvalidArgumentException('Live Update URL has an invalid or no value');
        }

        $this->liveUpdateURL = $liveUpdateURL;

        return $this;
    }

    /**
     * Sets the Test Mode
     *
     * @param bool|int $testMode 1/true for enabling the Test Mode; 0/false otherwise
     * @return LiveUpdate
     */
    public function setTestMode($testMode)
    {
        switch ($testMode) {
            case true:
            case 1:
                $this->testMode = true;
                break;

            case false:
            case 0:
            default:
                $this->testMode = false;
        }

        return false;
    }


    /**
     * Sets the order's language.
     *
     * @param string $language 2-letters language code
     * @return LiveUpdate
     */
    public function setLanguage($language)
    {
        $language = strtolower(trim($language));
        switch ($language) {
            case 'ro':
                $this->language = 'ro';
                break;
            case 'bg':
                $this->language = 'bg';
                break;
            case 'hu':
                $this->language = 'hu';
                break;
            case 'ru':
                $this->language = 'ru';
                break;
            case 'tr':
                $this->language = 'tr';
                break;
            case 'en':
            default:
                $this->language = 'en';
        }

        return $this;
    }

    /**
     * Sets the merchant's secret key
     *
     * @param string $secretKey Merchant's secret key
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the provided secret key is invalid
     */
    public function setSecretKey($secretKey)
    {
        if (!is_string($secretKey)) {
            throw new InvalidArgumentException('The secret key must be a string');
        }

        if (empty($secretKey)) {
            throw new InvalidArgumentException('The secret key is invalid');
        }

        if (strlen($secretKey) > 64) {
            throw new InvalidArgumentException('The secret key must have max. 64 chars');
        }

        if (preg_match('/ /i', $secretKey)) {
            throw new InvalidArgumentException('The secret key cannot contain any spaces');
        }

        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Sets the merchant code
     *
     * @param string $merchant Merchant's code
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the provided merchant code is invalid
     */
    public function setMerchant($merchant)
    {
        if (!is_string($merchant)) {
            throw new InvalidArgumentException('The merchant code must be a string');
        }

        if (empty($merchant)) {
            throw new InvalidArgumentException('The merchant code value is invalid');
        }

        $this->merchant = $merchant;

        return $this;
    }

    /**
     * Sets the Order Reference Number
     *
     * @param string $orderRef Order's reference number
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the provided Order Reference Number is invalid
     */
    public function setOrderRef($orderRef)
    {
        $orderRef = (string)$orderRef;

        if (empty($orderRef) || strlen($orderRef) > 100) {
            throw new InvalidArgumentException('The provided Order Reference Number is either invalid or exceeds 100 chars.');
        }

        $this->orderRef = $orderRef;

        return $this;
    }

    /**
     * Sets the Order Date
     *
     * @param string $orderDate The date or order, in YYYY-MM-DD HH:MM:SS format
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the provided Order Date is invalid
     */
    public function setOrderDate($orderDate)
    {
        $timestamp = strtotime($orderDate);

        if ($timestamp === -1 || $timestamp === false) {
            throw new InvalidArgumentException('The provided Order Date is invalid');
        }

        $this->orderDate = date('Y-m-d H:i:s', $timestamp);

        return $this;
    }

    /**
     * Adds a product to the order.
     *
     * @param string $code Product's unique code
     * @param string $name Product's name/title
     * @param float $price Product's price
     * @param int $quantity Product's quantity
     * @param float $vat VAT value
     * @param string $info Optional, additional product's info (displayed in the payment pages under the product name)
     * @param string $priceType Optional; whether the price includes or not the VAT ("GROSS" (VAT included) or "NET" (VAT will be added by PayU))
     * @param int $groupId optional, the group ID's managed in Control Panel - Products / Product Groups
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if any of the product's value
     */
    public function addProduct($code, $name, $price, $quantity, $vat, $info = null, $priceType = null, $groupId = null)
    {
        if (empty($code) || strlen($code) > 50) {
            throw new InvalidArgumentException("The product code is either invalid or exceeds the maximum length of 50, '$code' received'");
        }

        if (empty($name) || strlen($name) > 155) {
            throw new InvalidArgumentException("The product name is either invalid or exceeds the maximum length of 155, '$name' received");
        }

        if (!is_numeric($price)) {
            throw new InvalidArgumentException("The product price must be a numeric value, '$price' received");
        }

        if (!is_numeric($quantity) || $quantity <= 0) {
            throw new InvalidArgumentException("The product quantity must be a valid, numeric value, '$quantity' received");
        }

        if (!is_numeric($vat) || $vat < 0 || $vat >= 100) {
            throw new InvalidArgumentException("The product VAT must be between 0 and 100, '$vat' received");
        }

        if ($groupId !== null && !is_numeric($groupId)) {
            throw new InvalidArgumentException("The product group ID must be one of those  managed in Control Panel - Products / Product Groups, '$groupId' received");
        }

        $this->orderPCode[] = $code;
        $this->orderPName[] = $name;
        $this->orderPrice[] = $price;
        $this->orderQty[] = $quantity;
        $this->orderVAT[] = $vat;
        $this->orderPInfo[] = $info;
        $this->orderPriceType[] = $priceType;
        $this->orderPGroup[] = $groupId;

        return $this;
    }

    /**
     * Sets the Order Shipping
     *
     * @param float $orderShipping The Order Shipping value
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the Order Shipping value is invalid
     */
    public function setOrderShipping($orderShipping)
    {
        if (!is_numeric($orderShipping)) {
            throw new InvalidArgumentException("The order shipping must be a numeric value, '$orderShipping' received");
        }

        if ($orderShipping < 0) {
            $orderShipping = -1;
        }

        $this->orderShipping = $orderShipping;

        return $this;
    }

    /**
     * Sets the Order Currency
     *
     * @param string $pricesCurrency Order's Currency
     * @return LiveUpdate
     */
    public function setPricesCurrency($pricesCurrency)
    {
        $this->pricesCurrency = strtoupper(trim($pricesCurrency));

        return $this;
    }


    /**
     * Sets the order discount
     *
     * @param float $discount Order's discount
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the discount value is invalid
     */
    public function setDiscount($discount)
    {
        if (!is_numeric($discount) || $discount < 0) {
            throw new InvalidArgumentException("The order discount must be a numeric value, '$discount' received");
        }

        $this->discount = $discount;

        return $this;
    }

    /**
     * Sets the Destination City
     *
     * @param string $destinationCity Order's delivery destination city
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the destination city value is invalid
     */
    public function setDestinationCity($destinationCity)
    {
        $destinationCity = trim($destinationCity);

        if (!is_string($destinationCity) || $destinationCity === '') {
            throw new InvalidArgumentException("The destination city value is invalid, '$destinationCity' received");
        }

        $this->destinationCity = $destinationCity;

        return $this;
    }

    /**
     * Sets the Destination State
     *
     * @param string $destinationState Order's delivery destination state
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the destination state value is invalid
     */
    public function setDestinationState($destinationState)
    {
        $destinationState = trim($destinationState);

        if (!is_string($destinationState) || $destinationState === '') {
            throw new InvalidArgumentException("The destination state value is invalid, '$destinationState' received");
        }

        $this->destinationState = $destinationState;

        return $this;
    }

    /**
     * Sets the Destination Country
     *
     * @param string $destinationCountry Order's delivery destination country
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the destination country value is invalid
     */
    public function setDestinationCountry($destinationCountry)
    {
        if (!is_string($destinationCountry) || strlen($destinationCountry) !== 2) {
            throw new InvalidArgumentException("The 2-letters destination country code value is invalid, '$destinationCountry' received");
        }

        $this->destinationCountry = $destinationCountry;

        return $this;
    }

    /**
     * Sets the PayU's payment method
     *
     * @param string $payMethod PayU's payment method to be used
     *
     * @return LiveUpdate
     * @throws InvalidArgumentException if the payment method value is invalid
     */
    public function setPayMethod($payMethod)
    {
        $payMethod = strtoupper($payMethod);

        switch ($payMethod) {
            case 'CCVISAMC':
            case 'CCAMEX':
            case 'CCDINERS':
            case 'CCJCB':
            case 'WIRE':
            case 'CASH':

                $this->payMethod = $payMethod;
                break;

            default:
                throw new InvalidArgumentException("The payment method is not known or accepted, '$payMethod' received");
        }

        return $this;
    }
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

    public function setRefUrl($refUrl)
    {
        $this->refUrl = $refUrl;
        return $this;
    }
    /**
     * Sets the Billing parameters
     *
     * @param array $billing Billing parameters, according to the documentation
     *
     * @return LiveUpdate
     */
    public function setBilling($billing)
    {
        foreach ($billing as $key => $val) {
            $this->billing[$key] = $val;
        }

        $this->billingSet = true;

        return $this;
    }

    /**
     * @param $delivery
     *
     * @return LiveUpdate
     */
    public function setDelivery($delivery)
    {
        foreach ($delivery as $key => $val) {
            switch ($key) {
                case 'deliveryCity':
                    if ($this->destinationCity === null) {
                        $this->delivery[$key] = $val;
                    } else {
                        $this->delivery[$key] = $this->destinationCity;
                    }
                    break;

                case 'deliveryState':
                    if ($this->destinationState === null) {
                        $this->delivery[$key] = $val;
                    } else {
                        $this->delivery[$key] = $this->destinationState;
                    }
                    break;

                case 'deliveryCountryCode':
                    if (empty($this->destinationCountry)) {
                        $this->delivery[$key] = $val;
                    } else {
                        $this->delivery[$key] = $this->destinationCountry;
                    }
                    break;

                default:
                    $this->delivery[$key] = $val;
            }
        }

        $this->deliverySet = true;

        return $this;
    }

    /**
     * Sets the AIRLINE_INFO parameters
     *
     * @param array $airlineInfo AIRLINE_INFO parameters, according to the documentation
     */
    public function setAirlineInfo($airlineInfo)
    {
        $this->airlineInfo = $airlineInfo;
    }

    /**
     * This method returns the HTML code. This will include only the hidden
     * fields, not the <form></form> tags.
     *
     * @return string
     */
    public function getLiveUpdateHTML()
    {
        $htmlCode = '';

        // Merchant code
        if ($this->merchant === null) {
            return '<!-- EPAYMENT ERROR: Invalid merchant id. -->';
        }

        $htmlCode .= $this->createHiddenField('merchant', $this->merchant, false);

        // Order reference number
        if ($this->orderRef !== null) {
            $htmlCode .= $this->createHiddenField('order_ref', $this->orderRef, false);
        }

        // Order date
        if ($this->orderDate === null) {
            return '<!-- EPAYMENT ERROR: Invalid order date. -->';
        }

        $htmlCode .= $this->createHiddenField('order_date', $this->orderDate, false);

        // PNAME
        if (empty($this->orderPName)) {
            return '<!-- EPAYMENT ERROR: Invalid order name. -->';
        }

        $htmlCode .= $this->createHiddenField('order_pname', $this->orderPName);

        // PCGROUP
        if (!empty($this->orderPGroup)) {
            $htmlCode .= $this->createHiddenField('ORDER_PGROUP', $this->orderPGroup, true, true);
        }

        // PCODE
        if (empty($this->orderPCode)) {
            return '<!-- EPAYMENT ERROR: Invalid product codes. -->';
        }

        $htmlCode .= $this->createHiddenField('order_pcode', $this->orderPCode);

        // PINFO
        if (!empty($this->orderPInfo) && !in_array('', $this->orderPInfo)) {
            $htmlCode .= $this->createHiddenField('order_pinfo', $this->orderPInfo, true, true);
        }

        // PRICE
        if (empty($this->orderPrice)) {
            return '<!-- EPAYMENT ERROR: Invalid order prices. -->';
        }

        $htmlCode .= $this->createHiddenField('ORDER_PRICE', $this->orderPrice);

        // QTY
        if (empty($this->orderQty)) {
            return '<!-- EPAYMENT ERROR: Invalid quantity. -->';
        }

        $htmlCode .= $this->createHiddenField('order_qty', $this->orderQty);

        // VAT
        if (empty($this->orderVAT)) {
            return '<!-- EPAYMENT ERROR: Invalid order vat. -->';
        }

        $htmlCode .= $this->createHiddenField('order_vat', $this->orderVAT);

        // ORDER_SHIPPING
        if ($this->orderShipping === null) {
            return '<!-- EPAYMENT ERROR: Invalid shipping value. -->';
        } elseif (is_numeric($this->orderShipping) && $this->orderShipping >= 0) {
            $htmlCode .= $this->createHiddenField('ORDER_SHIPPING', $this->orderShipping, false);
        }

        // PRICES_CURRENCY
        if (!empty($this->pricesCurrency)) {
            $htmlCode .= $this->createHiddenField('PRICES_CURRENCY', $this->pricesCurrency, false);
        }

        // Discount
        if (!empty($this->discount)) {
            return '<!-- EPAYMENT ERROR: Invalid discount. -->';
        }

        $htmlCode .= $this->createHiddenField('discount', $this->discount, false);

        // Destination city
        if (!empty($this->destinationCity)) {
            $htmlCode .= $this->createHiddenField('DESTINATION_CITY', $this->destinationCity, false);
        }

        // Destination state
        if (!empty($this->destinationState)) {
            $htmlCode .= $this->createHiddenField('DESTINATION_STATE', $this->destinationState, false);
        }

        // Destination country
        if (!empty($this->destinationCountry)) {
            $htmlCode .= $this->createHiddenField('DESTINATION_COUNTRY', $this->destinationCountry, false);
        }

        // Pay method
        if (!empty($this->payMethod)) {
            $htmlCode .= $this->createHiddenField('PAY_METHOD', $this->payMethod, false);
        }

        // Price Type
        if (!empty($this->orderPriceType)) {
            $htmlCode .= $this->createHiddenField('ORDER_PRICE_TYPE', $this->orderPriceType);
        }

        // Order hash
        $hmacHash = $this->hmac($this->secretKey, $this->getHmacString());
        $htmlCode .= $this->createHiddenField('ORDER_HASH', $hmacHash, false);

        // Test mode?
        /*  if ($this->testMode) {
            $htmlCode .= "<input name=\"TESTORDER\" type=\"hidden\" value=\"TRUE\">\n";
        } */ 

        //add billing information if it is available
        if ($this->billingSet) {
            $billingFields = array(
                'billFName' => 'BILL_FNAME',
                'billLName' => 'BILL_LNAME',
                'billCISerial' => 'BILL_CISERIAL',
                'billCINumber' => 'BILL_CINUMBER',
                'billCIIssuer' => 'BILL_CIISSUER',
                'billCNP' => 'BILL_CNP',
                'billCompany' => 'BILL_COMPANY',
                'billFiscalCode' => 'BILL_FISCALCODE',
                'billRegNumber' => 'BILL_REGNUMBER',
                'billBank' => 'BILL_BANK',
                'billBankAccount' => 'BILL_BANKACCOUNT',
                'billEmail' => 'BILL_EMAIL',
                'billPhone' => 'BILL_PHONE',
                'billFax' => 'BILL_FAX',
                'billAddress1' => 'BILL_ADDRESS',
                'billAddress2' => 'BILL_ADDRESS2',
                'billZipCode' => 'BILL_ZIPCODE',
                'billCity' => 'BILL_CITY',
                'billState' => 'BILL_STATE',
                'billCountryCode' => 'BILL_COUNTRYCODE'
            );

            foreach ($this->billing as $key => $val) {
                $htmlCode .= $this->createHiddenField($billingFields[$key], $this->billing[$key], false);
            }
        }

        //add delivery information if it is available
        if ($this->deliverySet) {
            $deliveryFields = array(
                "deliveryFName" => 'DELIVERY_FNAME',
                "deliveryLName" => 'DELIVERY_LNAME',
                "deliveryCompany" => 'DELIVERY_COMPANY',
                "deliveryPhone" => 'DELIVERY_PHONE',
                "deliveryAddress1" => 'DELIVERY_ADDRESS',
                "deliveryAddress2" => 'DELIVERY_ADDRESS2',
                "deliveryZipCode" => 'DELIVERY_ZIPCODE',
                "deliveryCity" => 'DELIVERY_CITY',
                "deliveryState" => 'DELIVERY_STATE',
                "deliveryCountryCode" => 'DELIVERY_COUNTRYCODE'
            );
            foreach ($this->delivery as $key => $val) {
                $htmlCode .= $this->createHiddenField($deliveryFields[$key], $this->delivery[$key], false);
            }
        }

        if ($this->airlineInfo != null) {
            $htmlCode .= $this->createMultiArrayHiddenFields(['AIRLINE_INFO' => $this->airlineInfo]);
        }

        if (is_string($this->language) && !empty($this->language)) {
            $htmlCode .= $this->createHiddenField('LANGUAGE', $this->language, false);
        }
        if (is_string($this->callbackUrl) && !empty($this->callbackUrl)) {
            $htmlCode .= $this->createHiddenField('BACK_REF', $this->callbackUrl, false);
        }
      
        return $htmlCode;
    }

    /**
     * @param string $fieldName name/id of the hidden field in html code
     * @param string|array $fieldValue field/fields value/values
     * @param bool $isArray specifies if it should generate an array or not
     * @param bool $avoidEmptyEntries if $isArray == true, then avoid adding empty elements from array
     * @return string output html code
     */
    private function createHiddenField($fieldName, $fieldValue, $isArray = true, $avoidEmptyEntries = false)
    {
        $fieldName = strtoupper($fieldName);
        $retval = "";
        if ($isArray) {
            for ($i = 0, $iMax = count($fieldValue); $i < $iMax; $i++) {
                if ($avoidEmptyEntries && empty($fieldValue[$i])) {
                    continue;
                }

                $fieldValue[$i] = htmlspecialchars($fieldValue[$i]);
                $retval .= '<input name="' . $fieldName . '[]" type="hidden" value="' . $fieldValue[$i] . "\" id=\"$fieldName$i\">\n";
            }
        } else {
            $fieldValue = htmlspecialchars($fieldValue);
            $retval = "<input name=\"$fieldName\" type=\"hidden\" value=\"$fieldValue\" id=\"$fieldName\">\n";
        }

        return $retval;
    }

    /**
     * Given a multidimensional array, serialize it as hidden inputs.
     *
     * @param array $array
     * @return string
     */
    protected function createMultiArrayHiddenFields(array $array)
    {
        $retval = '';

        $queryString = http_build_query($array);

        $parameters = explode('&', $queryString);

        foreach ($parameters as $parameter) {
            list($fieldName, $fieldValue) = explode('=', $parameter);

            $retval .= $this->createHiddenField(urldecode($fieldName), urldecode($fieldValue), false);
        }

        return $retval;
    }

    /**
     * Creates source string for hmac hash
     * THIS FUNCTION SHOULD NOT BE MODIFIED.
     *
     * @return string
     */
    private function getHmacString()
    {
        $retval = '';

        $retval .= $this->expandString($this->merchant);
        $retval .= $this->expandString($this->orderRef);
        $retval .= $this->expandString($this->orderDate);
        $retval .= $this->expandArray($this->orderPName);
        $retval .= $this->expandArray($this->orderPCode);

        if (is_array($this->orderPInfo) && !empty($this->orderPInfo)) {
            $valuesCount = @array_count_values($this->orderPInfo);
            if (count($valuesCount) !== 0) {
                $retval .= $this->expandArray($this->orderPInfo);
            }
        }

        $retval .= $this->expandArray($this->orderPrice);
        $retval .= $this->expandArray($this->orderQty);
        $retval .= $this->expandArray($this->orderVAT);

        if (is_numeric($this->orderShipping) && $this->orderShipping >= 0) {
            $retval .= $this->expandString($this->orderShipping);
        }

        if (is_string($this->pricesCurrency) && !empty($this->pricesCurrency)) {
            $retval .= $this->expandString($this->pricesCurrency);
        }

        if (is_numeric($this->discount)) {
            $retval .= $this->expandString($this->discount);
        }

        if (is_string($this->destinationCity) && !empty($this->destinationCity)) {
            $retval .= $this->expandString($this->destinationCity);
        }

        if (is_string($this->destinationState) && !empty($this->destinationState)) {
            $retval .= $this->expandString($this->destinationState);
        }

        if (is_string($this->destinationCountry) && !empty($this->destinationCountry)) {
            $retval .= $this->expandString($this->destinationCountry);
        }

        if (is_string($this->payMethod) && !empty($this->payMethod)) {
            $retval .= $this->expandString($this->payMethod);
        }

        if (is_array($this->orderPGroup) && !empty($this->orderPGroup)) {
            $valuesCount = @array_count_values($this->orderPGroup);
            if (count($valuesCount) !== 0) {
                $retval .= $this->expandArray($this->orderPGroup);
            }
        }

        $retval .= $this->expandArray($this->orderPriceType);

        if (is_numeric($this->selectedInstallmentsNo) && !empty($this->selectedInstallmentsNo)) {
            $retval .= $this->expandString($this->selectedInstallmentsNo);
        }

        if (!empty($this->airlineInfo)) {
            $retval .= $this->expandArray($this->airlineInfo);
        }

      /*   if ($this->testMode) {
            $retval .= $this->expandString('TRUE');
        } */

        return $retval;
    }

    /**
     * Outputs a string for hmac format. For a string like 'a' it will return '1a'.
     *
     * @param string $string Parameter value
     * @return string
     */
    private function expandString($string)
    {
        return strlen($string) . $string;
    }

    /**
     * expandArray
     *
     * The same as expandString except that it receives an array of strings and
     * returns the string from all values within the array.
     *
     * @param array $array
     * @return string
     */
    private function expandArray($array)
    {
        $retval = '';

        foreach ($array as $item) {
            if (is_array($item)) {
                $retval .= $this->expandArray($item);
            } else {
                $retval .= $this->expandString($item);
            }
        }

        return $retval;
    }

    /**
     * Builds HMAC key.
     * Warning: This method should not be modified.
     *
     * @param string $key Secret Key
     * @param string $data Fields
     * @return string HMAC-computed string
     */
    private function hmac($key, $data)
    {
        $b = 64;

        if (strlen($key) > $b) {
            $key = pack('H*', md5($key));
        }

        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack('H*', md5($k_ipad . $data)));
    }
}

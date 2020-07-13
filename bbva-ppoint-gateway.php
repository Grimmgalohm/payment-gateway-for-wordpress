    <?php

class abcom_Bbva_Payment_Gateway extends WC_Payment_Gateway {
  function __construct(){
    //global id
    $this->id = "abcom_bbva_payment_gateway";

    //show title
    $this->method_title = _("BBVA-Abcom Payment Gateway");

    //Show description
    $this->method_description = __("Payment gateway developed for Abarrotero.comm e-commerce in WooCommerce", 'abcom-bbva-payment-gateway');

    //Vertical tab title
    $this->title = __("Abcom Payment Gateway", 'abcom-bbva-payment-gateway');

    $this->icon = null;

    $this->has_fields = true;

    //support default from with credit card
    //$this->supports = array('default_credit_card_form');

    //settings defines
    $this->init_form_fields();

    //load time variable setting
    $this->init_settings();

    //Turn these settings into variables we can use
    foreach($this->settings as $setting_key => $value){
      $this->$setting_key = $value;
    }

    //further check of SSL if you want
    add_action('admin_notices', array($this, 'do_ssl_check'));

    //save settings
    if(is_admin()){
      add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
    }
  }//Here is the End __construct()

  //Administation fields for specific gateway
  public function init_form_fields(){
    $this->form_fields = array(
      'enabled'=> array(
        'title'=>__('Enable / Disable', 'abcom-bbva-payment-gateway'),
        'label'=>__('Enable this payment gateway', 'abcom-bbva-payment-gateway'),
        'type'=>'checkbox',
        'default'=>'no',
      ),
      'title'=>array(
        'title'=>__('Title', 'abcom-bbva-payment-gateway'),
        'type'=>'text',
        'desc_tip' => __('Payment title of checkout process.', 'abcom-bbva-payment-gateway'),
        'default'=> __('Succesfully payment through credit card.', 'abcom-bbva-payment-gateway'),
        'css' => 'max-width:450px;'
      ),
      'merchant_id'=>array(
        'title'=>__('BBVA Merchant ID', 'abcom-bbva-payment-gateway'),
        'type'=> 'text',
        'desc_tip'=>__('Provided by BBVA on e-commerce register', 'abcom-bbva-payment-gateway'),
      ),
      'private_key'=>array(
        'title'=> __('Private Key','abcom-bbva-payment-gateway'),
        'type'=>'password',
        'desc_tip'=> __('Provided by BBVA on e-commerce register', 'abcom-bbva-payment-gateway')
      ),
      'public_key'=>array(
        'title'=> __('Public Key','abcom-bbva-payment-gateway'),
        'type'=>'password',
        'desc_tip'=> __('This APIProvided by BBVA on e-commerce register', 'abcom-bbva-payment-gateway')
      ),
      'enviroment'=>array(
        'title'=>__('Plugin Test Mode','abcom-bbva-payment-gateway'),
        'label'=>__('Enable Test Mode','abcom-bbva-payment-gateway'),
        'type'=>'checkbox',
        'description'=>__('This is the test mode of the gateway','abcom-bbva-payment-gateway'),
        'default'=>'no',
      )
    );
  }//End of administration fields

  //Personalizated payment fields :D
  public function payment_fields(){

    // I will echo() the form, but you can close PHP tags and print it directly in HTML
	echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

	// Add this action hook if you want your custom payment gateway to support it
  do_action( 'woocommerce_credit_card_form_start', $this->id );

	// I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
  ?>
  <div class="form-row">
    <div class="col-md-6">
      <label>Nombre del titular <span class="required">*</span></label>
      <input id="misha_ccNa" class="container-fluid form-control" style="width:100% !important;" type="text" autocomplete="off" placeholder="Como aparece la tarjeta">
    </div>

    <div class="col-md-6">
      <label>Número de la tarjeta <span class="required">*</span></label>
      <input id="misha_ccNo" class="container-fluid form-control" style="width:100% !important;" type="text" autocomplete="off">
    </div>

	</div>

  <div class="form-row">

    <div class="col-md-6">
      <label>Fecha de expiración <span class="required">*</span></label>
      <input id="misha_expdate_m" type="text" autocomplete="off" placeholder="Mes">
      <input id="misha_expdate_y" type="text" autocomplete="off" placeholder="Año">
    </div>

    <div class="col-md-6">
      <label>Código de seguridad <span class="required">*</span></label>
      <input id="misha_cvv" type="password" autocomplete="off" placeholder="3 dígitos">
    </div>

	</div>

	<div class="clear"></div>

    <?php
    do_action( 'woocommerce_credit_card_form_end', $this->id );
  echo '<div class="clear"></div></fieldset>';
  }

//Response handled for payment Gateway

public function process_payment($order_id){

  global $woocommerce;

  $customer_order = new WC_Order($order_id);

  //Setup M-ID & PK,
  Bbva::setId(merchant_id);
  Bbva::setApiKey(private_key);

  //Checking the transaction
  $environment = ($this->environment == "yes") ? 'TRUE' : 'FALSE';

  if($environment == 'TRUE'){
    Bbva::setProductionMode(true);
  }else {
    Bbva::setProductionMode(false);
  }

  //Decide which URL to post to
  $environment_url = ("FALSE" == $environment) ? 'https://api.ecommercebbva.com/':'https://sand-api.ecommercebbva.com/';

  $bbva = Bbva::getInstance(merchant_id , private_key);

  //This is where the funny stuff begins :'v
  $bbva = $chargeRequest = array(

    'affiliation_bbva' => '781500',
    'amount' => $customer_order->order_total,
    'description' => $customer_order->order_id.' Tienda en línea Abarrotero',
    'currency' => $customer_order->get_order_currency,
    'order_id' => $customer_order->order_id,
    'redirect_url' => $environment_url,
    'card' => array(
            'holder_name' => $_POST['abcom-bbva-payment-gateway-misha_ccNa'],
            'card_number' => str_replace(array(' ', '-'), '', $_POST['abcom-bbva-payment-gateway-misha_ccNo']),
            'expiration_month' => $_POST['abcom-bbva-payment-gateway-misha_expdate_m'],
            'expiration_year' => $_POST['abcom-bbva-payment-gateway-misha_expdate_y'],
            'cvv2' => ( isset( $_POST['abcom-bbva-payment-gateway-misha_cvv'] ) ) ? $_POST['abcom-bbva-payment-gateway-misha_cvv'] : ''
          ),
    'customer' => array(
        'name' => $customer_order->billing_first_name,
        'last_name' => $customer_order->billing_last_name,
        'email' => $customer_order->billing_email,
        'phone_number' => $customer_order->billing_phone
      )
  );

  //Creating charge for
  $charge = $bbva->charges->create($chargeRequest);


  //getting a response for status
  $response = wp_remote_post($environment_url, array(
    'method'    => 'POST',
    'body'      => http_build_query($charge),
    'timeout'   => 90,
    'sslverify' => false
  ));

  if(is_wp_error($response))
    throw new Exception(__('There is issue for connectin payment gateway. Sorry for the inconvenience.', 'abcom-bbva-payment-gateway'));

  if( empty($response['body']))
    throw new Exception(__('Abarrotero.com\'s Response was not get any data.', 'abcom-bbva-payment-gateway'));

  /* get body response while get not error
  $response_body = wp_remote_retrieve_body($response);

  foreach (preg_split("/\r?\n/", $response_body) as $line){

    $resp = explode("|", $line);

  }

  //values get
  $r['response_code']  = $resp[0],*/


}//Process payment ends

}//Class ends

?>

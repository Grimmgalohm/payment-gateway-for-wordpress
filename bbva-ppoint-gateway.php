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
    $this->supports = array('default_credit_card_form');

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
      'api_login'=>array(
        'title'=>__('Authorize BBVA API login', 'abcom-bbva-payment-gateway'),
        'type'=> 'text',
        'desc_tip'=>__('This API Login provided by BBVA when you signed up for an acount', 'abcom-bbva-payment-gateway'),
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

//Response handled for payment Gateway
/* public function process_payment($order_id){
  global $woocommerce;

  $customer_order = new WC_Order($order_id);

  //Checking the transaction
  $environment = ($this->environment == "yes") ? 'TRUE' : 'FALSE';

  //Decide which URL to post to
  $environment_url = ("FALSE" == $environment)
                        ? ''
                        : 'https://sand-api.ecommercebbva.com/'
}*/

}

?>

<?php
add_action( 'woocommerce_order_action_wc_custom_order_action', 'bbva_wc_process_order_meta_box_action');
add_action( 'woocommerce_order_actions', 'bbva_wc_add_order_meta_box_action');

/**
 * Add a custom action to order actions select box on edit order page
 * Only added for paid orders that haven't fired this action yet
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
function bbva_wc_add_order_meta_box_action( $actions ) {

    global $theorder;

    // bail if the order has been paid for or this action has been run
    if ( !$theorder->is_paid() ) {
        return $actions;
    }

    // add "mark printed" custom action
    $actions['wc_custom_order_action'] = __( 'Verify Payment Status', 'bbva-payments-woo' );
    return $actions;
}

/**
 * Add an order note when custom action is clicked
 * Add a flag on the order to show it's been run
 *
 * @param \WC_Order $order
 */
function bbva_wc_process_order_meta_box_action( $order ) {

  $order = wc_get_order($order->id);

  $charge_id = get_post_meta($order->get_id(), 'bbva_charge_id', true);

  //$charge = check_charge_status($charge_id);

  /*if( $charge != false ){

    $message = 'Status: '. $charge->status .
    '<br>Authorization: '.$charge->authorization.'.';

    $order->add_order_note($message);

  }else{

    $message_e = 'error';

    $order->add_order_note($message_e);

  }*/

  // HERE define you payment gateway ID (from $this->id in your plugin code)
  $payment_gateway_id = 'bbvapay';

  // Get an instance of the WC_Payment_Gateways object
  $payment_gateways   = WC_Payment_Gateways::instance();

  // Get the desired WC_Payment_Gateway object
  $payment_gateway    = $payment_gateways->payment_gateways()[$payment_gateway_id];

  //$message = $charge_id.'<br>'.$order->get_id();
  //$order->add_order_note($message);

  $bbva = Bbva::getInstance($payment_gateway->m_id, $payment_gateway->priv_key);

  try{

    $charge = $bbva->charges->get($charge_id);

    $message = 'Status: '. $charge->status .
    '<br>Authorization: '.$charge->authorization.
    '<br>ID: '.$charge_id.'.';

    $order->add_order_note($message);

  }catch(Exception $e){

    error_log('ERROR on the transaction: ' . $e->getMessage() .
      ' [error code: ' . $e->getErrorCode() .
      ', error category: ' . $e->getCategory() .
      ', HTTP code: '. $e->getHttpCode() .
      ', request ID: ' . $e->getRequestId() . ']', 0);

    $order->add_order_note($e);

  }

}

/*function check_charge_status($chargeId){

  // HERE define you payment gateway ID (from $this->id in your plugin code)
  $payment_gateway_id = 'bbvapay';

  // Get an instance of the WC_Payment_Gateways object
  $payment_gateways   = WC_Payment_Gateways::instance();

  // Get the desired WC_Payment_Gateway object
  $payment_gateway    = $payment_gateways->payment_gateways()[$payment_gateway_id];

  $bbva = Bbva::getInstance($payment_gateway->m_id, $payment_gateway->priv_key);

  try{

    $charge = $bbva->charges->get($chargeId);

    $message = 'Status: '. $charge->status .
    '<br>Authorization: '.$charge->authorization.'.';

    $order->add_order_note($message);

    return $charge;

  }catch(Exception $e){

    error_log('ERROR on the transaction: ' . $e->getMessage() .
      ' [error code: ' . $e->getErrorCode() .
      ', error category: ' . $e->getCategory() .
      ', HTTP code: '. $e->getHttpCode() .
      ', request ID: ' . $e->getRequestId() . ']', 0);

    return false;

  }

}*/

?>

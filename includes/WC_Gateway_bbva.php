<?php
/**
 * Cash on Delivery Gateway.
 *
 * Provides a Cash on Delivery Payment Gateway.
 *
 * @class       WC_Gateway_bbva
 * @extends     WC_Payment_Gateway
 * @version     0.1.0
 * @package     WooCommerce/Classes/Payment
 */

class WC_Gateway_bbva extends WC_Payment_Gateway {

  /**
   * Constructor for the gateway.
   */
   public function __construct() {
    // Setup general properties.
    $this->setup_properties();

    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();

    // Get settings.
    $this->title              = $this->get_option( 'title' );
    $this->pub_key            = $this->get_option( 'pub_key' );
    $this->priv_key           = $this->get_option( 'priv_key' );
    $this->m_id               = $this->get_option( 'm_id' );
    $this->affiliation_number = $this->get_option( 'affiliation_number' );
    $this->description        = $this->get_option( 'description' );
    $this->instructions       = $this->get_option( 'instructions' );
    $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
    $this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';
    $this->environment     = $this->get_option( 'environment', 'yes' ) === 'yes';

    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
    add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

    // Customer Emails.
    add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
  }

  /**
   * Setup general properties for the gateway.
   */
  protected function setup_properties() {
    $this->id                 = 'bbvapay';
    $this->icon               = apply_filters( 'woocommerce_bbvapay_icon', '' );
    $this->method_title       = __( 'BBVA Online Payments', 'bbva-payments-woo' );
    $this->method_description = __( 'Use your credit card to pay online.', 'bbva-payments-woo' );
    //Setting properties
    $this->pub_key            = __( 'Public Key.', 'bbva-payments-woo' );
    $this->priv_key           = __( 'Private Key.', 'bbva-payments-woo' );
    $this->m_id               = __( 'Merchant ID.', 'bbva-payments-woo' );
    $this->affiliation_number = __( 'Affiliation number.', 'bbva-payments-woo' );
    $this->environment     = __( 'Enable test environment', 'bbva-payments-woo');
    //Ends
    $this->has_fields         = false;
  }

  /**
   * Initialise Gateway Settings Form Fields.
   */
  public function init_form_fields() {
    $this->form_fields = array(
      'enabled'            => array(
        'title'       => __( 'Enable/Disable', 'bbva-payments-woo' ),
        'label'       => __( 'Enable cash on delivery', 'bbva-payments-woo' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no',
      ),
      'title'              => array(
        'title'       => __( 'Title', 'bbva-payments-woo' ),
        'type'        => 'text',
        'description' => __( 'BBVA Payment method description that the customer will see on your checkout.', 'bbva-payments-woo' ),
        'default'     => __( 'BBVA Online Payments', 'bbva-payments-woo' ),
        'desc_tip'    => true,
      ),
      //Personalizated forms
      'pub_key'              => array(
        'title'       => __( 'Public Key', 'bbva-payments-woo' ),
        'type'        => 'password',
        'description' => __( 'Place here you public key.', 'bbva-payments-woo' ),
        'desc_tip'    => true,
      ),
      'priv_key'             => array(
        'title'       => __( 'Private Key', 'bbva-payments-woo' ),
        'type'        => 'password',
        'description' => __( 'Place here you public key.', 'bbva-payments-woo' ),
        'desc_tip'    => true,
      ),
      'm_id'                 => array(
        'title'       => __( 'Merchant ID', 'bbva-payments-woo' ),
        'type'        => 'password',
        'description' => __( 'Is given by registering your commerce on BBVA.', 'bbva-payments-woo' ),
        'desc_tip'    => true,
      ),
      'affiliation_number'   => array(
        'title'       => __( 'Affiliation Number', 'bbva-payments-woo' ),
        'type'        => 'text',
        'description' => __( 'Is given by registering your commerce on BBVA.', 'bbva-payments-woo' ),
        'desc_tip'    => true,
      ),
      'environment'   => array(
        'title'       => __( 'Enable test environment', 'bbva-payments-woo' ),
        'type'        => 'checkbox',
        'description' => __( 'Enable sandbox mode for test purposes only.', 'bbva-payments-woo' ),
        'desc_tip'    => true,
        'default'     => 'yes',
      ),
      //Personalizated forms ENDS
      'description'        => array(
        'title'       => __( 'Description', 'bbva-payments-woo' ),
        'type'        => 'textarea',
        'description' => __( 'BBVA Payment method description that the customer will see on your website.', 'bbva-payments-woo' ),
        'default'     => __( 'Pay with cash upon delivery.', 'bbva-payments-woo' ),
        'desc_tip'    => true,
      ),
      'instructions'       => array(
        'title'       => __( 'Instructions', 'bbva-payments-woo' ),
        'type'        => 'textarea',
        'description' => __( 'Instructions that will be added to the thank you page.', 'bbva-payments-woo' ),
        'default'     => __( 'Pay with credit card.', 'bbva-payments-woo' ),
        'desc_tip'    => true,
      ),
      'enable_for_methods' => array(
        'title'             => __( 'Enable for shipping methods', 'bbva-payments-woo' ),
        'type'              => 'multiselect',
        'class'             => 'wc-enhanced-select',
        'css'               => 'width: 400px;',
        'default'           => '',
        'description'       => __( 'If BBVA Payment is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'bbva-payments-woo' ),
        'options'           => $this->load_shipping_method_options(),
        'desc_tip'          => true,
        'custom_attributes' => array(
          'data-placeholder' => __( 'Select shipping methods', 'bbva-payments-woo' ),
        ),
      ),
      'enable_for_virtual' => array(
        'title'   => __( 'Accept for virtual orders', 'bbva-payments-woo' ),
        'label'   => __( 'Accept BBVA Payment if the order is virtual', 'bbva-payments-woo' ),
        'type'    => 'checkbox',
        'default' => 'yes',
      ),
    );
  }

  /**
   * Check If The Gateway Is Available For Use.
   *
   * @return bool
   */
  public function is_available() {
    $order          = null;
    $needs_shipping = false;

    // Test if shipping is needed first.
    if ( WC()->cart && WC()->cart->needs_shipping() ) {
      $needs_shipping = true;
    } elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
      $order_id = absint( get_query_var( 'order-pay' ) );
      $order    = wc_get_order( $order_id );

      // Test if order needs shipping.
      if ( 0 < count( $order->get_items() ) ) {
        foreach ( $order->get_items() as $item ) {
          $_product = $item->get_product();
          if ( $_product && $_product->needs_shipping() ) {
            $needs_shipping = true;
            break;
          }
        }
      }
    }

    $needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

    // Virtual order, with virtual disabled.
    if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
      return false;
    }

    // Only apply if all packages are being shipped via chosen method, or order is virtual.
    if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
      $order_shipping_items            = is_object( $order ) ? $order->get_shipping_methods() : false;
      $chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

      if ( $order_shipping_items ) {
        $canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids( $order_shipping_items );
      } else {
        $canonical_rate_ids = $this->get_canonical_package_rate_ids( $chosen_shipping_methods_session );
      }

      if ( ! count( $this->get_matching_rates( $canonical_rate_ids ) ) ) {
        return false;
      }
    }

    return parent::is_available();
  }

  /**
   * Checks to see whether or not the admin settings are being accessed by the current request.
   *
   * @return bool
   */
  private function is_accessing_settings() {
    if ( is_admin() ) {
      // phpcs:disable WordPress.Security.NonceVerification
      if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
        return false;
      }
      if ( ! isset( $_REQUEST['tab'] ) || 'checkout' !== $_REQUEST['tab'] ) {
        return false;
      }
      if ( ! isset( $_REQUEST['section'] ) || 'bbvapay' !== $_REQUEST['section'] ) {
        return false;
      }
      // phpcs:enable WordPress.Security.NonceVerification

      return true;
    }

    /* This is for Jetpack... so it doesn´t matters
    if ( Constants::is_true( 'REST_REQUEST' ) ) {
      global $wp;
      if ( isset( $wp->query_vars['rest_route'] ) && false !== strpos( $wp->query_vars['rest_route'], '/payment_gateways' ) ) {
        return true;
      }
    }*/

    return false;
  }

  /**
   * Loads all of the shipping method options for the enable_for_methods field.
   *
   * @return array
   */
  private function load_shipping_method_options() {
    // Since this is expensive, we only want to do it if we're actually on the settings page.
    if ( ! $this->is_accessing_settings() ) {
      return array();
    }

    $data_store = WC_Data_Store::load( 'shipping-zone' );
    $raw_zones  = $data_store->get_zones();

    foreach ( $raw_zones as $raw_zone ) {
      $zones[] = new WC_Shipping_Zone( $raw_zone );
    }

    $zones[] = new WC_Shipping_Zone( 0 );

    $options = array();
    foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

      $options[ $method->get_method_title() ] = array();

      // Translators: %1$s shipping method name.
      $options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'woocommerce' ), $method->get_method_title() );

      foreach ( $zones as $zone ) {

        $shipping_method_instances = $zone->get_shipping_methods();

        foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

          if ( $shipping_method_instance->id !== $method->id ) {
            continue;
          }

          $option_id = $shipping_method_instance->get_rate_id();

          // Translators: %1$s shipping method title, %2$s shipping method id.
          $option_instance_title = sprintf( __( '%1$s (#%2$s)', 'woocommerce' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

          // Translators: %1$s zone name, %2$s shipping method instance name.
          $option_title = sprintf( __( '%1$s &ndash; %2$s', 'woocommerce' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'woocommerce' ), $option_instance_title );

          $options[ $method->get_method_title() ][ $option_id ] = $option_title;
        }
      }
    }

    return $options;
  }

  /**
   * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
   *
   * @since  3.4.0
   *
   * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
   * @return array $canonical_rate_ids    Rate IDs in a canonical format.
   */
  private function get_canonical_order_shipping_item_rate_ids( $order_shipping_items ) {

    $canonical_rate_ids = array();

    foreach ( $order_shipping_items as $order_shipping_item ) {
      $canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
    }

    return $canonical_rate_ids;
  }

  /**
   * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
   *
   * @since  3.4.0
   *
   * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
   * @return array $canonical_rate_ids  Rate IDs in a canonical format.
   */
  private function get_canonical_package_rate_ids( $chosen_package_rate_ids ) {

    $shipping_packages  = WC()->shipping()->get_packages();
    $canonical_rate_ids = array();

    if ( ! empty( $chosen_package_rate_ids ) && is_array( $chosen_package_rate_ids ) ) {
      foreach ( $chosen_package_rate_ids as $package_key => $chosen_package_rate_id ) {
        if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
          $chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
          $canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
        }
      }
    }

    return $canonical_rate_ids;
  }

  /**
   * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
   *
   * @since  3.4.0
   *
   * @param array $rate_ids Rate ids to check.
   * @return boolean
   */
  private function get_matching_rates( $rate_ids ) {
    // First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
    return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
  }

  /**
   * Process the payment and return the result.
   *
   * @param int $order_id Order ID.
   * @return array
   */

  public function process_payment( $order_id ) {

    $order = wc_get_order( $order_id );

    // checking for transiction
    $environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';
    // Decide which URL to post to
    $environment_url = ( "FALSE" == $environment ) ? '' : '';

    if($environment_url == "TRUE"){
      Bbva::setProductionMode(true);
    }else{
      Bbva::setProductionMode(false);
    }

    if ( $order->get_total() > 0 ) {

      $this->bbva_payment_processing($environment_url, $order->get_total);

    } else {
      $order->payment_complete();
    }

    // Remove cart.
    WC()->cart->empty_cart();

    // Return thankyou redirect.
    return array(
      'result'   => 'success',
      'redirect' => $this->get_return_url( $order ),
    );
  }

  private function bbva_payment_processing($environment_url, $order){

    $total = $order->get_total();
    $transaction = $order->get_transaction_id();
    $o_id = $order->get_id();
    $currency = $order->get_currency();
    $name = $order->get_shipping_first_name();
    $last_name = $order->get_shipping_last_name();
    $email = $order->get_billing_email();
    $phone_number = $order->get_billing_phone();

    //Set id and api key to use the bbva files
    Bbva::setId($this->m_id);
    Bbva::setApiKey($this->priv_key);

    $bbva = Bbva::getInstance($this->m_id, $pri_key);

    $chargeRequest = array(
      'affiliation_bbva' => $this->affiliation_number,
      'amount' => $order->get_total(),
      'description' => 'Pago de la orden: '. $transaction,
      'currency' => $currency,
      'order_id' => $o_id,
      'redirect_url' => $environment_url,
      'card' => array(
              'holder_name' => $_POST['card_name'],
              'card_number' => $_POST['card_number'],
              'expiration_month' => $_POST['card_mm'],
              'expiration_year' => $_POST['card_yy'],
              'cvv2' => $_POST['card_cvv']
            ),
      'customer' => array(
          'name' => $name,
          'last_name' => $last_name,
          'email' => $email,
          'phone_number' => $phone_number
        )
  );

  $charge = $bbva->charges->create($chargeRequest);

  $url = $environment_url .'/'. $this->m_id . '/' . $charge;

    //var_dump($url);

  	$response = wp_remote_post( $url, array( 'timeout' => 45 ) );

  	if ( is_wp_error( $response ) ) {
  		$error_message = $response->get_error_message();
  		return "Something went wrong: $error_message";
  	} else {
  		echo '<pre>';
  		var_dump( wp_remote_retrieve_body( $response ) );
  		echo '</pre>';
  	}
    // Mark as processing or on-hold (payment won't be taken until delivery).
    /*$order->update_status(
      apply_filters(
        'woocommerce_bbvapay_process_payment_order_status',
        $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ),
        __( 'Payment pending.', 'woocommerce' )
    ); //End apply_filters
    */
  }

  /**
   * Output for the order received page.
   */
  public function thankyou_page() {
    if ( $this->instructions ) {
      echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
    }
  }

  /**
   * Change payment complete order status to completed for COD orders.
   *
   * @since  3.1.0
   * @param  string         $status Current order status.
   * @param  int            $order_id Order ID.
   * @param  WC_Order|false $order Order object.
   * @return string
   */
  public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
    if ( $order && 'bbvapay' === $order->get_payment_method() ) {
      $status = 'completed';
    }
    return $status;
  }

  /**
   * Add content to the WC emails.
   *
   * @param WC_Order $order Order object.
   * @param bool     $sent_to_admin  Sent to admin.
   * @param bool     $plain_text Email format: plain text or HTML.
   */
  public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
    if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
      echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
    }
  }
}
?>

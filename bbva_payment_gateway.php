<?php
/**
	* Plugin Name: BBVA Payment Gateway
	* Plugin URI:
	* Author: Grimm with BBVA API
	* Description: An awesome payment gateway for wordpress
	* Version: 0.1.0
	* License: GPL
	* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
	* text-domain: bbva-woo
 	* Class WC_Gateway_bbva file.
 	*
 	* @package WooCommerce\bbva_payments
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action('plugins_loaded', 'bbva_init_gateway_class');
function bbva_init_gateway_class(){

	//include the package of the BBVA E-commerce github
	require(dirname(__FILE__) . '/BBVA-PHP/Bbva.php');

	include_once(dirname(__FILE__) . '/includes/WC_Gateway_bbva.php');

	include_once(dirname(__FILE__) . '/includes/WC_Checkout_Description_Fields.php');

	add_filter('woocommerce_payment_gateways', 'bbva_add_gateway_class');
	function bbva_add_gateway_class($gateways){

		$gateways[] = 'WC_Gateway_bbva';

		return $gateways;
	}

}//bbva init gateway class ENDS

//Add custom action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bbva_payments_aim_action_links');

function bbva_payments_aim_action_links($links){

	$plugin_links = array( '<a href="'. admin_url('admin.php?page=wc-settings&tab=checkout' .'">' . __('Settings', 'bbva-abcom-aim') .'</a>' ) );

	return array_merge($plugin_links, $links);

}

?>

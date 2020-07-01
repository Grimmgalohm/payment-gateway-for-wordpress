<?php
/*
Plugin Name: Abarrotero - BBVA Payment Gateway
Plugin URI: NA
Description: WooCommerce custom payment gateway for Abarrotero.com site with BBVA API.
Version: 1.0
*/

if(!defined('ABSPATH')) exit;

add_action('plugins_loaded', 'abcom_payment_gateway_init', 0);

function abcom_payment_gateway_init(){

  if(!class_exists('WC_Payment_Gateway')) return;

  include_once('bbva-ppoint-gateway.php');

  //include the package of the BBVA E-commerce github
  require(dirname(__FILE__) . '/BBVA-PHP/Bbva.php');

  //class add it to woocommerce
  add_filter('woocommerce_payment_gateways', 'abcom_add_bbva_aim_gateway');
  function abcom_add_bbva_aim_gateway($methods){
    $methods[]='abcom_Bbva_Payment_Gateway';
    return $methods;
  }
}

//Ads a custom action link
add_filter('plugin_action_links_'. plugin_basename(__FILE__), 'abcom_authorize_action_links');
function abcom_authorize_action_links($links){
  $plugin_links = array(
    '<a href="'.admin_url('admin.php?page=wc-settings&tab=checkout').'">'. __('Settings', 'abcom_Bbva_Payment_Gateway').'</a>',

  );

  return array_merge($plugin_links, $links);
}

?>

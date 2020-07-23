<?php

add_filter('woocommerce_gateway_description', 'abcom_bbva_payment_description_fields', 20, 2);
add_action('woocommerce_checkout_process', 'abcom_bbva_payment_description_fields_validation');
//add_action('woocommerce_checkout_update_order_meta', 'abcom_bbva_payment_checkout_update_order_meta', 10, 1);

function abcom_bbva_payment_description_fields($description, $payment_id){

  if( 'bbvapay' != $payment_id){

    return $description;

  }

  ob_start();

  echo '<form>
          <div class="form-row">
            <div class="form-group col-md-6">';
  //Nombre en la tarjeta
  woocommerce_form_field(
    'card_name',
    array(
      'type'=> 'text',
      'label'=> __('Nombre', 'bbva-payments-woo'),
      'class'=> array('form-row', 'form-row-wide'),
      'placeholder'=> __('Como aparece en la tarjeta'),
      'required'=> true,
    )
  );

  echo '</div>
          <div class="form-group col-md-6">';
  //Número de tarjeta
  woocommerce_form_field(
    'card_number',
    array(
      'type'=> 'number',
      'label'=> __('N° de tarjeta', 'bbva-payments-woo'),
      'class'=> array('form-row', 'form-row-wide'),
      'custom_attributes'=> array('style'=>'-webkit-appearance: textfield !important; margin: 0; -moz-appearance:textfield !important;'),
      'required'=> true,
    )
  );

  echo '</div>
          </div>';//Fin de la primera fila (form-row)

  echo '<div class="form-row">
          <div class="col-md-4 mb-3">';

  woocommerce_form_field(
    'card_mm',
    array(
      'type'=> 'select',
      'label'=> __('Mes de vencimiento', 'bbva-payments-woo'),
      'class'=> array('form-row', 'form-row-wide'),
      'options'=> array(''=>'Selecciona...',
      '01'=>'01',
      '02'=>'02',
      '03'=>'03',
      '04'=>'04',
      '05'=>'05',
      '06'=>'06',
      '07'=>'07',
      '08'=>'08',
      '09'=>'09',
      '10'=>'10',
      '11'=>'11',
      '12'=>'12'),
      'placeholder'=> __('MM'),
      'custom_attributes'=> array('style'=>'width:150px;'),
      'required'=> true,
    )
  );
  echo '</div>
          <div class="col-md-4 mb-3">';

  woocommerce_form_field(
    'card_yy',
    array(
      'type'=> 'number',
      'label'=> __('Año de vencimiento', 'bbva-payments-woo'),
      'class'=> array('form-row', 'form-row-wide'),
      'placeholder'=> __('YY'),
      'custom_attributes'=> array('style'=>'width:150px;'),
      'required'=> true,
    )
  );
  echo '</div>
          <div class="col-md-4 mb-3">';

  woocommerce_form_field(
    'card_cvv',
    array(
      'type'=> 'password',
      'label'=> __('CVC/CVV', 'bbva-payments-woo'),
      'class'=> array('form-row', 'form-row-wide'),
      'placeholder'=> __('3 números'),
      'maxlength'=> 3,
      'minlength'=> 3,
      'custom_attributes'=> array('style'=>'width:150px;'),
      'required'=> true,
    )
  );
  //echo '<img class="col-md-4 mb-3" src="' . plugins_url('../assets/credit-card-with-cvv-code.png', __FILE__) .'">';
  echo '</div>
          </div>';//fin de la segunda fila (form-row)

  $description .= ob_get_clean();

  return $description;

}

function abcom_bbva_payment_description_fields_validation(){

  if('bbvapay' == $_POST['payment_method'] && !isset( $_POST['card_name'] ) || empty( $_POST['card_name'] ) || !isset($_POST['card_number']) || empty($_POST['card_number']) || !isset($_POST['card_mm']) || empty($_POST['card_mm']) || !isset($_POST['card_yy']) || empty($_POST['card_yy']) || !isset($_POST['card_cvv']) || empty($_POST['card_cvv']) ){
        wc_add_notice('Ingresa todos los datos marcados como requeridos.', 'error');
    }
}

function abcom_bbva_payment_checkout_update_order_meta($order_id){

  if(isset($_POST['card_name']) || !empty($_POST['card_name']) || isset($_POST['card_number']) || !empty($_POST['card_number']) || isset($_POST['card_mm']) || !empty($_POST['card_mm']) || isset($_POST['card_yy']) || !empty($_POST['card_yy']) || isset($_POST['card_cvv']) || !empty($_POST['card_cvv']) ){
        update_post_meta($order_id, $card_name, $_POST['card_name']);
        update_post_meta($order_id, $card_number, $_POST['card_number']);
        update_post_meta($order_id, $card_mm, $_POST['card_mm']);
        update_post_meta($order_id, $card_yy, $_POST['$card_yy']);
        update_post_meta($order_id, $card_cvv, $_POST['card_cvv']);

    }
}

?>

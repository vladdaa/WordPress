<?php
include_once(plugin_dir_path(__FILE__) . 'cron-daily-update.php');

//Отримуємо дані
function np_get_post_meta_data($order_number) {
    $order_query = new WC_Order_Query(array(
        'limit' => 1,
        'return' => 'ids',
        'meta_key' => '_np_custom_order_number',
        'meta_value' => $order_number,
        'post_status' => array_keys(wc_get_order_statuses()), 
    ));
   
    $order_ids = $order_query->get_orders();
    
    if (!empty($order_ids)) {
   
        $order_id = $order_ids[0];
        $order = wc_get_order( $order_id );
        
        if ( $order ) {
          //Отримуємо дані отримувача
            $patronymic   = get_post_meta( $order_id, 'billing_patronymic', true );
            $first_name   = $order->get_billing_first_name();
            $last_name    = $order->get_billing_last_name();
            $phone        = $order->get_billing_phone();

            $contractor_first_name = clean($first_name);
            $contractor_last_name  = clean($last_name);
            $contractor_patronymic = clean($patronymic);
            $contractor_phone = $phone;
        
            create_counterparty_recipient( $contractor_first_name, $contractor_last_name, $contractor_patronymic, $contractor_phone );

        } else {
          wp_send_json_error( 'Помилка: Не вдалося завантажити замовлення.' );
        }
    } else {
      wp_send_json_error('Помилка!');
    }

}


function clean($input){

  $cleaned_input = trim(preg_replace('/[^а-яА-ЯіІїЇєЄґҐ\s]/u', '', $input));

  return $cleaned_input;
}

//Створюємо контрагента
function create_counterparty_recipient($first_name, $last_name, $patronymic, $phone ) {
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
   
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
      <apiKey>' . esc_html($api_key) . '</apiKey>
      <modelName>CounterpartyGeneral</modelName>
      <calledMethod>save</calledMethod>
      <methodProperties>
        <FirstName>' . esc_html($first_name) . '</FirstName>
        <MiddleName>' . esc_html($patronymic) . '</MiddleName>
        <LastName>' . esc_html($last_name) . '</LastName>
        <Phone>' . esc_html($phone) . '</Phone>
        <CounterpartyType>PrivatePerson</CounterpartyType>
        <CounterpartyProperty>Recipient</CounterpartyProperty>
      </methodProperties>
    </file>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_message = curl_error($ch); 
        curl_close($ch); 
        return array('error' => 'cURL Error: ' . $error_message);
    }
    curl_close($ch);

  $xml_response = simplexml_load_string($response);
  if ($xml_response->success == 'true') {
 
      $counterparty_id = (string)$xml_response->data->item->Ref;

        update_option('np_counterparty_refs', $counterparty_id);
       
      return $counterparty_id;   
  } else {
    return ['error' => 'Не вдалося створити контрагента'];
  }
}

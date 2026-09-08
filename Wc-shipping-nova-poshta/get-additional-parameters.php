<?php
include_once(plugin_dir_path(__FILE__) . 'cron-daily-update.php');


//Завантажуємо типи доставок
function get_service_types() {
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
    <apiKey>' . esc_html($api_key) . '</apiKey>
    <modelName>CommonGeneral</modelName>
    <calledMethod>getServiceTypes</calledMethod>
    <methodProperties></methodProperties>
    </file>';
  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/xml'
    ));

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_message = curl_error($ch); 
        curl_close($ch); 
        return array('error' => 'cURL Error: ' . $error_message);
    }
    curl_close($ch);

    if ($response === false) {
        return array('error' => 'Error fetching data');
    }

    $xml = simplexml_load_string($response);
    if ($xml === false) {
        return array('error' => 'Error parsing XML');
    }

    if ((string)$xml->success !== 'true') {
        return array('error' => (string)$xml->message);
    }


    $serviceType = array();
    foreach ($xml->data->item as $item) {
        $serviceType[] = array(
            'ref' => (string) $item->Ref
        );
    }

    return $serviceType;
}


//Зберігаємо відповідний тип
function save_service_types($delivery_type_recipient) {
    $delivery_type_sender = get_option('type_of_delivery_sender_option');
    $available_service_types = get_service_types();

    if ($delivery_type_recipient == 'Postomat') {
        $delivery_type_recipient = 'Warehouse';
    }

    if ($delivery_type_sender && $delivery_type_recipient) {
        $combined_type = $delivery_type_sender . $delivery_type_recipient;

        foreach ($available_service_types as $service) {
            if ($service['ref'] == $combined_type) {

                $selected_service_type = $service['ref'];
                update_option('service_type_option', $selected_service_type);
              
            }
        }
    } else {
        wp_die('Помилка');
    }
  
}

//Отримуємо габарити товару
function get_largest_dimensions_for_order($order) {
    $max_length = 0;
    $max_width = 0;
    $max_height = 0;
    $total_weight = 0;


    $has_items = false; //Прапорець для перевірки наявності товарів
    $missing_dimensions = false; //Прапорець для перевірки відсутніх габаритів

    $items_count = count($order->get_items());

    //Якщо в замовленні хоча б один товар
    if ($items_count > 0) {
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product(); 

            //Перевірка на наявність 
            if ($product && $product->is_type('simple')) {

                $length = $product->get_length();
                $width = $product->get_width();
                $height = $product->get_height();
                $weight = $product->get_weight();

                //Перевірка на відсутність 
                if (!$length || !$width || !$height) {
                    $missing_dimensions = true; //Позначаємо відсутність 
                }
                else {
                     //Додаємо вагу товару з урахуванням кількості
                        $quantity = $item->get_quantity(); 
                        $total_weight += $weight * $quantity;
                        
                        //Оновлюємо макс габарити 
                        $max_length = max($max_length, $length);
                        $max_width  = max($max_width, $width);
                        $max_height = max($max_height, $height);
                }
            }
        }

        if ($missing_dimensions) {
            return null;
        }

        update_option('total_weight_option', $total_weight);
        update_option('length_option', $max_length);
        update_option('width_option', $max_width);
        update_option('height_option', $max_height);

    } else {
        return null;
    }

    return [
        'length' => $max_length,
        'width'  => $max_width,
        'height' => $max_height,
        'weight' => $total_weight
    ];
}

//Завантажуємо контактну особу відправника
function get_contact_person_sender() {
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
    $ref = get_sender_reference();

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
    <apiKey>' . esc_html($api_key) . '</apiKey>
    <modelName>CounterpartyGeneral</modelName>
    <calledMethod>getCounterpartyContactPersons</calledMethod>
    <methodProperties>
    <Ref>' . esc_xml($ref) . '</Ref>
    </methodProperties>
    </file>';


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/xml'
    ));

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_message = curl_error($ch); 
        curl_close($ch); 
        return array('error' => 'cURL Error: ' . $error_message);
    }
    curl_close($ch);

    if ($response === false) {
        return array('error' => 'Error fetching data');
    }

    $xml = simplexml_load_string($response);
    if ($xml === false) {
        return array('error' => 'Error parsing XML');
    }

    if ((string)$xml->success !== 'true') {
        return array('error' => (string)$xml->message);
    }


    $contactPersonSender = array();
    foreach ($xml->data->item as $item) {
        $contactPersonSender[] = array(
            'ref' => (string) $item->Ref,
            'description' => (string) $item->Description
        );
    }

    return $contactPersonSender;
}

//Завантажуємо контрагентів отримувачів
function get_contact_person_recipient() {
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
    $refs = get_option('np_counterparty_refs');

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
    <apiKey>' . esc_html($api_key) . '</apiKey>
    <modelName>CounterpartyGeneral</modelName>
    <calledMethod>getCounterpartyContactPersons</calledMethod>
    <methodProperties>
    <Ref>' . esc_xml($refs) . '</Ref>
    </methodProperties>
    </file>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/xml'
    ));

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_message = curl_error($ch); 
        curl_close($ch); 
        return array('error' => 'cURL Error: ' . $error_message);
    }
    curl_close($ch);

    if ($response === false) {
        return array('error' => 'Error fetching data');
    }

    $xml = simplexml_load_string($response);
    if ($xml === false) {
        return array('error' => 'Error parsing XML');
    }

    if ((string)$xml->success !== 'true') {
        return array('error' => (string)$xml->message);
    }


    $contactPersonRecipient = array();
    foreach ($xml->data->item as $item) {
        $contactPersonRecipient[] = array(
            'ref' => (string) $item->Ref,
            'description' => (string) $item->Description
        );
    }

    return $contactPersonRecipient;
}


function find_contact_recipient_ref($order_id, $contactRecipientRef) {

    $order = wc_get_order($order_id);

    if (!$order) {
        return array('error' => 'Order not found');
    }
 
    $billing_first_name = $order->get_billing_first_name();
    $billing_last_name = $order->get_billing_last_name();
    $billing_patronymic = get_post_meta($order_id, 'billing_patronymic', true);
    $full_name = trim($billing_last_name . ' ' . $billing_first_name . ' ' . $billing_patronymic);

   //Шукаємо контактну особу
    foreach ($contactRecipientRef as $recipient) {
        if (stripos($recipient['description'], $full_name) !== false) {

            return $recipient['ref']; 
        }
    }

    return array('error' => 'Contact person not found: ' . $full_name);
}

function find_address_by_street_ref($order_id, $addressRecipientRef) {

    $addresses = get_post_meta($order_id, 'np_recipient_streets', true);
    $building = get_post_meta($order_id, 'np_buildingNumber', true);
    $flat = get_post_meta($order_id, 'np_flat', true);

    if (empty($addresses) || empty($building) || empty($flat)) {
        return array('error' => 'Incomplete address data');
    }

    $street_type = get_street_type($addresses);

    if (empty($street_type)) {
        return array('error' => 'Street type not found for: ' . $addresses);
    }

    $fullAddress = trim($addresses) . ' ' . trim($street_type) . ' ' . trim($building) . ' кв. ' . trim($flat);

    foreach ($addressRecipientRef as $address) {
       
        //Перевірка чи відповідає шуканій вулиці
        if (isset($address['description'])) {
            if (trim($address['description']) === $fullAddress) {

                return $address['ref'];  
            }
        }
    }
    return array('error' => 'Address not found: ' . $fullAddress);
}

function get_street_type($street_name) {
    global $wpdb;

    $table_name = "{$wpdb->prefix}novaposhta_street";
  
    $street_type = $wpdb->get_var($wpdb->prepare(
        "SELECT street_type FROM $table_name WHERE name = %s LIMIT 1",
        $street_name
    ));

    return $street_type ? $street_type : null; 
}

function find_address_by_street_sender_ref($order_id, $addressSenderRef) {

    $addresses = get_option('street_sender_option');
    $building = get_option('sender_building_number_option');
    $flat = get_option('sender_flat_option');

    if (empty($addresses) || empty($building) || empty($flat)) {
        return array('error' => 'Incomplete address data');
    }

    $street_type = get_street_type($addresses);

    if (empty($street_type)) {
        return array('error' => 'Street type not found for: ' . $addresses);
    }

    $fullAddress = trim($addresses) . ' ' . trim($street_type) . ' ' . trim($building) . ' кв. ' . trim($flat);

    foreach ($addressSenderRef as $address) {
        //Перевіряємо чи відповідає шуканій вулиці
        if (isset($address['description'])) {
            if (trim($address['description']) === $fullAddress) {
 
                return $address; 
            }
        }
    }
    return array('error' => 'Address not found: ' . $fullAddress);
}
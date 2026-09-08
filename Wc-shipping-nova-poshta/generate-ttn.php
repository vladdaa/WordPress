<?php

include_once(plugin_dir_path(__FILE__) . 'cron-daily-update.php');
include_once(plugin_dir_path(__FILE__) . 'np-get-account.php');
include_once(plugin_dir_path(__FILE__) . 'get-address-counterparty.php');
include_once(plugin_dir_path(__FILE__) . 'get-additional-parameters.php');

//Створення ТТН
function generate_ttn_function($order_id) {
    
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();

    $payerType =   get_post_meta($order_id, '_np_payer_type', true);
    $weight = get_option('total_weight_option');

    $serviceType = get_option('service_type_option');
    $senderRef = get_sender_reference();

    $citySender = get_option('city_ref_option');
    $getParameters = get_parameters();

    $addressSenderRef = $getParameters['ref_address_sender'];
    $addressSender = find_address_by_street_sender_ref($order_id, $addressSenderRef);
    $FindAddressSender = $addressSender['id'];

    $contactSender = get_contact_person_sender();
    $contactSenderRef = $contactSender[0]['ref'];
   
     
    $senderPhone = get_option('sender_phone_option');

    $specific_ref = get_option('np_counterparty_refs');

    $cityRecipient = get_post_meta($order_id, 'city_ref', true);

    $addressRecipientRef = $getParameters['ref_address_recipient'];

    $FindAddressRecipient = find_address_by_street_ref($order_id, $addressRecipientRef);

    $contactRecipientRef = $getParameters['ref_contact_recipient'];

    $FindContactRecipient = find_contact_recipient_ref($order_id, $contactRecipientRef);

    if (isset($FindContactRecipient['error'])) {
        return $FindContactRecipient['error'];
    }
    $contactRecipient = $FindContactRecipient;

    $recipientPhone = get_post_meta($order_id, '_billing_phone', true);

    date_default_timezone_set('Europe/Kiev');
    $date = date('d.m.Y'); 

    $description = get_post_meta($order_id, '_np_order_description', true);
    $clean_description = strip_tags($description);
    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>InternetDocumentGeneral</modelName>
        <calledMethod>save</calledMethod>
        <methodProperties>
            <PayerType>' . esc_xml($payerType) . '</PayerType>
            <PaymentMethod>Cash</PaymentMethod>
            <DateTime>' . esc_xml($date) . '</DateTime>
            <CargoType>Parcel</CargoType>
            <Weight>' . esc_xml($weight) . '</Weight>
            <ServiceType>' . esc_xml($serviceType) . '</ServiceType>
            <SeatsAmount>1</SeatsAmount>
            <Description>' . esc_xml($clean_description) . '</Description>
            <Cost>300</Cost>
            <CitySender>' . esc_xml($citySender) . '</CitySender>
            <Sender>' . esc_xml($senderRef) . '</Sender>
            <SenderAddress>' . esc_xml($FindAddressSender) . '</SenderAddress>
            <ContactSender>' . esc_xml($contactSenderRef) . '</ContactSender>
            <SendersPhone>' . esc_xml($senderPhone) . '</SendersPhone>
            <CityRecipient>' . esc_xml($cityRecipient) . '</CityRecipient>
            <Recipient>' . esc_xml($specific_ref) . '</Recipient>
            <RecipientAddress>' . esc_xml($FindAddressRecipient) . '</RecipientAddress>
            <ContactRecipient>' . esc_xml($FindContactRecipient) . '</ContactRecipient>
            <RecipientsPhone>' . esc_xml($recipientPhone) . '</RecipientsPhone>
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


    $createTTN = array();
    foreach ($xml->data->item as $item) {
        $createTTN[] = array(
            'id' => (string) $item->Ref,
            'cost' => (string) $item->CostOnSite,
            'number' => (string) $item->IntDocNumber
        );
    }
    
    if (!empty($createTTN) && isset($createTTN[0]['number'])) {
        return [
            'success' => true,
            'number' => $createTTN[0]['number'], 
            'items' => $createTTN
        ];
    }


    return [
        'success' => false,
        'error_message' => 'Помилка! Не вдалося згенерувати накладну.',
    ];
    
}

//Створення ТТН на поштомат
function get_ttn_postomat_function($order_id) {
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();

    $payerType =   get_post_meta($order_id, '_np_payer_type', true);
    $payment_method_type = get_post_meta($order_id, '_payment_method_type', true);

    date_default_timezone_set('Europe/Kiev');
    $date = date('d.m.Y'); 


    $weight = get_option('total_weight_option');
    $serviceType = get_option('service_type_option');
    $description = get_post_meta($order_id, '_np_order_description', true);
    $clean_description = strip_tags($description);
    $senderRef = get_sender_reference();


    $citySender = get_option('city_ref_option');
    $getParameters = get_parameters();

    $addressSenderRef = $getParameters['ref_address_sender'];
    $addressSender = find_address_by_street_sender_ref($order_id, $addressSenderRef);
    $FindAddressSender = $addressSender['id'];

    $contactSender = get_contact_person_sender();
    $contactSenderRef = $contactSender[0]['ref'];
     

    $senderPhone = get_option('sender_phone_option');

    $specific_ref = get_option('np_counterparty_refs');

    $cityRecipient = get_post_meta($order_id, 'city_ref', true);

    $addressRecipientRef = get_post_meta($order_id, 'np_post_office_ref', true); // реф поштомату
    

    $contactRecipientRef = $getParameters['ref_contact_recipient'];
    $FindContactRecipient = find_contact_recipient_ref($order_id, $contactRecipientRef);

    if (isset($FindContactRecipient['error'])) {
        return $FindContactRecipient['error'];
    }

    $contactRecipient = $FindContactRecipient;

    $recipientPhone = get_post_meta($order_id, '_billing_phone', true);

    $length = (float) get_option('length_option');
    $width = (float) get_option('width_option');
    $height = (float) get_option('height_option');

    $length_m = $length / 100;
    $width_m = $width / 100;
    $height_m = $height / 100;

    $volume = $length_m * $width_m * $height_m;
    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>InternetDocumentGeneral</modelName>
        <calledMethod>save</calledMethod>
        <methodProperties>
            <PayerType>' . esc_xml($payerType) . '</PayerType>
            <PaymentMethod>Cash</PaymentMethod>
            <DateTime>' . esc_xml($date) . '</DateTime>
            <CargoType>Parcel</CargoType>
            <Weight>' . esc_xml($weight) . '</Weight>
            <ServiceType>' . esc_xml($serviceType) . '</ServiceType>
            <SeatsAmount>1</SeatsAmount>
            <Description>' . esc_xml($clean_description) . '</Description>
            <Cost>300</Cost>
            <CitySender>' . esc_xml($citySender) . '</CitySender>
            <Sender>' . esc_xml($senderRef) . '</Sender>
            <SenderAddress>' . esc_xml($FindAddressSender) . '</SenderAddress>
            <ContactSender>' . esc_xml($contactSenderRef) . '</ContactSender>
            <SendersPhone>' . esc_xml($senderPhone) . '</SendersPhone>
            <CityRecipient>' . esc_xml($cityRecipient) . '</CityRecipient>
            <Recipient>' . esc_xml($specific_ref) . '</Recipient>
            <RecipientAddress>' . esc_xml($addressRecipientRef) . '</RecipientAddress>
            <ContactRecipient>' . esc_xml($contactRecipient) . '</ContactRecipient>
            <RecipientsPhone>' . esc_xml($recipientPhone) . '</RecipientsPhone>
            <OptionsSeat>
            <item>
            <volumetricVolume>' . esc_xml($volume) . '</volumetricVolume>
            <volumetricWidth>' . esc_xml($width) . '</volumetricWidth>
            <volumetricLength>' . esc_xml($length) . '</volumetricLength>
            <volumetricHeight>' . esc_xml($height) . '</volumetricHeight>
            <weight>' . esc_xml($weight) . '</weight>
            </item>
            </OptionsSeat>
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


    $createTTNPostomat = array();
    foreach ($xml->data->item as $item) {
        $createTTNPostomat[] = array(
            'id' => (string) $item->Ref,
            'cost' => (string) $item->CostOnSite,
            'number' => (string) $item->IntDocNumber
        );
    }
    
    if (!empty($createTTNPostomat) && isset($createTTNPostomat[0]['number'])) {
        return [
            'success' => true,
            'number' => $createTTNPostomat[0]['number']
        ];
    }

    return [
        'success' => false,
        'error_message' => 'Помилка! Не вдалося згенерувати накладну.',
    ];


}

//Додаткові параметри
function get_parameters() {
    $addressSender = get_address_sender();
    $addressRecipient = get_address_recipient();
    $contactRecipient = get_contact_person_recipient();


    return array (
        'ref_address_sender' => $addressSender,
        'ref_address_recipient' => $addressRecipient,
        'ref_contact_recipient' => $contactRecipient
    );
}

//Отримання списку ТТН 
function get_generate_ttn() {

    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
    $current_year = date('Y');
    $current_date = new DateTime();


    $date_time_from = (clone $current_date)->modify('-2 months')->format('d.m.Y');
    $date_time_to = $current_date->format('d.m.Y');     

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
    <apiKey>' . esc_xml($api_key) . '</apiKey>
    <modelName>InternetDocumentGeneral</modelName>
        <calledMethod>getDocumentList</calledMethod>
        <methodProperties>
               <DateTimeFrom>' . esc_xml($date_time_from) . '</DateTimeFrom>
                <DateTimeTo>' . esc_xml($date_time_to) . '</DateTimeTo>
                <Page>1</Page>
                <GetFullList>1</GetFullList>
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


    $ttnList = array();
    foreach ($xml->data->item as $item) {
        $ttnList[] = array(
            'ref' => (string) $item->Ref,
            'date' => (string) $item->DateTime,
            'number' => (string) $item->IntDocNumber
        );
    }

    return $ttnList;
}

//Видалення ТТН
function delete_ttn($ttn_numbers) {  
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
    <apiKey>' . esc_html($api_key) . '</apiKey>
    <modelName>InternetDocumentGeneral</modelName>
        <calledMethod>delete</calledMethod>
        <methodProperties>
            <DocumentRefs>' . esc_html($ttn_numbers) . '</DocumentRefs>
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


    $ttnDelete = array();
    foreach ($xml->data->item as $item) {
        $ttnDelete[] = array(
            'ref' => (string) $item->Ref
        );
    }

    if (empty($ttnDelete)) {
        return array('error' => 'No TTNs were deleted.');
    }

    return array(
        'success' => true,
        'deleted_ttns' => $ttnDelete,
    );

}
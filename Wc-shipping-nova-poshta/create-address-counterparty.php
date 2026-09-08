<?php
include_once(plugin_dir_path(__FILE__) . 'cron-daily-update.php');
include_once(plugin_dir_path(__FILE__) . 'np-get-account.php');
include_once(plugin_dir_path(__FILE__) . 'includes/Street.php');

//Створення адреси відправника
function createAddressSender() {
    $counterpartyData = np_get_counterparty_sender();
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
    $buildingNumb = get_option('sender_building_number_option');
    $flat = get_option('sender_flat_option');
    $counterparty_ref = $counterpartyData['ref'];
    $street_ref = $counterpartyData['street_ref'];

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>AddressGeneral</modelName>
        <calledMethod>save</calledMethod>
        <methodProperties>
            <CounterpartyRef>' . esc_xml($counterparty_ref) . '</CounterpartyRef>
             <StreetRef>' . esc_xml($street_ref) . '</StreetRef>
             <BuildingNumber>' . esc_xml($buildingNumb) . '</BuildingNumber>
             <Flat>' . esc_xml($flat) . '</Flat>
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


    $saveSender = array();
    foreach ($xml->data->item as $item) {
        $saveSender[] = array(
            'id' => (string) $item->Ref,
            'description' => (string) $item->Description
        );
    }
    return  $saveSender;
}


function np_get_counterparty_sender() {
    $ref = get_sender_reference(); 
    $street_ref = get_option('street_ref_option');

    return array(
        'ref' => $ref,
        'street_ref' => $street_ref
    ); 
} 

//Створення адреси контрагента отримувача
function createAddressRecipient($street_ref, $building_number, $flat) {
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
    $ref = get_option('np_counterparty_refs');

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>AddressGeneral</modelName>
        <calledMethod>save</calledMethod>
        <methodProperties>
            <CounterpartyRef>' . esc_xml($ref) . '</CounterpartyRef>
             <StreetRef>' . esc_xml($street_ref) . '</StreetRef>
             <BuildingNumber>' . esc_xml($building_number) . '</BuildingNumber>
             <Flat>' . esc_xml($flat) . '</Flat>
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

    $saveRecipient = array();
    foreach ($xml->data->item as $item) {
        $saveRecipient[] = array(
            'id' => (string) $item->Ref,
            'description' => (string) $item->Description
        );
    }
    
    return  $saveRecipient;
}



   



<?php
include_once(plugin_dir_path(__FILE__) . 'cron-daily-update.php');

//Отримуємо дані відправника
function get_sender_reference(){ 
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>Counterparty</modelName>
        <calledMethod>getCounterparties</calledMethod>
        <methodProperties>
            <CounterpartyProperty>Sender</CounterpartyProperty>
            <Page>1</Page>
        </methodProperties>
    </file>';

    $ch = curl_init();
    if ($ch === false) {
        return array();
    }

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

    $xml = simplexml_load_string($response);
    if ($xml === false) {
        return array(); 
    }

    if ((string)$xml->success !== 'true') {
        return array(); 
    }

    return (string) $xml->data->item[0]->Ref;
}

//Контактна особа 
function get_data_sender(){
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
    $ref = get_sender_reference();

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>CounterpartyGeneral</modelName>
        <calledMethod>getCounterpartyContactPersons</calledMethod>
        <methodProperties>
            <Ref>'. esc_xml($ref) .'</Ref>
            <Page>1</Page>
        </methodProperties>
    </file>';

    $ch = curl_init();
    if ($ch === false) {
        return array();
    }

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

    $xml = simplexml_load_string($response);
    if ($xml === false) {
        return array(); 
    }

    if ((string)$xml->success !== 'true') {
        return array(); 
    }

    $senderData = array();
    foreach ($xml->data->item as $item) {
        $senderData[] = array(
            'ref' => (string) $item->Ref,
            'phone' => (string) $item->Phones

        );
    }

    if (!empty($senderData)) {
        update_option('sender_phone_option', $senderData[0]['phone']);
    }
 
    return $senderData; 
}






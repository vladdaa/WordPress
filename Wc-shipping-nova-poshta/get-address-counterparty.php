<?php
include_once(plugin_dir_path(__FILE__) . 'cron-daily-update.php');
include_once(plugin_dir_path(__FILE__) . 'create-address-counterparty.php');
include_once(plugin_dir_path(__FILE__) . 'np-get-account.php');

//Отримуємо адреси
function get_address_sender(){ 
    $senderData = get_address_reference_sender();
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();

    $sender_ref = $senderData['ref']; //Референс відправника


    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>CounterpartyGeneral</modelName>
        <calledMethod>getCounterpartyAddresses</calledMethod>
        <methodProperties>
             <Ref>' . esc_xml($sender_ref) . '</Ref>
             <CounterpartyProperty>Sender</CounterpartyProperty>
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


    $getAddressSender = array();
    foreach ($xml->data->item as $item) {
        $getAddressSender[] = array(
            'id' => (string) $item->Ref,
            'description' => (string) $item->Description
        );
    }

    return  $getAddressSender;

}

function get_address_reference_sender() { 
    $ref = get_sender_reference();

    return array(
        'ref' => $ref,
    ); 
}


//Отримуємо адресу отримувача 
function get_address_recipient(){ 
    $api_key = decrypt_api_key();
    $url = update_novaposhta_data();
    $recipient_ref = get_option('np_counterparty_refs'); //референс отримувача

    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
    <file>
        <apiKey>' . esc_xml($api_key) . '</apiKey>
        <modelName>CounterpartyGeneral</modelName>
        <calledMethod>getCounterpartyAddresses</calledMethod>
        <methodProperties>
             <Ref>' . esc_xml($recipient_ref) . '</Ref>
             <CounterpartyProperty>Recipient</CounterpartyProperty>
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


    $getAddressRecipient = array();
    foreach ($xml->data->item as $item) {
        $getAddressRecipient[] = array(
            'ref' => (string) $item->Ref,
            'description' => (string) $item->Description
        );
    }
 
    return  $getAddressRecipient;
}


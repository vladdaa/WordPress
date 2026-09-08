<?php
/*
Plugin Name: WC Shipping Nova Poshta
Description: Shipping Nova Poshta.
Version: 1.0
Author: Vlada
*/

include(plugin_dir_path(__FILE__) . 'includes/main.php');
include(plugin_dir_path(__FILE__) . 'database/create-db.php');
include_once(plugin_dir_path(__FILE__) . 'cron-daily-update.php');
include_once(plugin_dir_path(__FILE__) . 'np-get-account.php');
include_once(plugin_dir_path(__FILE__) . 'create-address-counterparty.php');
include_once(plugin_dir_path(__FILE__) . 'get-address-counterparty.php');
include_once(plugin_dir_path(__FILE__) . 'get-additional-parameters.php');
include_once(plugin_dir_path(__FILE__) . 'np-create-counterparty.php');
include_once(plugin_dir_path(__FILE__) . 'generate-ttn.php');

register_activation_hook(__FILE__, 'np_activate_plugin');
register_activation_hook(__FILE__, 'np_create_db_table');

register_deactivation_hook(__FILE__, 'np_clear_options_on_deactivation');
register_deactivation_hook(__FILE__, 'np_clear_scheduled_events');

add_action('admin_enqueue_scripts', 'plugin_style_bootstrap');
add_action('admin_enqueue_scripts', 'np_scripts');
add_action('admin_enqueue_scripts', 'sender_suggestion_styles');

add_action( 'wp_ajax_save_fields_settings', 'np_save_fields_settings' );
add_action( 'wp_ajax_nopriv_save_fields_settings', 'np_save_fields_settings' );
add_action( 'wp_ajax_save_fields_sender_settings', 'np_save_fields_sender_settings' );
add_action( 'wp_ajax_nopriv_save_fields_sender_settings', 'np_save_fields_sender_settings' );
add_action('wp_ajax_np_delete_invoices_from_table', 'np_delete_invoices_from_table');
add_action('wp_ajax_nopriv_np_delete_invoices_from_table', 'np_delete_invoices_from_table');

function np_scripts() {
    wp_enqueue_script('jquery');
   wp_enqueue_script(
        'save-data-np', 
        plugins_url('/assets/js/np-save.js', __FILE__), 
        array('jquery'), 
        null, 
        true 
    );

    wp_enqueue_script(
        'city-ajax-sender', 
        plugins_url('/includes/js/city-ajax.js', __FILE__), 
        array('jquery'), 
        null, 
        true 
    );

    wp_enqueue_script(
        'warehouse-ajax-sender', 
        plugins_url('/includes/js/warehouse-ajax.js', __FILE__), 
        array('jquery'), 
        null, 
        true 
    );

    wp_enqueue_script(
        'street-ajax-sender', 
        plugins_url('/includes/js/street-ajax.js', __FILE__), 
        array('jquery'), 
        null, 
        true 
    );
    wp_localize_script('city-ajax-sender', 'ajax_object_admin', array('ajax_url' => admin_url('admin-ajax.php')));
}

function sender_suggestion_styles() {
    wp_enqueue_style( 'ListGroupStylesheetSender', plugins_url( 'assets/css/CitySuggestion.css', __FILE__ ) );
    wp_enqueue_style( 'ListGroupStylesheetsSender', plugins_url( 'assets/css/WarehouseSuggestion.css', __FILE__ ) );
}

function plugin_style_bootstrap() {
    $bootstrap_css_url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';
    wp_enqueue_style('bootstrap-css', $bootstrap_css_url);
}

add_action('admin_menu', 'wc_shipping_page');

function wc_shipping_page(){

    add_menu_page(
        'WC Shipping Nova Poshta',
        'WC Shipping Nova Poshta',
        'manage_options',
        'np_main',
        'np_main_page',
        plugin_dir_url(__FILE__) . 'assets/img/nova-poshta.svg', 
        15
    );

    add_submenu_page(
        'np_main',
        'Дані відправника',
        'Дані відправника',
        'manage_options',
        'np_main_data_sender',
        'np_main_data_sender_page'
    );

    add_submenu_page(
        'np_main',
        'Створені накладні',
        'Створені накладні',
        'manage_options',
        'np_main_invoices_created',
        'np_main_invoices_created_page'
    );
}

//Основні налаштування
function np_main_page(){

$decrypted_api_key = '';
$encrypted_data = get_option('option_encrypted_api_key');

if (!empty($encrypted_data)) {
    // Розшифровуємо API-ключ
    $decrypted_api_key = decrypt_api_key();
}

    ?>
<div id="message"></div>
<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
<div class="col-12">
        <ul class="nav nav-tabs mt-2 mb-3">
            <li class="nav-item">
                <a class="nav-link active" href="?page=np_main">Налаштування</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?page=np_main_data_sender">Дані відправника</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?page=np_main_invoices_created">Створені накладні</a>
            </li>
        </ul>
        <div class="border border-light-subtle border-top-0 p-3">
        <form id="fields_settings_form"  method="post">
        <input type="hidden" name="action" value="save_fields_settings">
                <div class="row col-5 mt-4">
                    <div class="col-md-5">
                        <label for="input_api_key" class="col-form-label">
                            <p class="fw-medium">API Key<p>
                        </label>
                    </div>
                    <div class="col-7">  
                        <input id="input_api_key" class="form-control" name="input_api_key" value="<?php echo $decrypted_api_key; ?>">
                        <small class="text-muted" style="white-space: nowrap; font-size: 75%;">У разі відсутності ключа, можна отримати його за посиланням  <br> <a href="https://new.novaposhta.ua/dashboard/settings/developers" target="_blank">new.novaposhta.ua/dashboard/settings/developers</a></small>
                    </div>
                </div>
            
            <div class="row col-5 mt-5">
                <div class="col-md-5">
                    <label for="checkbox_update_daily" class="col-form-label">
                        <p class="fw-medium">Оновлювати бази щодня<p>
                    </label>
                </div>
                <div class="col-7">  
                    <input type="checkbox" id="checkbox_update_daily" class="form-control" name="checkbox_update_daily" <?php checked(get_option('checkbox_update_daily_event_option')); ?>>
                </div>
            </div>
        </div>

        <div class="row col-6 mt-3">
            <div class="col-3">
                    <input type="submit" value="Зберегти зміни" name="button_save" class="btn btn-dark">
            </div>
        </div>
    
    </form>
</div>
<?php
}

//Налаштування даних відправника
function np_main_data_sender_page() {  
    ?>
<div id="message"></div>
<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

<div class="col-12">
        <ul class="nav nav-tabs mt-2 mb-3">
            <li class="nav-item">
                <a class="nav-link" href="?page=np_main">Налаштування</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="?page=np_main_data_sender">Дані відправника</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?page=np_main_invoices_created">Створені накладні</a>
            </li>
        </ul>
        <div class="border border-light-subtle border-top-0 p-3">
        <form id="fields_sender_settings_form"  method="post">
        <input type="hidden" name="action" value="save_fields_sender_settings">
            <div class="row col-4 mt-4">
                    <div class="col-md-5">
                        <label for="input_phone_sender" class="col-form-label">
                            <p class="fw-medium">Номер телефону<p>
                        </label>
                    </div>
                    <div class="col-7">  
                        <input id="input_phone_sender" class="form-control" name="input_phone_sender" type="tel" placeholder="380(XX)XXX-XX-XX" value="<?php echo esc_attr(get_option('sender_phone_option'));?>">
                    </div>
            </div>

            <div class="row col-4 mt-4">
                <div class="col-md-5">
                    <label for="input_description_order" class="col-form-label">
                        <p class="fw-medium">Опис товару<p>
                    </label>
                </div>
                <div class="col-7">  
                    <input id="input_description_order" class="form-control" name="input_description_order" type="text"  value="<?php echo esc_attr(get_option('description_order_option')); ?>">
                </div>
            </div>

            <div class="row col-4 mt-4">
                <div class="col-md-5">
                    <label for="select_area_sender" class="col-form-label">
                        <p class="fw-medium">Область<p>
                    </label>
                </div>
                <div class="col-7">  
                    <?php np_display_areas(); ?>
                </div>
            </div>

            <div class="row col-4 mt-4">
                <div class="col-md-5">
                    <label for="select_city_sender" class="col-form-label">
                        <p class="fw-medium">Місто<p>
                    </label>
                </div>
                <div class="col-7">  
                    <?php np_display_regions_input(); ?>
                    <small class="text-muted" style="white-space: nowrap; font-size: 75%;">Введіть перші 2-3 літери для пошуку</small>
                </div>
            </div>

            <div class="row col-4 mt-4">
                <div class="col-md-5">
                    <label for="type_of_delivery_sender" class="col-form-label">
                        <p class="fw-medium">Спосіб відправки</p>
                    </label>
                </div>
                <div class="col-7">   
                <?php $delivery_type_sender = get_option('type_of_delivery_sender_option');?>
                    <select id="type_of_delivery_sender" name="type_of_delivery_sender" class="form-control">
                        <option value="" <?php selected($delivery_type_sender, ''); ?>>Оберіть спосіб відправки</option>
                        <option value="Warehouse" <?php selected($delivery_type_sender, 'Warehouse'); ?>> Відділення</option>
                        <option value="Doors" <?php selected($delivery_type_sender, 'Doors'); ?>>Адреса</option>
                    </select>
                </div>
            </div>

            <div id="warehouse_field_sender" class="d-none row col-4 mt-4">
                <div class="col-md-5">
                    <label class="col-form-label" for="warehouse_sender"><p class="fw-medium">Відділення</p></label>
                </div>
                <div class="col-7">  
                    <?php np_display_warehouse_input(); ?>
                    <small class="text-muted" style="white-space: nowrap; font-size: 75%;">Введіть перші 2-3 літери або номер для пошуку</small>
                </div>
            </div>
            

            <div id="address_field_sender" class="d-none row col-4 mt-4">
                <div class="col-md-5"> 
                    <label class="col-form-label" for="street_sender"><p class="fw-medium">Вулиця</p></label>
                </div>
                <div class="col-7">
                   <?php  np_display_street_input(); ?>
                   <small class="text-muted" style="white-space: nowrap; font-size: 75%;">Введіть перші 2-3 літери для пошуку</small>
                </div>
            
                    <div class="col-md-5 mt-4">
                        <label class="col-form-label" for="building_number_sender"><p class="fw-medium">Будинок</p></label>
                    </div>
                    <div class="col-7 mt-4">
                        <input type="text" id="building_number_sender" name="building_number_sender" class="form-control" value="<?php echo esc_attr(get_option('sender_building_number_option')); ?>">
                    </div>
            
                    <div class="col-md-5 mt-4">
                        <label class="col-form-label" for="flat_sender"><p class="fw-medium">Квартира</p></label>
                    </div>  
                
                    <div class="col-7 mt-4">
                        <input type="text" id="flat_sender" name="flat_sender" class="form-control"  value="<?php echo esc_attr(get_option('sender_flat_option')); ?>">
                    </div>
              
            </div>

        </div>
        <div class="row col-6 mt-3">
            <div class="col-3">
                    <input type="submit" value="Зберегти зміни" name="button_save_data" class="btn btn-dark">
            </div>
        </div>
    </form>
</div>
<?php
}

function get_order_id_by_ttn($ttn_number) {
    
    // Перевіряємо, чи передано номер ТТН
    if (empty($ttn_number)) {
        return false;
    }

    //Отримуємо всі замовлення, які містять мета-дані з номером ТТН
    $args = array(
        'post_type' => 'shop_order', //Тип поста замовлення WooCommerce
        'posts_per_page' => -1, //Беремо всі замовлення
        'meta_key' => '_create_ttn_postomat', 
        'meta_value' => $ttn_number, 
        'post_status' => 'any', 
    );


    $orders = get_posts($args);

    if (!empty($orders)) {
        return $orders[0]->ID; 
    }


    return false;
}


//Сторінка створених накладних
function np_main_invoices_created_page() {
    ?>
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <div class="col-12">
        <ul class="nav nav-tabs mt-2 mb-3">
            <li class="nav-item">
                <a class="nav-link" href="?page=np_main">Налаштування</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?page=np_main_data_sender">Дані відправника</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="?page=np_main_invoices_created">Створені накладні</a>
            </li>
        </ul>
    <?php  
        $ttn_list = get_generate_ttn();
    ?>
    <form method="post" id="np_delete_ttn_form">
        <div class="mt-5">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col"><input type="checkbox" id="np_check_all" class="form-control" name="np_check_all"></th>
                    <th scope="col">ID</th>
                    <th scope="col">Номер замовлення</th>
                    <th scope="col">ТТН</th>
                    <th scope="col">Дата</th>
                    <th scope="col">Сума</th>
                    <th scope="col">Статус оплати</th>
                </tr>
                </thead>
                <tbody>
                <?php
                                
                $decrypted_api_key = '';
                $encrypted_data = get_option('option_encrypted_api_key');
                

                if (!empty($encrypted_data)) {
                    // Розшифровуємо API-ключ
                    $decrypted_api_key = decrypt_api_key();
    
                }
               
                $counter = 1; 
                foreach ($ttn_list as $ttn) {

                    if (!isset($ttn['number']) || empty($ttn['number'])) {
                        continue; 
                    }
                    $order_id = get_order_id_by_ttn($ttn['number']); 

                    if (empty($order_id)) {
                        continue; 
                    }

                    $order = wc_get_order($order_id);
                    if (!$order) {
                        continue; 
                    }

                    $order_number = $order->get_order_number();
                    $total = $order->get_total();
                    $payment_status = $order->is_paid() ? 'Оплачено' : 'Не оплачено';https://fotoprint/wp-admin/admin.php?page=wpclever
                    echo '<tr>';
                    echo '<td><input type="checkbox" class="form-control np_order_checkbox" name="np_order_checkbox[]" value=\'' . esc_attr(json_encode(array('ref' => $ttn['ref'], 'number' => $ttn['number']))) . '\'></td>';
                    echo '<td>' . esc_html($counter) . '</td>';
                    echo '<td>' . esc_html($order_number) . '</td>';
                    echo '<td><a href="https://my.novaposhta.ua/orders/printDocument/orders[]/' . esc_attr($ttn['number']) . '/type/pdf/apiKey/' . esc_attr($decrypted_api_key) . '" target="_blank">'.  $ttn['number'] . '</a></td>';
                    echo '<td>' . esc_html($ttn['date']) . '</td>';
                    echo '<td>' . esc_html($total) . '</td>';
                    echo '<td>' . esc_html($payment_status) . '</td>';
                    echo '</tr>';
                    $counter++;
                }
                            
                ?>
                </tbody>
            </table>
        </div>
        <div class="col-5 mt-5">
            <input type="submit" class="btn btn-dark" value='Видалити' id="delete_ttn_action"> 
        </div>
        </form>
        
    </div>
    
    <?php

}

//Видалення ТТН
function np_delete_invoices_from_table() {
    $ttn_refs = isset($_POST['ttn_refs']) ? array_map('sanitize_text_field', $_POST['ttn_refs']) : [];

    if (empty($ttn_refs)) {
        wp_send_json_error(array('error' => 'Немає вибраних ТТН.'));
    }

    $deleted_ttns = [];
    $deleted_ttn_ref = [];
    foreach ($ttn_refs as $ttn_ref) {

        $decoded_data = json_decode(stripslashes($ttn_ref), true);

        if (isset($decoded_data['ref']) && isset($decoded_data['number'])) {
        $ttn_ref = sanitize_text_field($decoded_data['ref']);
        
        $ttn_number = sanitize_text_field($decoded_data['number']);
       
        //Отримуємо ID замовлення
        $order_id = get_order_id_by_ttn($ttn_number);

        if ($order_id) {
            delete_post_meta($order_id, '_create_ttn_postomat');
            $deleted_ttns[] = $ttn_number; //Додаємо до списку видалених
        }
        $deleted_ttn_ref[] = $ttn_ref;
    }
}

    $ttn_numbers = implode(',', $deleted_ttn_ref); //Формуємо список ТТН
  
    $result = delete_ttn($ttn_numbers); 

    if (isset($result['error'])) {
        wp_send_json_error(array('error' => $result['error']));
    }

    if (empty($result['deleted_ttns'])) {
        wp_send_json_error(array('error' => 'Не вдалося видалити ТТН з Нової Пошти.'));
    }

    wp_send_json_success(array('deleted_ttns' => $result['deleted_ttns']));
}


//Зберігаємо основні налаштування
function np_save_fields_settings() {
    if (!isset($_POST['action']) || $_POST['action'] !== 'save_fields_settings') {
        wp_die('Invalid request');
    }

    header('Content-Type: application/xml; charset=utf-8');

    $response = array();

    if (isset($_POST['input_api_key'])) {
        $api_key = sanitize_text_field($_POST['input_api_key']);

        $encryption_key = bin2hex(random_bytes(32)); 
        $iv = openssl_random_pseudo_bytes(16); 
        $encrypted_api_key = encrypt_api_key($api_key, $encryption_key, $iv);
        
        //Збереження зашифрованих даних у форматі
        $encrypted_data = $encrypted_api_key . '::' . bin2hex($iv);
    
        update_option('option_encrypted_api_key', $encrypted_data);  
        update_option('option_encryption_key', $encryption_key); 
    
        $response['api_key'] = $encrypted_api_key;
    }
        else {
        $response['api_key'] = ''; 
    }

    if (isset($_POST['checkbox_update_daily'])) {
        update_option('checkbox_update_daily_event_option', '1');
        $response['checkbox_update_daily'] = '1';
    } else {
        update_option('checkbox_update_daily_event_option', '0');
        $response['checkbox_update_daily'] = '0';
    }

    $xml_data = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    $xml_data->addChild('status', 'success');
    $xml_data->addChild('api_key', $response['api_key']);
    $xml_data->addChild('checkbox_update_daily', $response['checkbox_update_daily']);

    echo $xml_data->asXML();
    decrypt_api_key();
    get_sender_reference(); //Отримуємо референс відправника    
    get_data_sender(); //Отримуємо номер телефону відправника
    np_schedule_update(); //Крон
    wp_die();
}

//Зберігаємо дані відправника
function np_save_fields_sender_settings() {
    if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'save_fields_sender_settings' ) {
        wp_die( 'Invalid request' );
    }

    header('Content-Type: application/xml; charset=utf-8');

    $response = array();
    if ( isset( $_POST['input_phone_sender'] ) ) {
        $phone_sender = sanitize_text_field( $_POST['input_phone_sender'] );
        update_option( 'sender_phone_option', $phone_sender );
        $response['phone_sender'] = $phone_sender;
    }
   
    if ( isset( $_POST['input_description_order'] ) ) {
        $description_order = sanitize_text_field( $_POST['input_description_order'] );
        update_option( 'description_order_option', $description_order );
        $response['description_order'] = $description_order;
    }

    if ( isset( $_POST['area_ref_sender'] ) ) {
        $phone_sender = sanitize_text_field( $_POST['area_ref_sender'] );
        update_option( 'area_sender_option', $phone_sender );
        $response['area_sender'] = $phone_sender;
    }

    if ( isset( $_POST['city_sender_input'] ) ) {
        $city_sender = sanitize_text_field( $_POST['city_sender_input'] );
        update_option( 'city_sender_option', $city_sender );
        $response['city_sender'] = $city_sender;

    }

    if ( isset( $_POST['city_sender_ref'] ) ) {
        $city_sender = sanitize_text_field( $_POST['city_sender_ref'] );
        update_option( 'city_ref_option', $city_sender );
        $response['city_ref'] = $city_sender;

    }
    
    if ( isset( $_POST['city_sender_delivery'] ) ) {
        $city_sender = sanitize_text_field( $_POST['city_sender_delivery'] );
        update_option( 'city_delivery_option', $city_sender );
        $response['city_delivery'] = $city_sender;

    }
    if ( isset( $_POST['warehouse_sender'] ) ) {
        $warehouse_sender = sanitize_text_field( $_POST['warehouse_sender'] );
        update_option( 'warehouse_sender_option', $warehouse_sender );
        $response['warehouses_sender'] = $warehouse_sender;
    }

    if ( isset( $_POST['street_sender'] ) ) {
        $street_sender = sanitize_text_field( $_POST['street_sender'] );
        update_option( 'street_sender_option', $street_sender );
        $response['streets_sender'] = $street_sender;
    }
    
    if ( isset( $_POST['street_sender_ref'] ) ) {
        $street_sender = sanitize_text_field( $_POST['street_sender_ref'] );
        update_option( 'street_ref_option', $street_sender );
        $response['streets_ref'] = $street_sender;

    }

    if ( isset( $_POST['building_number_sender'] ) ) {
        $building_sender = sanitize_text_field( $_POST['building_number_sender'] );
        update_option( 'sender_building_number_option', $building_sender );
        $response['building_numb_sender'] = $building_sender;
    }

    if ( isset( $_POST['flat_sender'] ) ) {
        $flat_sender = sanitize_text_field( $_POST['flat_sender'] );
        update_option( 'sender_flat_option', $flat_sender );
        $response['sender_flat'] = $flat_sender;
    }

    
    if ( isset( $_POST['type_of_delivery_sender'] ) ) {
        $delivery_type = sanitize_text_field( $_POST['type_of_delivery_sender'] );
        update_option( 'type_of_delivery_sender_option', $delivery_type );
    
        $response['delivery_type'] = $delivery_type;
    }
  
    $xml_data = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    $xml_data->addChild('status', 'success'); 
    $xml_data->addChild('phone_sender', $response['phone_sender']);
    $xml_data->addChild('description_order', $response['description_order']);
    $xml_data->addChild('area_sender', $response['area_sender']);
    $xml_data->addChild('city_sender', $response['city_sender']);
    $xml_data->addChild('city_ref', $response['city_ref']);
    $xml_data->addChild('city_delivery', $response['city_delivery']);

    $xml_data->addChild('delivery_type', $response['delivery_type']);
    $xml_data->addChild('warehouses_sender', $response['warehouses_sender']);
    $xml_data->addChild('streets_sender', $response['streets_sender']);
    $xml_data->addChild('streets_ref', $response['streets_ref']);
    $xml_data->addChild('building_numb_sender', $response['building_numb_sender']);
    $xml_data->addChild('sender_flat', $response['sender_flat']);  

    echo $xml_data->asXML();


    createAddressSender(); //Створюємо нову адресу
  
    wp_die();
}

//Очищуємо поля при деактивації
function np_clear_options_on_deactivation() {
    $default_options = array(
        'option_encrypted_api_key' => '',
        'option_encryption_key' => '',
        'checkbox_update_daily_event_option' => 0,
        'sender_phone_option' => '',
        'description_order_option' => '',
        'area_sender_option' => '',
        'city_sender_option' => '',
        'type_of_delivery_sender_option' => '',
        'warehouse_sender_option' => '',
        'street_sender_option' => '',
        'sender_building_number_option' => '',
        'sender_flat_option' => '',
        'np_counterparty_refs' => ''
    );

    foreach ($default_options as $option => $default_value) {
        update_option($option, $default_value);
    }
}

//Отримуємо дані відправника і назву при активації плагіну
function np_activate_plugin() {
    $blog_info = get_bloginfo('name');
    //Розшифровуємо 
    // $api_key = decrypt_api_key();
    update_option('description_order_option', $blog_info);

}

function encrypt_api_key($api_key, $encryption_key) {
    $iv = openssl_random_pseudo_bytes(16);

    //Шифруємо ключ
    $encrypted_key = openssl_encrypt($api_key, 'AES-256-CBC', $encryption_key, 0, $iv);

    return $encrypted_key . '::' . bin2hex($iv);
}




<?php

include(plugin_dir_path(__FILE__) . 'database/load-api.php');

add_action('wp', 'np_schedule_update');
add_filter('cron_schedules', 'np_add_cron_intervals');

add_action('np_update_poshtomat_event', 'np_update_poshtomat_cron');
add_action('np_update_warehouses_event', 'np_update_warehouses_cron');
add_action('np_update_cities_event', 'np_update_cities_cron');
add_action('np_update_areas_event', 'np_update_areas_cron');

function update_novaposhta_data() { 
    $url = 'https://api.novaposhta.ua/v2.0/xml/';
    return $url;
}

function np_add_cron_intervals($schedules) {

    $schedules['every_five_minute'] = array(
        'interval' => 300, // Інтервал у секундах
        'display'  => 'Once Every Five Minutes'
    );
    return $schedules;
}


function np_schedule_update() {
    $status_city = get_option('np_cities_loading_status');
    $status_warehouse = get_option('np_warehouses_loading_status');
    $status_poshtomat = get_option('np_poshtomats_loading_status');

    $daily_update_checkbox = get_option('checkbox_update_daily_event_option'); 


    if ($status_city === 'completed' && $status_warehouse === 'completed' && $status_poshtomat === 'completed' && $daily_update_checkbox === '1') {
            //Перевірка чи заплановано щоденне завантаження
        if (!wp_next_scheduled('np_update_areas_event')) {
            wp_schedule_event(time(), 'daily', 'np_update_areas_event');
        } else {
            //Видаляємо та перезаписуємо
            wp_clear_scheduled_hook('np_update_areas_event');
            wp_schedule_event(time(), 'daily', 'np_update_areas_event');
        }
        if (!wp_next_scheduled('np_update_cities_event')) {
            wp_schedule_event(time(), 'daily', 'np_update_cities_event');
        } else {
            wp_clear_scheduled_hook('np_update_cities_event');
            wp_schedule_event(time(), 'daily', 'np_update_cities_event');
            }
                
        if (!wp_next_scheduled('np_update_warehouses_event')) {
            wp_schedule_event(time(), 'daily', 'np_update_warehouses_event');
        } else {
                wp_clear_scheduled_hook('np_update_warehouses_event');
                wp_schedule_event(time(), 'daily', 'np_update_warehouses_event');
            }

        if (!wp_next_scheduled('np_update_poshtomat_event')) {
            wp_schedule_event(time(), 'daily', 'np_update_poshtomat_event');
        } else {
                wp_clear_scheduled_hook('np_update_poshtomat_event');
                wp_schedule_event(time(), 'daily', 'np_update_poshtomat_event');
            }
    } 
    elseif ($status_city !== 'completed' && $status_warehouse !== 'completed' && $status_poshtomat !== 'completed') {
        
        if ($daily_update_checkbox === '0' || $daily_update_checkbox === '1') {

            if (!wp_next_scheduled('np_update_areas_event')) {
                wp_schedule_event(time(), 'every_five_minute', 'np_update_areas_event');
            }
            if (!wp_next_scheduled('np_update_cities_event')) {
                wp_schedule_event(time(), 'every_five_minute', 'np_update_cities_event');
            }
            if (!wp_next_scheduled('np_update_warehouses_event')) {
                wp_schedule_event(time(), 'every_five_minute', 'np_update_warehouses_event');
            }
            if (!wp_next_scheduled('np_update_poshtomat_event')) {
                wp_schedule_event(time(), 'every_five_minute', 'np_update_poshtomat_event');
            }
        } 
    }
    elseif ($status_city === 'completed' && $status_warehouse === 'completed' && $status_poshtomat === 'completed' && $daily_update_checkbox === '0') {
        np_clear_scheduled_events();
    } 
    
}

function np_update_areas_cron() {
    $result = np_load_areas_into_db();

    if ($result === 'Дані успішно завантажено.') {
        wp_clear_scheduled_hook('np_update_areas_event');
    }
}

function np_update_cities_cron() {
    $result = np_load_cities_into_db();

    if ($result === 'Дані успішно завантажено.') {
        wp_clear_scheduled_hook('np_update_cities_event');
    }
}

function np_update_poshtomat_cron() {
    $result = np_load_poshtomat_into_db();
    if ($result === 'Дані успішно завантажено.') {
        wp_clear_scheduled_hook('np_update_poshtomat_event');
    }
}

function np_update_warehouses_cron() {
    $result = np_load_warehouse_into_db();
    if ($result === 'Дані успішно завантажено.') {
        wp_clear_scheduled_hook('np_update_warehouses_event');
    }
}

//Видалення запланованих задач
function np_clear_scheduled_events() {
    $timestamp = wp_next_scheduled('np_update_areas_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'np_update_areas_event');
    }

    $timestamp = wp_next_scheduled('np_update_cities_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'np_update_cities_event');
    }

    $timestamp = wp_next_scheduled('np_update_warehouses_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'np_update_warehouses_event');
    }

    $timestamp = wp_next_scheduled('np_update_poshtomat_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'np_update_poshtomat_event');
    }

    update_option('np_last_city_loaded_page', 1);
    update_option('np_last_warehouse_loaded_page', 1);
    update_option('np_last_poshtomat_loaded_page', 1);
}

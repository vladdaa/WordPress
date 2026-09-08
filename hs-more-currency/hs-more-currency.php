<?php
/*
Plugin Name: HS More Currency for WooCommerce
Description: Converts product prices according to the selected currency rate.
Version: 1.0
Author: Vlada
* Text Domain: hs-more-currency-translate
* Domain Path: /languages
*/

class HS_More_Currency_Plugin {
    public function __construct() {
        add_action('admin_enqueue_scripts', array( $this, 'hs_bootstrap' ));
        add_action('admin_menu', array( $this, 'add_settings_hs_page' ));
        add_action( 'plugins_loaded', array($this, 'load_plugin_hs_more_currency'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action( 'wp_enqueue_scripts', array( $this, 'true_jquery' ) );
        add_action('woocommerce_variation_options_pricing', array($this, 'add_currency_select'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_variation_currency'), 10, 2);

        add_action('wp_ajax_update_exchange_rate_checkbox', array($this, 'update_exchange_rate_checkbox'));
        add_action('save_post', array($this, 'save_original_price'), 10, 2);
        add_filter('woocommerce_get_price_html', array($this, 'display_converted_price'), 10, 2);
        add_action('wp_ajax_add_currency_row', array($this,'add_currency_row_callback'));
        add_action('wp_ajax_save_settings_callback',  array($this, 'save_settings'));
        add_action('wp_ajax_delete_row_callback',  array($this, 'delete_row_callback'));

        add_action('daily_update_exchange_rate_event', array($this,'daily_update_exchange_rate'));
        add_action( 'wp_ajax_get_exchange_rate', array( $this, 'get_exchange_rate_callback' ) );
        add_action( 'wp_ajax_nopriv_get_exchange_rate', array( $this, 'get_exchange_rate_callback' ) );
        add_action('wp_ajax_multiply_prices_rate', array( $this, 'multiply_prices_rate'));
        add_action('wp_ajax_nopriv_multiply_prices_rate', array( $this, 'multiply_prices_rate'));   
        add_action('admin_post_save_settings', array($this, 'save_settings'));

      
    }
   public function hs_bootstrap() {
        $bootstrap_css_url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';

        wp_enqueue_style('bootstrap-css', $bootstrap_css_url);
    }

   public function add_settings_hs_page()
    {
        add_menu_page(
            'HS More Currency for WooCommerc',
            'HS More Currency',
            'manage_options',
            'hs_more_currency_settings',
            array( $this, 'hs_more_currency_settings_page' ),
            'dashicons-money-alt',
            15

        );
    }

    public function load_plugin_hs_more_currency() {
        load_plugin_textdomain( 'hs-more-currency-translate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function register_settings() {
        register_setting('exchange_rate', 'exchange_rate');
    }

    public function true_jquery() {
        wp_enqueue_script( 'jquery' );
    }


    //Add currency select in variation card products
    public function add_currency_select($loop, $variation_data, $variation) {
        $currencies = get_option('currencies', []);
        $exchange_rates = get_option('exchange_rates', []);
    
        $currency_options = ['' => __('Default', 'woocommerce')];
        foreach ($currencies as $currency) {
            $currency_options[$currency] = $currency;
        }
    
        $variation_currency = get_post_meta($variation->ID, '_currency_select', true);
        $variation_rate = '';
        $converted_price = '';
    
        if ($variation_currency) {
            $variation_rate = isset($exchange_rates[$variation_currency]) ? $exchange_rates[$variation_currency] : '';
        }
    
        $original_price = floatval(get_post_meta($variation->ID, '_original_variation_price', true));  
    
        echo '<div class="options_group">';
        woocommerce_wp_select(
            array(
                'id'          => 'currency_select[' . $loop . ']',
                'label'       => __('Select Currency:', 'woocommerce'),
                'options'     => $currency_options,
                'value'       => $variation_currency,
            )
        );
    
        echo '</div>';
    }
        
    //Save settings variation products
    public function save_variation_currency($variation_id, $i) {
        $checkbox_value = get_option('round_up_checked_option');
        $select_round_value = get_option('round_value_option');
        $currencies = get_option('currencies', []);
        $exchange_rates = get_option('exchange_rates', []);
    
        $currency = isset($_POST['currency_select'][$i]) ? sanitize_text_field($_POST['currency_select'][$i]) : '';
    
        if (!in_array($currency, $currencies)) {
            $currency = ''; 
        }
    
        $rate = isset($exchange_rates[$currency]) ? floatval($exchange_rates[$currency]) : 0;
    
        $original_price = isset($_POST['variable_regular_price'][$i]) ? floatval($_POST['variable_regular_price'][$i]) : 0;
    
        update_post_meta($variation_id, '_currency_select', $currency);
        update_post_meta($variation_id, '_exchange_rate', $rate);
        update_post_meta($variation_id, '_original_variation_price', $original_price);

        $converted_price = $this->convert_and_update_variation_price($variation_id, $rate, $original_price);

        wp_cache_delete($variation_id, 'post_meta');
    
        wp_die();
    }
    
    
    //Convert variation products
    private function convert_and_update_variation_price($variation_id, $rate, $original_price) {
        $checkbox_value = get_option('round_up_checked_option');
        $select_round_value = get_option('round_value_option');
    
        if ($rate <= 0) {
            return $original_price; 
        }
        $converted_price = $original_price * $rate;
    
        if ($checkbox_value) {
            if ($select_round_value === 'modulo') {
                $decimal_part = $converted_price - floor($converted_price);
                if ($decimal_part >= 0.5) {
                    $converted_price = floor($converted_price) + 1;
                } else {
                    $converted_price = floor($converted_price);
                }
            } elseif ($select_round_value === 'mathematical') {
                $converted_price = round($converted_price, 1, PHP_ROUND_HALF_UP);
            } elseif ($select_round_value === 'nearest') {
          
                $remainder = $converted_price % 10;
                if ($remainder >= 5) {
                    $converted_price = floor($converted_price + (10 - $remainder));
                } else {
                    $converted_price = floor($converted_price - $remainder);
                }
            }
        }

        update_post_meta($variation_id, '_converted_variation_price', $converted_price);
    
        return $converted_price;
    }
    
    
    //Get API daily
    public function update_exchange_rate_checkbox($update_frequency) {
        update_option('update_currency_rate', $update_frequency);

        wp_clear_scheduled_hook('daily_update_exchange_rate_event');
    
        if ($update_frequency === 'Hourly') {
            wp_schedule_event(time(), 'hourly', 'daily_update_exchange_rate_event');
        } elseif ($update_frequency === 'Every six hours') {
            if (!wp_next_scheduled('daily_update_exchange_rate_event')) {
                wp_schedule_event(time(), 'six_hours', 'daily_update_exchange_rate_event');
            }
        } elseif ($update_frequency === 'Twice a day') {
            wp_schedule_event(time(), 'twicedaily', 'daily_update_exchange_rate_event');
        } elseif ($update_frequency === 'Daily') {
            wp_schedule_event(time(), 'daily', 'daily_update_exchange_rate_event');
        }
    }
    

    function daily_update_exchange_rate() {
        $update_frequency = get_option('update_currency_rate');
     
        if (!$update_frequency || $update_frequency === 'Off') {
            return; 
        }
    
        $api_url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';
        $response = wp_remote_get($api_url);
    
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            update_option('exchange_rate_data', $body);
        }
    }
    
    //Method for displaying the select field
    private function render_currencies_select() {
        $selected_exchange = get_option('exchange_select_option');
        $data_cc = get_option('exchange_rate_data'); 
    
        if (!$data_cc) {
            $api_url_cc = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';
            $response_cc = wp_remote_get($api_url_cc);
    
            if (!is_wp_error($response_cc)) {
                $body_cc = wp_remote_retrieve_body($response_cc);
                update_option('exchange_rate_data', $body_cc); 
                $data_cc = $body_cc;
            }
        }
    
        if ($data_cc) {
            $data_cc = simplexml_load_string($data_cc);
    
            foreach ($data_cc->children() as $currency_cc) {
                $get_cc = $currency_cc->cc;
                $selected = $selected_exchange === $get_cc ? 'selected' : ''; 
                echo "<option value=\"$get_cc\" $selected>$get_cc</option>";
            }
        }
    }
    

   //Method for displaying field
   private function render_rate_field(){
    ?>
    <div class="form-group">
        <input type="text" id="exchange_field" name="exchange_field" class="form-control" value="<?php echo esc_html(get_option('exchange_input_option')); ?>" pattern="\d*">
    </div>

    <script>
    jQuery(function($) {
        var lastValidValue = ''; 

        function setInputValue() {
        var selectedOption = "<?php echo get_option('exchange_select_option'); ?>";
        $('#currency_select').val(selectedOption);
        updateExchangeField(); 
    }

    function updateExchangeField() {
        var isChecked = $('#exchange_rate_checkbox').prop('checked');
        var selectedOption = $('#currency_select').val();

        if (isChecked) {
            setExchangeRate(selectedOption);
        } else {
            if (lastValidValue.trim() !== '') {
                $('#exchange_field').val(lastValidValue);
            }
        }
    }

    function setExchangeRate(selectedOption) {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'get_exchange_rate',
                currency_select: selectedOption
            },
            success: function(data) {
                lastValidValue = data; 
                $('#exchange_field').val(data); 
            }
        });
    }

    $('#exchange_field').on('input', function(e) {
        var value = e.target.value;
        e.target.value = value.replace(/[^\d.]/g, '');
    });

    $('#currency_select').on('change', function() {
        var selectedValue = $(this).val();
        var isChecked = $('#exchange_rate_checkbox').prop('checked');
        
        if (isChecked) {
            setExchangeRate(selectedValue); 
        }
        
        $.post(ajaxurl, {
            action: 'update_selected_currency',
            currency_select: selectedValue
        });
    });

    $('#exchange_rate_checkbox').change(function() {
        updateExchangeField();
        $.post(ajaxurl, {
            action: 'update_exchange_rate_checkbox',
            exchange_rate_checkbox: $(this).prop('checked'),
            currency_select: $('#currency_select').val()
        });
    });

        //Add rows
        $('#add_row_button').on('click', function(e) {
            e.preventDefault();
            
            var currency = $('#currency_select').val();
            var exchange_rate = $('#exchange_field').val();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'add_currency_row',
                    currency: currency,
                    exchange_rate: exchange_rate,
                },
                success: function(response) {
                    if (response.success) {
                        var id = response.data.id;
                        var newRow = '<tr data-id="' + id + '">' +
                            '<td class="col-1"><input type="checkbox" name="variable_product_select_checkbox[]" class="form-control variable_product_select_checkbox"></td>' +
                            '<td class="col-4">' + response.data.currency + '</td>' +
                            '<td class="col-4">' + response.data.exchange_rate + '</td>' +
                            '</tr>';
                        
                        if (response.data.updated) {
                            $('tr[data-id="' + id + '"]').replaceWith(newRow); 
                        } else {
                            $('#variable_products_body').append(newRow); 
                        }
                    } else {
                        console.log(response.data.message);
                    }
                }
            });
        });

    //Check all checkboxes
    $('#check_all').click(function() {
    var isChecked = $(this).prop('checked');
    $('.currency_select_checkbox').prop('checked', isChecked);
    });

    //Save selected rows
    $('#save_row_button').on('click', function(e) {
        e.preventDefault();
        $('#form_action').val('save_settings_callback');
        $('#variable_products_data').val(JSON.stringify(collectCurrencyData()));

        submitForm();
    });

    // Delete selected rows
    $('#delete_row_button').on('click', function(e) {
        e.preventDefault();
        $('#form_action').val('delete_row_callback');
        $('#variable_products_data').val(JSON.stringify(collectCurrencyData()));

        var selectedCurrencies = [];
        $('.currency_select_checkbox:checked').each(function() {
            var currency = $(this).closest('tr').data('id'); 
            selectedCurrencies.push(currency);
        });

        var $form = $('#combined_form');
        $form.find('input[name="row_ids[]"]').remove(); 
        selectedCurrencies.forEach(function(id) {
            $form.append('<input type="hidden" name="row_ids[]" value="' + id + '">');
        });

        submitForm();
    });

    //Submit the form
    function submitForm() {
        var formData = $('#combined_form').serialize();

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo admin_url('admin.php?page=hs_more_currency_settings&status=saved'); ?>';
                } else {
                    window.location.href = '<?php echo admin_url('admin.php?page=hs_more_currency_settings&status=error'); ?>';
                }
            },
            error: function(xhr, status, error) {
                window.location.href = '<?php echo admin_url('admin.php?page=hs_more_currency_settings&status=error'); ?>';
            }
        });
        }

        //Collect currency data
        function collectCurrencyData() {
        var data = [];
        $('#variable_products_body tr').each(function() {
        var currency = $(this).find('td:eq(1)').text().trim();
        var exchangeRate = $(this).find('td:eq(2)').text().trim();
        data.push({
            currency: currency,
            exchange_rate: exchangeRate
        });
            });
        return data;
        }
    
        setInputValue();
    });

    </script>
    <?php
   }

 
    //Get currency from API
    public function get_exchange_rate_callback() {
        if (isset($_POST['currency_select'])) {
            $currency_code = sanitize_text_field($_POST['currency_select']); 
            $data = get_option('exchange_rate_data');
    
            if ($data) {
                $data_xml = simplexml_load_string($data);
    
                foreach ($data_xml->children() as $currency) {
                    $clean_currency_code = trim($currency_code);
                    if ($currency->cc == $clean_currency_code) {
                        $exchange_rate = $currency->rate;
                        echo $exchange_rate;
                        wp_die();
                    }
                }
            }
        }
        wp_die();
    }
    //Add row
    public function add_currency_row_callback() {
        if (!isset($_POST['currency']) || !isset($_POST['exchange_rate'])) {
            wp_send_json_error(['message' => 'Invalid data.']);
        }
    
        $currency = sanitize_text_field($_POST['currency']);
        $exchange_rate = sanitize_text_field($_POST['exchange_rate']);
    
        $currencies = get_option('currencies', []);
        $exchange_rates = get_option('exchange_rates', []);
        $updated = false;
        
        if (in_array($currency, $currencies)) {
            $exchange_rates[$currency] = $exchange_rate;
            $updated = true;
        } else {
            $currencies[] = $currency;
            $exchange_rates[$currency] = $exchange_rate;
        }
    
        update_option('currencies', array_values($currencies));
        update_option('exchange_rates', $exchange_rates);
    
        $index = array_search($currency, $currencies);
    
        wp_send_json_success([
            'id' => $index,
            'currency' => $currency,
            'exchange_rate' => $exchange_rate,
            'updated' => $updated
        ]);
    }
    
    
    //Delete row
    public function delete_row_callback() {
        if (isset($_POST['row_ids']) && is_array($_POST['row_ids'])) {
            $row_ids = array_map('sanitize_text_field', $_POST['row_ids']);
            
            $currencies = get_option('currencies', []);
            $exchange_rates = get_option('exchange_rates', []);
            
            foreach ($row_ids as $row_id) {
                if (isset($currencies[$row_id])) {
                    $currency = $currencies[$row_id];
                    unset($currencies[$row_id]);
                    unset($exchange_rates[$currency]);
                }
            }
        
            update_option('currencies', array_values($currencies));
            update_option('exchange_rates', $exchange_rates);
        }
        wp_send_json_success();
    }
    
    
    
    //Display row
    public function render_variable_products() {
        $currencies = get_option('currencies', []);
        $exchange_rates = get_option('exchange_rates', []);
    
        if (!empty($currencies)) {
            foreach ($currencies as $index => $currency) {
                $exchange_rate = isset($exchange_rates[$currency]) ? $exchange_rates[$currency] : '';
    
                echo '<tr data-id="' . esc_attr($index) . '">' .
                    '<td class="col-1"><input type="checkbox" name="currency_select_checkbox[]" value="' . esc_attr($currency) . '" class="form-control currency_select_checkbox"></td>' .
                    '<td class="col-4">' . esc_html($currency) . '</td>' .
                    '<td class="col-4">' . esc_html($exchange_rate) . '</td>' . 
                    '</tr>';
            }
        }
    }
    
    
        
    //Display page
    public function hs_more_currency_settings_page() {
        if(isset($_GET['status']) && $_GET['status'] === 'saved'){
            ?>
            <div class="updated"><p><?php echo __('Settings are saved','hs-more-currency-translate') ?></p></div>
            <?php
        }
        elseif ($_GET['status'] === 'error') {
            ?>
            <div class="error"><p><?php echo __('Error', 'hs-more-currency-translate') ?></p></div>
            <?php
        } 
        $selected_value = get_option('update_currency_rate');

         ?>    
        <div class="wrap">
                <div class="row">
                        <div class="col-7">
                            <h1 class="ms-2"><?php echo __('HS More Currency Settings', 'hs-more-currency-translate')?></h1>
                            <div class="col-11">
                                <form id="combined_form" method="post" action="">
                                <input type="hidden" name="action" id="form_action" value="">
                                <input type="hidden" name="variable_products_data" id="variable_products_data" value="">  
                                    <table class="table mt-3">
                                            <thead>
                                                <tr>
                                                    <th class="col-4"><?php echo __('Currency', 'hs-more-currency-translate')?></th>
                                                    <th class="col-4"><?php echo __('Exchange rate', 'hs-more-currency-translate')?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="currency_settings_body">
                                                <tr>
                                                    <td class="col-3">
                                                        <div class="form-group">
                                                            <select name="currency_select" id="currency_select" class="form-control">
                                                            <?php
                                                            $this->render_currencies_select()
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="col-3">
                                                        <?php
                                                        $this->render_rate_field()
                                                            ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                    </table>
                            </div>
                        </div>
                       
                <div class="col-5">
                    <div class="d-flex justify-content-center">
                        <h1><?php echo __('Variable products', 'hs-more-currency-translate')?></h1>
                    </div>
                        <table class="table mt-3"> 
                            <thead>
                                <tr>
                                <th class="col-1"><input type="checkbox" id="check_all" name="check_all"></th>
                                <th class="col-4"><?php echo __('Currency', 'hs-more-currency-translate'); ?></th>
                                <th class="col-4"><?php echo __('Exchange rate', 'hs-more-currency-translate'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="variable_products_body">
                                <?php $this->render_variable_products(); ?>
                            </tbody>
                         </table>
                         <button type="submit" id="delete_row_button" class="btn btn-dark mt-3"><?php echo __('Delete', 'hs-more-currency-translate'); ?></button>
                    </div>         
                </div>   
                <div class="col-7">
                        <div class="mb-2 row align-items-center">
                            <div class="col-md-3">
                                <?php $round_select_value = get_option('round_value_option');?>
                                <select id="round_option_select" name="round_option_select" class="form-control">
                                    <option value="modulo" <?php selected($round_select_value, 'modulo');?>><?php echo __('Modulo', 'hs-more-currency-translate')?></option>
                                    <option value="mathematical" <?php selected($round_select_value, 'mathematical');?>><?php echo __('Mathematical', 'hs-more-currency-translate')?></option>
                                    <option value="nearest" <?php selected($round_select_value, 'nearest');?>><?php echo __('Until the nearest', 'hs-more-currency-translate')?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input id="exchange_rate_round_up" name="exchange_rate_round_up" type="checkbox" class="form-control ms-2" <?php checked(get_option('round_up_checked_option')); ?>>
                                <label for="exchange_rate_round_up" class="ms-1"><?php echo __('Round up', 'hs-more-currency-translate')?></label>
                            </div>
                            <div class="col-md-6 ">
                                <input id="exchange_rate_checkbox" name="exchange_rate_checkbox" type="checkbox" class="form-control ms-2" <?php checked(get_option('exchange_rate_checked_option')); ?>>
                                <label for="exchange_rate_checkbox" class="ms-1 mb-0"><?php echo __('Automatically display the exchange rate', 'hs-more-currency-translate')?></label>
                            </div>
                      
                            <div class="row col-9 mt-4 d-flex align-items-center">
                                <div class="col-md-3">
                                    <select id="update_currency_rate_select" class="form-control" name="update_currency_rate_select">
                                    <option value="Off" <?php selected($selected_value, 'Off'); ?>>Off</option>
                                    <option value="Hourly" <?php selected($selected_value, 'Hourly'); ?>>Hourly</option>
                                    <option value="Every six hours" <?php selected($selected_value, 'Every six hours'); ?>>Every six hours</option>
                                    <option value="Twice a day" <?php selected($selected_value, 'Twice a day'); ?>>Twice a day</option>
                                    <option value="Daily" <?php selected($selected_value, 'Daily'); ?>>Daily</option>
                                    </select>
                                </div>
                                <div class="col-9 d-flex align-items-center">
                                    <label for="update_currency_rate_select" class="mb-0 ms-2">
                                        <?php _e('Currency rate update frequency', 'hs-more-currency-translate')?>
                                    </label>
                                </div>
                            </div>

                    </div>
                    <button type="submit" id="save_row_button" class="btn btn-dark mt-3 me-2"><?php echo __('Save', 'hs-more-currency-translate')?></button>
                    <button type="submit" id="add_row_button" name="add_row_button" class="btn btn-dark mt-3"><?php echo __('Add currency', 'hs-more-currency-translate')?></button>
                   
                </div>

                </div>
                </form>
    </div>       
        <?php
    }

     //Get original price
     public function save_original_price($post_id, $post) {
        if ($post->post_type !== 'product' || wp_is_post_autosave($post_id)) {
            return;
        }
    
        $product = wc_get_product($post_id);
        if (!$product) {
            return;
        }
    
        $original_price = $product->get_regular_price();
        update_post_meta($post_id, '_original_price', $original_price);
    
        wc_delete_product_transients($post_id);
    }
    

    //Сonverting product prices
    public function get_converted_price($product_id) {
        $original_price = floatval(get_post_meta($product_id, '_original_price', true));
    
       
        $manual_rate = get_option('exchange_input_option');
    
        $rate = (float) $manual_rate;
    
        $converted_price = $original_price * $rate;
    
        return $converted_price;
    }
    
    
    //Rounding prices
    public function multiply_prices_rate(){
        $checkbox_value = get_option('round_up_checked_option');
        $select_round_value = get_option('round_value_option');
        $products = wc_get_products(array(
            'status' => 'publish',
            'limit' => -1,
        ));
    
        if ($products) {
            foreach ($products as $product) {
                $product_id = $product->get_id();

                $original_price = get_post_meta($product_id, '_original_price', true);
                if (!$original_price) {
                    $original_price = $product->get_price(); 
  
                    update_post_meta($product_id, '_original_price', $original_price);
                }
              
                  $converted_price = $this->get_converted_price($product_id);
                
            
                if($checkbox_value){
                    if($select_round_value === 'modulo') {
    
                        $decimal_part= $converted_price - floor($converted_price);
                        if( $decimal_part >= 0.5){
                            $converted_price = floor($converted_price) + 1;
    
                        } else {
                            $converted_price = floor($converted_price);
                        }
                    }
                    elseif($select_round_value === 'mathematical') {
                         if (strpos((string)$converted_price, '.') !== false) {
    
                            $converted_price = round($converted_price, 1, PHP_ROUND_HALF_UP);
                        } else {
    
                            $converted_price = number_format($converted_price, 1);
                        }
                    }
                    elseif($select_round_value === 'nearest') {
                        $remainder = $converted_price % 10;
                        if ($remainder >= 5) {
                            $converted_price = floor($converted_price + (10 - $remainder));
                        } else {
                            $converted_price = floor($converted_price - $remainder);
                        }
                    }
                    
                }
                update_post_meta($product_id, '_converted_price', $converted_price);
                wc_delete_product_transients($price);
  
            }
            update_option('price_updated', true);
        }
    }
    

    //Display price 
    public function display_converted_price($price, $product) {
        $product_id = $product->get_id();
        $converted_price = '';
    
        if ($product->is_type('variation')) {
            $converted_price = get_post_meta($product_id, '_converted_variation_price', true);
        } else {
            $converted_price = get_post_meta($product_id, '_converted_price', true);
        }
    
        if ($converted_price === '') {
            $converted_price = $this->get_converted_price($product_id);
        }
    
        return wc_price($converted_price);
    }
    

    //Method save settings
    public function save_settings() {
       
            $currency_select_value = sanitize_text_field($_POST['currency_select']);
            $exchange_field_value = floatval($_POST['exchange_field']);
            $exchange_rate_value = isset($_POST['exchange_rate_checkbox']) ? 1 : 0;
            $exchange_rate_round_up_value = isset($_POST['exchange_rate_round_up']) ? 1 : 0;
            $round_select_value = sanitize_text_field($_POST['round_option_select']);
            $update_currency_rate = sanitize_text_field($_POST['update_currency_rate_select']);
            
            $currency_select_value_option = get_option('exchange_select_option');
            $exchange_field_value_option = get_option('exchange_input_option');
            $exchange_rate_value_option = get_option('exchange_rate_checked_option');
            $exchange_rate_round_up_option = get_option('round_up_checked_option');
            $round_select_value_option = get_option('round_value_option');
            $update_currency_rate_value_option = get_option('update_currency_rate');
            if ($currency_select_value !== $currency_select_value_option || $exchange_field_value !== $exchange_field_value_option 
            || $exchange_rate_value !== (int)$exchange_rate_value_option || $exchange_rate_round_up_value !== (int)$exchange_rate_round_up_option 
            || $round_select_value !== $round_select_value_option ||  $update_currency_rate !==  $update_currency_rate_value_option) {
    
                update_option('exchange_select_option', $currency_select_value);
                update_option('exchange_input_option', $exchange_field_value);
                update_option('exchange_rate_checked_option', $exchange_rate_value);
                update_option('round_up_checked_option', $exchange_rate_round_up_value);
                update_option('round_value_option', $round_select_value);
                update_option('update_currency_rate', $update_currency_rate);
                $this->multiply_prices_rate($exchange_field_value);

                $this->update_exchange_rate_checkbox($update_currency_rate);
    
                if (isset($_POST['variable_products_data'])) {
                    $variable_products_data = json_decode(stripslashes($_POST['variable_products_data']), true);
                    $currencies = [];
                    $exchange_rates = [];
                    
                    foreach ($variable_products_data as $product) {
                        $currency = sanitize_text_field($product['currency']);
                        $exchange_rate = sanitize_text_field($product['exchange_rate']);
                        
                        if (!in_array($currency, $currencies)) {
                            $currencies[] = $currency;
                        }
                        $exchange_rates[$currency] = $exchange_rate;
                    }
                    
                    update_option('currencies', array_values($currencies));
                    update_option('exchange_rates', $exchange_rates);
                }
    
                wp_send_json_success();
            } else {
                wp_send_json_error();
            }
    }
    
}
$hs_more_currency_plugin = new HS_More_Currency_Plugin();



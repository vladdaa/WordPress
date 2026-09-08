<?php
require_once(plugin_dir_path(__FILE__) . 'xml-feed-for-GM.php');
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

function generate_xml_feed_file() {
    $args = get_products(); 
    $query = new WP_Query($args); 
    $page_id = get_next_feed_id();
    
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->formatOutput = true;
    
    $feed = $xml->createElement("feed");
    $feed->setAttribute("xmlns", "http://www.w3.org/2005/Atom");
    $feed->setAttribute("xmlns:g", "http://base.google.com/ns/1.0");
    $xml->appendChild($feed);

    $selected_categories = (array) get_option('shopdata_category' . $page_id);
    $pre_order_option = get_option('attribute_pre_order' . $page_id);
    if (empty($pre_order_option)) {
        $pre_order_option = 'instock';
    }
    
    $shop_name = get_option('shopdata_name' . $page_id, get_bloginfo('name'));
    $shop_url = get_bloginfo('url');

    $title_element = $xml->createElement("title", $shop_name);
    $link_element = $xml->createElement("link", $shop_url);
    $feed -> appendChild($title_element);
    $feed -> appendChild($link_element); 

    $skip_missing_products = get_option('filtration_skip_products' . $page_id);

    if ($query->have_posts()) { 
        while ($query->have_posts()) {
            $query->the_post();
            global $product, $wpdb;

            $product_id = get_the_ID();
            $stock_status = get_post_meta($product_id, '_stock_status', true); 
            $stock_status_lookup = $wpdb->get_var($wpdb->prepare(
                "SELECT stock_status FROM went_wc_product_meta_lookup WHERE product_id = %d",
                $product_id
            ));
    
            if (($pre_order_option === 'instock' && $stock_status !== 'instock') || 
                ($pre_order_option === 'outofstock' && $stock_status !== 'outofstock') || 
                ($pre_order_option === 'onbackorder' && $stock_status_lookup !== 'onbackorder')) {
                continue; 
            }

            $entry = $xml->createElement("entry");

            $id = $xml->createElement("g:id", get_the_ID()); //id
            $entry->appendChild($id);

            $title = $xml->createElement("g:title", get_the_title()); //title
            $entry->appendChild($title);

            $description = get_the_content(); //description
            $description = strip_tags($description); 
            $description = mb_substr($description, 0, 500, 'UTF-8');
            $last_dot_position = mb_strrpos($description, '.', 0, 'UTF-8');
            
            if ($last_dot_position !== false) {
                $description = mb_substr($description, 0, $last_dot_position + 1, 'UTF-8');
            }
            $description_element = $xml->createElement("g:description", htmlspecialchars($description, ENT_XML1, 'UTF-8')); 
            $entry->appendChild($description_element);     

            $categories = get_the_terms(get_the_ID(), 'product_cat' . $page_id); // category
            if ($categories && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    $category_element = $xml->createElement("g:category", $category->name);
                    $entry->appendChild($category_element);
                }
            }

            $link_value = get_permalink($product_id);
            $link = $xml->createElement("link", htmlspecialchars($link_value, ENT_XML1, 'UTF-8')); //link
            $entry->appendChild($link);
    
            $image_link = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : ""; //image link
            $image_link_element = $xml->createElement("g:image_link", $image_link);
            $entry->appendChild($image_link_element);
    
            $date_value = get_the_date(); //date
            $date = $xml->createElement("date_posts", $date_value);
            $entry->appendChild($date);

            if ($stock_status === 'instock') { //stock status
                $availability = "in stock"; 
            } elseif ($stock_status === 'outofstock') {
                $availability = "out of stock"; 
            } elseif ($stock_status_lookup === 'onbackorder') {
                $availability = "on backorder";
            } else {
                $availability = '';
            }

            $availability_element = $xml->createElement("g:availability", $availability);
            $entry->appendChild($availability_element);
    
            $product_type = $xml->createElement("g:product_type", $product->get_type()); //product type
            $entry->appendChild($product_type);
    
            $product_image_gallery = get_post_meta(get_the_ID(), '_product_image_gallery', true); //additional image link
        
            $image_ids = explode(',', $product_image_gallery);
        
            if (!empty($image_ids)) {
                
                foreach ($image_ids as $image_id) {
                        $image_url = wp_get_attachment_url($image_id);
                        if ($image_url) {
                            $image_link_element = $xml->createElement('g:additional_image_link', htmlspecialchars($image_url));
                            $entry->appendChild($image_link_element);
                        }
                    }
                }
                
            $round_price_checkbox = get_option('shopdata_checkbox_currency'. $page_id); //get option checkbox round price
            $currency = get_option('woocommerce_currency'. $page_id); //get option price
            $product_price = (float) $product->get_price(); //price
            $price_with_increment = $product_price + 1;
           $decimal_part = $price_with_increment - floor($price_with_increment);
   

           if ($round_price_checkbox) {
                if ($decimal_part >= 0.5) { 
                    $update_price = floor($price_with_increment);
                } else {
                    $update_price = floor($product_price);
                }
            } else {
                $update_price = $product_price;
            }
          
            $price_text_with_currency = $update_price . ' ' . $currency;
            
            $price = $xml->createElement("g:price", $price_text_with_currency);
            $entry->appendChild($price);
    
        
            $product_id = get_the_ID(); //sale price
            $sale_price = get_post_meta($product_id, '_sale_price', true);
            $sale_price_numeric = floatval($sale_price);

            if ($round_price_checkbox) {
                if (!empty($sale_price)) {
                    $sale_price_with_increment = $sale_price_numeric  + 1;
                    $decimal_part_sail_price =  $sale_price_with_increment - floor($sale_price_with_increment);
                              
                    if ($decimal_part_sail_price >= 0.5) { 
                    $update_sale_price = floor($sale_price_with_increment);
                    } else {
                    $update_sale_price = floor($sale_price_numeric);
                    }
                          
                    $sale_price_text = $update_sale_price;
                    } else {
                    $sale_price_text = ''; 
                    }
                }
            if (!empty($sale_price)) {
                $sale_price_text = $sale_price_numeric;
            } else {
                $sale_price_text = $sale_price ?: ''; 
            }
            
            if (!empty($sale_price_text)) {
             $sale_price_text_with_currency = $sale_price_text . ' ' . $currency;
             $sale = $xml->createElement("g:sale_price", htmlspecialchars($sale_price_text_with_currency));
             $entry->appendChild($sale);
            }
          
            $condition = get_post_meta(get_the_ID(),'_condition', true) ?: 'off'; //condition
            $condition_element = $xml->createElement("g:condition", $condition);
            $entry->appendChild($condition_element);

          
            $google_product_category = get_post_meta(get_the_ID(), '_google_product_category', true);//category
            if (!empty($google_product_category) && in_array($google_product_category, $selected_categories)) {
                $google_product_category_element = $xml->createElement("g:google_product_category", $google_product_category);
                $entry->appendChild($google_product_category_element);
            }

            $country_id = get_option('main_field_country' . $page_id); //get id country
             if (!empty($country_id) ) {
                if (class_exists('WC_Countries')) {
                    $countries_obj = new WC_Countries();
                    $countries = $countries_obj->get_countries();

                    if (isset($countries[$country_id])) {
                        $country_name = $countries[$country_id];

                        $country_element = $xml->createElement('g:country', $country_name);
                        $entry->appendChild($country_element);
                    }
                }
            }
            
            $store_code = get_option('shopdata_store_code' . $page_id);//store code
            if (!empty($store_code)) {
                $store_code_element = $xml->createElement('g:store_code', $store_code);
                $entry->appendChild($store_code_element);
            }
    
            $brand = get_post_meta(get_the_ID(), '_brand', true); //brand
            if(!empty($brand)) {
                $brand_element = $xml->createElement("g:brand", $brand);
                $entry->appendChild($brand_element);
            }

            if ($product->is_type('variable')) {
                $variations = $product->get_available_variations();
                if (!empty($variations)) {
                    $variation = current($variations);
                    $product_id = $variation['variation_id'];
                }
            }
            
            $gtin = get_post_meta(get_the_ID(), '_gtin', true); //GTIN
            if(!empty($gtin)){
                $gtin_element = $xml->createElement("g:gtin", $gtin);
                $entry->appendChild($gtin_element);
            }
    
            $mpn = get_post_meta(get_the_ID(), '_mpn', true); //MPN
            if(!empty($mpn)) {
                $mpn_element = $xml->createElement("g:mpn", $mpn);
                $entry->appendChild($mpn_element);
            }
    
            $adult = get_post_meta(get_the_ID(), '_adult', true); //adult
            if(!empty($adult)) {
                $adult_element = $xml->createElement("g:adult", $adult);
                $entry->appendChild($adult_element);
            }
            else {
                $adult === 'off';
                $adult_element = $xml->createElement("g:adult", $adult);
                $entry->appendChild($adult_element);
            }
    
            $age_group = get_post_meta(get_the_ID(), '_age_group', true); //age group
            if(!empty($age_group)) {
                $age_group_element = $xml->createElement("g:age_group", $age_group);
                $entry->appendChild($age_group_element);
            }
            else {
                $age_group === 'off';
                $age_group_element = $xml->createElement("g:age_group", $age_group);
                $entry->appendChild($age_group_element);
            }
    
            $gender = get_post_meta(get_the_ID(), '_gender', true); //gender
            if(!empty($gender)) {
                $gender_element = $xml->createElement("g:gender", $gender);
                $entry->appendChild($gender_element);
            } else {
                $gender === 'off';
                $gender_element = $xml->createElement("g:gender", $gender);
                $entry->appendChild($gender_element);
            }
    
            $material = get_post_meta(get_the_ID(), '_material', true); //material
            if(!empty($material)) {
                $material_element = $xml->createElement("g:material", $material);
                $entry->appendChild($material_element);
            }
    
            $pattern = get_post_meta(get_the_ID(), '_pattern', true); //pattern
            if(!empty($pattern)) {
                $pattern_element = $xml->createElement("g:pattern", $pattern);
                $entry->appendChild($pattern_element);
            }
    
            $size = get_post_meta(get_the_ID(), '_size', true); //size
            if(!empty($size)) {
                $size_element = $xml->createElement("g:_size", $size);
                $entry->appendChild($size_element);
            }
    
            $size_type = get_post_meta(get_the_ID(), '_size_type', true); //size type
            if(!empty($size_type)) {
                $size_type_element = $xml->createElement("g:size_type", $size_type);
                $entry->appendChild($size_type_element);
            } else {
                $size_type === 'off';
                $size_type_element = $xml->createElement("g:size_type", $size_type);
                $entry->appendChild($size_type_element);
            }
    
            $color = get_post_meta(get_the_ID(), '_color', true); //color
            if (!empty($color)) {
                $color_element = $xml->createElement("g:color", $color);
                $entry->appendChild($color_element);
            }
    
            $product_highlight = get_post_meta(get_the_ID(), '_product_highlight', true); //product_highlight
            if (!empty($product_highlight)) {
                $highlights = explode(', ', $product_highlight);
            
                foreach ($highlights as $highlight) {
                    $highlight_element = $xml->createElement("g:product_highlight", $highlight);
                    $entry->appendChild($highlight_element);
                }
            }

            $feed->appendChild($entry);
        } 
  
      
    } else {
        echo 'No products found.';
    }
    
  
    header('Content-type: text/xml');
    return $xml->saveXML();
}

function get_products() {
    $page_id = get_next_feed_id();
    $selected_categories = (array) get_option('shopdata_category'.  $page_id);
    $step_of_export = get_option('main_field_export' . $page_id);    
    $export_option = get_option('filtration_export' . $page_id, 'Simple & Variable products');
    

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $step_of_export,
    );

    if (!empty($selected_categories) && in_array('Other', $selected_categories)) {
    } elseif (!empty($selected_categories)) {
        $category_terms = array_map(function ($category_url) {
            $category = get_term_by('slug', basename(parse_url($category_url, PHP_URL_PATH)), 'product_cat');
            return $category ? $category->term_id : 0;
        }, $selected_categories);

        $category_terms = array_filter($category_terms);

        if (!empty($category_terms)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_terms,
                ),
            );
        }
    }

    if ($export_option === 'Only simple products') {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'simple',
        );
    } elseif ($export_option === 'Only Variable products') {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'variable',
        );
    }

    return $args;
}


function generate_csv_feed_file() {
    $args = get_products(); 
    $query = new WP_Query($args); 

    $page_id = get_next_feed_id();
    $pre_order_option = get_option('attribute_pre_order' . $page_id, 'instock');

    $csv_content = "\xEF\xBB\xBF"; 
    $csv_content .= implode(';', [ 
        'ID', 'Title', 'Description', 'Price', 'Category', 'Link', 'Image Link',
        'Date', 'Availability', 'Product Type', 'Sale Price', 'Condition',
        'Google Product Category', 'Country', 'Store Code', 'Brand', 
        'GTIN', 'MPN', 'Adult', 'Age Group', 'Gender', 'Material', 
        'Pattern', 'Size', 'Size Type', 'Color', 'Product Highlight'
    ]) . "\n"; //header

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product, $wpdb;

            $product_id = get_the_ID();
            $stock_status = get_post_meta($product_id, '_stock_status', true);  
            $stock_status_lookup = $wpdb->get_var($wpdb->prepare(
                "SELECT stock_status FROM went_wc_product_meta_lookup WHERE product_id = %d",
                $product_id
            ));
    
            if (($pre_order_option === 'instock' && $stock_status !== 'instock') || 
                ($pre_order_option === 'outofstock' && $stock_status !== 'outofstock') || 
                ($pre_order_option === 'onbackorder' && $stock_status_lookup !== 'onbackorder')) {
                continue; 
            }

            $title = mb_convert_encoding(get_the_title(), 'UTF-8'); //title

            $description_value = strip_tags(get_the_excerpt()); //description
            $description_value = mb_substr($description_value, 0, 500);
            $description_value = str_replace(["\r", "\n", "\""], [" ", " ", '""'], $description_value);
            $description_value = mb_convert_encoding($description_value, 'UTF-8');
            $last_dot_position = mb_strrpos($description_value, '.', 0, 'UTF-8');
            if ($last_dot_position !== false) {
                $description = mb_substr($description_value, 0, $last_dot_position + 1, 'UTF-8');
            }

            $round_price_checkbox = get_option('shopdata_checkbox_currency' . $page_id);  //price
            $currency = get_option('woocommerce_currency' . $page_id); 
            $product_price = (float) $product->get_price(); //price
            $price_with_increment = $product_price + 1;
            $decimal_part = $price_with_increment - floor($price_with_increment);
            
            if ($round_price_checkbox) {
                if ($decimal_part >= 0.5) { 
                    $update_price = floor($price_with_increment);
                } else {
                    $update_price = floor($product_price);
                }
            } else {
                $update_price = $product_price;
            } 
           
            $price_with_currency = $update_price . ' ' . $currency;


            $sale_price = get_post_meta($product_id, '_sale_price', true); //sale_price
            $sale_price_numeric = floatval($sale_price);

            if ($round_price_checkbox) {
                if (!empty($sale_price)) {
                    $sale_price_with_increment = $sale_price_numeric  + 1;
                    $decimal_part_sail_price =  $sale_price_with_increment - floor($sale_price_with_increment);
                                
                if ($decimal_part_sail_price >= 0.5) { 
                    $update_sale_price = floor($sale_price_with_increment);
                    } else {
                        $update_sale_price = floor($sale_price_numeric);
                        }
                            
                    $sale_price_text = $update_sale_price;
                    } else {
                        $sale_price_text = ''; 
                    }
            }
            if (!empty($sale_price)) {
                $sale_price_text = $sale_price_numeric;
            } else {
                $sale_price_text = $sale_price ?: ''; 
            }
            
            if(!empty($sale_price_text)){
            $sale_price_with_currency =  $sale_price_text . $currency;
            }
          
            $category = implode('; ', wp_list_pluck(get_the_terms($product_id, 'product_cat'), 'name')); //category
            $link = get_permalink(); //link
            $image_link = has_post_thumbnail() ? get_the_post_thumbnail_url($product_id, 'full') : ''; //image link
            $date = get_the_date(); //date
            $availability = ($stock_status === 'instock') ? 'in stock' : ($stock_status === 'outofstock' ? 'out of stock' : 'on backorder'); //availability
            $product_type_value = $product->get_type(); //product type
           
            $condition = mb_convert_encoding(get_post_meta($product_id, '_condition', true), 'UTF-8') ?: 'off'; //condition

            $google_product_category = mb_convert_encoding(get_post_meta($product_id, '_google_product_category', true), 'UTF-8'); //google product category

            $country = get_country_name(get_option('main_field_country' . $page_id)); //country


        

            $store_code = get_option('shopdata_store_code' . $page_id); //store code
            $brand = mb_convert_encoding(get_post_meta($product_id, '_brand', true) ?? '', 'UTF-8'); // brand
            $gtin = mb_convert_encoding(get_post_meta($product_id, '_gtin', true) ?? '', 'UTF-8'); // gtin
            $mpn = mb_convert_encoding(get_post_meta($product_id, '_mpn', true) ?? '', 'UTF-8'); // mpn
            $adult = mb_convert_encoding(!empty(get_post_meta($product_id, '_adult', true)) ? get_post_meta($product_id, '_adult', true) : 'off', 'UTF-8'); // adult
            $age_group = mb_convert_encoding(!empty(get_post_meta($product_id, '_age_group', true)) ? get_post_meta($product_id, '_age_group', true) : 'off', 'UTF-8'); // age_group
            $gender = mb_convert_encoding(!empty(get_post_meta($product_id, '_gender', true)) ? get_post_meta($product_id, '_gender', true) : 'off', 'UTF-8'); // gender
            $material = mb_convert_encoding(get_post_meta($product_id, '_material', true) ?? '', 'UTF-8'); // material
            $pattern = mb_convert_encoding(get_post_meta($product_id, '_pattern', true) ?? '', 'UTF-8'); // pattern
            $size = mb_convert_encoding(get_post_meta($product_id, '_size', true) ?? '', 'UTF-8'); // size
            $size_type = mb_convert_encoding(!empty(get_post_meta($product_id, '_size_type', true)) ? get_post_meta($product_id, '_size_type', true) : 'off', 'UTF-8'); // size_type
            $color = mb_convert_encoding(get_post_meta($product_id, '_color', true) ?? '', 'UTF-8'); // color
            $product_highlight = mb_convert_encoding(get_post_meta($product_id, '_product_highlight', true) ?? '', 'UTF-8'); // product_highlight

            $csv_content .= '"' . implode('";"', [ //output file.csv
                $product_id,
                $title,
               $description,
                $price_with_currency,
                $category,
                $link,
                $image_link,
                $date,
                $availability,
                $product_type_value,
                $sale_price_with_currency,
                $condition,
                $google_product_category,
                $country,
                $store_code,
                $brand,
                $gtin,
                $mpn,
                $adult,
                $age_group,
                $gender,
                $material,
                $pattern,
                $size,
                $size_type,
                $color,
                $product_highlight
            ]) . "\"\n";
        }
    } else {
        $csv_content .= '"No products found."' . "\n";
    }
    return $csv_content;
}

function get_country_name($country_id) {
    if (!empty($country_id) && class_exists('WC_Countries')) {
        $countries_obj = new WC_Countries();
        $countries = $countries_obj->get_countries();

        if (isset($countries[$country_id])) {
            return $countries[$country_id];
        }
    }
    return '';
}

function save_files() {
    $upload_dir = wp_upload_dir();
    $feeds_dir = $upload_dir['basedir'] . '/xml-feeds/';
    $page_id = get_next_feed_id();
    //Check fields xml-feeds
    if (!file_exists($feeds_dir)) {
        wp_mkdir_p($feeds_dir); 
    }

    $feed_name = get_option('attribute_name'. $page_id);

    $xml_file_name = $feed_name . '.xml';
    $csv_file_name = $feed_name . '.csv';
    
    $xml_file_path = $feeds_dir . $xml_file_name;
    $csv_file_path = $feeds_dir . $csv_file_name;

    $xml_content = generate_xml_feed_file();
    $csv_content = generate_csv_feed_file();
   
    file_put_contents($xml_file_path, $xml_content);
    file_put_contents($csv_file_path, $csv_content);

    return array('xml' => $xml_file_name, 'csv' => $csv_file_name);
}



function create_feed_file() {
    if (isset($_POST['create_feed_file'])) {
     
        $page_id = get_next_feed_id();

          $required_options = [
            'main' . $page_id,
            'main_check' . $page_id,
            'main_field' . $page_id,
            'main_field_export' . $page_id,
            'main_field_country' . $page_id,
            'shopdata_category' . $page_id,
            'shopdata_name' . $page_id,
            'shopdata_store_code' . $page_id,
            'woocommerce_currency' . $page_id,
            'shopdata_checkbox_currency' . $page_id,
            'attribute_name' . $page_id,
            'attribute_pre_order' . $page_id,
            'filtration_export' . $page_id,
        ];

        $settings_missing = false;

        foreach ($required_options as $option_name) {
            if (get_option($option_name) === false) {
                $settings_missing = true;
                break;
            }
        }

        if ($settings_missing) {
            wp_redirect(admin_url('admin.php?page=main_settings_main&error=settings_missing'));
            exit;
        }
    

        save_files($page_id);

        reset_all_settings();

        wp_redirect(admin_url('admin.php?page=exporter'));
        exit;
    }
}



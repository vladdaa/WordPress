<?php
/*
Plugin Name: XML Feed for Google Merchant Center
Description: XML Feed plugin for Google Merchant Center.
Version: 1.0
Author: Vlada
* Text Domain: xml-feed-translate
* Domain Path: /languages
*/

require_once(plugin_dir_path(__FILE__) . 'xml_feed_generate.php');
register_deactivation_hook(__FILE__, 'xml_feed_deactivation');

add_action('admin_enqueue_scripts', 'bootstrap');
add_action('admin_init', 'main_settings_init');
add_action('admin_menu', 'add_settings_page');
add_action('admin_menu', 'add_shop_data_page');
add_action('admin_menu', 'add_attribute_settings_page');
add_action('admin_menu', 'add_filtration_page');
add_action('admin_menu', 'add_export_page');
add_action( 'plugins_loaded', 'true_load_plugin_textdomain' );
add_action('woocommerce_product_data_tabs', 'add_custom_product_data_tab');
add_action('woocommerce_product_data_panels', 'custom_product_tab_content');
add_action('woocommerce_process_product_meta', 'save_custom_product_tab_content');
add_action('woocommerce_product_options_attributes', 'display_custom_fields');


function true_load_plugin_textdomain() {
	load_plugin_textdomain( 'xml-feed-for-GM', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}


function bootstrap() {
    $bootstrap_css_url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';

    wp_enqueue_style('bootstrap-css', $bootstrap_css_url);
}

function add_settings_page()
{
	add_menu_page(
        'Settings XML Feed for GM',
        'Settings XML Feed for GM',
        'manage_options',
        'main_settings_main',
        'main_settings_page',
        'dashicons-admin-generic',
        20

    );
}



function add_shop_data_page() {
    add_submenu_page(
        'main_settings_main',
        'Shop data',
        'Shop data',
        'manage_options',
        'shop_data',
        'shop_data_page'
    );
}

function add_attribute_settings_page() {
    add_submenu_page(
        'main_settings_main',
        'Attribute settings',
        'Attribute settings',
        'manage_options',
        'attribute_settings',
        'attribute_settings_page'
    );
}

function add_filtration_page() {
    add_submenu_page(
        'main_settings_main',
        'Filtration',
        'Filtration',
        'manage_options',
        'filtration',
        'filtration_page'
    );
}

function add_export_page() {
    add_submenu_page(
        'main_settings_main',
        'Exporter',
        'Exporter',
        'manage_options',
        'exporter',
        'exporter_page'
    );
}

//----------add attribute fields--------------------
function add_custom_product_data_tab($tabs) {
    if (!isset($tabs['xml_feed_for_GM'])) {
        $tabs['xml_feed_for_GM'] = array(
            'label'    => 'XML Feed for GM',
            'target'   => 'xml_feed_for_GM',
            'class'    => array('attribute_tab'),
            'priority' => 65, 
        );
    }
    return $tabs;
}

function custom_product_tab_content() {
    global $post;

    echo '<div id="xml_feed_for_GM" class="panel woocommerce_options_panel">';
    echo '<div class="ms-2 mb-3 mt-2"><h6>Individual product settings for XML feed for GM</h6></div>';

    woocommerce_wp_select(
        array(
            'id'          => '_condition',
            'label'       => __('Condition', 'xml-feed-for-GM'),
            'description' => '',  
            'desc_tip'    => 'true',
            'options'     => array(
                'off' => __('Off', 'xml-feed-for-GM'),
                'new' => __('New', 'xml-feed-for-GM'),
                'refurbished' => __('Refurbished', 'xml-feed-for-GM'),
                'used' => __('Used', 'xml-feed-for-GM'),
            ),
        )
    );

    echo '<p class="form-field field-description">' . __('Optional element _condition.', 'xml-feed-for-GM') . '</p>';


    woocommerce_wp_text_input(
        array(
            'id'          => '_google_product_category',
            'label'       => __('Google product category', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _google_product_category.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_product_type',
            'label'       => __('Product type', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _product_type.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_brand',
            'label'       => __('Brand', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _brand.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_gtin',
            'label'       => __('GTIN', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _gtin.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_mpn',
            'label'       => __('MPN', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _mpn.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_select(
        array(
            'id'          => '_adult',
            'label'       => __('Adult', 'xml-feed-for-GM'),
            'description' => '',  
            'desc_tip'    => 'true',
            'options'     => array(
                'off' => __('Off', 'xml-feed-for-GM'),
                'yes' => __('Yes', 'xml-feed-for-GM'),
                'no' => __('No', 'xml-feed-for-GM'),
            ),
        )
    );

    echo '<p class="form-field field-description">' . __('Optional element _adult.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_select(
        array(
            'id'          => '_age_group',
            'label'       => __('Age group', 'xml-feed-for-GM'),
            'description' => '',  
            'desc_tip'    => 'true',
            'options'     => array(
                'off' => __('Off', 'xml-feed-for-GM'),
                'newborn' => __('Newborn', 'xml-feed-for-GM'),
                'infant' => __('Infant', 'xml-feed-for-GM'),
                'toddler' => __('Toddler', 'xml-feed-for-GM'),
                'kids' => __('Kids', 'xml-feed-for-GM'), 
                'adult' => __('Adult', 'xml-feed-for-GM'), 
            ),
        )
    );

    echo '<p class="form-field field-description">' . __('Optional element _age_group.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_select(
        array(
            'id'          => '_gender',
            'label'       => __('Gender', 'xml-feed-for-GM'),
            'description' => '',  
            'desc_tip'    => 'true',
            'options'     => array(
                'off' => __('Off', 'xml-feed-for-GM'),
                'male' => __('Male', 'xml-feed-for-GM'),
                'female' => __('Female', 'xml-feed-for-GM'),
                'unisex' => __('Unisex', 'xml-feed-for-GM'),
            ),
        )
    );

    echo '<p class="form-field field-description">' . __('Optional element _gender.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_material',
            'label'       => __('Material', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _material.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_pattern',
            'label'       => __('Pattern', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _pattern.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_size',
            'label'       => __('Size', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _size.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_select(
        array(
            'id'          => '_size_type',
            'label'       => __('Size type', 'xml-feed-for-GM'),
            'description' => '',  
            'desc_tip'    => 'true',
            'options'     => array(
                'off' => __('Off', 'xml-feed-for-GM'),
                'regular' => __('Regular', 'xml-feed-for-GM'),
                'petite' => __('Petite', 'xml-feed-for-GM'),
                'plus' => __('Plus', 'xml-feed-for-GM'), 
                'tall' => __('Tall', 'xml-feed-for-GM'), 
                'big' => __('Big', 'xml-feed-for-GM'), 
                'maternity' => __('Maternity', 'xml-feed-for-GM'), 
            ),
        )
    );

    echo '<p class="form-field field-description">' . __('Optional element _size_type.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_product_highlight',
            'label'       => __('Product highlight', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _product_highlight.', 'xml-feed-for-GM') . '</p>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_color',
            'label'       => __('Color', 'xml-feed-for-GM'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => '',
        )
    );
    echo '<p class="form-field field-description">' . __('Optional element _color.', 'xml-feed-for-GM') . '</p>';

    echo '</div>';
}

function save_custom_product_tab_content($post_id) {
    $condition = isset($_POST['_condition']) ? sanitize_text_field($_POST['_condition']) : '';
    $google_product_category = isset($_POST['_google_product_category']) ? sanitize_text_field($_POST['_google_product_category']) : '';
    $product_type = isset($_POST['_product_type']) ? sanitize_text_field($_POST['_product_type']) : '';
    $brand = isset($_POST['_brand']) ? sanitize_text_field($_POST['_brand']) : '';
    $gtin = isset($_POST['_gtin']) ? sanitize_text_field($_POST['_gtin']) : '';
    $mpn = isset($_POST['_mpn']) ? sanitize_text_field($_POST['_mpn']) : '';
    $adult = isset($_POST['_adult']) ? sanitize_text_field($_POST['_adult']) : '';
    $age_group = isset($_POST['_age_group']) ? sanitize_text_field($_POST['_age_group']) : '';
    $gender = isset($_POST['_gender']) ? sanitize_text_field($_POST['_gender']) : '';
    $material = isset($_POST['_material']) ? sanitize_text_field($_POST['_material']) : '';
    $pattern = isset($_POST['_pattern']) ? sanitize_text_field($_POST['_pattern']) : '';
    $size = isset($_POST['_size']) ? sanitize_text_field($_POST['_size']) : '';
    $size_type = isset($_POST['_size_type']) ? sanitize_text_field($_POST['_size_type']) : '';
    $product_highlight = isset($_POST['_product_highlight']) ? sanitize_text_field($_POST['_product_highlight']) : '';
    $color = isset($_POST['_color']) ? sanitize_text_field($_POST['_color']) : '';

    update_post_meta($post_id, '_condition', $condition);
    update_post_meta($post_id, '_google_product_category', $google_product_category);
    update_post_meta($post_id, '_product_type', $product_type);
    update_post_meta($post_id, '_brand', $brand);
    update_post_meta($post_id, '_gtin', $gtin);
    update_post_meta($post_id, '_mpn', $mpn);
    update_post_meta($post_id, '_adult', $adult);
    update_post_meta($post_id, '_age_group', $age_group);
    update_post_meta($post_id, '_gender', $gender);
    update_post_meta($post_id, '_material', $material);
    update_post_meta($post_id, '_pattern', $pattern);
    update_post_meta($post_id, '_size', $size);
    update_post_meta($post_id, '_size_type', $size_type);
    update_post_meta($post_id, '_product_highlight', $product_highlight);
    update_post_meta($post_id, '_color', $color);
}

function display_custom_fields() {
    global $product;
    if ($product && is_a($product, 'WC_Product')) {
        $condition = get_post_meta($product->get_id(), '_condition', true);
        $google_product_category = get_post_meta($product->get_id(), '_google_product_category', true);
        $product_type = get_post_meta($product->get_id(), '_product_type', true);
        $brand = get_post_meta($product->get_id(), '_brand', true);
        $gtin = get_post_meta($product->get_id(), '_gtin', true);
        $mpn = get_post_meta($product->get_id(), '_mpn', true);
        $adult = get_post_meta($product->get_id(), '_adult', true);
        $age_group = get_post_meta($product->get_id(), '_age_group', true);
        $gender = get_post_meta($product->get_id(), '_gender', true);
        $material = get_post_meta($product->get_id(), '_material', true);
        $pattern = get_post_meta($product->get_id(), '_pattern', true);
        $size = get_post_meta($product->get_id(), '_size', true);
        $size_type = get_post_meta($product->get_id(), '_size_type', true);
        $product_highlight = get_post_meta($product->get_id(), '_product_highlight', true);
        $color = get_post_meta($product->get_id(), '_color', true);

        echo '<div class="options_group">';

        echo '<p class="form-field field-condition">';
        echo '<label for="_condition">' . __('Condition', 'xml-feed-for-GM') . '</label>';
        echo '<select id="_condition" name="_condition">';
        echo '<option value="Off" ' . selected($condition, 'Off', false) . '>Off</option>';
        echo '<option value="New" ' . selected($condition, 'New', false) . '>New</option>';
        echo '<option value="Refurbished" ' . selected($condition, 'Refurbished', false) . '>Refurbished</option>';
        echo '<option value="Used" ' . selected($condition, 'Used', false) . '>Used</option>';
        echo '</select></p>';

        echo '<p class="form-field field-google-product-category"><label for="_google_product_category">' . __('Google product category', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_google_product_category" name="_google_product_category" value="' . esc_attr($google_product_category) . '"></p>';

        echo '<p class="form-field field-product-type"><label for="_product_type">' . __('Product type', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_product_type" name="_product_type" value="' . esc_attr($product_type) . '"></p>';

        echo '<p class="form-field field-brand"><label for="_brand">' . __('Brand', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_brand" name="_brand" value="' . esc_attr($brand) . '"></p>';

        echo '<p class="form-field field-gtin"><label for="_gtin">' . __('GTIN', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_gtin" name="_gtin" value="' . esc_attr($gtin) . '"></p>';

        echo '<p class="form-field field-mpn"><label for="_mpn">' . __('MPN', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_mpn" name="_mpn" value="' . esc_attr($mpn) . '"></p>';

        echo '<p class="form-field field-adult">';
        echo '<label for="_adult">' . __('Adult', 'xml-feed-for-GM') . '</label>';
        echo '<select id="_adult" name="_adult">';
        echo '<option value="Off" ' . selected($adult, 'Off', false) . '>Off</option>';
        echo '<option value="Yes" ' . selected($adult, 'Yes', false) . '>Yes</option>';
        echo '<option value="No" ' . selected($adult, 'No', false) . '>No</option>';
        echo '</select></p>';

        echo '<p class="form-field field-age-group">';
        echo '<label for="_age_group">' . __('Age group', 'xml-feed-for-GM') . '</label>';
        echo '<select id="_age_group" name="_age_group">';
        echo '<option value="Off" ' . selected($age_group, 'Off', false) . '>Off</option>';
        echo '<option value="Newborn" ' . selected($age_group, 'Newborn', false) . '>Newborn</option>';
        echo '<option value="Infant" ' . selected($age_group, 'Infant', false) . '>Infant</option>';
        echo '<option value="Toddler" ' . selected($age_group, 'Toddler', false) . '>Toddler</option>';
        echo '<option value="Kids" ' . selected($age_group, 'Kids', false) . '>Kids</option>';
        echo '<option value="Adult" ' . selected($age_group, 'Adult', false) . '>Adult</option>';
        echo '</select></p>';
       
        
        echo '<p class="form-field field-gender">';
        echo '<label for="_gender">' . __('Gender', 'xml-feed-for-GM') . '</label>';
        echo '<select id="_gender" name="_gender">';
        echo '<option value="Off" ' . selected($gender, 'Off', false) . '>Off</option>';
        echo '<option value="Male" ' . selected($gender, 'Male', false) . '>Male</option>';
        echo '<option value="Female" ' . selected($gender, 'Female', false) . '>Female</option>';
        echo '<option value="Unisex" ' . selected($gender, 'Unisex', false) . '>Unisex</option>';
        echo '</select></p>';

        echo '<p class="form-field field-material"><label for="_material">' . __('Material', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_material" name="_material" value="' . esc_attr($material) . '"></p>';

        echo '<p class="form-field field-pattern"><label for="_pattern">' . __('Pattern', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_pattern" name="_pattern" value="' . esc_attr($pattern) . '"></p>';

        echo '<p class="form-field field-size"><label for="_size">' . __('Size', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_size" name="_size" value="' . esc_attr($size) . '"></p>';

        echo '<p class="form-field field-size-type">';
        echo '<label for="_size_type">' . __('Size Type', 'xml-feed-for-GM') . '</label>';
        echo '<select id="_size_type" name="_size_type">';
        echo '<option value="Off" ' . selected($size_type, 'Off', false) . '>Off</option>';
        echo '<option value="Regular" ' . selected($size_type, 'Regular', false) . '>Regular</option>';
        echo '<option value="Petite" ' . selected($size_type, 'Petite', false) . '>Petite</option>';
        echo '<option value="Plus" ' . selected($size_type, 'Plus', false) . '>Plus</option>';
        echo '<option value="Tall" ' . selected($size_type, 'Tall', false) . '>Tall</option>';
        echo '<option value="Big" ' . selected($size_type, 'Big', false) . '>Big</option>';
        echo '<option value="Maternity" ' . selected($size_type, 'Maternity', false) . '>Maternity</option>';
        echo '</select></p>';
       
        echo '<p class="form-field field-product-highlight"><label for="_product_highlight">' . __('Product highlight', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_product_highlight" name="_product_highlight" value="' . esc_attr($product_highlight) . '"></p>';

        echo '<p class="form-field field-color"><label for="_color">' . __('Color', 'xml-feed-for-GM') . '</label>';
        echo '<input type="text" id="_color" name="_color" value="' . esc_attr($color) . '"></p>';

        echo '</div>';
    } 
}

//-----------register settings-------------------------
function main_settings_init()
{
	register_setting('main', 'main');
}

//---------------main page------------------------------
function main_settings_page()
{
    $current_screen = get_current_screen();
   
    if ($current_screen) {
        $page_id = get_next_feed_id();
    
    }
    
 if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') {
        ?>
        <div class="updated"><p><?php _e('Settings are saved', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'reset') {
        ?>
        <div class="updated"><p><?php _e('Settings are reset to default', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'error') {
        ?>
        <div class="error"><p><?php _e('Error. No changes have been made', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    }
}

  if (isset($_GET['error']) && $_GET['error'] === 'settings_missing') : ?>
        <div class="error notice"><p><?php _e('Please choose settings before generating the file.', 'xml-feed-for-GM'); ?></p></div>
    <?php endif;

?>
 <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
   <!------ Tabs ------>
   <div class="col-11">
    <ul class="nav nav-tabs mt-2 mb-3">
    <li class="nav-item">
    <a class="nav-link active" href="?page=main_settings_main" ><?php _e('Main settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=shop_data"><?php  _e('Shop data', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=attribute_settings"><?php  _e('Attribute settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=filtration"><?php  _e('Filtration', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=exporter"><?php  _e('Exporter', 'xml-feed-for-GM'); ?></a>
    </li>
    </ul>
   
    <div class="border border-light-subtle p-3">

 
   
    <?php  echo '<h6>Main parameters (ID: ' . esc_html($page_id) . ')</h6>';?>
    
        
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="callback_main_settings">
    
    <div class="row col-9 mt-4">
           
        <div class="col-md-5">
            <label for="input_field" class="col-form-label">
                <p class="fw-medium"><?php _e('Automatic file creation', 'xml-feed-for-GM')?><p>
            </label>
        </div>
            
        <div class="col-md-3">
            <select id="input_field" class="form-control" name="input_field">
                <option value="Off" <?php selected(get_option('main' . $page_id), 'Off'); ?>>Off</option>
                <option  <?php selected(get_option('main'. $page_id), 'Hourly'); ?>>Hourly</option>
                <option  <?php selected(get_option('main'. $page_id), 'Every six hours'); ?>>Every six hours</option>
                <option  <?php selected(get_option('main'. $page_id), 'Twice a day'); ?>>Twice a day</option>
                <option <?php selected(get_option('main'. $page_id), 'Daily'); ?>>Daily</option>
            </select>
            <small class="text-muted " style="white-space: nowrap; font-size: 75%;"><?php _e('The refresh interval on your feed.', 'xml-feed-for-GM')?></small>
        </div>
       
    </div>
  


    <div class="row col-9 mt-4">
    <div class="col-md-5">
        <label for="input_field_check" class="col-form-label">
            <p class="fw-medium"><?php _e('Update feed when updating products', 'xml-feed-for-GM')?><p>
        </label>
    </div>
        <div class="col-md-4">  
            <input type="checkbox" id="input_field_check" class="form-control" name="input_field_check" <?php checked(get_option('main_check'. $page_id)); ?>>
        </div>
    </div>


    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_field_assignment" class="col-form-label" >
                <p class="fw-medium"><?php _e('Feed assignment', 'xml-feed-for-GM')?><p>
            </label>
        </div>
        <div class="col-md-4">  
            <input id="input_field_assignment" class="form-control" placeholder="For Google" name="input_field_assignment" value="<?php echo esc_attr(get_option('main_field'. $page_id)); ?>">
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php _e('Not used in feed. Inner note for your convience', 'xml-feed-for-GM')?></small>
        </div>
    </div>

    <div class="row col-9 mt-4">
    <div class="col-md-5">
        <label for="input_field_country" class="col-form-label">
            <p class="fw-medium"><?php _e('Target country', 'xml-feed-for-GM') ?><p>
        </label>
    </div>
    <div class="col-md-4"> 
        <?php
        if (class_exists('WC_Countries')) {
            $countries_obj = new WC_Countries();
            $countries = $countries_obj->get_countries();

            echo '<select id="input_field_country" name="input_field_country">';
            foreach ($countries as $code => $name) {
                $selected = selected(get_option('main_field_country' . $page_id), $code, false);
                echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
            }
            echo '</select>';
        } else {
            echo '<p style="color:red;">WooCommerce is not activated. Unable to get country list.</p>';
        }
        ?>
        <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php _e('Select your target country', 'xml-feed-for-GM') ?></small>
    </div>
</div>

    

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_field_export" class="col-form-label">
                <p class="fw-medium"><?php _e('Step of export', 'xml-feed-for-GM')?><p>
            </label>
        </div>
        <div class="col-md-3">
            <select id="input_field_export" class="form-control" name="input_field_export">
                <option value="80" <?php selected(get_option('main_field_export' . $page_id), '80') ?>>80</option>
                <option value="200" <?php selected(get_option('main_field_export'. $page_id), '200') ?>>200</option>
                <option value="300" <?php selected(get_option('main_field_export'. $page_id), '300') ?>>300</option>
                <option value="450" <?php selected(get_option('main_field_export'. $page_id), '450') ?>>450</option>
                <option value="500" <?php selected(get_option('main_field_export'. $page_id), '500') ?>>500</option>
                <option value="800" <?php selected(get_option('main_field_export'. $page_id), '800') ?>>800</option>
                <option value="1000" <?php selected(get_option('main_field_export'. $page_id), '1000') ?>>1000</option>
            </select>
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php _e('The value affects the speed of file creation. If you have any problems with the generation </br>of the file - try to reduce the value in this field.  More than 500 can only be installed on powerful servers', 'xml-feed-for-GM')?></small>
        </div>
    </div> 

    </div>
    
         <div class="row col-7 mt-4">

            <div class="col-3">
                <input type="submit" value="<?php _e('Save Changes', 'xml-feed-for-GM'); ?>" name="submit_button" class="btn btn-primary">
            </div>

            <div class="col-4">
                <input type="submit" value="<?php _e('Reset settings', 'xml-feed-for-GM'); ?>" name="reset_button" class="btn btn-primary">
            </div>

        </div>
    
	</form>
    </div>
</div>

<?php
}

//------------------------------shop data page----------------------------------
function shop_data_page(){

$options = get_option('main');
$current_screen = get_current_screen();
if ($current_screen) {
    $page_id = get_next_feed_id();

}

 if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') {
        ?>
        <div class="updated"><p><?php _e('Settings are saved', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'reset') {
        ?>
        <div class="updated"><p><?php _e('Settings are reset to default', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'error') {
        ?>
        <div class="error"><p><?php _e('Error. No changes have been made', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    }
}

    ?>

 <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
   <!------ Tabs ------>
   <div class="col-11">
   <ul class="nav nav-tabs mt-2 mb-3">
    <li class="nav-item">
    <a class="nav-link" href="?page=main_settings_main" ><?php _e('Main settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link active" href="?page=shop_data"><?php  _e('Shop data', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=attribute_settings"><?php  _e('Attribute settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=filtration"><?php  _e('Filtration', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=exporter"><?php  _e('Exporter', 'xml-feed-for-GM'); ?></a>
    </li>
    </ul>
   
    <div class="border border-light-subtle p-3">

 
    <?php  echo '<h6>Main parameters (ID: ' . esc_html($page_id) . ')</h6>';?>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="callback_shop_data_settings">

    <div class="row col-9 mt-4">
    <div class="col-md-5">
        <label for="input_category" class="col-form-label">
            <p class="fw-medium"><?php _e('Selection of product category', 'xml-feed-for-GM'); ?><p>
        </label>
    </div>
    <div class="col-md-4">
        <?php 
        $args  = array(
            'taxonomy'   => 'product_cat', 
            'hide_empty' => false, 
            'lang'       => 'uk'
        );
        $terms = get_terms($args); 
        ?>
        <select name="input_category[]" id="input_category" multiple>
            <option value="Other" <?php selected(in_array('Other', (array) get_option('shopdata_category' . $page_id))); ?>><?php _e('Other', 'xml-feed-for-GM'); ?></option> 

            <?php
            if ($terms && !is_wp_error($terms)) { 
                foreach ($terms as $term) { 
                    $term_link = get_term_link($term); 
                    $selected  = selected(in_array($term_link, (array) get_option('shopdata_category'. $page_id)));

                    echo '<option value="' . esc_attr($term_link) . '" ' . $selected . '>' . esc_html($term->name) . '</option>'; 
                }
            }
            ?>
        </select>
        <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php _e('Specify the main category', 'xml-feed-for-GM'); ?></small>
    </div>
    </div>

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_name" class="col-form-label">
                <p class="fw-medium"><?php  _e('Shop name', 'xml-feed-for-GM'); ?><p>
            </label>
        </div>
        <div class="col-md-4">  
             <?php 
                $option = get_option('shopdata_name' . $page_id, false);
                $shop_name_value = ($option === false || $option === '') ? get_bloginfo('name') : $option;

                printf(
                '<input type="text" id="input_name" name="input_name" value="%s" class="form-control" />',
                esc_attr($shop_name_value)
            ); 
            ?>
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php  _e('Required element title. The short name of the store should not exceed 20 characters.', 'xml-feed-for-GM'); ?></small>
        </div>
    </div>

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_store_code" class="col-form-label">
                <p class="fw-medium"><?php  _e('Store code ( In most cases, you can leave this field blank)', 'xml-feed-for-GM'); ?><p>
            </label>
        </div>
        <div class="col-md-4">  
            <input id="input_store_code" class="form-control" name="input_store_code" value="<?php echo esc_attr(get_option('shopdata_store_code'. $page_id))?>">
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php  _e('Optional attribute store_code.', 'xml-feed-for-GM'); ?></small>
        </div>
    </div>

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_currency" class="col-form-label">
                <p class="fw-medium"><?php  _e('Currency', 'xml-feed-for-GM'); ?><p>
            </label>
        </div>
        <div class="col-md-4">  
            <?php 
               $currency_option = get_option('woocommerce_currency' . $page_id);
             
              printf(
                  '<input type="text" id="input_currency" name="input_currency" value="%s" class="form-control" />',
                 
                  esc_attr( $currency_option )
              );
          
            ?>
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php  _e('For example: USD.', 'xml-feed-for-GM'); ?></small>
        </div>
    </div>

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_checkbox_currency" class="col-form-label">
            <p class="fw-medium"><?php  _e('Round the price', 'xml-feed-for-GM'); ?></p>
            </label>
        </div>
            <div class="col-md-4">  
                <input type="checkbox" id="input_checkbox_currency" class="form-control" name="input_checkbox_currency" <?php checked(get_option('shopdata_checkbox_currency'. $page_id)); ?>>
            </div>
        
    </div>

    </div>

    <div class="row col-7 mt-4">

        <div class="col-3">
            <input type="submit" value="<?php _e('Save Changes', 'xml-feed-for-GM'); ?>" name="submit_button" class="btn btn-primary">
        </div>

        <div class="col-4">
            <input type="submit" value="<?php _e('Reset settings', 'xml-feed-for-GM'); ?>" name="reset_button" class="btn btn-primary">
        </div>

    </div>
    </form>
   </div>

<?php
}

// --------------------------------atributte settings page---------------------------------
function attribute_settings_page () {
    $options = get_option('main');
    $current_screen = get_current_screen();
    if ($current_screen) {
    $page_id = get_next_feed_id();
}
  if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') {
        ?>
        <div class="updated"><p><?php _e('Settings are saved', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'reset') {
        ?>
        <div class="updated"><p><?php _e('Settings are reset to default', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'error') {
        ?>
        <div class="error"><p><?php _e('Error. No changes have been made', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    }
}


 ?>
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
   <!------ Tabs ------>
   <div class="col-11">
   <ul class="nav nav-tabs mt-2 mb-3">
    <li class="nav-item">
    <a class="nav-link" href="?page=main_settings_main" ><?php _e('Main settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=shop_data"><?php  _e('Shop data', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link active" href="?page=attribute_settings"><?php  _e('Attribute settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=filtration"><?php  _e('Filtration', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=exporter"><?php  _e('Exporter', 'xml-feed-for-GM'); ?></a>
    </li>
    </ul>
   
    <div class="border border-light-subtle p-3">

 
    <?php  echo '<h6>Main parameters (ID: ' . esc_html($page_id) . ')</h6>';?>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="callback_attribute_settings">

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_attribute_name" class="col-form-label">
                <p class="fw-medium"><?php  _e('The name of your data feed', 'xml-feed-for-GM'); ?><p>
            </label>
        </div>
        <div class="col-md-4">  
        <?php 
                $option = get_option('attribute_name' . $page_id, false);
                $shop_name_value = ($option === false || $option === '') ? get_bloginfo('name') : $option;
                printf(
                '<input type="text" id="input_attribute_name" name="input_attribute_name" value="%s" class="form-control" />',
                esc_attr($shop_name_value)
            ); 
            ?>
           
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php  _e('Required element description. The name of your data feed.', 'xml-feed-for-GM'); ?></small>
        </div>
    </div>

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_stock_status" class="col-form-label">
                <p class="fw-medium"><?php  _e('For pre-order products, establish availability equal to', 'xml-feed-for-GM'); ?><p>
            </label>
        </div>
        <div class="col-md-3">
            <?php
            $pre_order_option = get_option('attribute_pre_order'. $page_id);
            global $wpdb;

            $stock_statuses = $wpdb->get_col("SELECT DISTINCT stock_status FROM {$wpdb->prefix}wc_product_meta_lookup");
            
            if (!empty($stock_statuses)) {
                echo '<select name="input_stock_status" id="input_stock_status">';
            
                $is_first = true; 
                
                foreach ($stock_statuses as $stock_status) {
                    if (empty($stock_status)) {
                        continue; 
                    }
            
                    $selected = ($pre_order_option === $stock_status) ? 'selected="selected"' : ''; 
                    echo '<option value="'  . esc_attr($stock_status) . '"' . $selected . '>' . esc_html($stock_status) . '</option>';
                    $is_first = false; 
                }
            
                echo '</select>';        
            }
            ?>
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php  _e('For pre-order products, establish availability equal to in_stock/out_of_stock/backorder', 'xml-feed-for-GM'); ?></small>
        </div>
    </div>

    </div>

    
         <div class="row col-7 mt-4">

            <div class="col-3">
                <input type="submit" value="<?php _e('Save Changes', 'xml-feed-for-GM'); ?>" name="submit_button" class="btn btn-primary">
            </div>

            <div class="col-4">
                <input type="submit" value="<?php _e('Reset settings', 'xml-feed-for-GM'); ?>" name="reset_button" class="btn btn-primary">
            </div>
        </div>
       
    </form>
</div>
    
    <?php
}

//-------------------------Filtration page----------------------------------------
function filtration_page (){
    $current_screen = get_current_screen();
    if ($current_screen) {
    $page_id = get_next_feed_id();

    }

 if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') {
        ?>
        <div class="updated"><p><?php _e('Settings are saved', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'reset') {
        ?>
        <div class="updated"><p><?php _e('Settings are reset to default', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    } elseif ($_GET['status'] === 'error') {
        ?>
        <div class="error"><p><?php _e('Error. No changes have been made', 'xml-feed-for-GM'); ?></p></div> 
        <?php
    }
}

?>

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
   <!------ Tabs ------>
   <div class="col-11">
   <ul class="nav nav-tabs mt-2 mb-3">
    <li class="nav-item">
    <a class="nav-link" href="?page=main_settings_main" ><?php _e('Main settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=shop_data"><?php  _e('Shop data', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=attribute_settings"><?php  _e('Attribute settings', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link active" href="?page=filtration"><?php  _e('Filtration', 'xml-feed-for-GM'); ?></a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="?page=exporter"><?php  _e('Exporter', 'xml-feed-for-GM'); ?></a>
    </li>
    </ul>
   
    <div class="border border-light-subtle p-3">

 
    <?php  echo '<h6>Main parameters (ID: ' . esc_html($page_id) . ')</h6>';?>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="callback_filtration_settings">

    <div class="row col-9 mt-4">
        <div class="col-md-5">
            <label for="input_field_whot_export" class="col-form-label">
                <p class="fw-medium"><?php _e('Whot export', 'xml-feed-for-GM'); ?><p>
            </label>
        </div>
        <div class="col-md-3">
            <select id="input_field_whot_export" class="form-control" name="input_field_whot_export">
                <option value="Simple & Variable products" <?php selected(get_option('filtration_export'. $page_id), 'Simple & Variable products') ?>>Simple & Variable products</option>
                <option value="Only simple products" <?php selected(get_option('filtration_export'. $page_id), 'Only simple products') ?>>Only simple products</option>
                <option value="Only Variable products"  <?php selected(get_option('filtration_export'. $page_id), 'Only Variable products') ?>>Only Variable products</option>
            </select>
            <small class="text-muted" style="white-space: nowrap; font-size: 75%;"><?php _e('Whot export', 'xml-feed-for-GM'); ?></small>
        </div>
    </div>

    </div>
       <div class="row col-7 mt-4">

            <div class="col-3">
                <input type="submit" value="<?php _e('Save Changes', 'xml-feed-for-GM'); ?>" name="submit_button" class="btn btn-primary">
            </div>

            <div class="col-4">
                <input type="submit" value="<?php _e('Reset settings', 'xml-feed-for-GM'); ?>" name="reset_button" class="btn btn-primary">
            </div>
                       
        </div>

        <div class="row col-7 mt-3">
                <div class="col-3">
                    <input type="submit" value="<?php _e('Create feed', 'xml-feed-for-GM'); ?>" name="create_feed_file" class="btn btn-dark">
                </div>
        </div>
    </form>
</div>
<?php
}

//-------------------------Exporter page----------------------------------------
function exporter_page() {
  ?>
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <!------ Tabs ------>
    <div class="col-11">
        <ul class="nav nav-tabs mt-2 mb-3">
            <li class="nav-item">
            <a class="nav-link" href="?page=main_settings_main" ><?php _e('Main settings', 'xml-feed-for-GM'); ?></a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="?page=shop_data"><?php  _e('Shop data', 'xml-feed-for-GM'); ?></a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="?page=attribute_settings"><?php  _e('Attribute settings', 'xml-feed-for-GM'); ?></a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="?page=filtration"><?php  _e('Filtration', 'xml-feed-for-GM'); ?></a>
            </li>
            <li class="nav-item">
            <a class="nav-link active" href="?page=exporter"><?php  _e('Exporter', 'xml-feed-for-GM'); ?></a>
            </li>
        </ul>
     <div class="border border-light-subtle p-3">
     <form method="post" id="delete_feeds_form">
    <div class="row col-7">
        <div class="col-3">
            <input type="submit" value="<?php _e('Delete', 'xml-feed-for-GM'); ?>" name="delete_feed" class="btn btn-dark">
        </div>  
    </div> 
     <div class="mt-4">
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col"><input type="checkbox" id="feed_check_all" class="form-control" name="feed_check_all" <?php checked(get_option('feed_checked_option')); ?>></th>
                <th scope="col"><?php _e('Feed ID', 'xml-feed-for-GM'); ?></th>
                <th scope="col"><?php _e('XML File', 'xml-feed-for-GM'); ?></th>
                <th scope="col"><?php _e('URL', 'xml-feed-for-GM'); ?></th>
                <th scope="col"><?php _e('Automatic file creation', 'xml-feed-for-GM'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php   
                    $feed_files = get_feed_files(); 

                    foreach ($feed_files as $feed_file) {
                        ?>
                        <tr>
                        <td><input type="checkbox" class="form-control feed-checkbox" name="feed_checkbox[]" value="<?php echo esc_attr($feed_file['id']); ?>"></td>
                            <td><?php echo esc_html($feed_file['id']); ?></td>
                            <td><?php echo esc_html($feed_file['file_name']); ?>
                            <br> <small class="text-muted"><?php echo esc_html($feed_file['assignment_note']); ?></small>
                            </td>
                            <td><a href="<?php echo esc_url($feed_file['file_url']); ?>" target="_blank"><?php echo esc_html($feed_file['file_url']); ?></a></td>
                            <td><?php echo $feed_file['automatic_creation'] ?></td>
                        </tr>
                        <?php
                    }
                    ?>
            </tbody>
        </table>
    </form>
</div>
</div>
</div>
    <script>
    jQuery(document).ready(function($) {
    $('#feed_check_all').click(function() {
        var isChecked = $(this).prop('checked');
        $('.feed-checkbox').prop('checked', isChecked);
    });

    $('#delete_feeds_form').submit(function(e) {
        e.preventDefault();

        var selectedIds = [];
        $('.feed-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_selected_feeds_files',
                feed_ids: selectedIds
            },
            success: function(response) {
             
                location.reload(); 
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    });
});
</script>
<?php
}

add_action('wp_ajax_update_feed_checked_option', 'update_feed_checked_options_callback');
function update_feed_checked_options_callback() {
    if (isset($_POST['checked'])) {

        $checked = $_POST['checked'];
        update_option('feed_checked_option', $checked);
    
        wp_send_json_success();
    } 
}

add_action('wp_ajax_delete_selected_feeds_files', 'delete_selected_feeds_files_callback');

function delete_selected_feeds_files_callback() {
    if (isset($_POST['feed_ids'])) {
        $feed_ids = $_POST['feed_ids'];
        $upload_dir = wp_upload_dir();
        $feeds_dir = $upload_dir['basedir'] . '/xml-feeds/';

        //Get data of files
        $feed_files = get_feed_files();

        foreach ($feed_ids as $feed_id) {
            //Find a file by id
            $file_key = array_search($feed_id, array_column($feed_files, 'id'));

            if ($file_key !== false) {
                $file_path = $feeds_dir . $feed_files[$file_key]['file_name'];

                //Delete files
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

            }
        }
        wp_send_json_success();
    }
    wp_die();
}

function get_feed_files() {
    $upload_dir = wp_upload_dir();
    $feeds_dir = $upload_dir['basedir'] . '/xml-feeds/';
    $feed_files = array();
    if (file_exists($feeds_dir)) {
        $files = scandir($feeds_dir);
        $id_counter = 1; 
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && (pathinfo($file, PATHINFO_EXTENSION) == 'csv' || pathinfo($file, PATHINFO_EXTENSION) == 'xml')) {
                $file_path = $feeds_dir . $file;
                $file_url = $upload_dir['baseurl'] . '/xml-feeds/' . $file;
                $base_id = intval(($id_counter + 1) / 2) * 2 - 1; 
                $automatic_creation = get_option('main' . $base_id, 'Off');
                $assignment_note = get_option('main_field' . $base_id, ''); 
                
                $feed_files[] = array(
                    'id' => $id_counter++, 
                    'file_name' => $file, 
                    'file_url' => $file_url,
                    'csv_file_path' => (pathinfo($file, PATHINFO_EXTENSION) == 'csv') ? $file_path : '',
                    'xml_file_path' => (pathinfo($file, PATHINFO_EXTENSION) == 'xml') ? $file_path : '',
                    'automatic_creation' => $automatic_creation, 
                    'assignment_note' => $assignment_note,
                );
            }
        }
    }

    return $feed_files;
}
 
function get_next_feed_id() {
    $feeds = get_feed_files(); 
    $last_id = 0;
    if (!empty($feeds)) {
        foreach ($feeds as $feed) {
            if ($feed['id'] > $last_id) {
                $last_id = $feed['id'];
            }
        }
    }
    if ($last_id % 2 == 0) {
        $next_id = $last_id + 1;
    } else {
        $next_id = $last_id + 2;
    }
    return $next_id;
}
//---------------------------------Settings feed -----------------------------

add_action('init', 'setup_feed_update_schedule');
add_action('update_feed_hourly', 'update_feed_function');
add_action('update_feed_six_hours', 'update_feed_function');
add_action('update_feed_twice_day', 'update_feed_function');
add_action('update_feed_daily', 'update_feed_function');

function setup_feed_update_schedule() {

    $page_id = get_next_feed_id();
    $update_interval = get_option('main'. $page_id); 

    if ($update_interval === 'Hourly') {
        if (!wp_next_scheduled('update_feed_hourly')) {
            wp_schedule_event(time(), 'hourly', 'update_feed_hourly');
        }
    } elseif ($update_interval === 'Every six hours') {
        if (!wp_next_scheduled('update_feed_six_hours')) {
            wp_schedule_event(time(), 'six_hours', 'update_feed_six_hours');
        }
    } elseif ($update_interval === 'Twice a day') {
        if (!wp_next_scheduled('update_feed_twice_day')) {
            wp_schedule_event(time(), 'twicedaily', 'update_feed_twice_day');
        }
    } elseif ($update_interval === 'Daily') {
        if (!wp_next_scheduled('update_feed_daily')) {
            wp_schedule_event(time(), 'daily', 'update_feed_daily');
        }
    }
}


function update_feed_function() {
    $page_id = get_next_feed_id();
    $refresh_interval = get_option('main'. $page_id, 'Off'); 

    if ($refresh_interval === 'Off') {
        return;
    }

    $csv_feed_content = generate_csv_feed_file(); 
    $xml_feed_content = generate_xml_feed_file();
    $csv_file_path = wp_upload_dir()['basedir'] . '/xml-feeds/' . $page_id . '.csv';
    $xml_file_path = wp_upload_dir()['basedir'] . '/xml-feeds/' . $page_id . '.xml';

    file_put_contents($csv_file_path, $csv_feed_content);
    file_put_contents($xml_file_path, $xml_feed_content);
}


add_action('save_post_product', 'check_and_update_feed_on_product_change', 10, 3);

function check_and_update_feed_on_product_change($post_id, $post, $update) {
    if ($post->post_type !== 'product') {
        return;
    }
    $feeds = get_feed_files();
    foreach ($feeds as $feed) {
        $page_id = $feed['id'];
        $auto_update = get_option('main_check' . $page_id);

        if ($auto_update) {
            update_feed_on_product_change($page_id);
        }
    }
}

function update_feed_on_product_change($page_id) {
    $upload_dir = wp_upload_dir();
    $feeds_dir = $upload_dir['basedir'] . '/xml-feeds/';

    $feed_name = get_option('attribute_name');

    $csv_file_name = $feed_name . '.csv';
    $xml_file_name = $feed_name . '.xml';
    $csv_file_path = $feeds_dir . $csv_file_name;
    $xml_file_path = $feeds_dir . $xml_file_name;

    if (file_exists($csv_file_path)) {
        $csv_feed_content = generate_csv_feed_file(); 
        file_put_contents($csv_file_path, $csv_feed_content); 
    }

    if (file_exists($xml_file_path)) {
        $xml_feed_content = generate_xml_feed_file(); 
        file_put_contents($xml_file_path, $xml_feed_content); 
    }
}


//---------------------------------Submit and reset functions buttons -----------------------------
add_action('admin_post_callback_main_settings', 'callback_main_settings');

function callback_main_settings() {
    if (isset($_POST['submit_button'])) {
        $page_id = get_next_feed_id();
        //Get value of the fields
        $option_value = sanitize_text_field($_POST['input_field']);
        $checkbox_value = isset($_POST['input_field_check']) ? 1 : 0; 
        $field_assignment_value = $_POST['input_field_assignment'];
        $field_country_value = isset($_POST['input_field_country']) ? sanitize_text_field($_POST['input_field_country']) : '';
        $field_export_value = absint($_POST['input_field_export']);

      
        //Get the current value options
        $current_value = get_option('main' . $page_id);
        $current_checkbox_value = get_option('main_check'. $page_id);
        $current_field_assignment_value = get_option('main_field'. $page_id);
        $current_field_country = get_option('main_field_country'. $page_id, '');

        $current_field_export_value = get_option('main_field_export'. $page_id);
 
        //Checking does the new value math
        if ($option_value !== $current_value || $checkbox_value !== (int)$current_checkbox_value || $field_assignment_value !== $current_field_assignment_value || $field_country_value !== $current_field_country
        || $field_export_value !== (int)$current_field_export_value)
         {
          
            //Saved in database and update values
            update_option('main'. $page_id, $option_value);
            update_option('main_check'. $page_id, (int) $checkbox_value);
            update_option('main_field'. $page_id, $field_assignment_value);
            update_option('main_field_country'. $page_id, $field_country_value);
            update_option('main_field_export'. $page_id, $field_export_value);
            
           

            wp_redirect(admin_url('admin.php?page=main_settings_main&status=updated'));
        } else {
            //If no changes
            wp_redirect(admin_url('admin.php?page=main_settings_main&status=error'));
            exit;
        }
    } elseif (isset($_POST['reset_button'])) {
        //Call reset functions
        reset_main_settings(false);
    }
}


add_action('admin_post_reset_main_settings', 'reset_main_settings');

function reset_main_settings($is_add_new_feed) {
    $page_id = get_next_feed_id();
    //Default values

    $default_country_id = '';
    if (class_exists('WC_Countries')) {
        $countries_obj = new WC_Countries();
        $countries = $countries_obj->get_countries();
        $default_country_id = key($countries); 
    }
    update_option('main'. $page_id, 'Off');
    update_option('main_check'. $page_id, 0);
    update_option('main_field'. $page_id, '');
    update_option('main_field_export'. $page_id, '80');
    update_option('main_field_country'. $page_id, $default_country_id);

    if (!$is_add_new_feed) {
    wp_redirect(admin_url('admin.php?page=main_settings_main&status=reset'));
    exit;
    }
}

add_action('admin_post_callback_shop_data_settings', 'callback_shop_data_settings');


function callback_shop_data_settings() {
    if (isset($_POST['submit_button'])) {
        $page_id = get_next_feed_id();
        //Get value of the fields
        $shopdata_category_value = isset($_POST['input_category']) ? array_map(function($value) {
            return $value === 'Other' ? esc_attr($value) : esc_url($value);
        }, (array) $_POST['input_category']) : array();
        

        $shopdata_name_value = $_POST['input_name'];
        $shopdata_store_code_value = $_POST['input_store_code'];
        $shopdata_currency_value = $_POST['input_currency'];
        $shopdata_checkbox_currency_value = isset($_POST['input_checkbox_currency']) ? 1 : 0; 
    
        //Get the current value options
        $current_shopdata_category_value = get_option('shopdata_category'. $page_id);
        $current_shopdata_name_value = get_option('shopdata_name'. $page_id);
        $current_shopdata_store_code_value = get_option('shopdata_store_code'. $page_id);
        $current_shopdata_currency_value = get_option('woocommerce_currency'. $page_id);
        $current_shopdata_checkbox_currency_value = get_option('shopdata_checkbox_currency'. $page_id);

       //Checking does the new value math
        if ($shopdata_category_value !== $current_shopdata_category_value || $shopdata_name_value !== $current_shopdata_name_value 
        || $shopdata_store_code_value !== $current_shopdata_store_code_value || $shopdata_currency_value !== $current_shopdata_currency_value 
        || $shopdata_checkbox_currency_value !== $current_shopdata_checkbox_currency_value) {
            
            //Saved in database and update values
            update_option('shopdata_category'. $page_id, $shopdata_category_value);
            update_option('shopdata_name'. $page_id, $shopdata_name_value);
            update_option('shopdata_store_code'. $page_id, $shopdata_store_code_value);
            update_option('woocommerce_currency'. $page_id, $shopdata_currency_value);
            update_option('shopdata_checkbox_currency'. $page_id, $shopdata_checkbox_currency_value);
            
            wp_redirect(admin_url('admin.php?page=shop_data&status=updated'));
        } else {
            //If no changes
            wp_redirect(admin_url('admin.php?page=shop_data&status=error'));
            exit;
        }
    } elseif (isset($_POST['reset_button'])) {
        //Call reset functions
        reset_shop_data_settings(false);
    }
}

add_action('admin_post_reset_shop_data_settings', 'reset_shop_data_settings');

function reset_shop_data_settings($is_add_new_feed){
    $page_id = get_next_feed_id();
    //Default values
    $default_shop_name = get_bloginfo('name');
 
    update_option('shopdata_category'. $page_id, 'Other');
    update_option('shopdata_name'. $page_id, $default_shop_name );
    update_option('shopdata_store_code'. $page_id, '');
    update_option('woocommerce_currency'. $page_id, 'UAH');
    update_option('shopdata_checkbox_currency'. $page_id, 0);

    if (!$is_add_new_feed) {
    wp_redirect(admin_url('admin.php?page=shop_data&status=reset'));
    exit;
    }
}

add_action('admin_post_callback_attribute_settings', 'callback_attribute_settings');

function callback_attribute_settings() {
    if (isset($_POST['submit_button'])) {
        $page_id = get_next_feed_id();
        //Get value of the fields
        $attribute_name_value = $_POST['input_attribute_name'];
        $attribute_pre_order_value = sanitize_text_field($_POST['input_stock_status']);

        //Get the current value options
        $current_attribute_name_value = get_option('attribute_name'. $page_id);
        $current_pre_order_value = get_option('attribute_pre_order'. $page_id);
        
        //Checking does the new value math
        if ($attribute_name_value !== $current_attribute_name_value ||  $attribute_pre_order_value !== $current_pre_order_value ) {

            //Saved in database and update values
            update_option('attribute_name'. $page_id, $attribute_name_value);
            update_option('attribute_pre_order'. $page_id, $attribute_pre_order_value);

            wp_redirect(admin_url('admin.php?page=attribute_settings&status=updated'));
        } else {
            //If no changes
            wp_redirect(admin_url('admin.php?page=attribute_settings&status=error'));
            exit;
        }
    } elseif (isset($_POST['reset_button'])) {
        //Call reset functions
        reset_attribute_settings(false);
    }
}
add_action('admin_post_reset_attribute_settings', 'reset_attribute_settings');

function reset_attribute_settings($is_add_new_feed){
    $page_id = get_next_feed_id();
    //Default values
    $default_attribute_name = get_bloginfo('name');
    global $wpdb;
    $first_stock_status = $wpdb->get_var("SELECT DISTINCT stock_status FROM {$wpdb->prefix}wc_product_meta_lookup ORDER BY stock_status ASC LIMIT 1");

    update_option('attribute_name' . $page_id, $default_attribute_name);
    update_option('attribute_pre_order' . $page_id, $first_stock_status); 
   
    if (!$is_add_new_feed) {
    wp_redirect(admin_url('admin.php?page=attribute_settings&status=reset'));
    exit;
    }
}

add_action('admin_post_callback_filtration_settings', 'callback_filtration_settings');


function callback_filtration_settings() {
    if (isset($_POST['submit_button'])) {
        $page_id = get_next_feed_id();
        //Get value of the fields
        $option_value = sanitize_text_field($_POST['input_field_whot_export']);
        
        //Get the current value options
        $current_value = get_option('filtration_export'. $page_id);

        //Checking does the new value math
        if ($option_value !== $current_value) {
            
            //Saved in database and update values
            update_option('filtration_export'. $page_id, $option_value);           

            wp_redirect(admin_url('admin.php?page=filtration&status=updated'));
        } else {
            //If no changes
            wp_redirect(admin_url('admin.php?page=filtration&status=error'));
            exit;
        }
    } elseif (isset($_POST['reset_button'])) {
        //Call reset functions
        reset_filtration_settings(false);
    }
    elseif (isset($_POST['create_feed_file'])) {
        create_feed_file();
    }
}

add_action('admin_post_reset_filtration_settings', 'reset_filtration_settings');
function  reset_filtration_settings($is_add_new_feed) {
    $page_id = get_next_feed_id();
    //Default values
    update_option('filtration_export'. $page_id, 'Simple & Variable products');

    if (!$is_add_new_feed) {
    wp_redirect(admin_url('admin.php?page=filtration&status=reset'));
    exit;
    }
}
function reset_all_settings() {
    reset_main_settings(true);
    reset_shop_data_settings(true);
    reset_attribute_settings(true);
    reset_filtration_settings(true);
}

function xml_feed_deactivation() {

    $feeds = get_feed_files(); 

    if (!empty($feeds)) {
        

        foreach ($feeds as $feed) {
            $file_path = $feed['xml_file_path'] ?: $feed['csv_file_path']; 
            if (file_exists($file_path)) {
                unlink($file_path); 
            }
        }
    }
     $max_id = get_next_feed_id();

       for ($id = 1; $id <= $max_id; $id += 2) {
        //Delete main settings
        delete_option('main' . $id);
        delete_option('main_check' . $id);
        delete_option('main_field' . $id);
        delete_option('main_field_export' . $id);
        delete_option('main_field_country' . $id);

        //Delete shop data settings
        delete_option('shopdata_category' . $id);
        delete_option('shopdata_name' . $id);
        delete_option('shopdata_store_code' . $id);
        delete_option('woocommerce_currency' . $id);
        delete_option('shopdata_checkbox_currency' . $id);
 
        //Delete attribute settings
        delete_option('attribute_name' . $id);
        delete_option('attribute_pre_order' . $id);

        //Delete filtration settings
        delete_option('filtration_export' . $id);
    }

}



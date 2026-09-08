<?php
/*
Plugin Name: CRM for edu
Description: CRM for edu
Version: 1.0
Author: Vlada
* Text Domain: crm-for-edu
* Domain Path: /languages
*/

register_activation_hook(__FILE__, 'create_post_type');
register_deactivation_hook(__FILE__, 'uninstall');

add_action('admin_enqueue_scripts', 'crud_script');

add_action('init', 'create_post_type');
add_action('add_meta_boxes', 'add_lead_meta_boxes');
add_action('save_post', 'save_metabox', 10, 2);

add_filter('template_include', 'taxonomy_template_funnels', 99);

add_action('admin_enqueue_scripts', 'template_style_bootstrap', 99);
function template_style_bootstrap() {

    wp_enqueue_style(
        'bootstrap-css', 
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
    );

    wp_enqueue_script(
        'bootstrap-js', 
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', 
        array('jquery'), 
        '1.0', 
        true 
    );
}

function crud_script() {
    wp_enqueue_script(
        'crud-script', 
        plugins_url('assets/js/crud.js', __FILE__),
        array(), 
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/crud.js') 
    );
}

function taxonomy_template_funnels($template) {
    if (is_tax('Funnels')) { 
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/taxonomy-funnels.php'; 
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}

function add_lead_meta_boxes() {
    add_meta_box(
        'dynamic_fields_meta_box',         
        'Lead Settings',                  
        'add_lead_fields',  
        'lead',                          
        'normal',                           
        'default'                             
    );
}

function add_lead_fields($post) {
    $names = get_post_meta($post->ID, 'lead_name', true);
    $types = get_post_meta($post->ID, 'lead_type', true);
    $values = get_post_meta($post->ID, 'lead_value', true);
    $ids = get_post_meta($post->ID, 'lead_id', true);
    $checks = get_post_meta($post->ID, 'lead_check', true);
    
    $names  = (is_array($names) && !empty($names)) ? $names : [''];
    $types  = (is_array($types) && !empty($types)) ? $types : [''];
    $values = (is_array($values) && !empty($values)) ? $values : [''];
    $ids    = (is_array($ids) && !empty($ids)) ? $ids : ['0001'];
    $checks = (is_array($checks) && !empty($checks)) ? $checks : [];
    $last_id = max($ids);
    
    foreach ($names as $key => $lead_name) { 
        $lead_type  = isset($types[$key]) ? $types[$key] : '';
        $lead_value = isset($values[$key]) ? $values[$key] : '';
        $lead_id    = isset($ids[$key]) ? $ids[$key] : $last_id++;
        $is_checked = in_array($lead_id, $checks) ? 'checked' : '';
    ?>
        <div class="col-12 mt-4">
            <div class="group-fields">
                <div class="col-6 d-flex">
                    <div class="col-6 row">
                        <div class="col-3">
                            <label class="form-label" for="lead_name">
                                <p class="fw-medium">Name<p>
                            </label>
                        </div>
                        <div class="col-9">
                            <input type="text" class="form-control" name="lead_name[]" value="<?php echo esc_attr($lead_name); ?>">
                        </div>
    
                        <div class="col-3">
                            <label class="form-label" for="lead_type">
                                <p class="fw-medium">Type<p>
                            </label>
                        </div>
                        <div class="col-9">
                            <select class="form-control" name="lead_type[]">
                                <option value="empty">Select type</option>
                                <option value="text" <?php selected($lead_type, 'text'); ?>>Text</option>
                                <option value="number" <?php selected($lead_type, 'number'); ?>>Number</option>
                                <option value="list" <?php selected($lead_type, 'list'); ?>>List</option>
                            </select>
                        </div>
    
                        <div class="col-3">
                            <label class="form-label" for="lead_value">
                                <p class="fw-medium">Value<p>
                            </label>
                        </div>
                        <div class="col-9">
                            <input type="text" class="form-control" name="lead_value[]" value="<?php echo esc_attr($lead_value); ?>">
                        </div>
    
                        <div class="col-3">
                            <label for="lead_id">
                                <p class="fw-medium">ID<p>
                            </label>
                        </div>
                        <div class="col-9">
                            <input type="text" class="form-control lead_id" name="lead_id[]" value="<?php echo esc_attr($lead_id); ?>" readonly>
                        </div>
                    </div>
                    
                    <?php if ($key > 0): ?>
                    <div class="col-2 mt-2">
                        <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                        <input class="form-control check-field" type="checkbox" name="check[]" value="<?php echo esc_attr($lead_id); ?>" <?php echo $is_checked; ?>>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
        <div class="mt-3">
            <button type="button" id="add_field" class="button button-primary">Add Field</button>
            <button type="button" id="delete_field" class="button">Delete</button>
        </div>
    <?php
}

function save_metabox($post_id, $post) {
 
    if (!empty($_POST['lead_name']) && is_array($_POST['lead_name'])) {
        update_post_meta($post_id, 'lead_name', array_map('sanitize_text_field', $_POST['lead_name']));
    } else {
        delete_post_meta($post_id, 'lead_name');
    }

    if (!empty($_POST['lead_type']) && is_array($_POST['lead_type'])) {
        update_post_meta($post_id, 'lead_type', array_map('sanitize_text_field', $_POST['lead_type']));
    } else {
        delete_post_meta($post_id, 'lead_type');
    }

    if (!empty($_POST['lead_value']) && is_array($_POST['lead_value'])) {
        update_post_meta($post_id, 'lead_value', array_map('sanitize_text_field', $_POST['lead_value']));
    } else {
        delete_post_meta($post_id, 'lead_value');
    }

    if (!empty($_POST['lead_id']) && is_array($_POST['lead_id'])) {
        update_post_meta($post_id, 'lead_id', array_map('sanitize_text_field', $_POST['lead_id']));
    } else {
        delete_post_meta($post_id, 'lead_id');
    }

    if (!empty($_POST['check']) && is_array($_POST['check'])) {
        update_post_meta($post_id, 'lead_check', array_map('sanitize_text_field', $_POST['check']));
    } else {
        delete_post_meta($post_id, 'lead_check');
    }

    
}

function create_post_type() {
    register_post_type('lead',
        array(
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'lead'),
            'label' => __('Leads', 'crm-for-edu'),
            'supports' => array('title', 'editor', 'thumbnail'),
            // 'show_in_rest' => true
        )
    );

    $labels = array(
        'name'              => __('Funnels', 'crm-for-edu'),
			'singular_name'     => __('Funnels', 'crm-for-edu'),
			'search_items'      => __('Search Funnels', 'crm-for-edu'),
			'all_items'         => __('All Funnels', 'crm-for-edu'),
			'view_item '        => __('View Funnel', 'crm-for-edu'),
			'parent_item'       => __('Parent Funnel', 'crm-for-edu'),
			'parent_item_colon' => __('Parent Funnel:', 'crm-for-edu'),
			'edit_item'         => __('Edit Funnel', 'crm-for-edu'),
			'update_item'       => __('Update Funnel', 'crm-for-edu'),
			'add_new_item'      => __('Add New Funnel', 'crm-for-edu'),
			'new_item_name'     => __('New Funnel Name', 'crm-for-edu'),
			'menu_name'         => __('Funnels', 'crm-for-edu'),
			'back_to_items'     => __('← Back to Funnel', 'crm-for-edu'),
    );

    $args = array(
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug'=>'funnels'),
        'labels' => $labels,
    );

    register_taxonomy('Funnels','lead', $args);
}


function uninstall() { 
    $leads = get_posts(array('post_type'=>'lead','numberposts'=>-1));
    foreach($leads as $lead){
        wp_delete_post($lead->ID, true);
    }
}
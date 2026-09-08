<?php 
// Template Name: taxonomy-funnels
?>

<!doctype html>
<html lang="en">

<head>
  <?php wp_head(); ?>
</head>

<?php
// add_action('wp_enqueue_scripts', 'template_style_bootstrap', 99);
// function template_style_bootstrap() {

//     wp_enqueue_style(
//         'bootstrap-css', 
//         'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
//     );

//     wp_enqueue_script(
//         'bootstrap-js', 
//         'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', 
//         array('jquery'), 
//         '1.0', 
//         true 
//     );
// }


function my_plugin_enqueue_styles() {
    wp_enqueue_style(
        'my-plugin-style', 
        plugin_dir_url(__FILE__) . '../assets/css/style.css',
        array(), 
        filemtime(plugin_dir_path(__FILE__) . '..assets//css/style.css') 
    );
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');

get_header(); 

$terms = get_terms(array(
    'taxonomy' => 'Funnels', 
    'orderby' => 'name',
    'order' => 'ASC',
    'hide_empty' => false, 
));

?>
<div class="page-wrapper">
    <div class="to-do-board col-12">
        <div class="to-do-board-inside col-10 offset-1">
            <?php
            $printed_parents = array();

            foreach ($terms as $term):
                $parent_term = get_term($term->parent, 'Funnels');
                if ($term->parent && !is_wp_error($parent_term)) {
                    if (!in_array($parent_term->term_id, $printed_parents)) {
                        echo '<h1>' . $parent_term->name . '</h1>';
                        $printed_parents[] = $parent_term->term_id;
                    }
                }
            endforeach;
            ?>

            <div class="to-do-container mt-5 col-12">
                <div class="row">
                    <?php
                    foreach ($terms as $term):
                        $parent_term = get_term($term->parent, 'Funnels');
                        if ($term->parent && !is_wp_error($parent_term)): ?>
                            <div class="col-3" id="to-do-column-<?php echo $term->term_id; ?>">
                                <div class="card-container card text-bg-light">
                                    <div class="card-title p-3">
                                        <h3><?php echo $term->name; ?></h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="to-do-cards">
                                            <?php
                                            $leads = get_posts(array(
                                                'post_type'      => 'lead',
                                                'posts_per_page' => -1,
                                                'tax_query'      => array(
                                                    array(
                                                        'taxonomy' => 'Funnels',
                                                        'field'    => 'id',
                                                        'terms'    => $term->term_id,
                                                    ),
                                                ),
                                            ));

                                            foreach ($leads as $lead): ?>
                                                <li class="li-card card mb-2 p-3" data-lead-id="<?php echo $lead->ID; ?>">
                                                    <h4 ><?php echo get_the_title($lead); ?></h4>
                                                    <p><?php echo get_the_excerpt($lead); ?></p>
                                                </li>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
get_footer();

get_template_part('switcher-wrapper'); 
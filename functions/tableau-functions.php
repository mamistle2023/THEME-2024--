<?php

// Register Custom Post Type for Tableau
function T_create_tableau_type() {
    register_post_type('Tableau',
        array(
            'labels' => array(
                'name' => __('Tableaus'),
                'singular_name' => __('Tableau')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'tableau'),
            'show_in_rest' => false,
            'supports' => array('title', 'editor', 'thumbnail'),
            'taxonomies' => array('category')
        )
    );
}
add_action('init', 'T_create_tableau_type');

// Disable Gutenberg Editor for Tableau
function T_disable_gutenberg_for_tableau($is_enabled, $post_type) {
    if ($post_type === 'tableau') return false;
    return $is_enabled;
}
add_filter('use_block_editor_for_post_type', 'T_disable_gutenberg_for_tableau', 10, 2);

// Remove default meta boxes for Tableau
function T_remove_all_meta_boxes() {
    remove_meta_box('slugdiv', 'tableau', 'normal');
    remove_meta_box('authordiv', 'tableau', 'normal');
}
add_action('admin_menu', 'T_remove_all_meta_boxes');

// Add custom meta box for Tableau Images
function T_add_custom_box() {
    add_meta_box(
        'T_box_id',
        'Tableau Images',
        'T_display_images_meta_box',
        'Tableau'
    );
}
add_action('add_meta_boxes', 'T_add_custom_box');

// Meta box display callback
function T_display_images_meta_box($post) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

    $T_images_meta = get_post_meta($post->ID, '_T_images_meta', true);
    $T_checked_meta = get_post_meta($post->ID, '_T_checked_meta', true);
    $portfolio_id = get_post_meta($post->ID, '_portfolio_id', true);

    echo '<button id="T_upload_button" type="button" class="button">Upload</button>';
    echo '<ul id="T_image_list">';

    if (!empty($T_images_meta)) {
        foreach ($T_images_meta as $image_id) {
            $checked = (!empty($T_checked_meta) && is_array($T_checked_meta) && in_array($image_id, $T_checked_meta)) ? 'checked' : '';
            $image = wp_get_attachment_image_src($image_id, 'full')[0];
            echo '<li><input type="checkbox" class="T_checkbox" data-id="'.$image_id.'" '.$checked.'/><img id="image'.$image_id.'" src="'.$image.'" width="300px"/><input type="hidden" value="'.$image_id.'"/><button class="T_remove_button">Remove</button></li>';
        }
    }

    echo '</ul>';
    echo '<input type="hidden" id="portfolio_ID" value="'.$portfolio_id.'">';
    $Grid_image_positions_meta = get_post_meta($post->ID, '_Grid_image_positions_meta', true);
    echo '<input type="hidden" id="Grid_image_positions" value="' . esc_attr($Grid_image_positions_meta) . '">';
}

// Save post data
function debug_tableau_post($post_id) {
    $selected_categories = get_post_meta($post_id, 'selected_categories', true);
    $tg_state = get_post_meta($post_id, 'TG_state', true);
    $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'all'));

    error_log('Selected Categories for Post ID ' . $post_id . ': ' . print_r($selected_categories, true));
    error_log('TG State for Post ID ' . $post_id . ': ' . print_r($tg_state, true));
    error_log('Categories for Post ID ' . $post_id . ': ' . print_r($categories, true));
}

function T_save_postdata($post_id) {
    if (get_post_type($post_id) !== 'tableau') return;

    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    if ('Tableau' == $_POST["post_type"]) {
        if (!current_user_can("edit_post", $post_id))
            return $post_id;
    }

    if (isset($_POST["T_images"])) {
        $T_images_data = $_POST["T_images"];
        update_post_meta($post_id, "_T_images_meta", $T_images_data);
    }

    if (isset($_POST["T_checked"])) {
        $T_checked_data = $_POST["T_checked"];
        update_post_meta($post_id, "_T_checked_meta", $T_checked_data);
    }         

    if (isset($_POST["Grid_image_positions"])) {
        $Grid_image_positions_data = $_POST["Grid_image_positions"];
        update_post_meta($post_id, "_Grid_image_positions_meta", $Grid_image_positions_data);
    }

    $thumbnail_id = get_post_thumbnail_id($post_id);
    update_post_meta($thumbnail_id, '_associated_tableau_post_id', $post_id);

    debug_tableau_post($post_id);
}
add_action("save_post", "T_save_postdata");



add_action('wp_ajax_T_update_images', 'T_update_images');
function T_update_images() {
    $post_id = $_POST['post_id'];
    update_post_meta($post_id, '_T_images_meta', $_POST['images']);
    update_post_meta($post_id, '_T_checked_meta', $_POST['checked']);
    echo 'Images updated';
    wp_die();
}

function T_remove_editor() {
    remove_post_type_support('tableau', 'editor');
}
add_action('admin_init', 'T_remove_editor');

function TBI_add_metabox() {
    add_meta_box('TBI_tableau_metabox', 'Tableau Metabox', 'TBI_display_metabox', 'tableau', 'side', 'default');
}
add_action('add_meta_boxes', 'TBI_add_metabox');

function TBI_display_metabox($post) {
    $featured_image = get_the_post_thumbnail($post->ID, array(250, 9999));
    echo $featured_image ? $featured_image : '<p>Kein Beitragsbild für diesen Beitrag.</p>';
    echo '<p>Hier können Sie spezielle Einstellungen für Ihren Tableau-Beitrag vornehmen.</p>';
}

function tableau_add_categories_to_cpt() {
    register_taxonomy_for_object_type('category', 'tableau');
}
add_action('init', 'tableau_add_categories_to_cpt');

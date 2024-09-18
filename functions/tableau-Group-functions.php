<?php

// Register Custom Post Type for Tableau Group
function TG_create_tableau_group_post_type() {
    register_post_type('tableaugroup',
        array(
            'labels' => array(
                'name' => __('Tableau Groups'),
                'singular_name' => __('Tableau Group')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'tableaugroup'),
            'show_in_rest' => false,
            'supports' => array('title', 'thumbnail')
        )
    );
}
add_action('init', 'TG_create_tableau_group_post_type');

// Remove default meta boxes for Tableau Group
function TG_remove_default_metaboxes() {
    remove_post_type_support('tableaugroup', 'editor');
    remove_post_type_support('tableaugroup', 'comments');
}
add_action('admin_menu', 'TG_remove_default_metaboxes');

// Enqueue admin scripts for Tableau Group
function TG_enqueue_admin_scripts() {
    global $post;
    if (is_admin() && isset($post) && 'tableaugroup' === $post->post_type) {
        wp_enqueue_script('tg-group-script', get_template_directory_uri() . '/js/TableauGroup.js', array('jquery'), '1.0.0', true);
        wp_localize_script('tg-group-script', 'tgVars', array('ajax_url' => admin_url('admin-ajax.php'), 'post_id' => $post->ID));
    }
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'TG_enqueue_admin_scripts');

// Add meta box for Tableau Group
function TG_add_metabox() {
    add_meta_box('TG_images_metabox', __('Tableau Beitragsbilder'), 'TG_metabox_callback', 'tableaugroup', 'normal', 'high');
}
add_action('add_meta_boxes', 'TG_add_metabox');

function TG_metabox_callback($post) {
    TG_metabox_filter($post->ID);

    $args = array(
        'post_type' => 'tableau',
        'posts_per_page' => -1
    );
    $tableau_posts = get_posts($args);

    $order = get_post_meta($post->ID, 'TG_image_order', true);
    if (!empty($order) && is_array($order)) {
        usort($tableau_posts, function($a, $b) use ($order) {
            $pos_a = array_search($a->ID, $order);
            $pos_b = array_search($b->ID, $order);
            return $pos_a - $pos_b;
        });
    }

    echo '<div class="TG-images-list">';
    foreach ($tableau_posts as $tpost) {
        if (has_post_thumbnail($tpost->ID)) {
            $categories = get_the_category($tpost->ID);
            $category_classes = '';
            foreach ($categories as $category) {
                $category_classes .= ' cat-' . $category->term_id;
            }
            $image_data = wp_get_attachment_image_src(get_post_thumbnail_id($tpost->ID), 'medium')[0];
            echo '<div class="TG-image' . $category_classes . '"><img src="' . $image_data . '" data-id="' . $tpost->ID . '" alt="Image for ' . $tpost->post_title . '" style="max-width:300px;"></div>';
        }
    }
    echo '</div>';
}

function TG_metabox_filter($post_id) {
    $categories = get_categories();
    $selected_categories = maybe_unserialize(get_post_meta($post_id, 'selected_categories', true));

    echo '<div id="TG-category-filter">';
    foreach ($categories as $category) {
        $checked = in_array($category->term_id, (array)$selected_categories) ? 'checked' : '';
        echo '<input type="checkbox" name="tableau_group_categories[]" value="' . $category->term_id . '" id="cat-' . $category->term_id . '" ' . $checked . '>';
        echo '<label for="cat-' . $category->term_id . '">' . $category->name . '</label><br>';
    }
    echo '</div>';
}

function TG_load_state() {
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(array('message' => 'Invalid data'));
    }
    $post_id = intval($_POST['post_id']);
    $state = get_post_meta($post_id, 'TG_state', true);
    wp_send_json_success(array('state' => $state));
}
add_action('wp_ajax_TG_load_state', 'TG_load_state');

function TG_save_sort_order() {
    $post_id = $_POST['post_id'];
    $order = isset($_POST['order']) ? $_POST['order'] : [];
    update_post_meta($post_id, 'TG_image_order', maybe_serialize($order));
    echo json_encode(array('success' => true, 'message' => 'Sort order saved.'));
    wp_die();
}
add_action('wp_ajax_TG_save_sort_order', 'TG_save_sort_order');

add_action('save_post_tableaugroup', 'TG_set_first_image_as_thumbnail', 10, 3);
function TG_set_first_image_as_thumbnail($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!$update) return;
    $args = array('post_type' => 'tableau', 'posts_per_page' => -1);
    $tableau_posts = get_posts($args);
    $order = maybe_unserialize(get_post_meta($post_id, 'TG_image_order', true));
    if (!empty($order) && is_array($order)) {
        usort($tableau_posts, function($a, $b) use ($order) {
            $pos_a = array_search($a->ID, $order);
            $pos_b = array_search($b->ID, $order);
            return $pos_a - $pos_b;
        });
    }
    if (isset($tableau_posts[0]) && has_post_thumbnail($tableau_posts[0]->ID)) {
        set_post_thumbnail($post_id, get_post_thumbnail_id($tableau_posts[0]->ID));
    }
}

function save_tableau_sort_order($post_id) {
    if (get_post_type($post_id) !== 'tableaugroup') return;
    $category_id = get_post_meta($post_id, 'TG_state', true);
    if (!$category_id) return;
    $sort_order_option_name = 'tableau_sort_order_' . $category_id;
    $sort_order = maybe_unserialize(get_option($sort_order_option_name));
    if (!is_array($sort_order)) $sort_order = [];
    if (!in_array($post_id, $sort_order)) {
        $sort_order[] = $post_id;
    }
    if (!empty($sort_order)) {
        update_option($sort_order_option_name, maybe_serialize($sort_order));
    } else {
        delete_option($sort_order_option_name);
    }
}
add_action('save_post', 'save_tableau_sort_order');

// Funktion zur Aktualisierung des gefilterten TG State
function update_filtered_tg_state($post_id) {
    if (get_post_type($post_id) !== 'tableaugroup') return;

    $selected_categories = maybe_unserialize(get_post_meta($post_id, 'selected_categories', true));
    if (!is_array($selected_categories)) $selected_categories = [];

    $args = array(
        'post_type' => 'tableau',
        'posts_per_page' => -1
    );
    $tableau_posts = get_posts($args);

    $filtered_tg_state = array();
    foreach ($tableau_posts as $tpost) {
        $tableau_categories = wp_get_post_terms($tpost->ID, 'category', array('fields' => 'ids'));
        if (!empty(array_intersect($tableau_categories, $selected_categories))) {
            $filtered_tg_state[] = $tpost->ID;
        }
    }

    update_post_meta($post_id, 'tg-state-gefilter', maybe_serialize($filtered_tg_state));
}
add_action('save_post', 'update_filtered_tg_state');


// Speichern der Metadaten für Tableau Group
function save_tableau_group_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['tableau_group_categories'])) {
        $selected_categories = array_map('sanitize_text_field', $_POST['tableau_group_categories']);
        update_post_meta($post_id, 'selected_categories', maybe_serialize($selected_categories));
    } else {
        delete_post_meta($post_id, 'selected_categories');
    }

    if (isset($_POST['TG_state'])) {
        $tg_state = array_map('sanitize_text_field', $_POST['TG_state']);
        update_post_meta($post_id, 'TG_state', maybe_serialize($tg_state));
    } else {
        delete_post_meta($post_id, 'TG_state');
    }
}
add_action('save_post', 'save_tableau_group_meta');


function get_filtered_tableaus_for_group($group_id) {
    $selected_categories = maybe_unserialize(get_post_meta($group_id, 'selected_categories', true));
    if (!is_array($selected_categories)) {
        return [];
    }

    $args = [
        'post_type' => 'tableau',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $selected_categories,
                'operator' => 'IN',
            ],
        ],
    ];

    $query = new WP_Query($args);
    return $query->posts;
}

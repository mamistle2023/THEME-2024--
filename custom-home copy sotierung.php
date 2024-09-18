<?php

/**
 * Template Name: Custom 222 Home Page 2024111
 */

require_once get_template_directory() . '/functions/startingPage-functions.php';
require_once get_template_directory() . '/functions/grid-functions.php';
require_once get_template_directory() . '/functions/gridlayouts-functions.php';
require_once get_template_directory() . '/functions/tableau-functions.php';
require_once get_template_directory() . '/functions/tableau-Group-functions.php';

get_header();

$starting_page_id = 177;

// Abrufen der Bild-IDs in der Sortierreihenfolge aus den Metadaten der Starting Page
$image_order = get_post_meta($starting_page_id, 'sp_tableau_images_order', true);
$image_order = maybe_unserialize($image_order);

if (!is_array($image_order) || empty($image_order)) {
    $image_order = [];
    error_log('Keine Bildsortierung gefunden oder das Order-Array ist nicht gültig.');
}

$args = [
    'post_type' => 'tableaugroup',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'post__in' => $image_order,
    'orderby' => 'post__in'
];

$query = new WP_Query($args);
$tableau_groups = $query->posts;

function get_tableau_posts_by_group($group_id) {
    $tableau_order = get_tableau_group_image_order($group_id);
    if (!is_array($tableau_order) || empty($tableau_order)) {
        $tableau_order = []; // Standardaktion bei fehlenden Metadaten
        error_log('Keine Tableau-Bildsortierung gefunden oder das Order-Array ist nicht gültig für Gruppe ' . $group_id);
    }

    $args = [
        'post_type' => 'tableau',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post__in' => $tableau_order,
        'orderby' => 'post__in'
    ];

    $query = new WP_Query($args);
    return $query->posts;
}

function get_grid_positions($post_id) {
    $positions = get_post_meta($post_id, 'grid_image_positions', true);
    return is_array($positions) ? $positions : [];
}

function get_grid_layout($post_id) {
    $layout = get_post_meta($post_id, 'selected_grid_layout', true);
    return !empty($layout) ? $layout : 'layout_1';
}

function render_grid_layout($layout, $positions) {
    $html = '';
    switch ($layout) {
        case 'layout_1':
            $html = grid_layout_1();
            break;
        case 'layout_2':
            $html = grid_layout_2();
            break;
        case 'layout_3':
            $html = grid_layout_3();
            break;
        default:
            $html = grid_layout_1();
            break;
    }

    foreach ($positions as $column_id => $image_id) {
        if (preg_match('/\d+/', $image_id, $matches)) {
            $image_id = intval($matches[0]);
            $image_src = wp_get_attachment_image_src($image_id, 'full');
            if ($image_src && isset($image_src[0])) {
                $img_tag = '<img src="' . esc_url($image_src[0]) . '" alt="" class="img-fluid" />';
                $html = preg_replace('/>' . preg_quote($column_id, '/') . '</', '>' . $img_tag . '<', $html, 1);
            } else {
                $html = str_replace('>' . $column_id . '<', '>Bild nicht gefunden<', $html);
            }
        } else {
            $html = str_replace('>' . $column_id . '<', '>Ungültige Bild-ID<', $html);
        }
    }

    return $html;
}

if ($tableau_groups && !empty($tableau_groups)) {
    echo '<div id="fullpage" class="container">';
    foreach ($tableau_groups as $group) {
        echo '<div class="section row">';
        echo '<h2 class="col-12">' . esc_html($group->post_title) . '</h2>';

        $tableau_posts = get_tableau_posts_by_group($group->ID);

        if (!empty($tableau_posts)) {
            echo '<div class="slides">';
            foreach ($tableau_posts as $tableau_post) {
                if ($tableau_post) {
                    $grid_layout = get_grid_layout($tableau_post->ID);
                    $positions = get_grid_positions($tableau_post->ID);
                    $grid_content = render_grid_layout($grid_layout, $positions);

                    echo '<div class="slide ' . esc_attr($grid_layout) . '-grid">';
                    echo '<h3>' . esc_html($tableau_post->post_title) . '</h3>';
                    echo $grid_content;
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="slide">';
            echo '<p>Keine Tableau-Beiträge in dieser Kategorie gefunden</p>';
            echo '</div>';
        }

        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2>Keine Tableau-Gruppen gefunden</h2>';
    echo '</div>';
}

get_footer();
?>

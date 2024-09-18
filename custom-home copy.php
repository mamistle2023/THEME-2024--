<?php
/**
 * Template Name: sektion sort klappt
 */

require_once get_template_directory() . '/functions/startingPage-functions.php';
require_once get_template_directory() . '/functions/grid-functions.php';
require_once get_template_directory() . '/functions/gridlayouts-functions.php';
require_once get_template_directory() . '/functions/tableau-functions.php';
require_once get_template_directory() . '/functions/tableau-Group-functions.php';

get_header();


// Automatische Setzung der Starting Page ID
$args = [
    'post_type' => 'starting_pages',
    'posts_per_page' => 1,
    'post_status' => 'publish',
];
$starting_page_query = new WP_Query($args);

if ($starting_page_query->have_posts()) {
    $starting_page_query->the_post();
    $starting_page_id = get_the_ID();
    wp_reset_postdata();
} else {
    // Kein Starting Page Beitrag gefunden, Setzen Sie eine Default ID oder werfen Sie einen Fehler
    $starting_page_id = 0; // Default ID, ändern Sie dies nach Bedarf
}

// Abrufen der Bild-IDs in der Sortierreihenfolge aus den Metadaten der Starting Page
$image_order = get_post_meta($starting_page_id, 'sp_tableau_images_order', true);
$image_order = maybe_unserialize($image_order);

error_log('Image Order from Starting Page: ' . print_r($image_order, true));

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

error_log('Tableau Groups: ' . print_r($tableau_groups, true));

// Funktion zum Abrufen der Grid-Positionen
function get_grid_positions($post_id) {
    $positions = get_post_meta($post_id, 'grid_image_positions', true);
    error_log('Grid Positions for Post ID ' . $post_id . ': ' . print_r($positions, true));
    return is_array($positions) ? $positions : [];
}

// Funktion zum Abrufen des Grid-Layouts
function get_grid_layout($post_id) {
    $layout = get_post_meta($post_id, 'selected_grid_layout', true);
    error_log('Grid Layout for Post ID ' . $post_id . ': ' . print_r($layout, true));
    return !empty($layout) ? $layout : 'layout_1';
}

// Funktion zum Rendern des Grid-Layouts
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

    error_log('Rendering Grid Layout: ' . $layout);

    foreach ($positions as $column_id => $image_id) {
        error_log('Processing Position: ' . $column_id . ' with Image ID: ' . $image_id);
        if (preg_match('/\d+/', $image_id, $matches)) {
            $image_id = intval($matches[0]);
            $image_src = wp_get_attachment_image_src($image_id, 'full');
            if ($image_src && isset($image_src[0])) {
                $img_tag = '<img src="' . esc_url($image_src[0]) . '" alt="" class="img-fluid" />';
                error_log('Image Tag: ' . $img_tag);
                $html = preg_replace('/>' . preg_quote($column_id, '/') . '</', '>' . $img_tag . '<', $html, 1);
            } else {
                $html = str_replace('>' . $column_id . '<', '>Bild nicht gefunden<', $html);
                error_log('Image not found for Image ID: ' . $image_id);
            }
        } else {
            $html = str_replace('>' . $column_id . '<', '>Ungültige Bild-ID<', $html);
            error_log('Invalid Image ID: ' . $image_id);
        }
    }

    return $html;
}

if ($tableau_groups && !empty($tableau_groups)) {
    echo '<div id="fullpage" class="container">';
    foreach ($tableau_groups as $group) {
        echo '<div class="section row">';
        echo '<h2 class="col-12">' . esc_html($group->post_title) . '</h2>';

        $tableaus = get_filtered_tableaus_for_group($group->ID);

        error_log('Filtered Tableaus for Group ID ' . $group->ID . ': ' . print_r($tableaus, true));

        if (empty($tableaus)) {
            echo '<div class="slide">';
            echo '<p>Keine Tableau-Beiträge in dieser Kategorie gefunden</p>';
            echo '</div>';
            continue;
        }

        echo '<div class="slides">';
        foreach ($tableaus as $tableau) {
            $grid_layout = get_grid_layout($tableau->ID);
            $positions = get_grid_positions($tableau->ID);
            $grid_content = render_grid_layout($grid_layout, $positions);

            echo '<div class="slide ' . esc_attr($grid_layout) . '-grid">';
            echo '<h3>' . esc_html($tableau->post_title) . '</h3>';
            echo $grid_content;
            echo '</div>';
        }
        echo '</div>';
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

<!-- FullPage.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.9/fullpage.min.css">

<!-- FullPage.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.9/fullpage.min.js"></script>


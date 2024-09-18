<?php

/**
 * Template Name: index
 */

require_once get_template_directory() . '/functions/startingPage-functions.php';
require_once get_template_directory() . '/functions/grid-functions.php';
require_once get_template_directory() . '/functions/gridlayouts-functions.php';
require_once get_template_directory() . '/functions/tableau-functions.php';

get_header();

$starting_page_id = 191;

// Abrufen der Bild-IDs in der Sortierreihenfolge aus den Metadaten der Starting Page
$image_order = get_post_meta($starting_page_id, 'sp_tableau_images_order', true);
$image_order = maybe_unserialize($image_order);

error_log('Image Order: ' . print_r($image_order, true));

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

function get_tableau_posts_by_category($category_id) {
    global $wpdb;
    $tableau_posts = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID 
        FROM $wpdb->posts p
        JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
        JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE p.post_type = 'tableau' AND p.post_status = 'publish' AND tt.taxonomy = 'category' AND tt.term_id = %d
    ", $category_id));
    
    $post_ids = [];
    if ($tableau_posts) {
        foreach ($tableau_posts as $post) {
            $post_ids[] = $post->ID;
        }
    }
    error_log('Tableau Posts for Category ID ' . $category_id . ': ' . print_r($post_ids, true));
    return $post_ids;
}

function get_grid_positions($post_id) {
    $positions = get_post_meta($post_id, 'grid_image_positions', true);
    error_log('Grid Positions for Post ID ' . $post_id . ': ' . print_r($positions, true));
    return is_array($positions) ? $positions : [];
}

function get_grid_layout($post_id) {
    $layout = get_post_meta($post_id, 'selected_grid_layout', true);
    error_log('Grid Layout for Post ID ' . $post_id . ': ' . print_r($layout, true));
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

        $category_id_serialized_array = get_post_meta($group->ID, 'TG_state', true);
        error_log('Meta data for Tableau Group ' . $group->ID . ': ' . print_r($category_id_serialized_array, true));
        
        if (is_array($category_id_serialized_array) && !empty($category_id_serialized_array[0])) {
            $category_id = $category_id_serialized_array[0];
        } else {
            $category_id = null;
        }

        error_log('Category ID for Tableau Group ' . $group->ID . ': ' . $category_id);

        if ($category_id) {
            $tableau_posts = get_tableau_posts_by_category($category_id);
            if (!empty($tableau_posts)) {
                echo '<div class="slides">';
                foreach ($tableau_posts as $tableau_id) {
                    $tableau_post = get_post($tableau_id);
                    if ($tableau_post) {
                        $grid_layout = get_grid_layout($tableau_post->ID);
                        $positions = get_grid_positions($tableau_post->ID);
                        $grid_content = render_grid_layout($grid_layout, $positions);

                        echo '<div class="slide ' . esc_attr($grid_layout) . '-grid">';
                        echo '<h3>' . esc_html($tableau_post->post_title) . '</h3>';
                        echo $grid_content;
                        echo '</div>';
                    } else {
                        error_log('Tableau Post not found: ' . $tableau_id);
                    }
                }
                echo '</div>';
            } else {
                echo '<div class="slide">';
                echo '<p>Keine Tableau-Beiträge in dieser Kategorie gefunden</p>';
                echo '</div>';
            }
        } else {
            echo '<div class="slide">';
            echo '<p>Keine Kategorie ausgewählt</p>';
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

?>

<!-- FullPage.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.9/fullpage.min.css">

<!-- FullPage.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.9/fullpage.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded and parsed');
    if (typeof fullpage !== 'undefined') {
        console.log('FullPage.js object found');
        new fullpage('#fullpage', {
            autoScrolling: true,
            navigation: true,
            slidesNavigation: true,
            slidesNavPosition: 'bottom',
            afterRender: function() {
                console.log('FullPage.js has been rendered');
            },
            afterLoad: function(origin, destination, direction) {
                console.log('Section loaded: ' + destination.index);
            },
            afterSlideLoad: function(section, origin, destination, direction) {
                console.log('Slide loaded in section: ' + section.index + ', slide: ' + destination.index);
            }
        });
    } else {
        console.log('FullPage.js object not found');
    }
});
</script>

<?php
get_footer();
?>

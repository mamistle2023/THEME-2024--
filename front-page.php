<?php
get_header();
echo '<main id="fullpage">';

// Query for the 'starting_page' post type to get the order of featured images
$sp_args = array(
    'post_type' => 'starting_pages',
    'posts_per_page' => 1
);

$sp_query = new WP_Query($sp_args);
$featured_image_order = array();

if ($sp_query->have_posts()) {
    while ($sp_query->have_posts()) {
        $sp_query->the_post();
        
        // Get the order of featured images (Tableau Group posts)
        $featured_image_order = get_post_meta(get_the_ID(), 'sp_tableau_images_order', true);
    }
}

wp_reset_postdata();

if (!empty($featured_image_order)) {
    // Query for 'tableaugroup' post type based on the order retrieved from the 'starting_page' post
    $tg_args = array(
        'post_type' => 'tableaugroup',
        'post__in' => $featured_image_order,
        'orderby' => 'post__in',
        'posts_per_page' => -1
    );

    $tg_query = new WP_Query($tg_args);

    if ($tg_query->have_posts()) {
        while ($tg_query->have_posts()) {
            $tg_query->the_post();

            // Extract post title and format it to create a valid id and data-anchor attribute
            $title = get_the_title();
            $formatted_title = sanitize_title($title);

            echo '<section id="' . $formatted_title . '" data-anchor="' . $formatted_title . '">';
            echo '122323';
            echo '</section>';
        }
    }

    wp_reset_postdata();
}
echo '</main>';
get_template_part('startPage-footer');

?>

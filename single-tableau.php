<?php get_header(); ?>

<div class="tableau-content">
    <?php
    while (have_posts()) : the_post();

        // Hier die 'related_portfolio_id' abrufen
        $related_portfolio_id = get_post_meta(get_the_ID(), 'related_portfolio_id', true);

        // Abrufen und Anzeigen der Bilder in der vorgegebenen Reihenfolge und im Grid-Layout
        $image_order = get_post_meta(get_the_ID(), '_T_image_order', true);
        if (!$image_order) {
            $image_order = get_post_meta(get_the_ID(), '_T_images_meta', true);
            $image_order = maybe_unserialize($image_order);
        }

        if (!empty($image_order)) {
            echo '<div class="tableau-gallery">';
            foreach ($image_order as $image_id) {
                $image_url = wp_get_attachment_url($image_id);
                if ($image_url) {
                    // Erstellen des Links nur, wenn eine 'related_portfolio_id' vorhanden ist
                    if (!empty($related_portfolio_id)) {
                        $link_to_portfolio = get_permalink($related_portfolio_id) . '#portfolio-image-' . esc_attr($image_id);
                        echo '<a href="' . esc_url($link_to_portfolio) . '">';
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title($image_id)) . '">';
                        echo '</a>';
                    } else {
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title($image_id)) . '">';
                    }
                }
            }
            echo '</div>';
        } else {
            echo '<p>Keine Bilder in diesem Tableau gefunden.</p>';
        }

        echo '<div class="tableau-description">';
        the_content();
        echo '</div>';

    endwhile;
    ?>
</div>

<?php get_footer(); ?>

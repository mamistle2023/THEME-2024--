<?php get_header(); ?>

<div class="portfolio-content">
    <?php
    while ( have_posts() ) : the_post();

        // Abrufen der gespeicherten Bild-IDs aus den Portfolio-Meta-Daten
        $serialized_images = get_post_meta(get_the_ID(), '_P_images_meta', true);

        if (!empty($serialized_images)) {
            // Deserialisieren der Bild-IDs
            $image_ids = maybe_unserialize($serialized_images);

            if (is_array($image_ids) && !empty($image_ids)) {
                echo '<div class="portfolio-gallery">';
                // Erstellen eines Ankers und eines Bildes für jedes Bild in der Galerie
                foreach ($image_ids as $index => $image_id) {
                    $image_url = wp_get_attachment_url($image_id);
                    if (!empty($image_url)) {
                        // Der Anker 'portfolio-image-{BILD-ID}' ermöglicht direkte Verlinkungen
                        echo '<a href="#portfolio-image-' . esc_attr($image_id) . '" id="portfolio-image-' . esc_attr($image_id) . '">';
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title($image_id)) . '">';
                        echo '</a>';
                    }
                }
                echo '</div>';
            } else {
                echo '<p>Keine Bilder in diesem Portfolio gefunden.</p>';
            }
        } else {
            echo '<p>Keine Bilder in diesem Portfolio gefunden.</p>';
        }

        echo '<div class="portfolio-description">';
        the_content();
        echo '</div>';

    endwhile;
    ?>
</div>

<?php get_footer(); ?>

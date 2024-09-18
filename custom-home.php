<?php
get_header(); // Header der Seite einfügen
// Anzeige "TEST" am Anfang der Seite in großer Schrift
echo '<h1 style="font-size: 50px; color: red; text-align: center; margin-top: 20px;">TEST</h1>';
// Hauptcontainer der Startseite
echo '<div class="content-area">';
echo '<main class="site-main">';

// Debugging-Ausgabe
echo '<pre style="background-color: #f4f4f4; padding: 10px; border: 1px solid #ccc; font-size: 14px;">';

// Abfrage der Tableau Groups (benutzerdefinierter Beitragstyp 'tableau_group')
$args_tableau_group = array(
    'post_type' => 'tableau_group', // Definiert den Beitragstyp 'tableau_group'
    'posts_per_page' => -1, // Lädt alle Tableau Groups
    'orderby' => 'menu_order', // Sortiert nach der Reihenfolge im Admin-Bereich
    'order' => 'ASC' // Aufsteigende Sortierung
);

$tableau_group_query = new WP_Query($args_tableau_group);

echo "Tableau Groups Query Args: " . print_r($args_tableau_group, true) . "\n";

if ($tableau_group_query->have_posts()) :
    while ($tableau_group_query->have_posts()) : $tableau_group_query->the_post();

        // Debugging-Ausgabe für die aktuelle Tableau Group
        echo "Current Tableau Group ID: " . get_the_ID() . " - Title: " . get_the_title() . "\n";

        // Ausgabe der Tableau Group als Sektion
        echo '<section class="tableau-group">';
        echo '<h2>' . esc_html(get_the_title()) . '</h2>'; // Titel der aktuellen Tableau Group

        // Abruf der zugehörigen Tableaus für die aktuelle Tableau Group
        $tableaus_in_group = get_post_meta(get_the_ID(), 'related_tableaus', true); // Benutzerdefiniertes Feld 'related_tableaus' für verknüpfte Tableaus

        echo "Related Tableaus for Group: " . print_r($tableaus_in_group, true) . "\n";

        if (!empty($tableaus_in_group)) :
            foreach ($tableaus_in_group as $tableau_id) :
                $tableau_title = get_the_title($tableau_id);
                
                if (!empty($tableau_title)) :
                    echo '<div class="tableau">';
                    echo '<h3>' . esc_html($tableau_title) . '</h3>'; // Titel des aktuellen Tableaus

                    // Abruf der ausgewählten Bilder für das aktuelle Tableau
                    $checked_images = get_post_meta($tableau_id, 'portfolio_images_checked', true); // Angenommen: Bilder, die im Backend mit Häkchen markiert wurden

                    echo "Checked Images for Tableau ID $tableau_id: " . print_r($checked_images, true) . "\n";
                    
                    if (!empty($checked_images)) :
                        echo '<div class="tableau-images">';
                        foreach ($checked_images as $image_id) :
                            $image_url = wp_get_attachment_url($image_id); // URL des Bildes abrufen
                            $portfolio_link = get_permalink($tableau_id) . '#img' . $image_id; // Erzeugt den Link zur Portfolio-Unterseite mit Anker
                            echo '<a href="' . esc_url($portfolio_link) . '">';
                            echo '<img src="' . esc_url($image_url) . '" class="img-fluid" alt="' . esc_attr(get_the_title($image_id)) . '">';
                            echo '</a>';
                        endforeach;
                        echo '</div>'; // Ende der Div für die Bilder des Tableaus
                    else :
                        echo '<p>Keine ausgewählten Bilder für dieses Tableau gefunden.</p>';
                    endif;

                    echo '</div>'; // Ende der Div für das Tableau
                else :
                    echo '<p>Kein Tableau-Titel gefunden.</p>';
                endif;
            endforeach;
        else :
            echo '<p>Keine Tableau-Beiträge in dieser Kategorie gefunden.</p>';
        endif;

        echo '</section>'; // Ende der Sektion für die aktuelle Tableau Group
    endwhile;
    wp_reset_postdata(); // Zurücksetzen der globalen Postdaten nach der Schleife
else :
    echo '<p>Keine Tableau Groups gefunden.</p>';
endif;

echo '</pre>'; // Ende der Debugging-Ausgabe

echo '</main>'; // Ende des Hauptbereichs
echo '</div>'; // Ende des Hauptcontainers

get_footer(); // Footer der Seite einfügen
?>

<?php
/* Template Name: Custom Home Page */

// Einbindung des Headers
get_header();

// Debugging-Information: Absoluter Pfad des Themes
error_log('Theme Directory: ' . get_template_directory());

// Überprüfen, ob die Dateien existieren und binden sie ein
$starting_page_functions = get_template_directory() . '/functions/startingPage-functions.php';
$tableau_group_functions = get_template_directory() . '/functions/tableau-Group-functions.php';
$tableau_functions = get_template_directory() . '/functions/tableau-functions.php';

if (file_exists($starting_page_functions)) {
    require_once $starting_page_functions;
} else {
    error_log('File not found: ' . $starting_page_functions);
}

if (file_exists($tableau_group_functions)) {
    require_once $tableau_group_functions;
} else {
    error_log('File not found: ' . $tableau_group_functions);
}

if (file_exists($tableau_functions)) {
    require_once $tableau_functions;
} else {
    error_log('File not found: ' . $tableau_functions);
}

// Abrufen der geordneten Tableau Groups
if (function_exists('SP_get_ordered_tableau_groups')) {
    $ordered_groups = SP_get_ordered_tableau_groups();
} else {
    error_log('Function SP_get_ordered_tableau_groups does not exist.');
    $ordered_groups = array();
}

// Debugging-Informationen
if (empty($ordered_groups)) {
    echo '<p>Keine Tableau-Gruppen gefunden oder es gab ein Problem beim Abrufen der Gruppen.</p>';
    error_log('Keine Tableau-Gruppen gefunden oder es gab ein Problem beim Abrufen der Gruppen.');
} else {
    foreach ($ordered_groups as $group) {
        $group_id = $group->ID; // ID der Gruppe
        $group_title = function_exists('TG_get_tableau_group_title') ? TG_get_tableau_group_title($group_id) : 'Unbekannte Gruppe'; // Titel der Gruppe abrufen

        // Debugging-Informationen
        error_log('Group ID: ' . $group_id);
        error_log('Group Title: ' . $group_title);

        ?>

        <!-- Section für jede Tableau Group -->
        <div class="section">
            <h2><?php echo esc_html($group_title); ?></h2> <!-- Titel der Gruppe anzeigen -->
            <div class="grid-container">
                <?php
                // Abrufen der geordneten Bilder für diese Gruppe
                $image_order = get_post_meta($group_id, 'TG_image_order', true);

                // Debugging-Informationen
                if (empty($image_order)) {
                    echo '<p>Keine Bilder gefunden oder keine Bildreihenfolge definiert.</p>';
                    error_log('Keine Bilder gefunden oder keine Bildreihenfolge definiert für Group ID: ' . $group_id);
                } else {
                    foreach ($image_order as $image_id) {
                        $image_url = wp_get_attachment_url($image_id); // URL des Bildes abrufen

                        // Debugging-Informationen
                        if (!$image_url) {
                            error_log('Bild URL nicht gefunden für Bild ID: ' . $image_id);
                            continue;
                        }

                        ?>
                        <div class="grid-item">
                            <img src="<?php echo esc_url($image_url); ?>" alt="Image <?php echo esc_attr($image_id); ?>">
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }
}

// Einbindung des Footers
get_footer();
?>

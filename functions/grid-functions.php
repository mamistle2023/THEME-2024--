<?php
// Callback-Funktion zum Rendern der Metabox "Tableau Grid"
function render_tableau_grid_metabox($post) {
    $selected_layout = get_post_meta($post->ID, 'selected_grid_layout', true);

    // CSS-Styling für die Tableau Grid Metabox
    echo '
    <style>
        .tableau-grid-container {
            display: flex;
        }

        .tableau-grid-column {
            display: flex;
            justify-content: center;
            align-items: center;
            background-size: cover;
            background-position: center;
            height: 240px;
            width: 360px;
        }
    </style>
    ';

    if (isset($post->ID)) {
        // Hole die gespeicherten Positionen aus der Datenbank
        $positions = get_post_meta($post->ID, 'grid_image_positions', true);

        if (!empty($positions)) {
            // Schreibe die Positionen in eine globale JavaScript-Variable
            echo "<script>var loadedPositions = " . json_encode($positions) . ";</script>";
        }
    }

    // Inhalt der Metabox hier einfügen
    echo '
    <style>
        .tableau-grid-column {
            min-height: 200px;
        }
    </style>
    ';

    // Hole das entsprechende Grid-Layout basierend auf der Auswahl des Benutzers
    $grid_html = get_grid_layout_html($selected_layout);
    echo $grid_html;
}

function get_grid_layout_html($layout) {
    switch ($layout) {
        case 'layout_1':
            return grid_layout_1();
        case 'layout_2':
            return grid_layout_2();
        case 'layout_3':
            return grid_layout_3();
        default:
            return grid_layout_1(); // Standardlayout, falls keines ausgewählt ist
    }
}


// Funktion zum Hinzufügen der Metabox "Tableau Grid" für den Tableau-Beitrag
function tableau_grid_metabox() {
    add_meta_box(
        'tableau_grid_metabox', // ID der Metabox
        'Tableau Grid', // Titel der Metabox
        'render_tableau_grid_metabox', // Callback-Funktion, die den Inhalt der Metabox rendert
        'tableau', // Hier den Post Type "tableau" eintragen, um die Metabox nur für Tableau-Beiträge anzuzeigen
        'normal', // Position der Metabox (normal = unter dem Editor, side = rechts neben dem Editor)
        'high' // Priorität der Metabox (high = hohe Priorität)
    );
}
add_action('add_meta_boxes', 'tableau_grid_metabox');




function save_image_positions() {
    // Überprüfe, ob die Positionen und die Post-ID in der AJAX-Anforderung gesetzt sind
    if (isset($_POST['positions']) && isset($_POST['post_id'])) {
        $positions = $_POST['positions'];
        $post_id = $_POST['post_id'];

        // Speichere die Positionen in der Datenbank
        update_post_meta($post_id, 'grid_image_positions', $positions);

        // Sende eine Antwort zurück an das JavaScript
        echo 'Image positions saved successfully';
    } else {
        echo 'No positions received';
    }

    // Beende die Ausführung des Skripts
    die();
    
}
add_action('wp_ajax_save_image_positions', 'save_image_positions'); // Füge die Aktion hinzu, um auf die AJAX-Anforderung zu reagieren

// PHP
// Überprüfe, ob die Post-ID gesetzt ist
function load_image_positions() {
    // Check if the post ID is set in the AJAX request
    if (isset($_POST['post_id'])) {
        $post_id = $_POST['post_id'];

        // Get the stored positions from the database
        $positions = get_post_meta($post_id, 'grid_image_positions', true);

        // Send the positions back to the JavaScript
        echo json_encode($positions);
    } else {
        echo 'No post ID received';
    }

    // End the script execution
    wp_die();  // This is important to prevent WordPress from appending extra HTML to the output
}
add_action('wp_ajax_load_image_positions', 'load_image_positions'); // Add the action to respond to the AJAX request




function TBI_enqueue_admin_scripts() {
    global $post;

    // Überprüfen Sie, ob Sie sich im Admin-Bereich befinden und ob der aktuelle Beitrag vom Typ "tableau" ist
    if (is_admin() && isset($post) && 'tableau' === $post->post_type) {
        // Reihen Sie Ihr Skript ein
        wp_enqueue_script('tbi-grid-script', get_template_directory_uri() . '/js/tableau-grid.js', array('jquery'), '1.0.0', true);

        // Übergeben Sie Daten an das Skript
        wp_localize_script('tbi-grid-script', 'wpVars', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}
add_action('admin_enqueue_scripts', 'TBI_enqueue_admin_scripts');
function TBI_save_screenshot() {
    // Post-ID aus $_POST abrufen
    $post_id = intval($_POST['post_id']);
    error_log("Post ID: " . $post_id);

    $data = $_POST['image'];
    $upload_dir = wp_upload_dir();

    // Dekodieren Sie das Base64-Bild
    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data));

    // Dateinamen generieren
    $filename = "screenshot_" . $post_id . ".png";
    
    // Überprüfen Sie, ob der Ordner 'TBI' existiert und erstellen Sie ihn, falls er nicht vorhanden ist
    $tbi_dir = $upload_dir['basedir'] . '/TBI';
    if (!file_exists($tbi_dir)) {
        mkdir($tbi_dir, 0755, true);
    }

    $file_path = $tbi_dir . '/' . $filename;

    // Speichern Sie das Bild
    file_put_contents($file_path, $image_data);

    // Fügen Sie das Bild zur WordPress-Medienbibliothek hinzu
    $file_url = $upload_dir['baseurl'] . '/TBI/' . $filename;


    // Setzen Sie das Bild als Beitragsbild
    set_post_thumbnail($post_id, $attachment_id);

    wp_send_json_success(['message' => 'Bild erfolgreich gespeichert']);
}

add_action('wp_ajax_TBI_save_screenshot', 'TBI_save_screenshot');

// Die folgende Zeile ist für nicht angemeldete Benutzer, aber ich denke, Sie möchten diese Funktion nur für angemeldete Benutzer aktivieren, also können Sie sie entfernen.
// add_action('wp_ajax_nopriv_TBI_save_screenshot', 'TBI_save_screenshot');


function TBI_set_post_thumbnail_from_TBI_folder($post_id) {
    $upload_dir = wp_upload_dir();
    $filename = "screenshot_" . $post_id . ".png";
    $file_path = $upload_dir['basedir'] . '/TBI/' . $filename;

    // Überprüfen, ob die Datei existiert
    if (file_exists($file_path)) {
        // Fügen Sie das Bild zur WordPress-Medienbibliothek hinzu, falls es noch nicht hinzugefügt wurde
        $file_url = $upload_dir['baseurl'] . '/TBI/' . $filename;
        
        // Überprüfen, ob das Attachment bereits existiert
        $existing_attachment_id = attachment_url_to_postid($file_url);
        if (!$existing_attachment_id) {
            $attachment_id = wp_insert_attachment(array(
                'guid' => $file_url,
                'post_mime_type' => 'image/png',
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit'
            ), $file_path);
        } else {
            $attachment_id = $existing_attachment_id;
        }
        // Setzen Sie das Bild als Beitragsbild
        set_post_thumbnail($post_id, $attachment_id);

        // Speichern Sie die Post-ID des Tableau-Beitrags in den Metadaten des Bildes
        update_post_meta($attachment_id, '_associated_tableau_post_id', $post_id);
        // Setzen Sie das Bild als Beitragsbild
        set_post_thumbnail($post_id, $attachment_id);
        
    }
}
function TBI_auto_set_thumbnail($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
        return;

    // Fügen Sie hier ggf. weitere Bedingungen hinzu, z.B. um sicherzustellen, dass dies nur für bestimmte Beitragstypen geschieht
    TBI_set_post_thumbnail_from_TBI_folder($post_id);
}
add_action('save_post', 'TBI_auto_set_thumbnail');

// Diese Funktion kann aufgerufen werden, wenn Sie das Beitragsbild für einen bestimmten Beitrag setzen möchten:
// TBI_set_post_thumbnail_from_TBI_folder($post_id);



function TBI_add_screenshot_button() {
    global $post;
    if ('tableau' === $post->post_type) {
        echo '<button id="TBI_screenshot_button" class="button">Screenshot erstellen und speichern</button>';
    }
}
add_action('post_thumbnail_html', 'TBI_add_screenshot_button');








#######################



function render_grid_selection_metabox($post) {
    // Hole die aktuell gespeicherte Grid-Auswahl aus der Datenbank
    $selected_layout = get_post_meta($post->ID, 'selected_grid_layout', true);

    // Dropdown für Grid-Auswahl
    echo '<select name="grid_layout_selection" id="grid_layout_selection">';
    echo '<option value="layout_1"' . selected($selected_layout, 'layout_1', false) . '>Layout 1</option>';
    echo '<option value="layout_2"' . selected($selected_layout, 'layout_2', false) . '>Layout 2</option>';
    echo '<option value="layout_3"' . selected($selected_layout, 'layout_3', false) . '>Layout 3</option>';
    echo '</select>';
}


function save_grid_layout_selection($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
        return;

    if (isset($_POST['grid_layout_selection'])) {
        update_post_meta($post_id, 'selected_grid_layout', $_POST['grid_layout_selection']);
    }
}
add_action('save_post', 'save_grid_layout_selection');


function add_grid_selection_metabox() {
    add_meta_box(
        'grid_selection_metabox',       // ID der Metabox
        'Grid Auswahl',                 // Titel der Metabox
        'render_grid_selection_metabox', // Callback-Funktion, die den Inhalt der Metabox rendert
        'tableau',                      // Hier den Post Type "tableau" eintragen
        'normal',                         // Position der Metabox (side = rechts neben dem Editor)
        'high'                          // Priorität der Metabox
    );
}
add_action('add_meta_boxes', 'add_grid_selection_metabox');





<?php

// Get the post ID
$post_id = get_the_ID();


function save_starting_page_meta($post_id) {
    if (isset($_POST['sp_tableau_images_order'])) {
        $image_order = $_POST['sp_tableau_images_order'];
        update_post_meta($post_id, 'sp_tableau_images_order', maybe_serialize($image_order));
        error_log('Speichern der Bildsortierung: ' . print_r($image_order, true));
    }
}
add_action('save_post', 'save_starting_page_meta');



add_theme_support('post-thumbnails');


add_action( 'after_setup_theme', 'my_theme_setup' );
function my_theme_setup() {
    add_theme_support( 'static-front-page' );
}



// Diese Funktion zum Einfügen oder Aktualisieren der Meta-Daten für die Startseite
function update_sp_tableau_images_order($post_id) {
    if ($post_id == 191) {
        $order = array(55, 19); // Beispielwerte, bitte anpassen
        update_post_meta($post_id, 'sp_tableau_images_order', maybe_serialize($order));
    }
}
add_action('save_post', 'update_sp_tableau_images_order');




function load_jquery_ui() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'load_jquery_ui');


function enqueue_bootstrap() {
    // Bootstrap CSS
    wp_enqueue_style( 
        'bootstrap-css', 
        'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', 
        array(), 
        null 
    );

    // Bootstrap JS (abhängig von jQuery)
    wp_enqueue_script( 
        'bootstrap-js', 
        'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', 
        array('jquery'), 
        null, 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'enqueue_bootstrap' );





require_once get_template_directory() . '/functions/portfolio-functions.php';
require_once get_template_directory() . '/functions/gridlayouts-functions.php';
require_once get_template_directory() . '/functions/tableau-functions.php';
require_once get_template_directory() . '/functions/grid-functions.php';

require_once get_template_directory() . '/functions/verbinder_functions.php';
require get_template_directory() . '/functions/tableau-Group-functions.php';
require get_template_directory() . '/functions/startingPage-functions.php';
// Fügen Sie html2canvas nur im Admin-Bereich und nur für Tableau-Beiträge hinzu
// Fügen Sie html2canvas nur im Admin-Bereich und nur für Tableau-Beiträge hinzu
function TBI_enqueue_html2canvas() {
    global $post;

    // Überprüfen Sie, ob Sie sich im Admin-Bereich befinden und ob der aktuelle Beitrag vom Typ "tableau" ist
    if (is_admin() && isset($post) && 'tableau' === $post->post_type) {
        wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.3/html2canvas.min.js', array(), '1.3.3', true);
    }
}
add_action('admin_enqueue_scripts', 'TBI_enqueue_html2canvas');


// Funktion zum Hinzufügen der grid-tableau.css
function enqueue_grid_tableau_styles() {
    // Pfad zur grid-tableau.css relativ zum Theme-Verzeichnis
    $css_url = get_template_directory_uri() . '/css/grid-tableau.css';

    // grid-tableau.css in WordPress einbinden
    wp_enqueue_style('grid-tableau-style', $css_url);
}
add_action('wp_enqueue_scripts', 'enqueue_grid_tableau_styles');





function P_enqueue_scripts() {
    wp_enqueue_media();
    wp_enqueue_script('portfolio-admin', get_template_directory_uri() . '/js/portfolio-admin.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'P_enqueue_scripts');

function tableau_enqueue_scripts() {
    // jQuery UI Sortable als Abhängigkeit hinzufügen
    wp_enqueue_script(
        'tableau-admin',
        get_template_directory_uri() . '/js/tableau-admin.js',
        array('jquery', 'jquery-ui-sortable'), // jQuery UI Sortable als Abhängigkeit hinzufügen
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'tableau_enqueue_scripts');

function enqueue_grid_tableau_script() {
    wp_enqueue_script('grid-tableau', get_template_directory_uri() . '/js/tableau-grid.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_grid_tableau_script');












// PHP
// Überprüfe, ob die Post-ID gesetzt ist
if (isset($post_id)) {
    // Hole die gespeicherten Positionen aus der Datenbank
    $positions = get_post_meta($post_id, 'grid_image_positions', true);

    if (!empty($positions)) {
        // Schreibe die Positionen in eine globale JavaScript-Variable
        echo "<script>var loadedPositions = " . json_encode($positions) . ";</script>";
    }
}





#####frontpage####

function enqueue_custom_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new fullpage('#fullpage', {
            autoScrolling: true,
            navigation: true
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'enqueue_custom_scripts');








function add_menu_order_meta_box() {
    add_meta_box(
        'menu_order_meta_box',  // ID der Meta-Box
        'Sortier-Reihenfolge',  // Titel der Meta-Box
        'display_menu_order_meta_box',  // Callback-Funktion
        'tableau',  // Post-Type, wo die Meta-Box angezeigt wird
        'side',  // Kontext (Seitenleiste)
        'default'  // Priorität
    );
}
add_action('add_meta_boxes', 'add_menu_order_meta_box');

function display_menu_order_meta_box($post) {
    $menu_order = get_post_meta($post->ID, 'menu_order', true);
    ?>
    <label for="menu_order">Reihenfolge:</label>
    <input type="number" name="menu_order" value="<?php echo esc_attr($menu_order); ?>" />
    <?php
}

function save_menu_order_meta_box($post_id) {
    if (array_key_exists('menu_order', $_POST)) {
        update_post_meta(
            $post_id,
            'menu_order',
            intval($_POST['menu_order'])
        );
    }
}
add_action('save_post', 'save_menu_order_meta_box');








?>

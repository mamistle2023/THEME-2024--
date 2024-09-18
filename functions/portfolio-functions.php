<?php


// Register a custom post type
function P_create_portfolio_type() {
    register_post_type('Portfolio',
        array(
            'labels' => array(
                'name' => __('Portfolios'),
                'singular_name' => __('Portfolio')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'portfolio'),
            'show_in_rest' => false, // false to disable Gutenberg Editor
        )
    );
}
add_action('init', 'P_create_portfolio_type');


function P_disable_gutenberg_for_portfolio($is_enabled, $post_type) {
    if ($post_type === 'portfolio') return false;
    return $is_enabled;
}
add_filter('use_block_editor_for_post_type', 'P_disable_gutenberg_for_portfolio', 10, 2);


function P_remove_all_meta_boxes() {
   
    remove_meta_box('slugdiv', 'portfolio', 'normal');
    remove_meta_box('authordiv', 'portfolio', 'normal');
    // Fügen Sie weitere Aufrufe von remove_meta_box hier hinzu, um weitere Metaboxen zu entfernen
}
add_action('admin_menu', 'P_remove_all_meta_boxes');

// Add the meta box
function P_add_custom_box() {
    add_meta_box(
        'P_box_id',           // Unique ID
        'Portfolio Images',  // Box title
        'P_display_images_meta_box',  // Content callback
         'Portfolio'              // Post type
    );
    
}
add_action('add_meta_boxes', 'P_add_custom_box');

// Meta box HTML
function P_custom_box_html($post) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    ?>
    <div>
        <input type="button" id="P_upload_button" value="Bild hochladen" />
        <ul id="P_image_list"></ul>
    </div>
    <?php
}

function P_save_postdata($post_id) {
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    if ('Portfolio' == $_POST["post_type"]) {
        if (!current_user_can("edit_post", $post_id))
            return $post_id;

        // Get the corresponding Tableau post ID
        $tableau_post_id = get_post_meta($post_id, '_tableau_id', true);

        // If a corresponding Tableau post exists, update its title
        if ($tableau_post_id) {
            $portfolio_post = get_post($post_id);
            $tableau_post_title = 'Tableau for ' . $portfolio_post->post_title;
            $tableau_post = array(
                'ID'           => $tableau_post_id,
                'post_title'   => $tableau_post_title,
            );
            wp_update_post($tableau_post);
        }
    }

    if (isset($_POST["P_images"])) {
        $P_images_data = $_POST["P_images"];
        update_post_meta($post_id, "_P_images_meta", $P_images_data);
    }
}
add_action("save_post", "P_save_postdata");



// ...

add_action('wp_ajax_P_update_images', 'P_update_images');

function P_update_images() {
    $post_id = $_POST['post_id'];
    $images = isset($_POST['images']) ? $_POST['images'] : [];
$checked = isset($_POST['checked']) ? $_POST['checked'] : [];


    error_log('Post ID: ' . $post_id);
    error_log('Images: ' . print_r($images, true));
    error_log('Checked: ' . print_r($checked, true));
       
    update_post_meta($post_id, '_P_images_meta', $images);
    update_post_meta($post_id, '_P_checked_meta', $checked);

    echo 'Images updated';
    wp_die();
}


// ...
function P_display_images_meta_box($post) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

    $P_images_meta = get_post_meta($post->ID, '_P_images_meta', true);
    $P_checked_meta = get_post_meta($post->ID, '_P_checked_meta', true);

    if (!is_array($P_checked_meta)) {
        $P_checked_meta = array();
    }

    echo '<button id="P_upload_button" type="button" class="button">Upload</button>';
    echo '<ul id="P_image_list">';

    if (!empty($P_images_meta)) {
        foreach ($P_images_meta as $image_id) {
            $image = wp_get_attachment_image_src($image_id, 'full')[0];
            $checked = in_array($image_id, $P_checked_meta) ? 'checked' : '';
            echo '<li><input type="checkbox" class="P_checkbox" data-id="'.$image_id.'" '.$checked.'/><img src="'.$image.'" width="300px"/><input type="hidden" value="'.$image_id.'"/><button class="P_remove_button">Remove</button></li>';
        }
    }

    echo '</ul>';
}

// ...


// Schritt 3: Funktionen für den Portfolio-Beitragstyp

// Funktion zum Speichern der Bilder im Metawert
function P_save_portfolio_images( $post_id ) {
    if ( isset( $_POST['portfolio_images'] ) ) {
        $images = array_map( 'sanitize_text_field', $_POST['portfolio_images'] );
        update_post_meta( $post_id, 'portfolio_images', $images );
    }
}

// Funktion zum Anzeigen der Bilder im Backend
function P_display_saved_portfolio_images( $post ) {
    $images = get_post_meta( $post->ID, 'portfolio_images', true );
    $image_ids = get_post_meta( $post->ID, 'portfolio_image_ids', true );

    if ( ! empty( $images ) ) {
        echo '<ul id="portfolio-images">';
        foreach ( $images as $image ) {
            echo '<li><img src="' . esc_attr( $image ) . '" width="300" /><br />';
            echo '<input type="checkbox" name="portfolio_image_ids[]" value="' . esc_attr( $image ) . '"' . ( in_array( $image, $image_ids ) ? ' checked' : '' ) . ' /> Entfernen</li>';
        }
        echo '</ul>';
    }
}

// Schritt 10: Vorherige Anzeigefunktion ersetzen
remove_action( 'edit_form_after_title', 'P_display_portfolio_images' );
add_action( 'edit_form_after_title', 'P_display_saved_portfolio_images' );

// Schritt 8: Funktion zum Speichern der Bild-IDs aktualisieren
function P_save_portfolio_image_ids( $post_id ) {
    if ( isset( $_POST['portfolio_image_ids'] ) ) {
        $image_ids = array_map( 'sanitize_text_field', $_POST['portfolio_image_ids'] );
        update_post_meta( $post_id, 'portfolio_image_ids', $image_ids );
    }
}

add_action( 'save_post_portfolio', 'P_save_portfolio_image_ids' );

function P_remove_editor() {
    remove_post_type_support('portfolio', 'editor');
}
add_action('admin_init', 'P_remove_editor');


?>

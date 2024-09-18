<?php

function create_T_tableau_post($post_id) {
    // Check if this is an autosave
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    // Check if the post is a revision
    if ( wp_is_post_revision($post_id) ) return;
    // Check if the post type is 'Portfolio'
    if ( get_post_type($post_id) != 'Portfolio' ) return;

    $portfolio_post = get_post($post_id);
    $tableau_post_title = 'Tableau: ' . $portfolio_post->post_title;

    // Check if a Tableau post with this title already exists
    $existing_tableau_post = get_page_by_title($tableau_post_title, OBJECT, 'Tableau');
    if ($existing_tableau_post) return;  // If a Tableau post already exists, do nothing

    // Prepare the new Tableau post data
    $tableau_post = array(
        'post_title'    => $tableau_post_title,
        'post_content'  => $portfolio_post->post_content,
        'post_status'   => 'publish',
        'post_type'     => 'Tableau',
    );

    // Insert the new Tableau post
    $tableau_post_id = wp_insert_post($tableau_post);

    // If the Tableau post was successfully created, save the ID of the Portfolio post
    if ($tableau_post_id != 0) {
        update_post_meta($tableau_post_id, '_portfolio_id', $post_id);

        // Copy the images and checked values from the Portfolio post to the Tableau post
        $P_images_meta = get_post_meta($post_id, '_P_images_meta', true);
        $P_checked_meta = get_post_meta($post_id, '_P_checked_meta', true);
        update_post_meta($tableau_post_id, '_T_images_meta', $P_images_meta);
        update_post_meta($tableau_post_id, '_T_checked_meta', $P_checked_meta);
    }
 // Get the image list from the portfolio post
 $P_images_meta = get_post_meta($post_id, '_P_images_meta', true);
 $P_checked_meta = get_post_meta($post_id, '_P_checked_meta', true);
 

 // Copy the image list to the tableau post
 update_post_meta($tableau_post_id, '_T_images_meta', $P_images_meta);
 update_post_meta($tableau_post_id, '_T_checked_meta', $P_checked_meta);

}
add_action('save_post', 'create_T_tableau_post');

function delete_T_tableau_post($post_id) {
    // Check if the post type is 'Portfolio'
    if ( get_post_type($post_id) != 'Portfolio' ) return;

    $portfolio_post = get_post($post_id);
    $tableau_post_title = 'Tableau: ' . $portfolio_post->post_title;

    // Check if a Tableau post with this title exists
    $existing_tableau_post = get_page_by_title($tableau_post_title, OBJECT, 'Tableau');
    if ($existing_tableau_post) {
        // If a Tableau post exists, delete it
        wp_delete_post($existing_tableau_post->ID, true);
    }
}
add_action('before_delete_post', 'delete_T_tableau_post');

function update_tableau_images($post_id) {
    // Get the tableau post id
    $tableau_post_id = get_post_meta($post_id, '_tableau_id', true);

    // If there is no tableau post, do nothing
    if (!$tableau_post_id) {
        return;
    }

    // Get the image list from the portfolio post
    $P_images_meta = get_post_meta($post_id, '_P_images_meta', true);
    $P_checked_meta = get_post_meta($post_id, '_P_checked_meta', true);

    // Update the image list in the tableau post
    update_post_meta($tableau_post_id, '_T_images_meta', $P_images_meta);
    update_post_meta($tableau_post_id, '_T_checked_meta', $P_checked_meta);
}
add_action('save_post_portfolio', 'update_tableau_images');
add_action('save_post_tableau', 'update_tableau_images');

add_action('save_post_portfolio', 'V_create_tableau_post');

add_action('save_post_portfolio', 'V_create_tableau_post');

function V_create_tableau_post($post_id) {
    // Überprüfen Sie, ob dies ein automatisches Speichern ist, wenn ja, überspringen Sie es
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Überprüfen Sie, ob der aktuelle Benutzer die Berechtigung hat, Beiträge zu bearbeiten
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Überprüfen Sie, ob bereits ein zugehöriger Tableau-Beitrag existiert
    $existing_tableau_id = get_post_meta($post_id, '_tableau_id', true);
    if ($existing_tableau_id) {
        return;
    }

    // Den Titel des zugehörigen Portfolio-Beitrags abrufen
    $portfolio_title = get_the_title($post_id);

    // Erstellen Sie den neuen Tableau-Beitrag
    $tableau_post = array(
        'post_title'    => 'Tableau for ' . $portfolio_title,
        'post_status'   => 'publish',
        'post_type'     => 'tableau',
    );

    // Fügen Sie den neuen Tableau-Beitrag zur Datenbank hinzu
    $tableau_id = wp_insert_post($tableau_post);

    // Verknüpfen Sie das Portfolio mit dem Tableau-Beitrag
    update_post_meta($post_id, '_tableau_id', $tableau_id);
    update_post_meta($tableau_id, '_portfolio_id', $post_id);
}


function update_tableau_post_title($post_id) {
    // Check if this is an autosave or revision
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    // Check if the post type is 'Portfolio'
    if (get_post_type($post_id) != 'portfolio') return;

    // Get the portfolio post title
    $portfolio_title = get_the_title($post_id);

    // Get the connected tableau post id
    $tableau_id = get_post_meta($post_id, '_tableau_id', true);

    // Update the tableau post title with the portfolio post title
    if ($tableau_id) {
        $tableau_post = array(
            'ID'           => $tableau_id,
            'post_title'   => 'Tableau for ' . $portfolio_title,
        );

        // Update the tableau post
        wp_update_post($tableau_post);
    }
}
add_action('save_post_portfolio', 'update_tableau_post_title');

function move_tableau_to_trash($post_id) {
    if (get_post_type($post_id) == 'portfolio' && 'trash' === $_REQUEST['action']) {
        // Get the connected Tableau post ID
        $tableau_id = get_post_meta($post_id, '_tableau_id', true);

        // If a connected Tableau post exists, move it to the trash
        if (!empty($tableau_id)) {
            wp_trash_post($tableau_id);
        }
    }
}
add_action('transition_post_status', 'move_tableau_to_trash', 10, 3);

function delete_connected_tableau($post_id) {
    // Check if the post being deleted is a 'portfolio' post type.
    if (get_post_type($post_id) == 'portfolio') {
        // Get the connected Tableau post ID.
        $tableau_id = get_post_meta($post_id, '_tableau_id', true);

        // If a connected Tableau post exists, delete it.
        if (!empty($tableau_id)) {
            wp_delete_post($tableau_id, false); // Set second parameter to true if you want to bypass the trash.
        }
    }
}
add_action('before_delete_post', 'delete_connected_tableau');

function update_portfolio_post($post_id) {
    // Check if this is an autosave or a revision
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    // Check if the post type is 'Tableau'
    if (get_post_type($post_id) != 'tableau') return;

    // Get the portfolio post id
    $portfolio_id = get_post_meta($post_id, '_portfolio_id', true);

    // Update the portfolio post with the images and checked values from the tableau post
    if ($portfolio_id) {
        $T_images_meta = get_post_meta($post_id, '_T_images_meta', true);
        $T_checked_meta = get_post_meta($post_id, '_T_checked_meta', true);
        update_post_meta($portfolio_id, '_P_images_meta', $T_images_meta);
        update_post_meta($portfolio_id, '_P_checked_meta', $T_checked_meta);
    }
}
add_action('save_post_tableau', 'update_portfolio_post');

?>

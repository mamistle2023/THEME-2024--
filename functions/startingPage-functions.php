<?php
// In Ihrer functions.php oder einem spezifischen Plugin-Datei

// Registrierung des "Starting Page" Beitragstyps
function SP_register_starting_pages_post_type() {
    $labels = array(
        'name' => __('Starting Pages'),
        'singular_name' => __('Starting Page')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title') // Nur Titel-Unterstützung, da wir keinen Standardeditor verwenden
    );

    register_post_type('starting_pages', $args);
}

add_action('init', 'SP_register_starting_pages_post_type');

// Metabox hinzufügen
function SP_add_metabox() {
    add_meta_box(
        'sp_tableaugroup_metabox',
        'Tableau Group Bilder',
        'SP_display_metabox',
        'starting_pages',
        'normal',
        'high'
    );
}

function SP_display_metabox($post) {
    echo '<ul class="sp-tableau-images">';  // Beginn der sortierbaren Liste

    // Hole alle veröffentlichten 'tableaugroup' Beiträge
    $args = array(
        'post_type' => 'tableaugroup',
        'post_status' => 'publish',
        'numberposts' => -1
    );
    $tableau_posts = get_posts($args);

    // Sortierungscode
    $order = get_post_meta($post->ID, 'sp_tableau_images_order', true);
    if (!empty($order)) {
        $order = unserialize($order);
        if (is_array($order)) {
            usort($tableau_posts, function($a, $b) use ($order) {
                $pos_a = array_search($a->ID, $order);
                $pos_b = array_search($b->ID, $order);
                return $pos_a - $pos_b;
            });
        } else {
            error_log('Das Order-Array ist nicht gültig.');
        }
    } else {
        error_log('Keine Sortierreihenfolge gefunden oder das Order-Array ist nicht gültig.');
    }

    foreach ($tableau_posts as $tableau_post) {
        echo '<li data-post-id="' . $tableau_post->ID . '">';  // Daten-Attribut hinzugefügt

        // Überprüfen, ob der Beitrag ein Thumbnail hat
        if (has_post_thumbnail($tableau_post->ID)) {
            $thumb_id = get_post_thumbnail_id($tableau_post->ID);
            $thumb_url = wp_get_attachment_url($thumb_id);
            echo "<img src='" . $thumb_url . "' alt='Thumbnail'>";
        } else {
            echo "Kein Thumbnail für den Beitrag mit der ID " . $tableau_post->ID . "<br>";
        }

        $checked = get_post_meta($post->ID, 'SP_remove_' . $tableau_post->ID, true) ? 'checked' : '';
        echo '<input type="checkbox" class="sp-remove-checkbox" name="SP_remove_' . $tableau_post->ID . '" ' . $checked . '> Entfernen ' . get_the_title($tableau_post->ID);
        echo '</li>';
    }

    echo '</ul>';  // Ende der sortierbaren Liste
}

add_action('add_meta_boxes', 'SP_add_metabox');

// Speichern der Metadaten
function SP_save_postdata($post_id) {
    if (get_post_type($post_id) !== 'starting_pages') {
        return;
    }

    // Überprüfen, ob bereits ein "Starting Page" Beitrag existiert
    $args = [
        'post_type' => 'starting_pages',
        'post__not_in' => [$post_id],
        'post_status' => 'publish',
        'numberposts' => 1,
    ];
    $existing_posts = get_posts($args);

    if (!empty($existing_posts)) {
        // Wenn bereits ein Beitrag existiert, setzen wir diesen zurück auf Entwurf
        foreach ($existing_posts as $existing_post) {
            wp_update_post([
                'ID' => $existing_post->ID,
                'post_status' => 'draft',
            ]);
        }
    }

    $args = array(
        'post_type' => 'tableaugroup',
        'post_status' => 'publish',
        'numberposts' => -1
    );
    $tableau_posts = get_posts($args);

    foreach ($tableau_posts as $tableau_post) {
        if (isset($_POST['SP_remove_' . $tableau_post->ID])) {
            update_post_meta($post_id, 'SP_remove_' . $tableau_post->ID, 'yes');

            // Setzen Sie den Status des "Tableau Group" Beitrags auf 'nicht veröffentlicht'
            wp_update_post(array('ID' => $tableau_post->ID, 'post_status' => 'draft'));
        } else {
            delete_post_meta($post_id, 'SP_remove_' . $tableau_post->ID);
        }
    }
}

add_action('save_post', 'SP_save_postdata');

// Sortierreihenfolge speichern
function SP_save_sort_order() {
    if (isset($_POST['order']) && isset($_POST['post_id'])) {
        $order = $_POST['order'];
        $post_id = $_POST['post_id'];
        update_post_meta($post_id, 'sp_tableau_images_order', serialize($order));
        error_log('Speichere Sortierreihenfolge für Post ID: ' . $post_id);
        error_log('Sortierreihenfolge: ' . print_r($order, true));
    }
    die();
}

add_action('wp_ajax_sp_save_sort_order', 'SP_save_sort_order');

// Enqueue Scripts für Admin-Seite
function SP_enqueue_scripts() {
    // Stellen Sie sicher, dass das Skript nur im Admin-Bereich und nur auf der "Starting Pages" Bearbeitungsseite geladen wird
    $screen = get_current_screen();
    if ($screen->id == 'starting_pages') {
        wp_enqueue_script('jquery-ui-sortable'); // WordPress eingebautes jQuery UI Sortable Skript
        wp_enqueue_script('sp-script', get_template_directory_uri() . '/js/starting.js', array('jquery', 'jquery-ui-sortable'), '1.0.0', true);
    }
}

add_action('admin_enqueue_scripts', 'SP_enqueue_scripts');

// Admin Styles hinzufügen
function SP_admin_styles() {
    echo '
        <style>
            .sp-tableau-images img {
                max-width: 450px;
                width: auto;
                height: auto;
            }
        </style>
    ';
}
add_action('admin_head', 'SP_admin_styles');

function SP_get_ordered_tableau_groups($starting_page_id) {
    global $wpdb;

    $order_meta_key = 'sp_tableau_groups_order';
    
    // Retrieve the serialized order from the post meta
    $order = get_post_meta($starting_page_id, $order_meta_key, true);
    error_log("Abrufen der Sortierreihenfolge für Starting Page ID: " . $starting_page_id);
    error_log("Gefundene Sortierreihenfolge (serialisiert): " . $order);

    if (!$order) {
        error_log("Keine Sortierreihenfolge gefunden oder das Order-Array ist nicht gültig.");
        return [];
    }

    $order = maybe_unserialize($order);
    error_log("Deserialisierte Sortierreihenfolge: " . print_r($order, true));

    if (!is_array($order) || empty($order)) {
        error_log("Keine gültige Sortierreihenfolge gefunden.");
        return [];
    }

    // Get tableau groups in the specified order
    $placeholders = implode(',', array_fill(0, count($order), '%d'));
    $query = $wpdb->prepare("
        SELECT * 
        FROM $wpdb->posts 
        WHERE ID IN ($placeholders) 
        AND post_type = 'tableaugroup' 
        AND post_status = 'publish' 
        ORDER BY FIELD(ID, $placeholders)
    ", $order);

    $tableau_groups = $wpdb->get_results($query);

    if (!$tableau_groups) {
        error_log("Keine tableau groups gefunden.");
    }

    return $tableau_groups ? $tableau_groups : [];
}


// Add a new column to the starting_pages post type
function sp_add_custom_column($columns) {
    $columns['sp_published'] = __('Published', 'textdomain');
    return $columns;
}
add_filter('manage_starting_pages_posts_columns', 'sp_add_custom_column');

// Populate the custom column with radio buttons
function sp_custom_column_content($column_name, $post_id) {
    if ($column_name == 'sp_published') {
        $published_checked = get_post_status($post_id) == 'publish' ? 'checked' : '';
        $draft_checked = get_post_status($post_id) == 'draft' ? 'checked' : '';
        echo '<label><input type="radio" name="sp_status_' . $post_id . '" value="publish" ' . $published_checked . '> Publish</label>';
        echo '<label><input type="radio" name="sp_status_' . $post_id . '" value="draft" ' . $draft_checked . '> Draft</label>';
    }
}
add_action('manage_starting_pages_posts_custom_column', 'sp_custom_column_content', 10, 2);

// Enqueue the script to handle the radio button clicks
function sp_enqueue_admin_script($hook) {
    if ($hook != 'edit.php' || get_current_screen()->post_type != 'starting_pages') {
        return;
    }
    wp_enqueue_script('sp-admin-script', get_template_directory_uri() . '/js/sp-admin.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'sp_enqueue_admin_script');

// Handle the AJAX request to set the selected post status
function sp_set_post_status() {
    if (isset($_POST['post_id']) && isset($_POST['status'])) {
        $post_id = intval($_POST['post_id']);
        $status = sanitize_text_field($_POST['status']);

        // If setting to publish, set all other starting_pages posts to draft
        if ($status === 'publish') {
            $args = array(
                'post_type' => 'starting_pages',
                'post_status' => array('publish', 'draft'),
                'posts_per_page' => -1
            );
            $starting_pages = get_posts($args);

            foreach ($starting_pages as $page) {
                if ($page->ID != $post_id) {
                    wp_update_post(array('ID' => $page->ID, 'post_status' => 'draft'));
                }
            }
        }

        // Update the selected post status
        wp_update_post(array('ID' => $post_id, 'post_status' => $status));

        wp_send_json_success();
    }
    wp_send_json_error();
}
add_action('wp_ajax_sp_set_post_status', 'sp_set_post_status');


// Hinzufügen der Quick Edit Checkbox
function SP_add_quick_edit($column_name, $post_type) {
    if ($post_type == 'starting_pages' && $column_name == 'title') {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label class="alignleft">
                    <span class="title">Set as Active</span>
                    <span class="input-text-wrap">
                        <input type="checkbox" name="sp_set_active" value="1">
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action('quick_edit_custom_box', 'SP_add_quick_edit', 10, 2);

// Daten der Quick Edit Checkbox speichern
function SP_save_quick_edit_data($post_id) {
    if (isset($_POST['sp_set_active'])) {
        // Setzt alle anderen Starting Page Beiträge auf "Entwurf"
        $args = array(
            'post_type' => 'starting_pages',
            'post_status' => array('publish', 'draft'),
            'post__not_in' => array($post_id)
        );
        $other_posts = get_posts($args);
        foreach ($other_posts as $post) {
            wp_update_post(array(
                'ID' => $post->ID,
                'post_status' => 'draft'
            ));
        }
        // Setzt den aktuellen Beitrag auf "veröffentlicht"
        wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'publish'
        ));
    }
}
add_action('save_post', 'SP_save_quick_edit_data');

// Anpassung der Spalten für den Beitragstyp "starting_pages"
function SP_manage_columns($columns) {
    $columns['active'] = 'Active';
    return $columns;
}
add_filter('manage_starting_pages_posts_columns', 'SP_manage_columns');

// Inhalt für die neue Spalte "Active" hinzufügen
function SP_render_active_column($column, $post_id) {
    if ($column == 'active') {
        $status = get_post_status($post_id);
        echo $status == 'publish' ? 'Yes' : 'No';
    }
}
add_action('manage_starting_pages_posts_custom_column', 'SP_render_active_column', 10, 2);


// Daten für Quick Edit vorbereiten
function SP_quick_edit_javascript() {
    global $current_screen;
    if ($current_screen->post_type != 'starting_pages') return;
    ?>
    <script type="text/javascript">
        function setQuickEditValues(post_id, active) {
            var $ = jQuery;
            var $checkbox = $('input[name="sp_set_active"]');
            $checkbox.prop('checked', active);
        }

        jQuery(document).ready(function ($) {
            var $wp_inline_edit = inlineEditPost.edit;
            inlineEditPost.edit = function (post_id) {
                $wp_inline_edit.apply(this, arguments);
                var post_id = 0;
                if (typeof (post_id) == 'object') {
                    post_id = parseInt(this.getId(post_id));
                }
                if (post_id > 0) {
                    var $edit_row = $('#edit-' + post_id);
                    var active = $('#post-' + post_id).hasClass('status-publish');
                    setQuickEditValues(post_id, active);
                }
            }
        });
    </script>
    <?php
}
add_action('admin_footer-edit.php', 'SP_quick_edit_javascript');

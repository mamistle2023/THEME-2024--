<?php
namespace CustomNamespace;

require_once get_template_directory() . '/functions/gridlayouts-functions.php';

// Funktion zum Rendern des Grids basierend auf dem ausgewählten Layout
function render_tableau_grid($grid_layout, $positions) {
    switch ($grid_layout) {
        case 'layout_1':
            echo \grid_layout_1($positions);
            break;
        case 'layout_2':
            echo \grid_layout_2($positions);
            break;
        case 'layout_3':
            echo \grid_layout_3($positions);
            break;
        default:
            echo \grid_layout_1($positions);
            break;
    }
}

// Falls die Funktion grid_layout_1 im globalen Namespace noch nicht existiert, wird sie hier definiert
if (!function_exists('CustomNamespace\grid_layout_1')) {
    function grid_layout_1($positions) {
        ?>
        <div class="grid-layout-1">
            <?php foreach ($positions as $column_id => $image_id): ?>
                <div id="<?php echo esc_attr($column_id); ?>" class="tableau-grid-column">
                    <?php echo wp_get_attachment_image($image_id, 'full'); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

// Falls die Funktion grid_layout_2 im globalen Namespace noch nicht existiert, wird sie hier definiert
if (!function_exists('CustomNamespace\grid_layout_2')) {
    function grid_layout_2($positions) {
        ?>
        <div class="grid-layout-2">
            <?php foreach ($positions as $column_id => $image_id): ?>
                <div id="<?php echo esc_attr($column_id); ?>" class="tableau-grid-column">
                    <?php echo wp_get_attachment_image($image_id, 'full'); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

// Falls die Funktion grid_layout_3 im globalen Namespace noch nicht existiert, wird sie hier definiert
if (!function_exists('CustomNamespace\grid_layout_3')) {
    function grid_layout_3($positions) {
        ?>
        <div class="grid-layout-3">
            <?php foreach ($positions as $column_id => $image_id): ?>
                <div id="<?php echo esc_attr($column_id); ?>" class="tableau-grid-column">
                    <?php echo wp_get_attachment_image($image_id, 'full'); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

// Funktion zur Anzeige des Grids im Frontend
function display_frontend_grid($post_id) {
    $positions = get_post_meta($post_id, 'grid_image_positions', true);
    $grid_layout = get_post_meta($post_id, 'selected_grid_layout', true);

    if (!$grid_layout) {
        $grid_layout = 'layout_1';
    }

    if (is_array($positions) && !empty($positions)) {
        render_tableau_grid($grid_layout, $positions);
    } else {
        echo '<p>Keine Bilder gefunden</p>';
    }
}
?>

<?php
function render_grid_layout($layout, $positions) {
    $html = '';
    switch ($layout) {
        case 'layout_1':
            $html = grid_layout_1();
            break;
        case 'layout_2':
            $html = grid_layout_2();
            break;
        case 'layout_3':
            $html = grid_layout_3();
            break;
        default:
            $html = grid_layout_1();
            break;
    }

    foreach ($positions as $column_id => $image_id) {
        if (preg_match('/\d+/', $image_id, $matches)) {
            $image_id = intval($matches[0]);
            $image_src = wp_get_attachment_image_src($image_id, 'full');
            if ($image_src && isset($image_src[0])) {
                error_log('Bild-ID: ' . $image_id . ', Bild-URL: ' . $image_src[0]);
                $img_tag = '<img src="' . esc_url($image_src[0]) . '" alt="" class="img-fluid" />';
                $html = str_replace('>' . $column_id . '<', '>' . $img_tag . '<', $html);
            } else {
                error_log('Bild nicht gefunden für Bild-ID: ' . $image_id);
                $html = str_replace('>' . $column_id . '<', '>Bild nicht gefunden<', $html);
            }
        } else {
            error_log('Ungültige Bild-ID: ' . $image_id);
            $html = str_replace('>' . $column_id . '<', '>Ungültige Bild-ID<', $html);
        }
    }

    return $html;
}
?>
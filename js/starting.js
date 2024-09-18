jQuery(document).ready(function($) {
    $('.sp-tableau-images').sortable({
        update: function(event, ui) {
            var order = $(this).sortable('toArray', {
                attribute: 'data-post-id'
            });
            var post_id = $('#post_ID').val();
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'sp_save_sort_order',
                    order: order,
                    post_id: post_id
                },
                success: function(response) {
                    console.log('Sortierreihenfolge gespeichert');
                }
            });
        }
    });
});


jQuery(document).ready(function($) {
    $('input[type="radio"][name^="sp_status_"]').on('change', function() {
        var post_id = $(this).attr('name').replace('sp_status_', '');
        var status = $(this).val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'sp_set_post_status',
                post_id: post_id,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    // Uncheck all radio buttons and check the selected one
                    $('input[type="radio"][name^="sp_status_"]').each(function() {
                        $(this).prop('checked', false);
                    });
                    $('input[type="radio"][name="sp_status_' + post_id + '"][value="' + status + '"]').prop('checked', true);
                } else {
                    alert('Fehler beim Setzen des Beitragsstatus');
                }
            }
        });
    });
});

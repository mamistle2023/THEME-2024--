(function($) {

    var mediaUploader;

    $(document).ready(function() {
        $('#P_upload_button').on('click', function(e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: true
            });
            $('#P_image_list').sortable();
            mediaUploader.on('select', function() {
                var selection = mediaUploader.state().get('selection');
                selection.map( function( attachment ) {
                    attachment = attachment.toJSON();
                    $('#P_image_list').append('<li><input type="checkbox" class="P_checkbox" data-id="'+ attachment.id +'"/><img src="'+ attachment.url +'" width="300px"/><input type="hidden" value="'+ attachment.id +'"/><button class="P_remove_button">Remove</button></li>');
                });
            });
            mediaUploader.open();
        });

        $('#P_image_list').on('click', '.P_remove_button', function() {
            var imageID = $(this).siblings('input[type="hidden"]').val();
            $(this).parent().remove();
            P_update_images();
        });

        $('#P_image_list').on('click', '.P_checkbox', function() {
            P_update_images();
        });

        // Function to handle image updates
        function P_update_images() {
            var data = {
                'action': 'P_update_images',
                'post_id': $('#post_ID').val(),
                'images': $('#P_image_list input[type="hidden"]').map(function(){return $(this).val();}).get(),
                'checked': $('#P_image_list input[type="checkbox"]:checked').map(function(){return $(this).data('id');}).get()
            };
        
            $.post(ajaxurl, data, function(response) {
                // The alert has been removed
            });
        }
        

        $('#P_image_list').sortable({
            stop: function() {
                P_update_images();
            }
        });
    });
    $('#post').on('submit', function(e) {
        var images = [];
        var checked = [];
        $('#P_image_list li').each(function() {
            var id = $(this).find('input[type="hidden"]').val();
            images.push(id);
            if ($(this).find('input[type="checkbox"]').is(':checked')) {
                checked.push(id);
            }
        });
    
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            async: false,
            data: {
                action: 'P_update_images',
                post_id: $('#post_ID').val(),
                images: images,
                checked: checked
            },
            
        });
    });
    
})(jQuery);

jQuery(document).ready(function($) {
    $('#file-upload-form').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Serialize the form data
        var formData = new FormData($(this)[0]);

        // Add AJAX nonce
        formData.append('action', 'handle_file_upload');
        formData.append('file_upload_nonce_field', $('#file_upload_nonce_field').val());

        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: file_upload_ajax.ajaxurl, // WordPress AJAX URL
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#file-upload-message').html(response);
            },
            error: function(xhr, status, error) {
                $('#file-upload-message').html('<div class="error">Error: ' + xhr.responseText + '</div>');
            }
        });
    });
});

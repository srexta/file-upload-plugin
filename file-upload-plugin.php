<?php
/*
Plugin Name: File Upload Plugin
Description: A plugin to allow frontend file uploads.
Version: 1.0
*/

// Enqueue scripts and styles
function file_upload_enqueue_scripts() {
    wp_enqueue_script('file-upload-script', plugin_dir_url(__FILE__) . 'js/file-upload.js', array('jquery'), '1.0', true);
    // Localize the AJAX URL
    wp_localize_script('file-upload-script', 'file_upload_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'file_upload_enqueue_scripts');

// Create the frontend form
function file_upload_form() {
    ob_start(); ?>
    <form id="file-upload-form" action="#" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload File" name="submit">
        <?php wp_nonce_field('file_upload_nonce', 'file_upload_nonce_field'); ?>
    </form>
    <div id="file-upload-message"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('file_upload_form', 'file_upload_form');

// Handle file upload
function handle_file_upload() {
    // Verify nonce
    if (!isset($_POST['file_upload_nonce_field']) || !wp_verify_nonce($_POST['file_upload_nonce_field'], 'file_upload_nonce')) {
        die('Security check');
    }

    if (isset($_FILES['file'])) {
        $uploaded_file = $_FILES['file'];

        $file_type = wp_check_filetype($uploaded_file['name']);
        $allowed_types = array('jpg', 'jpeg', 'png');
        if (!in_array($file_type['ext'], $allowed_types)) {
            echo "Error: Only JPG, JPEG, and PNG formats are allowed.";
            die();
        }

        $upload = wp_upload_bits($uploaded_file['name'], null, file_get_contents($uploaded_file['tmp_name']));

        if (!$upload['error']) {
            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title' => sanitize_file_name($uploaded_file['name']),
                'post_content' => '',
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'guid' => $upload['url']
            );

            $attachment_id = wp_insert_attachment($attachment, $upload['file']);

            if (!is_wp_error($attachment_id)) {
                $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                wp_update_attachment_metadata($attachment_id, $attach_data);
            } else {
                echo "Error: Unable to create attachment.";
            }
        } else {
            echo esc_html($upload['error']);
        }

        die(); // Always exit to avoid further execution
    }
}
add_action('wp_ajax_handle_file_upload', 'handle_file_upload');
add_action('wp_ajax_nopriv_handle_file_upload', 'handle_file_upload'); // Allow non-logged in users to upload files via AJAX

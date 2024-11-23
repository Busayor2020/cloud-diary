<?php
/**
 * Plugin Name: cloud diary
 * Plugin URI: https://wordpress.org/plugins/user-notes
 * Description: Allows users to take notes with local storage
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Victor Busayor
 * Author URI: https://d-e-portfolio.vercel.app/
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cloud diary
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts and styles
 *
 * @return void
 */
function user_notes_enqueue_scripts() {
    wp_enqueue_script(
        'user-notes-js', 
        plugin_dir_url(__FILE__) . 'js/user-notes.js', 
        array('jquery'), 
        '1.0.0', 
        true
    );
    
    wp_enqueue_style(
        'user-notes-css', 
        plugin_dir_url(__FILE__) . 'css/user-notes.css',
        array(),
        '1.0.0'
    );
    
    wp_localize_script('user-notes-js', 'userNotesAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('user_notes_nonce'),
        'saveSuccess' => esc_html__('Notes saved successfully', 'user-notes'),
        'saveError' => esc_html__('Failed to save notes', 'user-notes')
    ));
}
add_action('wp_enqueue_scripts', 'user_notes_enqueue_scripts');

/**
 * Shortcode to display note-taking interface
 *
 * @return string
 */
function user_notes_shortcode() {
    if (!is_user_logged_in()) {
        return esc_html__('Please log in to use notes.', 'user-notes');
    }
    
    $user_id = get_current_user_id();
    $saved_notes = get_user_meta($user_id, 'user_notes', true);
    
    ob_start();
    ?>
<div id="user-notes-container">
    <textarea id="user-notes-content"
        aria-label="<?php esc_attr_e('User Notes', 'user-notes'); ?>"><?php echo esc_textarea($saved_notes); ?></textarea>
    <button id="user-notes-save" class="button button-primary"><?php esc_html_e('Save Notes', 'user-notes'); ?></button>
    <div class="notes-saved-message" aria-live="polite"></div>
</div>
<?php
    return ob_get_clean();
}
add_shortcode('user_notes', 'user_notes_shortcode');

/**
 * AJAX handler to save notes to user meta
 *
 * @return void
 */
function save_user_notes() {
    if (!check_ajax_referer('user_notes_nonce', 'nonce', false)) {
        wp_send_json_error(esc_html__('Security check failed', 'user-notes'));
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(esc_html__('User not logged in', 'user-notes'));
    }
    
    if (!isset($_POST['notes'])) {
        wp_send_json_error(esc_html__('No notes provided', 'user-notes'));
    }
    
    $user_id = get_current_user_id();
    $notes = sanitize_textarea_field(wp_unslash($_POST['notes']));
    $update_result = update_user_meta($user_id, 'user_notes', $notes);
    
    if ($update_result !== false) {
        wp_send_json_success(esc_html__('Notes saved successfully', 'user-notes'));
    } else {
        wp_send_json_error(esc_html__('Failed to save notes', 'user-notes'));
    }
}
add_action('wp_ajax_save_user_notes', 'save_user_notes');
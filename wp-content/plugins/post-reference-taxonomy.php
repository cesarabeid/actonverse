<?php
/*
Plugin Name: Post Reference Taxonomy
Description: Adds a custom taxonomy for referencing other posts.
Version: 1.0
Author: Cesar Abeid
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'init', 'register_post_references_taxonomy' );

function register_post_references_taxonomy() {
    $args = array(
        'labels' => array(
            'name' => 'Post References',
            'singular_name' => 'Post Reference'
        ),
        'public' => true,
        'hierarchical' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'post_references'),
    );

    register_taxonomy('post_references', 'post', $args);
}

// Next

add_action('add_meta_boxes', 'add_post_references_meta_box');

function add_post_references_meta_box() {
    add_meta_box('post-references-meta-box', 'Parent Actons', 'post_references_meta_box_callback', 'post', 'side');
}

function post_references_meta_box_callback($post) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field('post_references_meta_box', 'post_references_meta_box_nonce');

    $selected_posts = get_post_meta($post->ID, '_post_references', true) ?: array();

    echo '<label for="post_references_field">Select Parent Actons:</label>';
    echo '<select id="post_references_field" name="post_references_field[]" multiple="multiple" style="width:100%;max-width:250px;height:100px;">';

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'post',
        'post_status' => 'publish',
        'post__not_in' => array($post->ID) // Exclude current post
    );
    $posts = get_posts($args);
    foreach ($posts as $ref_post) {
        $selected = in_array($ref_post->ID, $selected_posts) ? ' selected' : '';
        echo '<option value="' . esc_attr($ref_post->ID) . '"' . $selected . '>' . esc_html($ref_post->post_title) . '</option>';
    }

    echo '</select>';
}

add_action('add_meta_boxes', 'add_custom_fields_meta_box');

function add_custom_fields_meta_box() {
    add_meta_box('custom-fields-meta-box', 'Custom Fields', 'custom_fields_meta_box_callback', 'post', 'side');
}

function custom_fields_meta_box_callback($post) {
    wp_nonce_field('custom_fields_meta_box', 'custom_fields_meta_box_nonce');

    // Owner Field
    $owner = get_post_meta($post->ID, '_owner', true);
    $users = get_users(array('fields' => array('ID', 'display_name')));
    echo '<p><label for="owner_field">Owner:</label>';
    echo '<select id="owner_field" name="owner_field">';
    echo '<option value="">Select a User</option>';
    foreach ($users as $user) {
        echo '<option value="' . esc_attr($user->ID) . '"' . selected($owner, $user->ID) . '>' . esc_html($user->display_name) . '</option>';
    }
    echo '</select></p>';
    
    // Start Time Field
    $start_time = get_post_meta($post->ID, '_start_time', true);
    echo '<p><label for="start_time_field">Start Time:</label>';
    echo '<input type="datetime-local" id="start_time_field" name="start_time_field" value="' . esc_attr($start_time) . '" /></p>';

    // End Time Field
    $end_time = get_post_meta($post->ID, '_end_time', true);
    echo '<p><label for="end_time_field">End Time:</label>';
    echo '<input type="datetime-local" id="end_time_field" name="end_time_field" value="' . esc_attr($end_time) . '" /></p>';

    // Status Field
    $status = get_post_meta($post->ID, '_status', true);
    echo '<p><label for="status_field">Status:</label>';
    echo '<select id="status_field" name="status_field">';
    echo '<option value="not_done"' . selected($status, 'not_done', false) . '>Not Done</option>';
    echo '<option value="in_progress"' . selected($status, 'in_progress', false) . '>In Progress</option>';
    echo '<option value="done"' . selected($status, 'done', false) . '>Done</option>';
    echo '</select></p>';
}


add_action('save_post', 'save_post_references', 'save_custom_fields_data');

function save_post_references($post_id) {
    if (!isset($_POST['post_references_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['post_references_meta_box_nonce'], 'post_references_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['post_references_field'])) {
        return;
    }

    $post_references = array_map('sanitize_text_field', $_POST['post_references_field']);
    update_post_meta($post_id, '_post_references', $post_references);
}

add_action('save_post', 'save_custom_fields_data');

function save_custom_fields_data($post_id) {
    if (!isset($_POST['custom_fields_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['custom_fields_meta_box_nonce'], 'custom_fields_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save Owner
    if (isset($_POST['owner_field'])) {
        update_post_meta($post_id, '_owner', sanitize_text_field($_POST['owner_field']));
    }

    // Save Start Time
    if (isset($_POST['start_time_field'])) {
        update_post_meta($post_id, '_start_time', sanitize_text_field($_POST['start_time_field']));
    }

    // Save End Time
    if (isset($_POST['end_time_field'])) {
        update_post_meta($post_id, '_end_time', sanitize_text_field($_POST['end_time_field']));
    }

    // Save Status
    if (isset($_POST['status_field'])) {
        update_post_meta($post_id, '_status', sanitize_text_field($_POST['status_field']));
    }
}



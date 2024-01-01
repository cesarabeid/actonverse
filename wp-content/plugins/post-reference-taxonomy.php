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


add_action('save_post', 'save_post_references');

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


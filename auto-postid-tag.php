<?php
/**
 * Plugin Name: Auto Post ID Tag
 * Description: Automatically creates a tag with the name as the Post ID each time a new post is published.
 * Version: 1.0
 * Author: Cesar Abeid
 */

// Hook into the 'publish_post' action
add_action('publish_post', 'create_post_id_tag', 10, 2);

/**
 * Creates a tag that matches the Post ID.
 * 
 * @param int $post_ID Post ID.
 * @param WP_Post $post Post object.
 */
function create_post_id_tag($post_ID, $post) {
    // Check if the tag already exists
    if (!term_exists($post_ID, 'post_tag')) {
        // Create the tag
        wp_insert_term(
            (string) $post_ID, // The tag name (as a string)
            'post_tag'         // The taxonomy (in this case, tags)
        );
    }

    // Assign the tag to the post
    wp_set_post_tags($post_ID, (string) $post_ID, true);
}


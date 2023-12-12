<?php
/**
 * Plugin Name: Tag to Title List
 * Description: For each post, adds a line at the top listing titles of posts corresponding to its tags.
 * Version: 1.0
 * Author: Cesar Abeid
 */

// Hook into 'the_content' to modify the post content
add_filter('the_content', 'add_titles_of_tagged_posts');

/**
 * Adds a list of post titles corresponding to each tag of the post at the beginning of the post content.
 *
 * @param string $content The original content of the post.
 * @return string Modified content of the post.
 */
function add_titles_of_tagged_posts($content) {
    global $post;

    // Get tags of the current post
    $tags = wp_get_post_tags($post->ID);

    // Initialize an array to store titles
    $titles = [];

    foreach ($tags as $tag) {
        // Check if the tag name is numeric and a valid Post ID
        if (is_numeric($tag->name)) {
            $tag_post_id = intval($tag->name);

            // Get the post by ID and store its title if it exists
            $tag_post = get_post($tag_post_id);
            if ($tag_post) {
                $titles[] = $tag_post->post_title;
            }
        }
    }

    // Create a string from the titles, separated by commas
    $titles_string = implode(', ', $titles);

    // Add this string to the top of the post content
    return $titles_string ? "<p>Related Posts: $titles_string</p>" . $content : $content;
}


<?php
/**
 * Plugin Name: Acton Custom Blocks
 * Description: Adds custom Gutenberg blocks to Actonverse.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
function my_custom_blocks_editor_assets() {
    wp_enqueue_script(
        'my-custom-blocks-js',
        plugins_url( 'build/index.js', __FILE__ ),
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
        '1.0.0',
        true // Load in footer.
    );
}
add_action( 'enqueue_block_editor_assets', 'my_custom_blocks_editor_assets' );

function my_custom_blocks_register_meta() {
    register_post_meta( 'post', '_your_custom_field_meta_key', array(
        'show_in_rest' => true,
        'type' => 'string',
        'single' => true,
    ) );
}
add_action( 'init', 'my_custom_blocks_register_meta' );

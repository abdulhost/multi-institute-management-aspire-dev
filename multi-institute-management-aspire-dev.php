<?php
/*
Plugin Name: Cpt-Educational-Aspiredev
Description: A custom plugin to manage educational centers, students, teachers, and classes.
Version: 1.0
Author: Your Name
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Register Custom Post Types for Educational Centers
function education_center_custom_post_types() {
    // Educational Center Custom Post Type
    register_post_type( 'education_center', array(
        'labels' => array(
            'name' => 'Educational Centers',
            'singular_name' => 'Educational Center',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Educational Center',
            'edit_item' => 'Edit Educational Center',
            'new_item' => 'New Educational Center',
            'view_item' => 'View Educational Center',
            'search_items' => 'Search Educational Centers',
            'not_found' => 'No Educational Centers found',
            'not_found_in_trash' => 'No Educational Centers found in Trash',
            'menu_name' => 'Educational Centers'
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-building',
    ));
}
add_action( 'init', 'education_center_custom_post_types' );

// Add Meta Boxes for Educational Center (Image, Logo, etc.)
function education_center_meta_boxes( $meta_boxes ) {
    $prefix = '';

    $meta_boxes[] = [
        'title'    => esc_html__( 'Educational Center Details', 'online-generator' ),
        'id'       => 'education_center_details',
        'post_types' => ['education_center'], // Ensure this is for 'education_center' post type
        'context'  => 'normal',
        'autosave' => true,
        'fields'   => [
            [
                'type' => 'image',
                'name' => esc_html__( 'School Logo', 'online-generator' ),
                'id'   => $prefix . 'school_logo', // Meta key for logo
            ],
        ],
    ];

    return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'education_center_meta_boxes' );

// Save Meta Data for Educational Center
function save_education_center_meta( $post_id ) {
    // Avoid autosave and nonce validation for security
    if ( !isset( $_POST['education_center_nonce'] ) ) {
        return;
    }

    if ( !wp_verify_nonce( $_POST['education_center_nonce'], 'save_education_center_details' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Saving school details
    if ( isset( $_POST['school_name'] ) ) {
        update_post_meta( $post_id, '_school_name', sanitize_text_field( $_POST['school_name'] ) );
    }
    if ( isset( $_POST['school_logo'] ) ) {
        update_post_meta( $post_id, '_school_logo', sanitize_text_field( $_POST['school_logo'] ) );
    }
    if ( isset( $_POST['headmaster_name'] ) ) {
        update_post_meta( $post_id, '_headmaster_name', sanitize_text_field( $_POST['headmaster_name'] ) );
    }
    if ( isset( $_POST['location'] ) ) {
        update_post_meta( $post_id, '_location', sanitize_text_field( $_POST['location'] ) );
    }
}
add_action( 'save_post', 'save_education_center_meta' );

?>

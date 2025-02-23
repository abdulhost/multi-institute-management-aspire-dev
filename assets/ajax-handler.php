<?php
// ajax-handler.php

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Include WordPress core functions
require_once(ABSPATH . 'wp-load.php');

// Handle the AJAX request
if (isset($_POST['action']) && $_POST['action'] === 'add_student') {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'add_student_nonce')) {
        wp_send_json_error('Invalid nonce.');
    }

    // Get form data
    $student_name = sanitize_text_field($_POST['student_name']);
    $student_email = sanitize_email($_POST['student_email']);
    $student_class = sanitize_text_field($_POST['student_class']);
    $educational_center_id = sanitize_text_field($_POST['educational_center_id']);
    $student_id = sanitize_text_field($_POST['student_id']); // Unique Student ID

    // Insert the student into the 'students' CPT
    $student_post = array(
        'post_title'   => $student_name,
        'post_type'    => 'students',
        'post_status'  => 'publish',
        'meta_input'   => array(
            'student_email'           => $student_email,
            'student_class'           => $student_class,
            'educational_center_id'   => $educational_center_id,
            'student_id'              => $student_id,
        ),
    );
    $student_id = wp_insert_post($student_post);

    // Provide feedback
    if ($student_id) {
        wp_send_json_success('Student added successfully.');
    } else {
        wp_send_json_error('Error adding student.');
    }
}
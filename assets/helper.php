<?php
// Helper function to get all classes (Post Object field)
function get_all_classes() {
    return get_posts(array(
        'post_type' => 'classes', // Replace with your CPT slug for classes
        'posts_per_page' => -1,
    ));
}

// Helper function to get sections for a specific class (Taxonomy field)
function get_sections_for_class($class_id) {
    return get_terms(array(
        'taxonomy' => 'sections', // Replace with your taxonomy slug for sections
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key' => 'class', // Meta key linking sections to classes
                'value' => $class_id,
                'compare' => '='
            )
        )
    ));
}
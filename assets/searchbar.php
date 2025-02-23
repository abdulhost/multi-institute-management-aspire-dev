<?php
/**
 * Search Bar Template
 *
 * @param string $table_id The ID of the table to search in.
 * @param string $search_input_id The ID of the search input field.
 */
$table_id = isset($args['table_id']) ? $args['table_id'] : 'students-table_2';
$search_input_id = isset($args['search_input_id']) ? $args['search_input_id'] : 'search_text_2';
?>

<!-- Search Form -->
<div class="form-group search-form">
    <div class="input-group">
        <span class="input-group-addon">Search</span>
        <input type="text" id="<?php echo esc_attr($search_input_id); ?>" placeholder="Search by Student Details" class="form-control" />
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

<script>
    // Bind the keyup event to the search input
    $('#<?php echo esc_js($search_input_id); ?>').keyup(function() {
        var searchText = $(this).val().toLowerCase(); // Convert input to lowercase

        // Loop through all table rows
        $('#<?php echo esc_js($table_id); ?> tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase(); // Get the text of the entire row

            // If the row contains the search text, show it; otherwise, hide it
            if (rowText.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
</script>
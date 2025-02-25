// jQuery(document).ready(function($) {
//     // Handle search
//     // $('#search-button').on('click', function() {
//     //     var student_id = $('#search-student-id').val();
//     //     var student_name = $('#search-student-name').val();
//     //     var class_name = $('#search-class').val();
//     //     var section = $('#search-section').val();

//     //     $.ajax({
//     //         url: attendance_ajax.ajax_url,
//     //         type: 'POST',
//     //         data: {
//     //             action: 'search_attendance',
//     //             nonce: attendance_ajax.nonce,
//     //             student_id: student_id,
//     //             student_name: student_name,
//     //             class_name: class_name,
//     //             section: section
//     //         },
//     //         success: function(response) {
//     //             $('#attendance-table tbody').html(response);
//     //         }
//     //     });
//     // });

//     // Handle bulk import button click
//     $('#bulk-import-button').on('click', function() {
//         // Redirect to bulk import page or show modal
//         alert('Bulk import functionality to be implemented.');
//     });

//     // Handle add attendance button click
//     $('#add-attendance-button').on('click', function() {
//         // Redirect to add attendance page or show modal
//         alert('Add attendance functionality to be implemented.');
//     });
// });


jQuery(document).ready(function($) {
    function loadAttendanceTable() {
        var classVal = $('#search-class').val() || '';
        var sectionVal = $('#search-section').val() || '';
        var monthVal = $('#search-month').val() || '';
        var yearVal = $('#search-month option:selected').data('year') || '';
        var studentIdVal = $('#search-student-id').val() || ''; // Add student ID
        var studentNameVal = $('#search-student-name').val() || ''; // Add student name

        $.ajax({
            url: attendance_ajax.ajax_url,
            method: 'POST',
            data: {
                class: classVal,
                section: sectionVal,
                month: monthVal,
                year: yearVal,
                student_id: studentIdVal, // Send student ID
                student_name: studentNameVal, // Send student name
                _wpnonce: attendance_ajax.nonce
            },
            headers: {
                'X-WP-Nonce': attendance_ajax.nonce
            },
            beforeSend: function() {
                $('#attendance-table-container').html('<p class="loading-message">Loading attendance data...</p>');
            },
            success: function(response) {
                if (response.success && response.data && response.data.html) {
                    $('#attendance-table-container').html(response.data.html);
                } else {
                    $('#attendance-table-container').html('<p>No data returned. Please adjust your filters.</p>');
                    console.log('Unexpected response:', response);
                }
            },
            error: function(xhr, status, error) {
                $('#attendance-table-container').html('<p>Error loading data: ' + error + '</p>');
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    // Load table on page load with default current month
    loadAttendanceTable();

    // Reload table on any filter change or button click
    $('#search-class, #search-section, #search-month, #search-student-id, #search-student-name').on('change', loadAttendanceTable);
    $('#search-button').on('click', loadAttendanceTable);
});
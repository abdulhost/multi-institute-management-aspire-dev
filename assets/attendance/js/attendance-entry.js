jQuery(document).ready(function($) {
    // Load students when class and section are selected
    $('#attendance-class, #attendance-section').on('change', function() {
        var classVal = $('#attendance-class').val();
        var sectionVal = $('#attendance-section').val();

        if (classVal && sectionVal) {
            $.ajax({
                url: attendance_entry_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'fetch_students_for_attendance',
                    class: classVal,
                    section: sectionVal,
                    nonce: attendance_entry_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var students = response.data;
                        var html = '<div class="bulk-actions">' +
                            '<label for="bulk-status">Bulk Update:</label>' +
                            '<select id="bulk-status" class="form-select">' +
                                '<option value="">Select Status</option>' +
                                '<option value="Present">Present</option>' +
                                '<option value="Late">Late</option>' +
                                '<option value="Absent">Absent</option>' +
                                '<option value="Full Day">Full Day</option>' +
                                '<option value="Holiday">Holiday</option>' +
                            '</select>' +
                            '<button id="apply-bulk-status" class="form-button">Apply</button>' +
                            '</div>' +
                            '<h3 class="form-label">Students</h3>' +
                            '<table><thead><tr>' +
                                '<th><input type="checkbox" id="select-all-students"></th>' +
                                '<th>Student ID</th>' +
                                '<th>Name</th>' +
                                '<th>Status</th>' +
                            '</tr></thead><tbody>';

                        if (students.length > 0) {
                            students.forEach(function(student) {
                                html += '<tr>' +
                                    '<td><input type="checkbox" class="student-checkbox" name="selected_students[]" value="' + student.student_id + '"></td>' +
                                    '<td>' + student.student_id + '</td>' +
                                    '<td>' + student.student_name + '<input type="hidden" name="student_names[' + student.student_id + ']" value="' + student.student_name + '"></td>' +
                                    '<td><select name="attendance[' + student.student_id + ']" class="student-status">' +
                                        '<option value="">-- Select Status --</option>' +
                                        '<option value="Present">Present</option>' +
                                        '<option value="Late">Late</option>' +
                                        '<option value="Absent">Absent</option>' +
                                        '<option value="Full Day">Full Day</option>' +
                                        '<option value="Holiday">Holiday</option>' +
                                    '</select></td>' +
                                '</tr>';
                            });
                        } else {
                            html += '<tr><td colspan="4">No students found for this class and section.</td></tr>';
                        }
                        html += '</tbody></table>';
                        $('#students-list').html(html);

                        // Select all checkbox functionality
                        $('#select-all-students').on('change', function() {
                            $('.student-checkbox').prop('checked', $(this).is(':checked'));
                        });

                        // Bulk status update
                        $('#apply-bulk-status').on('click', function() {
                            var bulkStatus = $('#bulk-status').val();
                            if (bulkStatus) {
                                $('.student-checkbox:checked').each(function() {
                                    var $select = $(this).closest('tr').find('.student-status');
                                    $select.val(bulkStatus);
                                    $(this).prop('checked', true); // Ensure checked after bulk update
                                });
                            }
                        });

                        // Track status changes to mark as selected
                        $('.student-status').on('change', function() {
                            var $checkbox = $(this).closest('tr').find('.student-checkbox');
                            $checkbox.prop('checked', true); // Check the student if status is changed
                        });
                    } else {
                        $('#students-list').html('<p class="form-message form-error">Error: ' + response.data + '</p>');
                    }
                },
                error: function() {
                    $('#students-list').html('<p class="form-message form-error">Error loading students.</p>');
                }
            });
        }
    });

    // Submit attendance form and reset on success
    $('#attendance-entry-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: attendance_entry_ajax.ajax_url,
            method: 'POST',
            data: formData + '&action=submit_attendance&nonce=' + attendance_entry_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    $('#attendance-message').html('<p class="form-message form-success">' + response.data + '</p>');
                    $('#attendance-entry-form')[0].reset();
                    $('#students-list').html('<p class="form-message">Select class and section to load students.</p>');
                    setTimeout(() => $('#attendance-message').html(''), 3000);
                } else {
                    $('#attendance-message').html('<p class="form-message form-error">Error: ' + response.data + '</p>');
                }
            },
            error: function() {
                $('#attendance-message').html('<p class="form-message form-error">Error submitting attendance.</p>');
            }
        });
    });
});
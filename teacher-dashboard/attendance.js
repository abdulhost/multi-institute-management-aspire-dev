jQuery(document).ready(function($) {
    // Class and section dropdown population
    var sectionsData = attendance_entry2_ajax.sections_data;

    // Populate sections when class changes
    $('#class_nameadd').on('change', function() {
        var selectedClass = $(this).val();
        var sectionSelect = $('#sectionadd');

        if (selectedClass && sectionsData[selectedClass]) {
            sectionSelect.html('<option value="">Select Section</option>');
            sectionsData[selectedClass].forEach(function(section) {
                sectionSelect.append('<option value="' + section + '">' + section + '</option>');
            });
            sectionSelect.prop('disabled', false).val(''); // Reset to force user selection
        } else {
            sectionSelect.html('<option value="">Select Class First</option>').prop('disabled', true);
        }
    });

    // Trigger initial change to set up sections
    $('#class_nameadd').trigger('change');

    // Function to fetch students (like loadAttendanceTable)
    function fetchStudents() {
        var classVal = $('#class_nameadd').val();
        var sectionVal = $('#sectionadd').val();

        if (classVal && sectionVal && sectionVal !== 'Select Class First') {
            

            $.ajax({
                url: attendance_entry2_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'fetch_students_for_attendance2',
                    class: classVal,
                    section: sectionVal,
                    nonce: attendance_entry2_ajax.nonce
                },
                beforeSend: function() {
                    $('#students-list').html('<p class="loading-message">Loading students...</p>');
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

                        // Bind events for bulk actions and checkboxes
                        $('#select-all-students').on('change', function() {
                            $('.student-checkbox').prop('checked', $(this).is(':checked'));
                        });

                        $('#apply-bulk-status').on('click', function() {
                            var bulkStatus = $('#bulk-status').val();
                            if (bulkStatus) {
                                $('.student-checkbox:checked').each(function() {
                                    var $select = $(this).closest('tr').find('.student-status');
                                    $select.val(bulkStatus);
                                    $(this).prop('checked', true);
                                });
                            }
                        });

                        $('.student-status').on('change', function() {
                            var $checkbox = $(this).closest('tr').find('.student-checkbox');
                            $checkbox.prop('checked', true);
                        });
                    } else {
                        $('#students-list').html('<p class="form-message form-error">Error: ' + response.data + '</p>');
                        if (attendance_entry2_ajax.debug) {
                            console.log('Fetch students error:', response.data);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $('#students-list').html('<p class="form-message form-error">Error loading students.</p>');
                    if (attendance_entry2_ajax.debug) {
                        console.log('AJAX error:', status, error);
                    }
                }
            });
        }
    }

    // Debounced fetch on change
    let timeoutId = null;
    $('#class_nameadd, #sectionadd').on('change', function() {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(fetchStudents, 300); // 300ms debounce
    });

    // Submit attendance form
    $('#attendance-entry-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: attendance_entry2_ajax.ajax_url,
            method: 'POST',
            data: formData + '&action=submit_attendance2&nonce=' + attendance_entry2_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    $('#attendance-message').html('<p class="form-message form-success">' + response.data + '</p>');
                    $('#attendance-entry-form')[0].reset();
                    $('#students-list').html('<p class="form-message">Select class and section to load students.</p>');
                    $('#sectionadd').prop('disabled', true);
                    setTimeout(() => $('#attendance-message').html(''), 3000);
                } else {
                    $('#attendance-message').html('<p class="form-message form-error">Error: ' + response.data + '</p>');
                    if (attendance_entry2_ajax.debug) {
                        console.log('Submit attendance error:', response.data);
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#attendance-message').html('<p class="form-message form-error">Error submitting attendance.</p>');
                if (attendance_entry2_ajax.debug) {
                    console.log('Submit AJAX error:', status, error);
                }
            }
        });
    });
});
<?php
document.addEventListener('DOMContentLoaded', function () {
    // Toggle form visibility
    const addStudentBtn = document.getElementById('add-student-btn');
    const addStudentForm = document.getElementById('add-student-form');
    if (addStudentBtn && addStudentForm) {
        addStudentBtn.addEventListener('click', function () {
            addStudentForm.style.display = addStudentForm.style.display === 'none' ? 'block' : 'none';
        });
    }

    // Handle form submission via AJAX
    const studentForm = document.getElementById('student-form');
    if (studentForm) {
        studentForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(studentForm);
            formData.append('action', 'add_student'); // AJAX action
            formData.append('nonce', '<?php echo wp_create_nonce('add_student_nonce'); ?>'); // Add nonce for security

            // Send AJAX request to the custom endpoint
            fetch('<?php echo plugin_dir_url(__FILE__); ?>ajax-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const responseMessage = document.getElementById('form-response-message');
                if (data.success) {
                    responseMessage.innerHTML = '<p class="success-message">' + data.data + '</p>';
                    // Optionally, refresh the student list or append the new student dynamically
                    location.reload(); // Reload the page to show the updated list
                } else {
                    responseMessage.innerHTML = '<p class="error-message">' + data.data + '</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
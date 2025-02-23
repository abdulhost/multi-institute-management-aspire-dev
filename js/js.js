// Use jQuery to make sure the DOM is fully loaded
// jQuery(document).ready(function($) {
//     // Event listener for the camera icon
//     $('.edit-logo-icon').on('click', function() {
//         // Trigger the file input when the camera icon is clicked
//         $('#logo-file-input').click();
//     });
// });


function previewLogo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const logoImage = document.getElementById('institute-logo-image');
            if (logoImage) {
                logoImage.src = e.target.result;
            } else {
                const logoContainer = document.querySelector('.institute-logo-container');
                logoContainer.innerHTML = `
                    <div class="institute-logo" style="position: relative;">
                        <img id="institute-logo-image" src="${e.target.result}" alt="Institute Logo" style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover;">
                        <span class="edit-logo-icon" onclick="document.getElementById('logo-file-input').click();">&#128247;</span>
                    </div>
                `;
            }
        };
        reader.readAsDataURL(file);
    }
}
// jQuery(document).ready(function ($) {
//     // Handle sidebar clicks
//     $('.sidebar li').click(function () {
//         // Remove active class from all sidebar items
//         $('.sidebar li').removeClass('active');
//         // Add active class to the clicked item
//         $(this).addClass('active');

//         // Hide all sections
//         $('.section').removeClass('active');
//         // Show the selected section
//         const sectionId = $(this).data('section');
//         $('#' + sectionId).addClass('active');
//     });
// });

// // Toggle the Add Student form when the button is clicked
// document.getElementById('add-student-btn').addEventListener('click', function() {
//     var form = document.getElementById('add-student-form');
//     if (form.style.display === 'none' || form.style.display === '') {
//         form.style.display = 'block';
//     } else {
//         form.style.display = 'none';
//     }
// });
jQuery(document).ready(function ($) {
    // Function to show the selected section
    function showSection(section) {
        $(".section").removeClass("active");
        $("#" + section).addClass("active");
        $(".sidebar li").removeClass("active");
        $("[data-section=" + section + "]").addClass("active");
    }

    // Handle sidebar clicks
    $('.sidebar li').click(function (e) {
        e.stopPropagation(); // Prevent event bubbling

        // Remove active class from all sidebar items
        $('.sidebar li').removeClass('active');
        // Add active class to the clicked item
        $(this).addClass('active');

        // Hide all sections
        $('.section').removeClass('active');
        // Show the selected section
        const sectionId = $(this).data('section');
        $('#' + sectionId).addClass('active');
    });

    // Handle submenu clicks
    $('.sidebar .submenu li').click(function (e) {
        e.stopPropagation(); // Prevent event bubbling

        const sectionId = $(this).data('section');
        window.location.hash = sectionId; // Update the URL hash
        showSection(sectionId); // Show the corresponding section

        // Keep the parent list item expanded
        $(this).closest('.has-submenu').addClass('active');
    });

    // Expand submenu on hover
    $('.sidebar .has-submenu').hover(function () {
        $(this).find('.submenu').addClass('expanded');
    }, function () {
        $(this).find('.submenu').removeClass('expanded');
    });

    // Show the section based on the URL hash
    var hash = window.location.hash.substring(1);
    if (hash) {
        showSection(hash);
    }
});

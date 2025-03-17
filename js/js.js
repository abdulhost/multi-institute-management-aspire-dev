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
// // });
// jQuery(document).ready(function ($) {
//     // Function to show the selected section
//     function showSection(section) {
//         $(".section").removeClass("active");
//         $("#" + section).addClass("active");
//         $(".sidebar li").removeClass("active");
//         $("[data-section=" + section + "]").addClass("active");
//     }

//     // Handle sidebar clicks
//     $('.sidebar li').click(function (e) {
//         e.stopPropagation(); // Prevent event bubbling

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

//     // Handle submenu clicks
//     $('.sidebar .submenu li').click(function (e) {
//         e.stopPropagation(); // Prevent event bubbling

//         const sectionId = $(this).data('section');
//         window.location.hash = sectionId; // Update the URL hash
//         showSection(sectionId); // Show the corresponding section

//         // Keep the parent list item expanded
//         $(this).closest('.has-submenu').addClass('active');
//     });

//     // Expand submenu on hover
//     $('.sidebar .has-submenu').hover(function () {
//         $(this).find('.submenu').addClass('expanded');
//     }, function () {
//         $(this).find('.submenu').removeClass('expanded');
//     });

//     // Show the section based on the URL hash
//     var hash = window.location.hash.substring(1);
//     if (hash) {
//         showSection(hash);
//     }
// });

// jQuery(document).ready(function ($) {
//     // Function to show the selected section
//     function showSection(section) {
//         $(".section").removeClass("active");
//         $("#" + section).addClass("active");
//         $(".sidebar li").removeClass("active");
//         $("[data-section=" + section + "]").addClass("active");
//     }

//     // Handle sidebar clicks
//     $('.sidebar li').click(function (e) {
//         e.stopPropagation(); // Prevent event bubbling

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

//     // Handle submenu clicks
//     $('.sidebar .submenu li').click(function (e) {
//         e.stopPropagation(); // Prevent event bubbling

//         const sectionId = $(this).data('section');
//         window.location.hash = sectionId; // Update the URL hash
//         showSection(sectionId); // Show the corresponding section

//         // Keep the parent list item expanded
//         $(this).closest('.has-submenu').addClass('active');
//     });

//     $('.sidebar .has-submenu > a').click(function (e) {
//         e.preventDefault();
//         $(this).siblings('.submenu').toggleClass('expanded');
//     });

//     // Show the section based on the URL hash
//     var hash = window.location.hash.substring(1);
//     if (hash) {
//         showSection(hash);
//     }
// });

const toggleButton = document.getElementById('toggle-btn');
const sidebar = document.getElementById('sidebar');

// Variables to store the state of open submenus and rotated buttons
let savedSubMenus = [];
let savedButtons = [];

function toggleSidebar() {
  const isClosing = sidebar.classList.contains('close');

  if (!isClosing) {
    // Before closing, save the state of open submenus and buttons
    savedSubMenus = Array.from(sidebar.getElementsByClassName('show'));
    savedButtons = Array.from(sidebar.getElementsByClassName('rotate')).filter(btn => btn.classList.contains('dropdown-btn'));
    closeAllSubMenus(); // Close all submenus when sidebar closes
  }

  sidebar.classList.toggle('close');
  toggleButton.classList.toggle('rotate');

  if (isClosing && savedSubMenus.length > 0) {
    // When reopening, restore the saved state
    restoreSavedSubMenus();
  }
}

function toggleSubMenu(button) {
  const submenu = button.nextElementSibling;

  if (!submenu.classList.contains('show')) {
    closeAllSubMenus();
  }

  submenu.classList.toggle('show');
  button.classList.toggle('rotate');

  if (sidebar.classList.contains('close')) {
    sidebar.classList.toggle('close');
    toggleButton.classList.toggle('rotate');
  }
}

function closeAllSubMenus() {
  Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
    ul.classList.remove('show');
    ul.previousElementSibling.classList.remove('rotate');
  });
}

function restoreSavedSubMenus() {
  savedSubMenus.forEach(submenu => {
    submenu.classList.add('show');
  });
  savedButtons.forEach(button => {
    button.classList.add('rotate');
  });
}

// Optional: Initialize active submenus on page load (if needed)
document.addEventListener('DOMContentLoaded', () => {
  const activeLis = sidebar.querySelectorAll('li.active');
  activeLis.forEach(li => {
    const submenu = li.querySelector('.sub-menu');
    const button = li.querySelector('.dropdown-btn');
    if (submenu && button) {
      submenu.classList.add('show');
      button.classList.add('rotate');
    }
  });
});

jQuery(document).ready(function($) {
  $('#exam-search').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      $('#exams-table tr').filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
  });

  window.addSubjectRow = function() {
      var table = document.getElementById('subjects-table').getElementsByTagName('tbody')[0];
      var row = table.insertRow();
      row.innerHTML = `
          <td><input type="text" name="subjects[]" class="form-control" required></td>
          <td><input type="number" name="max_marks[]" class="form-control" value="100" step="0.01" min="0" required></td>
          <td><button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
      `;
  };
});



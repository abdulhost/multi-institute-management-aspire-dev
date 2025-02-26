
// const toggleButton = document.getElementById('toggle-btn');
// const sidebar = document.getElementById('sidebar');

// // Variables to store the state of open submenus and rotated buttons
// let savedSubMenus = [];
// let savedButtons = [];

// function toggleSidebar() {
//   const isClosing = sidebar.classList.contains('close');

//   if (!isClosing) {
//     // Before closing, save the state of open submenus and buttons
//     savedSubMenus = Array.from(sidebar.getElementsByClassName('show'));
//     savedButtons = Array.from(sidebar.getElementsByClassName('rotate')).filter(btn => btn.classList.contains('dropdown-btn'));
//     closeAllSubMenus(); // Close all submenus when sidebar closes
//   }

//   sidebar.classList.toggle('close');
//   toggleButton.classList.toggle('rotate');

//   if (isClosing && savedSubMenus.length > 0) {
//     // When reopening, restore the saved state
//     restoreSavedSubMenus();
//   }
// }

// function toggleSubMenu(button) {
//   const submenu = button.nextElementSibling;

//   if (!submenu.classList.contains('show')) {
//     closeAllSubMenus();
//   }

//   submenu.classList.toggle('show');
//   button.classList.toggle('rotate');

//   if (sidebar.classList.contains('close')) {
//     sidebar.classList.toggle('close');
//     toggleButton.classList.toggle('rotate');
//   }
// }

// function closeAllSubMenus() {
//   Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
//     ul.classList.remove('show');
//     ul.previousElementSibling.classList.remove('rotate');
//   });
// }

// function restoreSavedSubMenus() {
//   savedSubMenus.forEach(submenu => {
//     submenu.classList.add('show');
//   });
//   savedButtons.forEach(button => {
//     button.classList.add('rotate');
//   });
// }

// // Optional: Initialize active submenus on page load (if needed)
// document.addEventListener('DOMContentLoaded', () => {
//   const activeLis = sidebar.querySelectorAll('li.active');
//   activeLis.forEach(li => {
//     const submenu = li.querySelector('.sub-menu');
//     const button = li.querySelector('.dropdown-btn');
//     if (submenu && button) {
//       submenu.classList.add('show');
//       button.classList.add('rotate');
//     }
//   });
// });
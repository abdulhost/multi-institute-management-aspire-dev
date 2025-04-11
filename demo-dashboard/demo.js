// demo.js (version 1.0.11)
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('close');
    } else {
        console.error('Sidebar element not found');
    }
}

function toggleSubMenu(button) {
    var submenu = button.nextElementSibling;
    var allSubmenus = document.querySelectorAll('.sub-menu');
    var allButtons = document.querySelectorAll('.dropdown-btn');

    allSubmenus.forEach(function(menu) {
        if (menu !== submenu) {
            menu.classList.remove('show');
        }
    });
    allButtons.forEach(function(btn) {
        if (btn !== button) {
            btn.classList.remove('rotate');
        }
    });

    if (submenu) {
        submenu.classList.toggle('show');
        button.classList.toggle('rotate');
    } else {
        console.error('Submenu element not found');
    }
}
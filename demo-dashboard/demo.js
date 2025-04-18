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


// Export Functions
function exportData(format, type) {
    showLoader();
    const data = window[`${type}Data`] || [];
    const templates = {
        students: {
            title: 'Students Report',
            columns: ['ID', 'Name', 'Class', 'Grade'],
            data: data.map(item => [item.id, item.name, item.class, item.grade]),
        },
        fees: {
            title: 'Fees Report',
            columns: ['Student ID', 'Name', 'Amount', 'Due Date'],
            data: data.map(item => [item.student_id, item.name, item.amount, item.due_date]),
        },
        teachers: {
            title: 'Teachers Report',
            columns: ['ID', 'Name', 'Subject', 'Contact'],
            data: data.map(item => [item.id, item.name, item.subject, item.contact]),
        },
    };

    const template = templates[type] || templates.students;

    if (format === 'pdf') {
        exportPDF(template);
    } else if (format === 'csv') {
        exportCSV(template);
    } else if (format === 'excel') {
        exportExcel(template);
    }

    hideLoader();
}

function exportPDF(template) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Add Logo
    const logo = new Image();
    logo.src = '/wp-content/plugins/institute-management/logo instituto.jpg';
    doc.addImage(logo, 'JPEG', 10, 10, 30, 30);

    // Add Title and Date
    doc.setFontSize(18);
    doc.text(template.title, 50, 20);
    doc.setFontSize(12);
    doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 50, 30);

    // Add Table
    doc.autoTable({
        startY: 50,
        head: [template.columns],
        body: template.data,
        theme: 'striped',
        headStyles: { fillColor: [26, 115, 232] },
    });

    doc.save(`${template.title.replace(' ', '_')}.pdf`);
}

function exportCSV(template) {
    const csv = Papa.unparse({
        fields: template.columns,
        data: template.data,
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${template.title.replace(' ', '_')}.csv`;
    link.click();
}

function exportExcel(template) {
    const ws = XLSX.utils.json_to_sheet(template.data.map(item => {
        const obj = {};
        template.columns.forEach((col, i) => {
            obj[col] = item[i];
        });
        return obj;
    }), { header: template.columns });
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, template.title);
    XLSX.write(wb, `${template.title.replace(' ', '_')}.xlsx`);
}


// Loader Functions
function showLoader() {
    document.getElementById('loader').style.display = 'flex';
}

function hideLoader() {
    document.getElementById('loader').style.display = 'none';
}

// Search Bar
document.getElementById('search-input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#students-table tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Modal Functions
function openModal(modalId, id) {
    const modal = document.getElementById(`${modalId}-modal`);
    modal.style.display = 'flex';
    if (modalId === 'edit-student' && id) {
        const student = studentsData.find(s => s.id === id);
        document.getElementById('student-id').value = student.id;
        document.getElementById('student-name').value = student.name;
        document.getElementById('student-class').value = student.class;
        document.getElementById('student-grade').value = student.grade;
    }
}

function closeModal(modalId) {
    document.getElementById(`${modalId}-modal`).style.display = 'none';
}

// Form Submission (Sample)
document.getElementById('edit-student-form').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoader();
    // Simulate AJAX save
    setTimeout(() => {
        alert('Student updated successfully!');
        closeModal('edit-student');
        hideLoader();
    }, 1000);
});

// Delete Student (Sample)
function deleteStudent(id) {
    if (confirm('Are you sure you want to delete this student?')) {
        showLoader();
        // Simulate AJAX delete
        setTimeout(() => {
            alert('Student deleted successfully!');
            hideLoader();
        }, 1000);
    }
}

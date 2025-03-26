function generate_reusable_pdf(options) {
    const defaults = {
        student_id: 'Unknown',
        student_name: 'Unknown',
        title: 'Document',
        table_data: [],
        details: [],
        filename_prefix: 'document',
        orientation: 'portrait',
        institute_name: 'Unknown Institute',
        institute_logo: ''
    };
    const opts = { ...defaults, ...options };

    const student_id = opts.student_id;
    const student_name = opts.student_name;
    const title = opts.title;
    const table_data = opts.table_data;
    const details = opts.details;
    const filename_prefix = opts.filename_prefix;
    const orientation = opts.orientation;
    const institute_name = opts.institute_name;
    const institute_logo = opts.institute_logo;
    const border_color = [26, 43, 95]; // #1a2b5f

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation });
    const pageWidth = doc.internal.pageSize.width;
    const pageHeight = doc.internal.pageSize.height;
    const margin = 10;

    doc.setDrawColor(...border_color);
    doc.setLineWidth(1);
    doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);

    if (institute_logo) {
        try {
            doc.addImage(institute_logo, 'PNG', (pageWidth - 24) / 2, 15, 24, 24);
        } catch (e) {
            console.log('Logo loading failed:', e);
            doc.setFontSize(10);
            doc.text('No logo available', pageWidth / 2, 20, { align: 'center' });
        }
    }
    doc.setFontSize(18);
    doc.setTextColor(...border_color);
    doc.text(institute_name.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
    doc.setFontSize(12);
    doc.setTextColor(102);
    doc.text(title, pageWidth / 2, 55, { align: 'center' });
    doc.setDrawColor(...border_color);
    doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

    let y = 70;
    const base_details = [
        ['Student Name', student_name],
        ['Student ID', student_id],
        ['Date', new Date().toLocaleDateString()]
    ];
    const all_details = [...base_details, ...details];
    all_details.forEach(([label, value]) => {
        doc.setFillColor(245, 245, 245);
        doc.rect(margin + 5, y, 50, 6, 'F');
        doc.setTextColor(...border_color);
        doc.setFont('helvetica', 'bold');
        doc.text(label, margin + 7, y + 4);
        doc.setTextColor(51);
        doc.setFont('helvetica', 'normal');
        doc.text(value, margin + 60, y + 4);
        y += 6;
    });

    if (table_data.length > 0 && typeof doc.autoTable === 'function') {
        doc.autoTable({
            startY: y + 10,
            head: [table_data[0]],
            body: table_data.slice(1),
            theme: 'striped',
            styles: {
                fontSize: 11,
                cellPadding: 2,
                overflow: 'linebreak',
                halign: 'center',
                textColor: [51, 51, 51]
            },
            headStyles: {
                fillColor: border_color,
                textColor: [255, 255, 255],
                fontStyle: 'bold'
            },
            alternateRowStyles: { fillColor: [249, 249, 249] },
            tableLineColor: [204, 204, 204],
            tableLineWidth: 0.1
        });

        const finalY = doc.lastAutoTable.finalY || y + 10;
        doc.setFontSize(9);
        doc.setTextColor(102);
        doc.text(`This is an Online Generated ${title} issued by ${institute_name}`, pageWidth / 2, finalY + 20, { align: 'center' });
        doc.text(`Generated on ${new Date().toISOString().slice(0, 10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
        doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
        doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
        doc.text('Managed by Instituto Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

        doc.save(`${filename_prefix}_${student_id}_${new Date().toISOString().slice(0,10)}.pdf`);
    } else {
        console.error('jsPDF autoTable plugin not loaded or no table data');
        alert('PDF generation failed: autoTable plugin or data missing');
    }
}
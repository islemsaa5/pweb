/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */

document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('table');
    const pageHeader = document.querySelector('.page-header');
    
    const currentPage = window.location.pathname.split('/').pop();

    if (tables.length > 0 && pageHeader && !currentPage.includes('dashboard')) {

        const exportBtn = document.createElement('button');
        exportBtn.className = 'btn-export';
        exportBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Télécharger PDF Officiel';
        exportBtn.style.marginLeft = 'auto';

        const textWrapper = document.createElement('div');
        while (pageHeader.firstChild) {
            textWrapper.appendChild(pageHeader.firstChild);
        }

        pageHeader.style.display = 'flex';
        pageHeader.style.justifyContent = 'space-between';
        pageHeader.style.alignItems = 'center';
        pageHeader.style.gap = '20px';
        
        pageHeader.appendChild(textWrapper);
        pageHeader.appendChild(exportBtn);
        
        exportBtn.addEventListener('click', function() {
            exportToPDF();
        });
    }
});

/**
 * Main export function
 */
function exportToPDF() {
    // Load the logo first
    const logoImg = new Image();
    logoImg.src = 'assets/img/logo.png';
    
    logoImg.onload = function() {
        generatePDF(logoImg);
    };
    logoImg.onerror = function() {
        // Fallback if logo fails to load
        generatePDF(null);
    };
}

function generatePDF(logoImg) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'pt', 'a4'); // Landscape for better table fit

    let pageTitle = document.querySelector('.page-header h1')?.innerText || 'Document Officiel';
    const subTitle = document.querySelector('.page-header p')?.innerText || '';
    const date = new Date().toLocaleString('fr-FR');

    if (pageTitle.toLowerCase().includes('saisie des notes')) {
        pageTitle = 'Procès-Verbal des Notes (PV)';
    }

    // Header Setup
    if (logoImg) {
        doc.addImage(logoImg, 'PNG', 40, 25, 60, 60);
        doc.addImage(logoImg, 'PNG', 740, 25, 60, 60); // Right side logo
    }

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);
    doc.text('République Algérienne Démocratique et Populaire', 420, 35, { align: 'center' });
    doc.text('Ministère de l\'Enseignement Supérieur et de la Recherche Scientifique', 420, 50, { align: 'center' });

    doc.setFontSize(10);
    doc.text('Université des Sciences et de la Technologie Houari Boumediene', 420, 65, { align: 'center' });
    doc.text('Faculté d\'Informatique', 420, 80, { align: 'center' });

    // Draw line
    doc.setLineWidth(1);
    doc.line(40, 95, 800, 95);

    // Title
    doc.setFontSize(16);
    doc.setTextColor(44, 62, 128); // Primary color
    doc.text(pageTitle.toUpperCase(), 420, 120, { align: 'center' });
    
    if (subTitle) {
        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(100, 100, 100);
        doc.text(subTitle, 420, 135, { align: 'center' });
    }
    
    let currentY = 160;

    const tables = document.querySelectorAll('table');
    tables.forEach((table, index) => {

        let tableTitle = '';
        const container = table.closest('.table-container');
        const containerHeader = container?.querySelector('.table-header h3');
        const siblingHeader = container?.previousElementSibling;
        
        if (containerHeader) {
            tableTitle = containerHeader.innerText;
        } else if (siblingHeader && (siblingHeader.tagName === 'H3' || siblingHeader.tagName === 'H2')) {
            tableTitle = siblingHeader.innerText;
        }

        if (tableTitle) {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(12);
            doc.setTextColor(44, 62, 128);
            doc.text(tableTitle, 40, currentY);
            currentY += 15;
        }

        const headers = Array.from(table.querySelectorAll('thead th'));
        const columnsToSkip = headers
            .map((th, i) => (['actions', 'delete', 'modifier'].includes(th.innerText.toLowerCase().trim()) ? i : -1))
            .filter(i => i !== -1);

        doc.autoTable({
            html: table,
            startY: currentY,
            theme: 'grid',
            styles: {
                fontSize: 9,
                cellPadding: 6,
                font: 'helvetica',
                textColor: [0, 0, 0],
                lineColor: [100, 100, 100],
                lineWidth: 0.5,
                halign: 'center',
                valign: 'middle'
            },
            headStyles: {
                fillColor: [220, 225, 240], // Light blueish gray
                textColor: [0, 0, 0],
                fontStyle: 'bold',
                lineWidth: 0.5,
                lineColor: [100, 100, 100]
            },
            alternateRowStyles: {
                fillColor: [249, 249, 255]
            },
            margin: { left: 40, right: 40 },
            columns: headers.length > 0 ? Array.from({length: headers.length}, (_, i) => i).filter(i => !columnsToSkip.includes(i)) : null,
            didPageHtmlData: true,
            didDrawPage: function(data) {
                doc.setFontSize(8);
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(150);
                
                const footerY = doc.internal.pageSize.height - 20;
                doc.text(`Généré le: ${date}`, 40, footerY);
                doc.text('Page ' + doc.internal.getNumberOfPages(), 420, footerY, { align: 'center' });
                doc.text('USTHB Scolarité - Document Officiel', 800, footerY, { align: 'right' });
                
                // Top border for footer
                doc.setLineWidth(0.5);
                doc.line(40, footerY - 10, 800, footerY - 10);
            }
        });
        
        currentY = doc.lastAutoTable.finalY + 30;
    });

    const filename = pageTitle.toLowerCase().replace(/[^a-z0-9]/g, '_') + '.pdf';
    doc.save(filename);
}


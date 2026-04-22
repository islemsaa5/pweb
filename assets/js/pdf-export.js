/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */
/**
 * Projet: Gestion de ScolaritÃ© USTHB
 * Ã‰quipe:
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
        exportBtn.innerHTML = '<i class="fas fa-file-pdf"></i> TÃ©lÃ©charger PDF';
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
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4');

    const pageTitle = document.querySelector('.page-header h1')?.innerText || 'Document';
    const subTitle = document.querySelector('.page-header p')?.innerText || '';
    const date = new Date().toLocaleDateString('fr-FR');

    doc.setFontSize(18);
    doc.setTextColor(44, 62, 128);
    doc.text('USTHB - ScolaritÃ©', 40, 40);
    
    doc.setFontSize(14);
    doc.setTextColor(33, 33, 33);
    doc.text(pageTitle, 40, 70);
    
    if (subTitle) {
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(subTitle, 40, 85);
    }
    
    doc.setFontSize(9);
    doc.text(`GÃ©nÃ©rÃ© le: ${date}`, 480, 40);
    
    let currentY = 110;

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
            doc.setFontSize(12);
            doc.setTextColor(44, 62, 128);
            doc.text(tableTitle, 40, currentY);
            currentY += 15;
        }

        const headers = Array.from(table.querySelectorAll('thead th'));
        const columnsToSkip = headers
            .map((th, i) => (['actions', 'Ã©tat', 'etat', 'delete', 'modifier'].includes(th.innerText.toLowerCase().trim()) ? i : -1))
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
                lineColor: [0, 0, 0],
                lineWidth: 0.8,
            },
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold',
                lineWidth: 0.8,
                lineColor: [0, 0, 0]
            },
            alternateRowStyles: {
                fillColor: [255, 255, 255]
            },
            margin: { left: 40, right: 40 },
            columns: headers.length > 0 ? Array.from({length: headers.length}, (_, i) => i).filter(i => !columnsToSkip.includes(i)) : null,
            didPageHtmlData: true,
            didDrawPage: function(data) {

                doc.setFontSize(8);
                doc.setTextColor(150);
                doc.text('Page ' + doc.internal.getNumberOfPages(), data.settings.margin.left, doc.internal.pageSize.height - 20);
                doc.text('USTHB ScolaritÃ© - Document Officiel', 400, doc.internal.pageSize.height - 20);
            }
        });
        
        currentY = doc.lastAutoTable.finalY + 40;
    });

    const filename = pageTitle.toLowerCase().replace(/\s+/g, '_') + '.pdf';
    doc.save(filename);
}


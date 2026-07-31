// Citation functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set current date
    const currentDate = new Date().toLocaleDateString('es-ES');
    document.getElementById('current-date').textContent = currentDate;

    // Copy citation functionality
    const copyButtons = document.querySelectorAll('.citation-copy');
    const copyStatus = document.getElementById('citation-copy-status');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const format = this.getAttribute('data-format');
            const citationText = this.previousElementSibling;
            let textToCopy = '';

            if (format === 'bibtex') {
                textToCopy = citationText.querySelector('pre').textContent;
            } else {
                textToCopy = citationText.textContent.trim();
            }

            navigator.clipboard.writeText(textToCopy).then(() => {
                // Show success feedback
                const originalHTML = this.innerHTML;
                this.textContent = '✅ Copiado';
                this.classList.add('is-copied');
                if (copyStatus) {
                    copyStatus.textContent = '✅ Copiado';
                }

                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.classList.remove('is-copied');
                }, 2000);
            }).catch(err => {
                console.error('Error copying text: ', err);
                alert('Error al copiar la cita');
            });
        });
    });

    // Export all citations
    document.getElementById('export-all-citations').addEventListener('click', function() {
        const citations = [];
        const citationFormats = document.querySelectorAll('.citation-format');
        
        citationFormats.forEach(format => {
            const title = format.querySelector('h3').textContent;
            const text = format.querySelector('.citation-text');
            let citationText = '';
            
            if (title === 'BibTeX') {
                citationText = text.querySelector('pre').textContent;
            } else {
                citationText = text.textContent.trim();
            }
            
            citations.push(`${title}:\n${citationText}\n`);
        });

        const allCitations = citations.join('\n---\n\n');
        
        // Create and download file
        const blob = new Blob([allCitations], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'citas-articulo-gonzalez-perez-2025.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });

    // Download RIS file
    document.getElementById('download-ris').addEventListener('click', function() {
        const risContent = `TY  - JOUR
TI  - La naturaleza del ser en la filosofía contemporánea
AU  - González, María
AU  - Pérez, Juan
T2  - LOGO ET SPES
PY  - 2025
VL  - 12
IS  - 2
SP  - 15
EP  - 32
DO  - Próximamente
UR  - https://logo-et-spes.cenfiss.net/single-article
ER  - `;

        const blob = new Blob([risContent], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'gonzalez-perez-2025-naturaleza-ser.ris';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
});

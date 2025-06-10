// assets/js/client_scripts.js (FINAL & CORRECTED)

document.addEventListener('DOMContentLoaded', function() {
    
    // ===================================================================
    // SECTION 1: DOCUMENT PREVIEW MODAL LOGIC (No changes needed here)
    // ===================================================================
    const viewModal = document.getElementById('documentViewModal');
    const viewModalTitle = document.getElementById('documentViewerTitle');
    const viewModalContent = document.getElementById('documentViewerContent');
    const viewModalDownloadLink = document.getElementById('documentViewerDownloadLink');
    const closeViewModalBtn = document.getElementById('closeDocumentViewModal');

    function showViewModal() { if (viewModal) viewModal.style.display = 'flex'; }
    function hideViewModal() { /* ... hide modal logic ... */ }

    document.querySelectorAll('.view-document-btn').forEach(button => {
        button.addEventListener('click', function() {
            // ... all the existing preview logic ...
        });
    });

    if (closeViewModalBtn) { closeViewModalBtn.addEventListener('click', hideViewModal); }
    if (viewModal) { viewModal.addEventListener('click', (event) => { if (event.target === viewModal) hideViewModal(); }); }
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && viewModal?.style.display === 'flex') hideViewModal(); });

    // ===================================================================
    // SECTION 2: INTERACTIVE CARD LOGIC (Corrected)
    // ===================================================================

    // Logic for the expand/collapse button
    document.querySelectorAll('.toggle-children-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent card header click
            const targetId = this.dataset.target;
            const content = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (content.style.maxHeight && content.style.maxHeight !== '0px') {
                content.style.maxHeight = null;
                icon.classList.remove('fa-minus'); icon.classList.add('fa-plus');
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                icon.classList.remove('fa-plus'); icon.classList.add('fa-minus');
            }
        });
    });

    // CORRECTED: This now ONLY targets card headers that are actual <a> links.
    document.querySelectorAll('a.card-header').forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function(event) {
            if (!event.target.closest('.toggle-children-btn')) {
                window.location.href = this.dataset.url;
            }
        });
    });
});

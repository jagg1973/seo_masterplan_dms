// assets/js/client_scripts.js (FINAL & CORRECTED)

document.addEventListener('DOMContentLoaded', function() {
    
    // ===================================================================
    // SECTION 1: DOCUMENT PREVIEW MODAL LOGIC
    // ===================================================================
    const viewModal = document.getElementById('documentViewModal');
    const viewModalTitle = document.getElementById('documentViewerTitle');
    const viewModalContent = document.getElementById('documentViewerContent');
    const viewModalDownloadLink = document.getElementById('documentViewerDownloadLink');
    const closeViewModalBtn = document.getElementById('closeDocumentViewModal');
    let activePreviewButton = null; // To keep track of which button opened the modal

    function showViewModal() { if (viewModal) viewModal.style.display = 'flex'; }
    
    function hideViewModal() {
        if (viewModal) {
            viewModal.style.display = 'none';
            if (viewModalContent) {
                // Clear previous content to prevent showing old data while new one loads
                // and to free up resources (e.g., for iframes)
                viewModalContent.innerHTML = '';
            }
            if (viewModalTitle) {
                viewModalTitle.textContent = 'Document Preview'; // Reset title
            }
            if (viewModalDownloadLink) {
                viewModalDownloadLink.href = '#'; // Reset download link
                viewModalDownloadLink.style.display = 'none'; // Hide by default
            }
            activePreviewButton = null;
        }
    }

    // Consolidated listener for all preview buttons
    document.querySelectorAll('.view-document-btn, .hierarchy-view-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default action if it's an <a> tag
            event.stopPropagation(); // Prevent event bubbling

            // If a modal is already open from a different button, close it first
            if (viewModal.style.display === 'flex' && activePreviewButton !== this) {
                hideViewModal();
            }
            activePreviewButton = this;

            const filePath = this.dataset.filepath;
            const fileName = this.dataset.filename || 'Document';
            const fileExt = (this.dataset.fileext || '').toLowerCase();

            if (!filePath) {
                console.error("Preview Error: File path is missing from button's data attributes.");
                viewModalContent.innerHTML = '<div class="preview-not-available"><strong>Error:</strong> File path not provided.</div>';
                showViewModal();
                return;
            }

            if (viewModalTitle) viewModalTitle.textContent = fileName;
            if (viewModalContent) viewModalContent.innerHTML = '<div class="preview-loading"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading preview...</p></div>'; // Loading indicator

            if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(fileExt)) {
                viewModalContent.innerHTML = `<img src="${filePath}" alt="${fileName}" style="max-width: 100%; max-height: 100%; object-fit: contain; display: block; margin: auto;">`;
            } else if (fileExt === 'pdf') {
                viewModalContent.innerHTML = `<iframe src="${filePath}" style="width: 100%; height: 100%; border: none;" title="${fileName}"></iframe>`;
            } else {
                viewModalContent.innerHTML = `<div class="preview-not-available"><i class="fas fa-file-alt fa-3x mb-3"></i><strong>Preview Not Available</strong><p>Direct preview for .${fileExt.toUpperCase()} files is not supported.<br>You can download the file to view it.</p></div>`;
            }

            if (viewModalDownloadLink) {
                viewModalDownloadLink.href = filePath;
                viewModalDownloadLink.setAttribute('download', fileName); // Suggest original filename for download
                viewModalDownloadLink.style.display = 'inline-block';
            }
            showViewModal();
        });
    });

    if (closeViewModalBtn) { closeViewModalBtn.addEventListener('click', hideViewModal); }
    if (viewModal) { viewModal.addEventListener('click', (event) => { if (event.target === viewModal) hideViewModal(); }); }
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && viewModal?.style.display === 'flex') hideViewModal(); });

    // ===================================================================
    // SECTION 2: DOCUMENT CARD INTERACTIVITY
    // ===================================================================

    // Logic for the expand/collapse button in document cards
    document.querySelectorAll('.document-card-toggle').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); 
            const targetSelector = this.dataset.target;
            const content = document.querySelector(targetSelector);

            if (content) {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                if (isExpanded) {
                    // Collapse
                    content.style.maxHeight = null;
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    // Expand
                    content.style.maxHeight = content.scrollHeight + "px";
                    this.setAttribute('aria-expanded', 'true');
                }
            }
        });
    });

    // Ensure clicks on card headers (if they become links for non-container items) don't trigger collapse if a button inside was clicked.
    // This example assumes card headers are not directly clickable for navigation for now,
    // as navigation is handled by links within the title or child items.
    // If card headers themselves become <a href="..."> tags for direct links, 
    // you might need to add event.stopPropagation() to the toggle button's click listener
    // and ensure the header link click doesn't also trigger the toggle.

    // Re-initialize preview buttons if they are dynamically added or if the selector needs to be more specific
    // The '.hierarchy-view-btn' class is used in the new card HTML for preview buttons.
    // The existing modal logic in SECTION 1 should pick these up if the class matches.
    // If you used a different class, ensure it's added to the querySelectorAll in SECTION 1.
    // For example, if you used '.document-child-preview-btn':
    // document.querySelectorAll('.view-document-btn, .hierarchy-view-btn, .document-child-preview-btn').forEach(button => { ... });
    // For now, '.hierarchy-view-btn' is consistent.

    // Handle clicks on document card titles that are links (for non-container direct items)
    document.querySelectorAll('a.document-card-title-link').forEach(link => {
        link.addEventListener('click', function(event) {
            // Allow default link behavior
            // If the card itself was clickable and also had a toggle, you'd need event.stopPropagation() here.
            // But since only the title is a link for direct items, this should be fine.
        });
    });

    // Handle clicks on child item titles that are links
    document.querySelectorAll('a.document-child-title-link').forEach(link => {
        link.addEventListener('click', function(event) {
            // If the link is not for a document to be previewed/downloaded (e.g., an external link)
            // and its parent .document-child-item also has action buttons,
            // you might want to stop propagation if clicking the title shouldn't also trigger actions.
            // For now, assume direct navigation is the primary action for these title links.
            if (!this.closest('.document-child-item').querySelector('.document-child-actions')) {
                 // This is a link without sibling action buttons, let it navigate.
            } else {
                // This link has sibling action buttons.
                // If clicking the title should NOT also trigger a preview/download if those buttons exist,
                // you might add logic here. For now, default behavior is fine.
            }
        });
    });
});

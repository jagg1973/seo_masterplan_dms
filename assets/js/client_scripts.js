document.addEventListener('DOMContentLoaded', function() {
    const viewModal = document.getElementById('documentViewModal');
    const viewModalTitle = document.getElementById('documentViewerTitle');
    const viewModalContent = document.getElementById('documentViewerContent');
    const viewModalDownloadLink = document.getElementById('documentViewerDownloadLink');
    const closeViewModalBtn = document.getElementById('closeDocumentViewModal');

    // Function to show the modal
    function showViewModal() {
        if (viewModal) viewModal.style.display = 'flex';
    }

    // Function to hide the modal
    function hideViewModal() {
        if (viewModal) viewModal.style.display = 'none';
        if (viewModalContent) viewModalContent.innerHTML = ''; // Clear previous content
        if (viewModalTitle) viewModalTitle.textContent = 'Document Preview'; // Reset title
        if (viewModalDownloadLink) viewModalDownloadLink.href = '#'; // Reset download link
    }

    // Attach event listeners to all "View" buttons
    document.querySelectorAll('.view-document-btn').forEach(button => {
        button.addEventListener('click', function() {
            const filePath = this.dataset.filepath;
            const fileName = this.dataset.filename;
            const fileExt = this.dataset.fileext.toLowerCase();

            if (viewModalTitle) viewModalTitle.textContent = fileName;
            if (viewModalDownloadLink) {
                viewModalDownloadLink.href = filePath;
                viewModalDownloadLink.download = fileName; // Set original filename for download
            }
            
            viewModalContent.innerHTML = ''; // Clear previous content

            if (fileExt === 'pdf') {
                // Using <embed> as it's often better for PDFs within modals than <iframe>
                // Can also use: const viewer = `<iframe src="${filePath}" frameborder="0"></iframe>`;
                const viewer = `<embed src="${filePath}" type="application/pdf" width="100%" height="100%">`;
                viewModalContent.innerHTML = viewer;
            } else if (['jpg', 'jpeg', 'png', 'gif', 'svg'].includes(fileExt)) {
                const viewer = `<img src="${filePath}" alt="${fileName}">`;
                viewModalContent.innerHTML = viewer;
            } else {
                viewModalContent.innerHTML = `<div class="preview-not-available">Direct preview is not available for "<strong>${fileName}</strong>".<br>Please download the file to view.</div>`;
            }
            showViewModal();
        });
    });

    // Event listener for "Close" button on modal
    if (closeViewModalBtn) {
        closeViewModalBtn.addEventListener('click', hideViewModal);
    }

    // Optional: Close modal if user clicks outside the modal content (on the overlay)
    if (viewModal) {
        viewModal.addEventListener('click', function(event) {
            if (event.target === viewModal) {
                hideViewModal();
            }
        });
    }

    // Optional: Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && viewModal && viewModal.style.display === 'flex') {
            hideViewModal();
        }
    });
});
// admin/assets/js/admin_scripts.js
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('customConfirmModal');
    if (!modalOverlay) return; // Do nothing if modal isn't on the page

    const modalTitle = document.getElementById('confirmModalTitle');
    const modalMessage = document.getElementById('confirmModalMessage');
    const confirmYesButton = document.getElementById('confirmModalYes');
    const confirmNoButton = document.getElementById('confirmModalNo');
    let confirmUrl = null; // To store the URL to navigate to if "Yes" is clicked

    // Function to show the modal
    function showConfirmModal(title, message, url) {
        if(modalTitle) modalTitle.textContent = title;
        if(modalMessage) modalMessage.innerHTML = message; // Use innerHTML if message contains HTML (e.g., bold item name)
        confirmUrl = url;
        modalOverlay.style.display = 'flex';
    }

    // Function to hide the modal
    function hideConfirmModal() {
        modalOverlay.style.display = 'none';
        confirmUrl = null;
    }

    // Event listener for "Yes" button
    if (confirmYesButton) {
        confirmYesButton.addEventListener('click', function() {
            if (confirmUrl) {
                window.location.href = confirmUrl; // Proceed with the action
            }
            hideConfirmModal();
        });
    }

    // Event listener for "No/Cancel" button
    if (confirmNoButton) {
        confirmNoButton.addEventListener('click', function() {
            hideConfirmModal();
        });
    }

    // Attach to delete links
    // For this to work, delete links need a common class, e.g., 'delete-link-modal'
    // and a data attribute for the message, e.g., data-confirm-message="Are you sure...?"
    // data-confirm-title="Confirm Delete"

    document.querySelectorAll('a.delete, a.js-confirm-modal').forEach(function(link) {
        link.addEventListener('click', function(event) {
            // Check if it's one of the links we want to hijack
            if (link.classList.contains('delete') || link.classList.contains('js-confirm-modal')) {
                event.preventDefault(); // Prevent the default link navigation

                const url = link.href;
                let title = link.getAttribute('data-confirm-title') || 'Confirm Action';
                let message = link.getAttribute('data-confirm-message') || 'Are you sure you want to delete this item? This action cannot be undone.';
                const itemName = link.getAttribute('data-item-name'); // Optional: for more specific messages

                if (itemName) { // Customize message if item name is provided
                    message = `Are you sure you want to delete "<strong>${itemName}</strong>"?<br>This action cannot be undone.`;
                    if (!link.hasAttribute('data-confirm-title')) { // Set a default title if not provided
                        title = `Confirm Deletion`;
                    }
                }
                showConfirmModal(title, message, url);
            }
        });
    });

    // Optional: Close modal if user clicks outside the modal content (on the overlay)
    modalOverlay.addEventListener('click', function(event) {
        if (event.target === modalOverlay) { // Check if click is directly on the overlay
            hideConfirmModal();
        }
    });

     // Optional: Close modal with Escape key
     document.addEventListener('keydown', function(event) {
         if (event.key === 'Escape' && modalOverlay.style.display === 'flex') {
             hideConfirmModal();
         }
     });

});
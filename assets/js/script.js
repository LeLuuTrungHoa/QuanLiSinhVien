/**
 * This file contains shared JavaScript functions for the Student Management System.
 */

document.addEventListener('DOMContentLoaded', function () {
    
    // This function is generic and can handle edit modals for users, faculties, classes, etc.
    // It requires the modal to have an ID like 'editUserModal' and form inputs with IDs like 'edit_full_name'.
    // The button that triggers it should have data attributes like `data-bs-target="#editUserModal"`
    // and `data-entity-json='{"id": 1, "full_name": "Test"}'`.
    
    const editModals = document.querySelectorAll('.modal[id^="edit"]');

    editModals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            if (!button) return;

            const entityJson = button.getAttribute('data-entity-json');
            if (!entityJson) return;

            const entity = JSON.parse(entityJson);
            
            // Loop through the entity's properties and populate form fields
            for (const key in entity) {
                const input = modal.querySelector(`#edit_${key}`);
                if (input) {
                    input.value = entity[key];
                }
            }
        });
    });

});

/**
 * Creates a live preview for a file input.
 * @param {HTMLInputElement} fileInput The file input element.
 * @param {HTMLImageElement} previewImage The image element to use for preview.
 */
function previewImageOnChange(fileInput, previewImage) {
    if (!fileInput || !previewImage) {
        console.error("Preview function requires a valid file input and image element.");
        return;
    }

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
}

// Note: The original `openEditModal` functions inside PHP files will be replaced by data attributes on the edit buttons.
// For example:
// <button type="button" class="btn btn-sm btn-warning" 
//         data-bs-toggle="modal" 
//         data-bs-target="#editUserModal"
//         data-entity-json='<?php echo htmlspecialchars(json_encode($user)); ?>'>
//     <i class="bi bi-pencil-square"></i>
// </button>

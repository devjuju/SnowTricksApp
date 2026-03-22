document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('featured-container');
    const preview = document.getElementById('featured-preview');
    const placeholder = document.getElementById('featured-placeholder');
    const fileInput = container.querySelector('input[type="file"]');
    const deleteInput = document.querySelector('[name$="[deleteFeaturedImage]"]');
    const deleteBtn = document.getElementById('delete-featured');
    const editBtn = document.getElementById('edit-featured');
    const errorDiv = document.getElementById('featured-error'); // ⚡ le message d'erreur

    const existingImage = container.dataset.existingImage;

    if (!existingImage) {
        hideFeaturedImage();
        showError("La figure doit avoir une image mise en avant.");
    } else {
        showFeaturedImage();
        hideError();
    }

    function showFeaturedImage() {
        preview.classList.remove('opacity-0');
        placeholder.classList.add('opacity-0');
        deleteBtn.classList.remove('hidden');
        hideError();
    }

    function hideFeaturedImage() {
        preview.src = '';
        preview.classList.add('opacity-0');
        placeholder.classList.remove('opacity-0');
        deleteBtn.classList.add('hidden');
        showError("La figure doit avoir une image mise en avant.");
    }

    function showError(message) {
        if (errorDiv) {
            errorDiv.textContent = message;
        }
    }

    function hideError() {
        if (errorDiv) {
            errorDiv.textContent = '';
        }
    }

    async function deleteTempFeaturedImage() {
        await fetch('/profile/featured-image/temp/delete', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
    }

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('featuredImage', file);

        const response = await fetch('/profile/featured-image/temp', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.url) {
            preview.src = data.url;
            showFeaturedImage();
            deleteInput.value = 0;
        }
    });

    deleteBtn.addEventListener('click', async () => {
        await deleteTempFeaturedImage();
        hideFeaturedImage();
        fileInput.value = '';
        deleteInput.value = 1;
    });

    editBtn.addEventListener('click', () => fileInput.click());
});
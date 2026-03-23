document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('avatar-container');
    const preview = document.getElementById('avatar-preview');
    const placeholder = document.getElementById('avatar-placeholder');
    const fileInput = container.querySelector('input[type="file"]');
    const deleteInput = document.querySelector('[name$="[deleteAvatar]"]');
    const deleteBtn = document.getElementById('delete-avatar');
    const editBtn = document.getElementById('edit-avatar');

    const existingImage = container.dataset.existingImage;

    if (!existingImage) {
        deleteBtn.classList.add('hidden');
        preview.classList.add('opacity-0');
        placeholder.classList.remove('opacity-0');
    } else {
        deleteBtn.classList.remove('hidden');
        preview.classList.remove('opacity-0');
        placeholder.classList.add('opacity-0');
    }

    const showAvatar = () => {
        preview.classList.remove('opacity-0');
        placeholder.classList.add('opacity-0');
        deleteBtn.classList.remove('hidden');
    };

    const hideAvatar = () => {
        preview.src = '';
        preview.classList.add('opacity-0');
        placeholder.classList.remove('opacity-0');
        deleteBtn.classList.add('hidden');
    };

    const deleteTempAvatar = async () => {
        await fetch('/profile/avatar/temp/delete', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    };

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('avatar', file);

        const response = await fetch('/profile/avatar/temp', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.url) {
            preview.src = data.url;
            showAvatar();
            deleteInput.value = 0;
        }
    });

    deleteBtn.addEventListener('click', async () => {
        await deleteTempAvatar();   // 👈 nettoyage serveur
        hideAvatar();               // UI
        fileInput.value = '';
        deleteInput.value = 1;
    });

    editBtn.addEventListener('click', () => fileInput.click());
});
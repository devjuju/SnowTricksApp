document.addEventListener('DOMContentLoaded', () => {
  const imagesContainer = document.querySelector('#image-wrapper');
  const addImageButton = document.querySelector('#add-image');
  const prototypeTemplate = document.querySelector('#image-prototype');

  let index = imagesContainer.querySelectorAll('.media-item').length;

  function setupImageItem(item) {
    const removeBtn = item.querySelector('.remove-item');
    const addBtn = item.querySelector('.item-add');
    const closeBtn = item.querySelector('.item-close');
    const inputFile = item.querySelector('.item-input');
    const previewImg = item.querySelector('.image-preview');
    const placeholder = item.querySelector('.image-placeholder');
    const uploadedFilename = item.querySelector('.uploaded-filename');

    if (previewImg && previewImg.src) {
      placeholder?.classList.add('hidden');
      addBtn?.classList.add('hidden');
      closeBtn?.classList.remove('hidden');
    }

    addBtn?.addEventListener('click', () => inputFile?.click());

    inputFile?.addEventListener('change', (e) => {
      const file = e.target.files?.[0];
      if (!file) return;

      const reader = new FileReader();

      reader.onload = (event) => {
        if (!previewImg) return;

        previewImg.src = event.target.result;
        previewImg.classList.remove('hidden');

        placeholder?.classList.add('hidden');
        addBtn?.classList.add('hidden');
        closeBtn?.classList.remove('hidden');
      };

      reader.readAsDataURL(file);

      if (uploadedFilename) {
        uploadedFilename.value = file.name;
      }
    });

    removeBtn?.addEventListener('click', () => {
      const removedInput = item.querySelector('.removed-image');

      if (removedInput && uploadedFilename?.value) {
        removedInput.value = uploadedFilename.value;
      }

      item.remove();
    });

    closeBtn?.addEventListener('click', () => {
      previewImg?.classList.add('hidden');
      placeholder?.classList.remove('hidden');

      closeBtn?.classList.add('hidden');
      addBtn?.classList.remove('hidden');

      if (inputFile) inputFile.value = '';
      if (uploadedFilename) uploadedFilename.value = '';
    });
  }

  // init existing items
  imagesContainer
    .querySelectorAll('.media-item')
    .forEach(setupImageItem);

  addImageButton?.addEventListener('click', () => {
    if (!prototypeTemplate) return;

    const template = document.createElement('template');

    // SAFE: DOM-based template cloning (no raw HTML injection)
    template.innerHTML = prototypeTemplate.innerHTML;

    const node = template.content.cloneNode(true);

    const newItem = node.firstElementChild || node;

    // Symfony placeholder replacement (safe because NOT innerHTML injection)
    newItem.innerHTML = newItem.innerHTML.replace(/__name__/g, index);

    imagesContainer.appendChild(newItem);

    setupImageItem(newItem);

    index++;

    newItem.scrollIntoView({ behavior: 'smooth', inline: 'center' });
  });
});
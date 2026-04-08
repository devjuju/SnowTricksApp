document.addEventListener('DOMContentLoaded', () => {
  const imagesContainer = document.querySelector('#image-wrapper');
  const addImageButton = document.querySelector('#add-image');
  const prototypeTemplate = document.querySelector('#image-prototype');

  let index = imagesContainer.querySelectorAll('.media-item').length;

  function replaceIndex(node, index) {
    const walker = document.createTreeWalker(node, NodeFilter.SHOW_ELEMENT, null);

    while (walker.nextNode()) {
      const el = walker.currentNode;

      [...el.attributes].forEach(attr => {
        if (attr.value && attr.value.includes('__name__')) {
          el.setAttribute(
            attr.name,
            attr.value.replace(/__name__/g, index)
          );
        }
      });

      if (el.value && typeof el.value === 'string') {
        if (el.value.includes('__name__')) {
          el.value = el.value.replace(/__name__/g, index);
        }
      }
    }
  }

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

  imagesContainer.querySelectorAll('.media-item').forEach(setupImageItem);

  addImageButton?.addEventListener('click', () => {
    if (!prototypeTemplate) return;

    const fragment = prototypeTemplate.content.cloneNode(true);

    replaceIndex(fragment, index);

    const newItem = fragment.querySelector('.media-item');

    imagesContainer.appendChild(fragment);

    if (newItem) {
      setupImageItem(newItem);
      newItem.scrollIntoView({ behavior: 'smooth', inline: 'center' });
    }

    index++;
  });
});
document.addEventListener('DOMContentLoaded', () => {
  const imagesContainer = document.querySelector('#image-wrapper');
  const addImageButton = document.querySelector('#add-image');
  const prototypeHtml = document.querySelector('#image-prototype').dataset.prototype;

  let index = imagesContainer.querySelectorAll('.media-item').length;

  function setupImageItem(item) {
    const removeBtn = item.querySelector('.remove-item');
    const addBtn = item.querySelector('.item-add');
    const closeBtn = item.querySelector('.item-close');
    const inputFile = item.querySelector('.item-input');
    const previewImg = item.querySelector('.image-preview');
    const placeholder = item.querySelector('.image-placeholder');
    const uploadedFilename = item.querySelector('.uploaded-filename');

    // Initial state
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
        previewImg.src = event.target.result;
        previewImg.classList.remove('hidden');
        placeholder.classList.add('hidden');
        addBtn.classList.add('hidden');
        closeBtn.classList.remove('hidden');
      };
      reader.readAsDataURL(file);

      uploadedFilename.value = file.name;
    });

    removeBtn?.addEventListener('click', () => {
      if (uploadedFilename.value) {
        item.querySelector('.removed-image').value = uploadedFilename.value;
      }
      item.remove();
    });

    closeBtn?.addEventListener('click', () => {
      previewImg.classList.add('hidden');
      placeholder.classList.remove('hidden');
      closeBtn.classList.add('hidden');
      addBtn.classList.remove('hidden');
      inputFile.value = '';
      uploadedFilename.value = '';
    });
  }

  // ----------------------
  // SAFE HTML PARSING
  // ----------------------

  function createElementFromHTML(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    return doc.body.firstElementChild;
  }

  function getPrototype(index) {
    return prototypeHtml.replace(/__name__/g, String(index));
  }

  // ----------------------
  // INIT
  // ----------------------

  imagesContainer.querySelectorAll('.media-item').forEach(setupImageItem);

  addImageButton.addEventListener('click', () => {
    const html = getPrototype(index);
    const newItem = createElementFromHTML(html);

    if (!newItem) return;

    imagesContainer.appendChild(newItem);
    setupImageItem(newItem);

    index++;

    newItem.scrollIntoView({ behavior: 'smooth', inline: 'center' });
  });
});
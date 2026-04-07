document.addEventListener('DOMContentLoaded', () => {
  const imagesContainer = document.querySelector('#image-wrapper');
  const addImageButton = document.querySelector('#add-image');
  const template = document.querySelector('#image-prototype');

  let index = imagesContainer.querySelectorAll('.media-item').length;

  function setupImageItem(item) {
    const removeBtn = item.querySelector('.remove-item');
    const addBtn = item.querySelector('.item-add');
    const closeBtn = item.querySelector('.item-close');
    const inputFile = item.querySelector('.item-input');
    const previewImg = item.querySelector('.image-preview');
    const placeholder = item.querySelector('.image-placeholder');
    const uploadedFilename = item.querySelector('.uploaded-filename');

    if (previewImg?.src) {
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
        placeholder?.classList.add('hidden');
        addBtn?.classList.add('hidden');
        closeBtn?.classList.remove('hidden');
      };
      reader.readAsDataURL(file);

      uploadedFilename.value = file.name;
    });

    removeBtn?.addEventListener('click', () => {
      const removed = item.querySelector('.removed-image');
      if (uploadedFilename.value && removed) {
        removed.value = uploadedFilename.value;
      }
      item.remove();
    });

    closeBtn?.addEventListener('click', () => {
      previewImg.classList.add('hidden');
      placeholder?.classList.remove('hidden');
      closeBtn.classList.add('hidden');
      addBtn.classList.remove('hidden');
      inputFile.value = '';
      uploadedFilename.value = '';
    });
  }

  function getPrototype(index) {
    const html = template.innerHTML;
    return html.replace(/__name__/g, index);
  }

  function createElementFromHTML(html) {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    return wrapper.firstElementChild;
  }

  imagesContainer
    .querySelectorAll('.media-item')
    .forEach(setupImageItem);

  addImageButton?.addEventListener('click', () => {
    const html = getPrototype(index);
    const newItem = createElementFromHTML(html);

    if (!newItem) return;

    imagesContainer.appendChild(newItem);
    setupImageItem(newItem);

    index++;

    newItem.scrollIntoView({ behavior: 'smooth', inline: 'center' });
  });
});
document.addEventListener('DOMContentLoaded', () => {
  const imagesContainer = document.querySelector('#image-wrapper');
  const addImageButton = document.querySelector('#add-image');
  const prototypeHtml = document.querySelector('#image-prototype').dataset.prototype;
  let index = imagesContainer.querySelectorAll('.media-item').length;

  // Fonction commune pour tous les items (existants, temporaires, nouveaux)
  function setupImageItem(item) {
    const removeBtn = item.querySelector('.remove-item');
    const addBtn = item.querySelector('.item-add');
    const closeBtn = item.querySelector('.item-close');
    const inputFile = item.querySelector('.item-input');
    const previewImg = item.querySelector('.image-preview');
    const placeholder = item.querySelector('.image-placeholder');
    const uploadedFilename = item.querySelector('.uploaded-filename');

    // Affichage initial
    if (previewImg && previewImg.src) {
      placeholder.classList.add('hidden');
      addBtn.classList.add('hidden');
      closeBtn.classList.remove('hidden');
    }

    // Ouvrir le file picker
    addBtn.addEventListener('click', () => inputFile.click());

    // Prévisualisation de l'image sélectionnée
    inputFile.addEventListener('change', (e) => {
      const file = e.target.files[0];
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

    // Supprimer l'image
    removeBtn.addEventListener('click', () => {
      if (uploadedFilename.value) {
        item.querySelector('.removed-image').value = uploadedFilename.value;
      }
      item.remove();
    });

    // Fermer / réinitialiser l'image
    closeBtn.addEventListener('click', () => {
      previewImg.classList.add('hidden');
      placeholder.classList.remove('hidden');
      closeBtn.classList.add('hidden');
      addBtn.classList.remove('hidden');
      inputFile.value = '';
      uploadedFilename.value = '';
    });
  }

  // Initialiser toutes les images existantes et temporaires
  imagesContainer.querySelectorAll('.media-item').forEach(setupImageItem);

  // Ajouter un nouvel élément via prototype
  addImageButton.addEventListener('click', () => {
    const template = document.createElement('div');
    template.innerHTML = prototypeHtml.replace(/__name__/g, index);
    const newItem = template.firstElementChild;
    imagesContainer.appendChild(newItem);
    index++;
    setupImageItem(newItem);
    // Scroll vers la nouvelle image
    newItem.scrollIntoView({ behavior: 'smooth', inline: 'center' });
  });
});
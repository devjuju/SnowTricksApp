document.addEventListener("DOMContentLoaded", () => {
    const imageWrapper = document.getElementById("image-wrapper");
    if (!imageWrapper) return;

    let index = parseInt(imageWrapper.dataset.index || 0);

    const validateFile = (file) => {
        const allowedTypes = ["image/jpeg", "image/png", "image/webp"];
        const maxSize = 2 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            alert("Type de fichier non autorisé");
            return false;
        }

        if (file.size > maxSize) {
            alert("Fichier trop lourd (max 2 Mo)");
            return false;
        }

        return true;
    };

    const initImageItem = (item, isNew = false) => {

        const input = item.querySelector(".item-input");
        const preview = item.querySelector(".image-preview");
        const placeholder = item.querySelector(".image-placeholder");

        const addBtn = item.querySelector(".item-add");
        const editBtn = item.querySelector(".item-edit");
        const closeBtn = item.querySelector(".item-close");
        const removeBtn = item.querySelector(".remove-item");

        const hiddenInput = item.querySelector(".uploaded-filename");
        const removedInput = item.querySelector(".removed-image");

        if (!hiddenInput) return;

        // STATE
        const state = {
            isTemp: item.dataset.state === "temp" || isNew,
            isLocked: false
        };

        const showPreview = (src) => {
            preview.src = src;
            preview.classList.remove("hidden");
            placeholder?.classList.add("hidden");
        };

        const hidePreview = () => {
            preview.removeAttribute("src");
            preview.classList.add("hidden");
            placeholder?.classList.remove("hidden");
        };

        const updateUI = () => {
            const hasImage = !!hiddenInput.value;
            const isOpen = input && !input.classList.contains("w-0");

            // 🔒 Lock uniquement pour images TEMP après upload
            if (state.isTemp && state.isLocked) {
                addBtn?.classList.add("hidden");
                editBtn?.classList.add("hidden");
                closeBtn?.classList.add("hidden");
                removeBtn?.classList.remove("hidden");

                input?.classList.add("pointer-events-none", "opacity-50");
                return;
            }

            // 🧾 Images existantes → éditables
            addBtn?.classList.toggle("hidden", isOpen || hasImage);
            editBtn?.classList.toggle("hidden", !(hasImage && !isOpen));
            closeBtn?.classList.toggle("hidden", !isOpen);
            removeBtn?.classList.toggle("hidden", isOpen);
        };

        const openInput = () => {
            input?.click();
        };

        const closeInput = () => {
            input?.classList.add("w-0", "opacity-0");
            input?.classList.remove("w-full", "opacity-100");
            updateUI();
        };

        const upload = async (file, replace = false) => {
            if (!validateFile(file)) return;

            const formData = new FormData();
            formData.append("images[]", file);

            try {
                const res = await fetch("/profile/images/temp", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const data = await res.json();

                if (!res.ok || !data.images?.[0]) {
                    alert(data.error || "Erreur upload");
                    return;
                }

                const image = data.images[0];
                const oldFilename = hiddenInput.value;

                // remplacement existant
                if (replace && oldFilename) {
                    const form = item.closest("form");
                    const inputHidden = document.createElement("input");

                    inputHidden.type = "hidden";
                    inputHidden.name = `replace_images[${oldFilename}]`;
                    inputHidden.value = image.filename;

                    form.appendChild(inputHidden);
                }

                hiddenInput.value = image.filename;
                showPreview(image.url);

                // 🔒 verrouillage uniquement si image était temporaire
                if (state.isTemp) {
                    state.isLocked = true;
                    item.dataset.state = "existing";
                    input?.classList.add("pointer-events-none", "opacity-50");
                }

                updateUI();

            } catch (e) {
                console.error(e);
                alert("Erreur serveur");
            }
        };

        // EVENTS
        input?.addEventListener("change", () => {
            if (!input.files?.[0]) return;
            upload(input.files[0], !!hiddenInput.value);
        });

        addBtn?.addEventListener("click", openInput);
        editBtn?.addEventListener("click", openInput);
        closeBtn?.addEventListener("click", closeInput);

        removeBtn?.addEventListener("click", () => {
            if (!confirm("Supprimer cette image ?")) return;

            const filename = hiddenInput.value;

            if (removedInput && filename) {
                removedInput.value = filename;
            }

            item.classList.add("opacity-30", "pointer-events-none");
        });

        // INIT preview
        if (preview?.getAttribute("src")) {
            hiddenInput.value = preview.dataset.filename || "";
        } else {
            hidePreview();
        }

        isNew ? openInput() : updateUI();
    };

    const addImage = () => {
        const element = cloneTemplate("image-prototype", index++);
        if (!element) return;

        imageWrapper.appendChild(element);
        initImageItem(element, true);

        smartScroll(imageWrapper, element);

        element.classList.add("ring-2", "ring-blue-400");
        setTimeout(() => {
            element.classList.remove("ring-2", "ring-blue-400");
        }, 1500);
    };

    document.getElementById("add-image")?.addEventListener("click", addImage);

    imageWrapper
        .querySelectorAll(".media-item")
        .forEach((item) => initImageItem(item));
});
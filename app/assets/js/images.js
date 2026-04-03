document.addEventListener("DOMContentLoaded", () => {
    const imageWrapper = document.getElementById("image-wrapper");
    if (!imageWrapper) return;

    let index = parseInt(imageWrapper.dataset.index || 0);

    // =========================
    // 🔍 VALIDATION
    // =========================
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

    // =========================
    // 🎯 INIT ITEM
    // =========================
    const initImageItem = (item) => {
        const input = item.querySelector(".item-input");
        const preview = item.querySelector(".image-preview");
        const placeholder = item.querySelector(".image-placeholder");

        const addBtn = item.querySelector(".item-add");
        const editBtn = item.querySelector(".item-edit");
        const closeBtn = item.querySelector(".item-close");
        const removeBtn = item.querySelector(".remove-item");

        const hiddenInput = item.querySelector(".uploaded-filename");
        const removedInput = item.querySelector(".removed-image");
        const replaceInput = item.querySelector(".replace-image");

        let isOpen = false;

        const getPublicId = () => hiddenInput?.value || null;

        // =========================
        // UI
        // =========================
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

        const render = () => {
            const hasImage = !!getPublicId();

            addBtn?.classList.toggle("hidden", isOpen || hasImage);
            editBtn?.classList.toggle("hidden", !hasImage || isOpen);
            closeBtn?.classList.toggle("hidden", !isOpen);
            removeBtn?.classList.toggle("hidden", isOpen);
        };

        const openInput = () => {
            isOpen = true;
            input.classList.remove("w-0", "opacity-0");
            input.classList.add("w-full", "opacity-100");
            input.focus();
            render();
        };

        const closeInput = () => {
            isOpen = false;
            input.classList.add("w-0", "opacity-0");
            input.classList.remove("w-full", "opacity-100");
            render();
        };

        // =========================
        // UPLOAD (ADD / REPLACE)
        // =========================
        const upload = async (file) => {
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
                const newPublicId = image.publicId;
                const oldPublicId = getPublicId();

                // =========================
                // REPLACE (ONLY IF EXISTS)
                // =========================
                if (oldPublicId && replaceInput) {
    replaceInput.value = newPublicId;

    // 🔥 supprimer toute éventuelle temp affichée
    document
        .querySelector(`[data-publicid="${newPublicId}"]`)
        ?.closest(".media-item")
        ?.remove();
}

                // =========================
                // UPDATE STATE
                // =========================
                hiddenInput.value = newPublicId;
                item.dataset.imageStatus = "existing";

                showPreview(image.url);
                closeInput();

            } catch (e) {
                console.error(e);
                alert("Erreur serveur");
            }
        };

        // =========================
        // DELETE
        // =========================
        const remove = () => {
            if (!confirm("Supprimer cette image ?")) return;

            const publicId = getPublicId();

            if (publicId && removedInput) {
                removedInput.value = publicId;
            }

            // clean replace if exists
            if (replaceInput) {
                replaceInput.value = "";
            }

            item.classList.add("opacity-30", "pointer-events-none");
            hidePreview();
        };

        // =========================
        // EVENTS
        // =========================
        input?.addEventListener("change", () => {
            const file = input.files?.[0];
            if (!file) return;

            upload(file);
        });

        addBtn?.addEventListener("click", openInput);
        editBtn?.addEventListener("click", openInput);
        closeBtn?.addEventListener("click", closeInput);
        removeBtn?.addEventListener("click", remove);

        // =========================
        // INIT
        // =========================
        if (!getPublicId()) hidePreview();

        render();
    };

    // =========================
    // ➕ ADD NEW IMAGE ITEM
    // =========================
    const addImage = () => {
        const element = cloneTemplate("image-prototype", index++);
        if (!element) return;

        imageWrapper.appendChild(element);
        initImageItem(element);

        smartScroll?.(imageWrapper, element);
    };

    document.getElementById("add-image")?.addEventListener("click", addImage);

    // =========================
    // INIT EXISTING
    // =========================
    imageWrapper.querySelectorAll(".media-item").forEach(initImageItem);
});
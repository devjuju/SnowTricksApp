document.addEventListener("DOMContentLoaded", () => {
    const imageWrapper = document.getElementById("image-wrapper");
    if (!imageWrapper) return;

    let index = parseInt(imageWrapper.dataset.index || 0);

    // =========================
    // 🔍 VALIDATION FILE
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

        const status = item.dataset.imageStatus;

        const isExisting = status === "existing";
        const isNew = status === "new";

        if (!hiddenInput) return;

        let isOpen = false;

        // =========================
        // STATE
        // =========================
        const getState = () => ({
            hasImage: item.dataset.imageStatus === "existing",
            isOpen
        });

        // =========================
        // PREVIEW
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

        // =========================
        // UI RENDER
        // =========================
        const renderUI = (state) => {
            const isExisting = item.dataset.imageStatus === "existing";

            // ADD
            addBtn?.classList.toggle("hidden", state.isOpen || state.hasImage);

            // EDIT (STRICT)
            if (!isExisting) {
                editBtn?.classList.add("hidden");
            } else {
                editBtn?.classList.toggle("hidden", !(state.hasImage && !state.isOpen));
            }

            // CLOSE
            closeBtn?.classList.toggle("hidden", !state.isOpen);

            // REMOVE
            removeBtn?.classList.toggle("hidden", state.isOpen);
        };

        const render = () => renderUI(getState());

        // =========================
        // OPEN / CLOSE INPUT
        // =========================
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
        // UPLOAD
        // =========================
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

                // replace image
                if (replace && oldFilename) {
                    const form = item.closest("form");
                    const inputReplace = document.createElement("input");

                    inputReplace.type = "hidden";
                    inputReplace.name = `replace_images[${oldFilename}]`;
                    inputReplace.value = image.filename;

                    form.appendChild(inputReplace);
                }

                // update state
                hiddenInput.value = image.filename;
                item.dataset.imageStatus = "existing";

                showPreview(image.url);

                closeInput();
                render();

            } catch (e) {
                console.error(e);
                alert("Erreur serveur");
            }
        };

        // =========================
        // EVENTS
        // =========================
        input?.addEventListener("change", () => {
            const file = input.files?.[0];

            if (!file) {
                if (!hiddenInput.value) item.remove();
                return;
            }

            upload(file, !!hiddenInput.value);
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
            hidePreview();
        });

        // =========================
        // INIT STATE
        // =========================
        if (preview?.src) {
            hiddenInput.value = preview.dataset.filename || "";
        }

        if (!hiddenInput.value) {
            hidePreview();
        }

        requestAnimationFrame(() => {
            if (isNew) openInput();
        });

        render();
    };

    // =========================
    // ➕ ADD IMAGE
    // =========================
    const addImage = () => {
        const element = cloneTemplate("image-prototype", index++);
        if (!element) return;

        imageWrapper.appendChild(element);
        initImageItem(element);

        if (typeof smartScroll === "function") {
            smartScroll(imageWrapper, element);
        }
    };

    document.getElementById("add-image")?.addEventListener("click", addImage);

    // =========================
    // INIT EXISTING ITEMS
    // =========================
    imageWrapper.querySelectorAll(".media-item").forEach(initImageItem);
});
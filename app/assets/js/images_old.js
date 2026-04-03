document.addEventListener("DOMContentLoaded", () => {
    const imageWrapper = document.getElementById("image-wrapper");
    if (!imageWrapper) return;

    let index = parseInt(imageWrapper.dataset.index || 0);

    /* =========================
       TOAST (reuse si existe)
    ========================= */
    const showToast = (message, type = "error") => {
        const container = document.getElementById("toast-container");
        if (!container) return;

        const toast = document.createElement("div");

        const baseClasses =
            "flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 transform opacity-0 translate-y-2";

        const types = {
            error: "bg-[#f8285a] text-white",
            success: "bg-[#17c653] text-white",
            info: "bg-[#1b84ff] text-white"
        };

        toast.className = `${baseClasses} ${types[type] || types.error}`;

        toast.innerHTML = `
            <span>${message}</span>
            <button class="ml-auto text-white/80 hover:text-white">&times;</button>
        `;

        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove("opacity-0", "translate-y-2");
        });

        toast.querySelector("button").addEventListener("click", () => {
            toast.remove();
        });

        setTimeout(() => toast.remove(), 4000);
    };

    /* =========================
       VALIDATION FILE
    ========================= */
    const validateFile = (file) => {
        const allowedTypes = ["image/jpeg", "image/png", "image/webp"];
        const maxSize = 2 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            showToast("Type de fichier non autorisé");
            return false;
        }

        if (file.size > maxSize) {
            showToast("Fichier trop lourd (max 2 Mo)");
            return false;
        }

        return true;
    };

    /* =========================
       DUPLICATE CHECK
    ========================= */
    const isDuplicateImage = (currentItem, filename) => {
        const allInputs = imageWrapper.querySelectorAll(".uploaded-filename");

        for (const input of allInputs) {
            if (input === currentItem.querySelector(".uploaded-filename")) continue;

            if (input.value && input.value === filename) {
                return true;
            }
        }

        return false;
    };

    /* =========================
       INIT ITEM
    ========================= */
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

        if (!hiddenInput) return;

        let isOpen = false;

        /* -------- STATE -------- */
        const getState = () => ({
            hasImage: !!hiddenInput.value,
            isOpen
        });

        /* -------- PREVIEW -------- */
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

        /* -------- UI -------- */
        const renderUI = (state) => {
            const isExisting = item.dataset.imageStatus === "existing";

            addBtn?.classList.toggle("hidden", state.isOpen || state.hasImage);

            if (!isExisting) {
                editBtn?.classList.add("hidden");
            } else {
                editBtn?.classList.toggle("hidden", !(state.hasImage && !state.isOpen));
            }

            closeBtn?.classList.toggle("hidden", !state.isOpen);
            removeBtn?.classList.toggle("hidden", state.isOpen);
        };

        const render = () => renderUI(getState());

        /* -------- INPUT -------- */
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

        /* -------- UPLOAD -------- */
        const upload = async (file, replace = false) => {
            if (!validateFile(file)) return;

            const formData = new FormData();
            formData.append("images[]", file);

            try {
                const res = await fetch(IMAGE_ROUTES.upload, {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const data = await res.json();

                if (!res.ok || !data.images?.[0]) {
                    showToast(data.error || "Erreur upload");
                    return;
                }

                const image = data.images[0];

                // 🔥 DUPLICATE CHECK
                if (isDuplicateImage(item, image.filename)) {
                    showToast("Cette image est déjà ajoutée.", "error");
                    return;
                }

                const oldFilename = hiddenInput.value;

                // replace logique
                if (replace && oldFilename) {
                    const form = item.closest("form");
                    const inputReplace = document.createElement("input");

                    inputReplace.type = "hidden";
                    inputReplace.name = `replace_images[${oldFilename}]`;
                    inputReplace.value = image.filename;

                    form.appendChild(inputReplace);
                }

                hiddenInput.value = image.filename;
                item.dataset.imageStatus = "existing";

                showPreview(image.url);

                closeInput();
                render();

            } catch (e) {
                console.error(e);
                showToast("Erreur serveur");
            }
        };

        /* -------- EVENTS -------- */
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

        /* -------- INIT -------- */
        if (preview?.dataset.filename) {
            hiddenInput.value = preview.dataset.filename;
        }

        if (!hiddenInput.value) {
            hidePreview();
        }

        requestAnimationFrame(() => {
            if (item.dataset.imageStatus === "new") {
                openInput();
            }
        });

        render();
    };

    /* =========================
       ADD IMAGE
    ========================= */
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

    /* =========================
       INIT EXISTING
    ========================= */
    imageWrapper.querySelectorAll(".media-item").forEach(initImageItem);
});
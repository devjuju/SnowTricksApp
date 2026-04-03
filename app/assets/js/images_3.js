document.addEventListener("DOMContentLoaded", () => {
    const imageWrapper = document.getElementById("image-wrapper");
    const form = imageWrapper?.closest("form");
    const payloadInput = document.getElementById("media_payload");

    if (!imageWrapper || !form || !payloadInput) return;

    let index = parseInt(imageWrapper.dataset.index || 0);

    /* =========================
       GLOBAL STATE (CMS CORE)
    ========================= */
    let media = [];

    const syncPayload = () => {
        payloadInput.value = JSON.stringify(media);
    };

    /* =========================
       TOAST
    ========================= */
    const showToast = (message, type = "error") => {
        const container = document.getElementById("toast-container");
        if (!container) return;

        const toast = document.createElement("div");

        const base =
            "flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 transform opacity-0 translate-y-2";

        const types = {
            error: "bg-[#f8285a] text-white",
            success: "bg-[#17c653] text-white",
            info: "bg-[#1b84ff] text-white"
        };

        toast.className = `${base} ${types[type] || types.error}`;

        toast.innerHTML = `
            <span>${message}</span>
            <button class="ml-auto text-white/80 hover:text-white">&times;</button>
        `;

        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove("opacity-0", "translate-y-2");
        });

        toast.querySelector("button").addEventListener("click", () => toast.remove());

        setTimeout(() => toast.remove(), 4000);
    };

    /* =========================
       VALIDATION FILE
    ========================= */
    const validateFile = (file) => {
        const allowed = ["image/jpeg", "image/png", "image/webp"];
        const maxSize = 2 * 1024 * 1024;

        if (!allowed.includes(file.type)) {
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
       HELPERS
    ========================= */
    const findMedia = (id) => media.find(m => m.id === id);

    const upsertMedia = (item) => {
        const index = media.findIndex(m => m.domId === item.domId);

        if (index >= 0) {
            media[index] = item;
        } else {
            media.push(item);
        }

        syncPayload();
    };

    const removeMedia = (domId) => {
        const item = findMedia(domId);
        if (!item) return;

        item.state = "deleted";
        syncPayload();
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

        const hiddenInput = item.querySelector(".uploaded-publicid");

        if (!hiddenInput) return;

        const domId = hiddenInput.dataset.domId || crypto.randomUUID();

        let isOpen = false;

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
            const hasImage = !!hiddenInput.value;

            addBtn?.classList.toggle("hidden", isOpen || hasImage);
            editBtn?.classList.toggle("hidden", !(hasImage && !isOpen));
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

        /* =========================
           UPLOAD
        ========================= */
        const upload = async (file) => {
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

                const mediaItem = {
                    domId,
                    id: image.publicId,
                    type: "image",
                    state: hiddenInput.value ? "existing" : "new"
                };

                hiddenInput.value = image.publicId;
                item.dataset.imageStatus = "existing";

                showPreview(image.url);

                upsertMedia(mediaItem);

                closeInput();
                render();

            } catch (e) {
                console.error(e);
                showToast("Erreur serveur");
            }
        };

        /* =========================
           EVENTS
        ========================= */
        input?.addEventListener("change", () => {
            const file = input.files?.[0];

            if (!file) {
                if (!hiddenInput.value) item.remove();
                return;
            }

            upload(file);
        });

        addBtn?.addEventListener("click", openInput);
        editBtn?.addEventListener("click", openInput);
        closeBtn?.addEventListener("click", closeInput);

        removeBtn?.addEventListener("click", () => {
            if (!confirm("Supprimer cette image ?")) return;

            hiddenInput.value = "";

            item.classList.add("opacity-30", "pointer-events-none");
            hidePreview();

            removeMedia(domId);
        });

        /* =========================
           INIT EXISTING
        ========================= */
        if (preview?.dataset.publicid) {
            hiddenInput.value = preview.dataset.publicid;

            upsertMedia({
                domId,
                id: hiddenInput.value,
                type: "image",
                state: "existing"
            });
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

    /* INIT EXISTING */
    imageWrapper.querySelectorAll(".media-item").forEach(initImageItem);

    /* FINAL SYNC BEFORE SUBMIT */
    form.addEventListener("submit", () => {
        syncPayload();
    });
});
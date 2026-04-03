document.addEventListener("DOMContentLoaded", () => {
    /* =========================
       TOAST (GLOBAL)
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
            removeToast(toast);
        });

        setTimeout(() => {
            removeToast(toast);
        }, 4000);
    };

    const removeToast = (toast) => {
        toast.classList.add("opacity-0", "translate-y-2");
        setTimeout(() => toast.remove(), 300);
    };

    /* =========================
       VIDEO SECTION
    ========================= */
    const videoWrapper = document.getElementById("video-wrapper");

    if (!videoWrapper) return;

    let videoIndex = videoWrapper.querySelectorAll(".media-item").length;

    /* -------- HELPERS -------- */

    const extractYoutubeId = (url) => {
        if (!url) return null;

        try {
            const parsed = new URL(url);

            if (parsed.hostname.includes("youtube.com")) {
                return parsed.searchParams.get("v");
            }

            if (parsed.hostname === "youtu.be") {
                return parsed.pathname.substring(1);
            }
        } catch (e) {
            return null;
        }

        return null;
    };

    const validateVideoUrl = (url) => {
        if (!url) return false;

        try {
            const parsed = new URL(url.trim());

            const isYoutube =
                parsed.hostname.includes("youtube.com") ||
                parsed.hostname === "youtu.be";

            if (!isYoutube) return false;

            const id = extractYoutubeId(url);
            return !!id;
        } catch (e) {
            return false;
        }
    };

    const isDuplicateVideo = (currentInput, id) => {
        const allInputs = videoWrapper.querySelectorAll(".item-input");

        for (const input of allInputs) {
            if (input === currentInput) continue;

            const existingId =
                input.dataset.youtubeId ||
                extractYoutubeId(input.value.trim());

            if (existingId && existingId === id) {
                return true;
            }
        }

        return false;
    };

    /* -------- INIT ITEM -------- */

    const initVideoItem = (item, isNew = false) => {
        const input = item.querySelector(".item-input");
        if (!input) return;

        const preview = item.querySelector("iframe");
        const placeholder = item.querySelector(".video-placeholder");

        const addBtn = item.querySelector(".item-add");
        const editBtn = item.querySelector(".item-edit");
        const closeBtn = item.querySelector(".item-close");
        const removeBtn = item.querySelector(".remove-item");

        const updatePreview = () => {
            const id = extractYoutubeId(input.value.trim());

            if (id) {
                preview.src = `https://www.youtube.com/embed/${id}`;
                preview.classList.remove("hidden");
                placeholder?.classList.add("hidden");
                input.dataset.youtubeId = id;
            } else {
                preview.src = "";
                preview.classList.add("hidden");
                placeholder?.classList.remove("hidden");
                delete input.dataset.youtubeId;
            }
        };

        const updateUI = () => {
            const hasValue = input.value.trim() !== "";
            const isOpen = !input.classList.contains("w-0");
            const isValid = validateVideoUrl(input.value.trim());

            addBtn?.classList.toggle("hidden", isOpen || hasValue);
            editBtn?.classList.toggle("hidden", !(hasValue && !isOpen));
            closeBtn?.classList.toggle("hidden", !isOpen);
            removeBtn?.classList.toggle("hidden", isOpen || !isValid);
        };

        const openInput = () => {
            input.classList.remove("w-0", "opacity-0");
            input.classList.add("w-full", "opacity-100");
            updateUI();
            input.focus();
        };

        const closeInput = () => {
            const url = input.value.trim();

            if (url) {
                if (!validateVideoUrl(url)) {
                    input.classList.add("border-red-500", "ring-2", "ring-red-400");
                    input.focus();

                    showToast("Veuillez corriger l’URL avant de fermer.", "error");
                    return;
                }

                const id = extractYoutubeId(url);

                if (isDuplicateVideo(input, id)) {
                    input.classList.add("border-red-500", "ring-2", "ring-red-400");
                    input.focus();

                    showToast("Cette vidéo est déjà ajoutée.", "error");
                    return;
                }
            }

            input.classList.remove("border-red-500", "ring-2", "ring-red-400");

            input.classList.add("w-0", "opacity-0");
            input.classList.remove("w-full", "opacity-100");

            updateUI();
        };

        input.addEventListener("input", () => {
            const url = input.value.trim();

            if (!url) {
                updatePreview();
                updateUI();
                return;
            }

            if (!validateVideoUrl(url)) {
                preview.src = "";
                preview.classList.add("hidden");
                placeholder?.classList.remove("hidden");
                delete input.dataset.youtubeId;

                updateUI();
                return;
            }

            const id = extractYoutubeId(url);

            if (id && isDuplicateVideo(input, id)) {
                preview.src = "";
                preview.classList.add("hidden");
                placeholder?.classList.remove("hidden");

                input.classList.add("border-red-500", "ring-2", "ring-red-400");

                showToast("Cette vidéo est déjà ajoutée.", "error");
                return;
            }

            input.classList.remove("border-red-500", "ring-2", "ring-red-400");

            updatePreview();
            updateUI();
        });

        addBtn?.addEventListener("click", openInput);
        editBtn?.addEventListener("click", openInput);
        closeBtn?.addEventListener("click", closeInput);

        removeBtn?.addEventListener("click", () => {
            if (!confirm("Supprimer cette vidéo ?")) return;

            const inputHidden = item.querySelector(".removed-video");

            const id =
                input.dataset.youtubeId ||
                extractYoutubeId(input.value.trim()) ||
                null;

            if (inputHidden && id) {
                inputHidden.value = id;
            } else if (inputHidden) {
                inputHidden.value = input.value.trim();
            }

            item.classList.add("opacity-30", "relative");

            const overlay = document.createElement("div");
            overlay.className =
                "absolute inset-0 bg-black/50 flex items-center justify-center text-white font-bold text-sm rounded";
            overlay.textContent = "Supprimée";

            item.querySelector(".mb-3").appendChild(overlay);

            item.classList.add("pointer-events-none");
        });

        updatePreview();
        isNew ? openInput() : updateUI();
    };

    /* -------- ADD VIDEO -------- */

    const addVideo = () => {
        const element = cloneTemplate("video-prototype", videoIndex++);
        if (!element) return;

        videoWrapper.appendChild(element);
        initVideoItem(element, true);
        smartScroll(videoWrapper, element);
    };

    document
        .getElementById("add-video")
        ?.addEventListener("click", addVideo);

    videoWrapper
        .querySelectorAll(".media-item")
        .forEach((item) => initVideoItem(item));
});
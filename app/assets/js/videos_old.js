document.addEventListener("DOMContentLoaded", () => {
    /* -------- VIDEO SECTION -------- */
    const videoWrapper = document.getElementById("video-wrapper");

    if (videoWrapper) {
        let videoIndex = videoWrapper.querySelectorAll(".media-item").length;

        const extractYoutubeId = (url) => {
    if (!url) return null;

    try {
        const parsed = new URL(url);

        // youtube.com/watch?v=
        if (parsed.hostname.includes("youtube.com")) {
            return parsed.searchParams.get("v");
        }

        // youtu.be/ID
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



       const initVideoItem = (item, isNew = false) => {
    const input = item.querySelector(".item-input");
    if (!input) return;

    const preview = item.querySelector("iframe");
    const placeholder = item.querySelector(".video-placeholder");

    const addBtn = item.querySelector(".item-add");
    const editBtn = item.querySelector(".item-edit");
    const closeBtn = item.querySelector(".item-close");
    const removeBtn = item.querySelector(".remove-item");

    // 🔥 update preview basé sur ID
    const updatePreview = () => {
        const id = extractYoutubeId(input.value.trim());

        if (id) {
            preview.src = `https://www.youtube.com/embed/${id}`;
            preview.classList.remove("hidden");
            placeholder?.classList.add("hidden");

            // 🔥 IMPORTANT → stocker ID
            input.dataset.youtubeId = id;
        } else {
            preview.src = "";
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

    // 🔥 ICI : delete caché si invalide ou vide
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

    // 🔥 si l'utilisateur a écrit quelque chose mais invalide → on bloque
    if (url && !validateVideoUrl(url)) {
        input.classList.add("border-red-500", "ring-2", "ring-red-400");
        input.focus();

        alert("Veuillez corriger l’URL avant de fermer.");
        return;
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

    updatePreview();
    updateUI();
});

    addBtn?.addEventListener("click", openInput);
    editBtn?.addEventListener("click", openInput);
    closeBtn?.addEventListener("click", closeInput);

    // 🔥 DELETE → envoie ID uniquement
    removeBtn?.addEventListener("click", () => {
    if (!confirm("Supprimer cette vidéo ?")) return;

    const inputHidden = item.querySelector(".removed-video");

    // 🔥 fallback si dataset absent
    const id =
        input.dataset.youtubeId ||
        extractYoutubeId(input.value.trim()) ||
        null;

    // on marque suppression même si URL invalide
    if (inputHidden && id) {
        inputHidden.value = id;
    } else if (inputHidden) {
        // 🔥 cas URL invalide → on envoie quand même une valeur brute
        inputHidden.value = input.value.trim(); 
    }

    item.classList.add("opacity-30", "relative");

    const overlay = document.createElement("div");
    overlay.className =
        "absolute inset-0 bg-black/50 flex items-center justify-center text-white font-bold text-sm rounded";

    item.querySelector(".mb-3").appendChild(overlay);

    item.classList.add("pointer-events-none");
});

    updatePreview();
    isNew ? openInput() : updateUI();
};

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
    }
});
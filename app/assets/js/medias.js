document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       ELEMENTS
    ========================= */
    const imageWrapper = document.getElementById("image-wrapper");
    const videoWrapper = document.getElementById("video-wrapper");
    const payloadInput = document.getElementById("media_payload");

    if (!payloadInput) return;

    /* =========================
       STATE GLOBAL
    ========================= */
    let media = [];

    const syncPayload = () => {
        payloadInput.value = JSON.stringify(
            media.filter(m => m.state !== "deleted")
        );
    };

    /* =========================
       HELPERS STORE
    ========================= */
    const upsertMedia = (item) => {
        const index = media.findIndex(m => m.domId === item.domId);

        if (index >= 0) media[index] = item;
        else media.push(item);

        syncPayload();
    };

    const removeMedia = (domId) => {
        const item = media.find(m => m.domId === domId);
        if (!item) return;

        item.state = "deleted";
        syncPayload();
    };

    /* =========================
       ROUTER INIT ITEM
    ========================= */
    const initMediaItem = (item) => {
        if (item.classList.contains("media-image")) {
            initImageItem(item);
        }

        if (item.classList.contains("media-video")) {
            initVideoItem(item);
        }
    };

    /* =========================
       IMAGE ITEM
    ========================= */
    const initImageItem = (item) => {
        const input = item.querySelector(".item-input");
        const preview = item.querySelector(".image-preview");
        const placeholder = item.querySelector(".image-placeholder");
        const hidden = item.querySelector(".uploaded-publicid");

        const removeBtn = item.querySelector(".remove-item");

        const domId = hidden?.dataset.domId || crypto.randomUUID();
        if (hidden) hidden.dataset.domId = domId;

        const showPreview = (src) => {
            if (!preview) return;
            preview.src = src;
            preview.classList.remove("hidden");
            placeholder?.classList.add("hidden");
        };

        const hidePreview = () => {
            if (!preview) return;
            preview.removeAttribute("src");
            preview.classList.add("hidden");
            placeholder?.classList.remove("hidden");
        };

        const saveImage = (publicId) => {
            upsertMedia({
                domId,
                id: publicId,
                type: "image",
                state: "existing"
            });
        };

        input?.addEventListener("change", async () => {
            const file = input.files?.[0];
            if (!file) return;

            const formData = new FormData();
            formData.append("images[]", file);

            try {
                const res = await fetch(IMAGE_ROUTES.upload, {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const data = await res.json();

                if (!res.ok || !data.images?.[0]) return;

                const img = data.images[0];

                hidden.value = img.publicId;
                showPreview(img.url);

                saveImage(img.publicId);

            } catch (e) {
                console.error(e);
            }
        });

        removeBtn?.addEventListener("click", () => {
            if (!confirm("Supprimer cette image ?")) return;

            hidden.value = "";
            item.classList.add("opacity-30", "pointer-events-none");

            hidePreview();
            removeMedia(domId);
        });

        /* INIT EXISTING */
        if (hidden?.value) {
            saveImage(hidden.value);
        }
    };

    /* =========================
       VIDEO ITEM
    ========================= */
    const initVideoItem = (item) => {
        const input = item.querySelector(".item-input");
        const iframe = item.querySelector("iframe");
        const placeholder = item.querySelector(".video-placeholder");
        const removeBtn = item.querySelector(".remove-item");

        const domId = input.dataset.domId || crypto.randomUUID();
        input.dataset.domId = domId;

        const extractId = (url) => {
            try {
                const u = new URL(url);
                if (u.hostname.includes("youtube.com")) {
                    return u.searchParams.get("v");
                }
                if (u.hostname === "youtu.be") {
                    return u.pathname.slice(1);
                }
            } catch {
                return null;
            }
        };

        const showPreview = (id) => {
            if (!iframe) return;
            iframe.src = `https://www.youtube.com/embed/${id}`;
            iframe.classList.remove("hidden");
            placeholder?.classList.add("hidden");
        };

        const hidePreview = () => {
            if (!iframe) return;
            iframe.src = "";
            iframe.classList.add("hidden");
            placeholder?.classList.remove("hidden");
        };

        const saveVideo = (url) => {
            const id = extractId(url);
            if (!id) return;

            upsertMedia({
                domId,
                id,
                type: "video",
                state: "existing"
            });
        };

        input?.addEventListener("input", () => {
            const url = input.value.trim();
            const id = extractId(url);

            if (!id) {
                hidePreview();
                return;
            }

            showPreview(id);
            saveVideo(url);
        });

        removeBtn?.addEventListener("click", () => {
            if (!confirm("Supprimer cette vidéo ?")) return;

            item.classList.add("opacity-30", "pointer-events-none");
            removeMedia(domId);
        });

        /* INIT EXISTING */
        const existingId = extractId(input?.value || "");
        if (existingId) {
            showPreview(existingId);
            saveVideo(input.value);
        }
    };

    /* =========================
       INIT EXISTING ITEMS
    ========================= */
    document.querySelectorAll(".media-item").forEach(initMediaItem);

    /* =========================
       ADD IMAGE
    ========================= */
    document.getElementById("add-image")?.addEventListener("click", () => {
        const tpl = document.getElementById("image-prototype");
        const clone = tpl.content.cloneNode(true);

        const item = clone.querySelector(".media-item");

        imageWrapper.appendChild(clone);
        initImageItem(item);
    });

    /* =========================
       ADD VIDEO
    ========================= */
    document.getElementById("add-video")?.addEventListener("click", () => {
        const tpl = document.getElementById("video-prototype");
        const clone = tpl.content.cloneNode(true);

        const item = clone.querySelector(".media-item");

        videoWrapper.appendChild(clone);
        initVideoItem(item);
    });

    /* =========================
       SUBMIT
    ========================= */
    document.querySelector("form")?.addEventListener("submit", () => {
        syncPayload();
    });

});
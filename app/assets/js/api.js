document.addEventListener("DOMContentLoaded", () => {

    const wrapper = document.getElementById("image-wrapper");
    if (!wrapper) return;

    const validateFile = (file) => {
        const allowed = ["image/jpeg", "image/png", "image/webp"];
        const maxSize = 2 * 1024 * 1024;

        if (!allowed.includes(file.type)) {
            alert("Format non autorisé");
            return false;
        }

        if (file.size > maxSize) {
            alert("Fichier trop lourd");
            return false;
        }

        return true;
    };

    const uploadTemp = async (file) => {
        const formData = new FormData();
        formData.append("images[]", file);

        const res = await fetch("/profile/images/temp", {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });

        const data = await res.json();

        if (!res.ok || !data.images?.length) {
            alert("Erreur upload");
            return null;
        }

        return data.images[0];
    };

    const createPreviewCard = (image) => {

        const div = document.createElement("div");
        div.className = "media-item w-40";

        div.innerHTML = `
            <div class="aspect-video border rounded overflow-hidden">
                <img src="${image.url}" class="w-full h-full object-cover">
            </div>
            <input type="hidden" name="images_filenames[]" value="${image.filename}">
            <input type="hidden" name="replace_images[]" value="">
        `;

        return div;
    };

    const handleFile = async (file, item = null) => {

        if (!validateFile(file)) return;

        const uploaded = await uploadTemp(file);
        if (!uploaded) return;

        const oldFilename = item?.querySelector('[name="images_filenames[]"]')?.value;

        if (item && oldFilename) {
            // remplacement
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = `replace_images[${oldFilename}]`;
            input.value = uploaded.filename;

            item.appendChild(input);

            // update preview
            const img = item.querySelector("img");
            img.src = uploaded.url;

        } else {
            // ajout
            const preview = createPreviewCard(uploaded);
            wrapper.appendChild(preview);
        }
    };

    document.getElementById("add-image")?.addEventListener("click", async () => {
        const input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*";

        input.onchange = async () => {
            if (!input.files[0]) return;
            await handleFile(input.files[0]);
        };

        input.click();
    });

    // EDIT EXISTING
    wrapper.querySelectorAll(".media-item").forEach(item => {

        const input = item.querySelector(".item-input");

        input?.addEventListener("change", async () => {
            if (!input.files[0]) return;
            await handleFile(input.files[0], item);
        });

        item.querySelector(".item-edit")?.addEventListener("click", () => {
            input?.click();
        });

        item.querySelector(".remove-item")?.addEventListener("click", () => {
            if (!confirm("Supprimer ?")) return;

            const hidden = item.querySelector(".removed-image");
            const filename = item.querySelector('[name="images_filenames[]"]').value;

            if (hidden) hidden.value = filename;

            item.remove();
        });
    });

});
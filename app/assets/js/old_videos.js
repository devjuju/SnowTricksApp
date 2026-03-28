("DOMContentLoaded", () => {
	    const videoWrapper = document.getElementById("video-wrapper");
	    if (!videoWrapper) return;
	
	    // 🔍 Extract YouTube ID
	    const extractYoutubeId = (url) => {
	        try {
	            const parsed = new URL(url);
	            let id = null;
	
	            if (parsed.hostname.includes("youtube.com")) {
	                id = parsed.searchParams.get("v");
	            } else if (parsed.hostname === "youtu.be") {
	                id = parsed.pathname.substring(1);
	            }
	
	            return /^[a-zA-Z0-9_-]{11}$/.test(id) ? id : null;
	        } catch {
	            return null;
	        }
	    };
	
	    const initVideoItem = (item, isNew = false) => {
	        const input = item.querySelector(".item-input");
	        const iframe = item.querySelector("iframe");
	        const placeholder = item.querySelector(".video-placeholder");
	
	        const addBtn = item.querySelector(".item-add");
	        const editBtn = item.querySelector(".item-edit");
	        const closeBtn = item.querySelector(".item-close");
	        const removeBtn = item.querySelector(".remove-item");
	
	        const hiddenInput = item.querySelector(".video-url");
	        const removedInput = item.querySelector(".removed-video");
	
	        if (!hiddenInput) return;
	
	        const state = {
	            isLocked: false,
	            isExisting: !isNew && !!hiddenInput.value
	        };
	
	        // 🎥 Preview
	        const showPreview = (url) => {
	            const id = extractYoutubeId(url);
	            if (id) {
	                iframe.src = `https://www.youtube.com/embed/${id}`;
	                iframe.classList.remove("hidden");
	                placeholder?.classList.add("hidden");
	            } else {
	                hidePreview();
	            }
	        };
	
	        const hidePreview = () => {
	            iframe.src = "";
	            iframe.classList.add("hidden");
	            placeholder?.classList.remove("hidden");
	        };
	
	        // 🎛 UI
	        const updateUI = () => {
	            const hasVideo = !!hiddenInput.value;
	            const isOpen = input && !input.classList.contains("w-0");
	
	            // Les vidéos existantes peuvent être éditées et supprimées
	            if (state.isExisting) {
	                addBtn?.classList.add("hidden");
	                editBtn?.classList.toggle("hidden", isOpen || !hasVideo);
	                closeBtn?.classList.toggle("hidden", !isOpen);
	                removeBtn?.classList.toggle("hidden", isOpen);
	            } else {
	                // Les nouvelles vidéos : pas d'edit, uniquement suppression
	                addBtn?.classList.add("hidden");
	                editBtn?.classList.add("hidden");
	                closeBtn?.classList.toggle("hidden", !isOpen);
	                removeBtn?.classList.toggle("hidden", !isOpen);
	            }
	        };
	
	        const openInput = () => {
	            input?.classList.remove("w-0", "opacity-0");
	            input?.classList.add("w-full", "opacity-100");
	            input?.focus();
	            updateUI();
	        };
	
	        const closeInput = () => {
	            input?.classList.add("w-0", "opacity-0");
	            input?.classList.remove("w-full", "opacity-100");
	            updateUI();
	        };
	
	        // ✏️ Update value
	        input?.addEventListener("input", () => {
	            const url = input.value.trim();
	            hiddenInput.value = url;
	
	            if (url) showPreview(url);
	            else hidePreview();
	
	            updateUI();
	        });
	
	        // 🎯 Paste instant preview
	        input?.addEventListener("paste", () => {
	            setTimeout(() => input.dispatchEvent(new Event("input")), 10);
	        });
	
	        // 🎯 Actions
	        addBtn?.addEventListener("click", openInput);
	        editBtn?.addEventListener("click", openInput);
	        closeBtn?.addEventListener("click", closeInput);
	
	        // 🗑 Suppression
	        removeBtn?.addEventListener("click", () => {
	            if (!confirm("Supprimer cette vidéo ?")) return;
	
	            const url = hiddenInput.value;
	            if (removedInput && url) removedInput.value = url;
	
	            item.classList.add("opacity-30", "pointer-events-none");
	        });
	
	        // INIT
	        if (hiddenInput.value) {
	            showPreview(hiddenInput.value);
	        } else {
	            hidePreview();
	        }
	
	        isNew ? openInput() : updateUI();
	    };
	
	    // ➕ Ajouter vidéo
	    const addVideo = () => {
	        const template = document.getElementById("video-prototype");
	        if (!template) return;
	
	        const element = template.content.firstElementChild.cloneNode(true);
	        videoWrapper.appendChild(element);
	        initVideoItem(element, true);
	
	        // Scroll smooth si défini
	        if (typeof smartScroll === "function") {
	            smartScroll(videoWrapper, element);
	        }
	
	        // Focus et effet visuel
	        element.classList.add("ring-2", "ring-blue-400");
	        setTimeout(() => element.classList.remove("ring-2", "ring-blue-400"), 1500);
	    };
	
	    document.getElementById("add-video")?.addEventListener("click", addVideo);
	
	    // INIT existants
	    videoWrapper.querySelectorAll(".media-item").forEach((item) => initVideoItem(item));
	});
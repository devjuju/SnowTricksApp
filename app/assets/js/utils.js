window.smartScroll = (wrapper, element) => {
    const isRow = window.getComputedStyle(wrapper).flexDirection === "row";

    element.scrollIntoView({
        behavior: "smooth",
        inline: isRow ? "start" : "nearest",
        block: isRow ? "nearest" : "start",
    });
};

window.cloneTemplate = (id, index) => {
    const template = document.getElementById(id);

    if (!template || !template.content) return null;

    const clone = template.content.cloneNode(true);

    const replacePlaceholders = (node) => {
        if (node.nodeType === Node.TEXT_NODE) {
            node.textContent = node.textContent.replace(/__name__/g, index);
        }

        if (node.nodeType === Node.ELEMENT_NODE) {
            [...node.attributes].forEach(attr => {
                if (attr.value.includes("__name__")) {
                    node.setAttribute(
                        attr.name,
                        attr.value.replace(/__name__/g, index)
                    );
                }
            });

            node.childNodes.forEach(replacePlaceholders);
        }
    };

    replacePlaceholders(clone);

    return clone.firstElementChild;
};
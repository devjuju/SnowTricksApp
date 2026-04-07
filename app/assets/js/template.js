
window.cloneTemplate = (templateId, index) => {
    const template = document.getElementById(templateId);
    if (!template?.content) return null;

    const fragment = template.content.cloneNode(true);

    fragment.querySelectorAll("*").forEach((el) => {
        for (const attr of el.attributes) {
            if (attr.value.includes("__name__")) {
                attr.value = attr.value.replace(/__name__/g, String(index));
            }
        }
    });

    return fragment.firstElementChild ?? null;
};
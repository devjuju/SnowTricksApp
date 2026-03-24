/* -------- SAFE TEMPLATE CLONE -------- */
const cloneTemplate = (templateId, index) => {
    const template = document.getElementById(templateId);
    if (!template) return null;

    const clone = template.content.cloneNode(true);

    // remplace __name__ dans TOUS les attributs
    clone.querySelectorAll("*").forEach((el) => {
        [...el.attributes].forEach((attr) => {
            if (attr.value.includes("__name__")) {
                attr.value = attr.value.replace(/__name__/g, index);
            }
        });
    });

    return clone.firstElementChild;
};

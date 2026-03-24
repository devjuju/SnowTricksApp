window.smartScroll = (wrapper, element) => {
    element.scrollIntoView({
        behavior: "smooth",
        inline: window.getComputedStyle(wrapper).flexDirection === "row"
            ? "start"
            : "nearest",
        block: window.getComputedStyle(wrapper).flexDirection === "row"
            ? "nearest"
            : "start",
    });
};

window.cloneTemplate = (id, index) => {
    const template = document.getElementById(id);
    if (!template) return null;

    const div = document.createElement("div");
    div.innerHTML = template.innerHTML.replace(/__name__/g, index);

    return div.firstElementChild;
};
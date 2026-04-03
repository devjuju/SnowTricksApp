const toastContainer = document.createElement("div");
toastContainer.className =
    "fixed top-5 right-5 space-y-2 z-[9999]";
document.body.appendChild(toastContainer);

const showToast = (message, type = "error") => {
    const toast = document.createElement("div");

    const base =
        "px-4 py-3 rounded shadow-lg text-white text-sm animate-fade-in transition-all";

    const styles = {
        error: "bg-red-500",
        success: "bg-green-500",
        warning: "bg-yellow-500 text-black",
        info: "bg-blue-500",
    };

    toast.className = `${base} ${styles[type] || styles.error}`;
    toast.textContent = message;

    toastContainer.appendChild(toast);

    // auto remove
    setTimeout(() => {
        toast.style.opacity = "0";
        toast.style.transform = "translateX(20px)";
        setTimeout(() => toast.remove(), 300);
    }, 2500);
};
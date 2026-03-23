/* ============================================================
       API HELPERS
    ============================================================ */
const uploadImage = async (file) => {
    const formData = new FormData();
    formData.append("images[]", file);

    const res = await fetch("/profile/images/temp", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    });

    const data = await res.json();

    if (!res.ok || !data.images?.[0]) {
        throw new Error(data.error || "Erreur upload");
    }

    return data.images[0];
};

const deleteTempImage = async (filename) => {
    await fetch("/profile/images/temp/delete", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({ filename }),
    });
};

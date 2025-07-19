window.updateAvatar = (button) => {
    const url = button.dataset.url;
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/png, image/jpeg"; // Allow only JPG and PNG
    input.multiple = false;
    input.style.width = "0";
    input.style.height = "0";
    input.style.opacity = "0";
    document.body.appendChild(input);
    input.click();

    input.addEventListener("change", () => {
        const file = input.files[0];
        if (!file) return;

        const validationResult = validateFile(file);
        if (!validationResult.valid) {
            toastr.error(validationResult.message);
            input.remove();
            return;
        }

        const formData = new FormData();
        formData.append("avatar", file);

        fetch(url, {
            method: "POST",
            headers: {
                ...settings.headers,
            },
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "200") {

                    toastr.success(data.msg);
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    if (data.jsFunction) {
                        if (typeof window[data.jsFunction] === "function") {
                            if (window[data.jsFunction].length === 0) {
                                window[data.jsFunction]();
                            } else {
                                window[data.jsFunction](...data.parameters);
                            }
                        }
                        return;
                    }
                    return;
                }
                if (data.status === "400") {
                    toastr.error(data.msg);
                    return;
                }
                if (data.status === "500") {
                    toastr.error(data.msg);
                    return;
                }
            })
            .catch((error) => {
                toastr.error(error);
            });

        input.remove();
    });
};
function validateFile(file) {
    const allowedTypes = ["image/jpeg", "image/png"];
    const maxSize = 2 * 1024 * 1024; // 2 MB
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: "Only JPG and PNG files are allowed." };
    }
    if (file.size > maxSize) {
        return { valid: false, message: "File size must be less than 2 MB." };
    }
    return { valid: true };
}

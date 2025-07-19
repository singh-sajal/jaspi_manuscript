/**
 * Global settings object containing various configurations.
 * @type {object}
 */
const settings = {
    spinner: `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="color:white;"></span>`,
    loading: `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right:10px"></span>Loading...`,
    headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
};

/**
 * Fetches content from the specified URL and displays it in the specified modal.
 * @param {string} url - The URL to fetch the content from.
 * @param {string} modalName - The name of the modal to display the content in.
 * @returns {Promise} A promise that resolves when the content is fetched and displayed.
 */
const getContent = (url, modalName) => {
    return new Promise((resolve, reject) => {
        fetch(url, {
            headers: {
                ...settings.headers,
                Accept: "text/html, application/json",
            },
            method: "GET",
        })
            .then((response) => response.text())
            .then((data) => {
                const modal = document.getElementById(`${modalName}`);
                if (modal) $(modal).html(data).modal("show");
                else
                    throw new Error(
                        toastr.error(
                            `Modal with name '${modalName}' not found.`
                        )
                    );

                resolve();
            })
            .catch((error) => {
                reject(error);
            });
    });
};

/**
 * Prompts the user for confirmation before proceeding with a deletion operation.
 * @param {string} url - The URL for the deletion operation.
 * @returns {Promise} A promise that resolves when the user confirms the deletion or cancels.
 */
const confirmDelete = (url, msg = null) => {
    return new Promise((resolve, reject) => {
        let message = msg || "Your record will be deleted permanently!";
        swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                deleteHandler(url)
                    .then(() => resolve())
                    .catch((error) => reject(error));
            } else resolve();
        });
    });
};

/**
 *Performs The updation of status of current data
 * @param {string} url - The URL for the deletion operation.
 * @returns {Promise} A promise that resolves when the user confirms the deletion or cancels.
 */
const toggleStatus = (url) => {
    return new Promise((resolve, reject) => {
        fetch(url, {
            headers: {
                ...settings.headers,
                Accept: "application/json",
            },
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "200") {
                    toastr.options = {
                        positionClass: "toast-top-center",
                    };
                    toastr.success(data.msg);
                    const dataTable = document.getElementById("DataTable");
                    if (dataTable)
                        $(dataTable).DataTable().ajax.reload(null, false);
                    if (
                        window.saarni &&
                        typeof window.saarni.ajaxReload === "function"
                    ) {
                        window.saarni.ajaxReload();
                    }
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
                            return;
                        }
                    }
                    return;
                }

                if (data.status === "500") {
                    toastr.error(data.msg);
                    return;
                }
                throw new Error("Unknown status code");
            })
            .catch((error) => {
                toastr.error(error);
            });
    });
};

/**
 * Performs a delete operation based on the specified URL.
 * @param {string} url - The URL for the delete operation.
 * @returns {Promise} A promise that resolves when the delete operation is completed.
 */
const deleteHandler = (url) => {
    return new Promise((resolve, reject) => {
        fetch(url, {
            headers: settings.headers,
            method: "DELETE",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "200") {
                    toastr.options = {
                        positionClass: "toast-top-center",
                    };
                    toastr.success(data.msg);
                    const dataTable = document.getElementById("DataTable");
                    if (dataTable) {
                        $(dataTable).DataTable().ajax.reload(null, false);
                    }
                    if (
                        window.saarni &&
                        typeof window.saarni.ajaxReload === "function"
                    ) {
                        window.saarni.ajaxReload();
                    }
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                    if (data.jsFunction) {
                        if (typeof window[data.jsFunction] === "function") {
                            if (window[data.jsFunction].length === 0) {
                                window[data.jsFunction]();
                            } else {
                                window[data.jsFunction](...data.parameters);
                            }
                            return;
                        }
                    }
                    resolve();
                } else if (data.status === "500") {
                    toastr.error(data.msg);
                    resolve();
                } else {
                    throw new Error("Unknown status code");
                }
            })
            .catch((error) => {
                toastr.error("Failed to delete the record.");
                reject();
            });
    });
};

/**
 * Handles action button events.
 * @param {Element} target - The target element that triggered the event.
 */
const actionHandler = (target) => {
    const url = target.dataset.url;
    const modalName = target.dataset.modal || "AjaxModal";
    const action = target.dataset.action;
    const originalHTML = target.innerHTML;
    target.disabled = true;
    target.innerHTML = settings.spinner;
    if (action === "delete") {
        const message = target.dataset.message || null;
        confirmDelete(url, message).finally(() => {
            target.disabled = false;
            target.innerHTML = originalHTML;
        });
    } else if (action == "togglestatus")
        toggleStatus(url).finally(() => {
            target.disabled = false;
            target.innerHTML = originalHTML;
        });
    else {
        getContent(url, modalName).finally(() => {
            target.disabled = false;
            target.innerHTML = originalHTML;
        });
    }
};

/**
 * Validates the form and returns whether it is valid or not.
 * @param {HTMLFormElement} f - The form to validate.
 * @returns {boolean} Indicates whether the form is valid or not.
 */

const validateForm = (f) => {
    const fe = f.elements;
    let isValid = true;

    // Validate each form element
    Array.from(fe).forEach((e) => {
        e.classList.remove("validation-error");
        e.classList.remove("validation-success");
        if (
            e.tagName === "BUTTON" &&
            ["submit", "reset", "button"].includes(e.type)
        ) {
            return;
        }
        if (!e.checkValidity()) {
            e.classList.add("validation-error");
            if (e.validity.patternMismatch) {
                markElementAsInvalid(e, e.title);
            } else {
                markElementAsInvalid(
                    e,
                    e.dataset.errorMessage || e.validationMessage
                );
            }
            isValid = false;
        } else {
            markElementAsValid(e);
        }
        if (e.type === "file" && e.dataset.maxSize) {
            let maxSize = parseInt(e.dataset.maxSize, 10);
            maxSize = maxSize * 1024;
            const maxTitle =
                e.dataset.maxTitle ||
                `File size cannot exceed ${maxSize / 1024} KB`;
            let file = e.files[0];
            if (e.files.length > 0) {
                if (file.size > maxSize) {
                    e.classList.remove("validation-success");
                    e.classList.add("validation-error");
                    markElementAsInvalid(e, maxTitle);
                    isValid = false;
                } else {
                    markElementAsValid(e);
                }
            }
        }
        if (e.type === "file" && e.dataset.fileTypes) {
            const allowedFileTypes = e.dataset.fileTypes.split(",");
            if (e.files.length > 0) {
                const fileName = e.files[0].name;
                const fileExtension = fileName.split(".").pop().toLowerCase();
                if (!allowedFileTypes.includes(fileExtension)) {
                    e.classList.remove("validation-success");
                    e.classList.add("validation-error");
                    const fileTypesTitle =
                        e.dataset.fileTypesTitle ||
                        `Invalid file type. Allowed types: ${allowedFileTypes.join(
                            ","
                        )}`;
                    markElementAsInvalid(e, fileTypesTitle);
                    isValid = false;
                } else {
                    markElementAsValid(e);
                }
            }
        }
    });

    return isValid;
};

/**
 * Marks an element as invalid by adding error styles and an error message.
 * @param {HTMLElement} e - The element to mark as invalid.
 * @param {string} errorMsg - The error message to display.
 */
const markElementAsInvalid = (e, errorMsg) => {
    e.classList.add("validation-error");
    const errorMessage = document.querySelector(`[data-error="${e.name}"]`);
    if (!errorMessage)
        e.insertAdjacentHTML(
            "afterend",
            `<span data-error="${e.name}" class="text-danger mt-2">${errorMsg}</span>`
        );
    else errorMessage.textContent = errorMsg;
};

/**
 * Marks an element as valid by adding success styles.
 * @param {HTMLElement} e - The element to mark as valid.
 */
const markElementAsValid = (e) => {
    // const isRequired = e.required;
    // if (isRequired) return;
    e.classList.remove("validation-error");
    e.classList.add("validation-success");
    const errorMessage = document.querySelector(`[data-error="${e.name}"]`);
    if (errorMessage) {
        errorMessage.textContent = "";
    }
};

const intializeQuill = (modal) => {
    const editor = modal.querySelector("#editor");
    if (!editor) {
        return;
    }
    const quill = new Quill(editor, {
        modules: {
            toolbar: [
                [
                    {
                        font: [],
                    },
                    {
                        size: [],
                    },
                ],
                ["bold", "italic", "underline"],
                [
                    {
                        color: [],
                    },
                    {
                        background: [],
                    },
                ],
                [
                    {
                        script: "super",
                    },
                    {
                        script: "sub",
                    },
                ],
                [
                    {
                        header: [!1, 1, 2, 3, 4, 5, 6],
                    },
                ],
                [
                    {
                        list: "ordered",
                    },
                    {
                        list: "bullet",
                    },
                    {
                        indent: "-1",
                    },
                    {
                        indent: "+1",
                    },
                ],
                [
                    {
                        align: [],
                    },
                ],
            ],
        },
        theme: "snow",
    });

    const descriptionInput = modal.querySelector("#description");
    // if (descriptionInput) {
    //     descriptionInput.value = quill.root.innerHTML;
    //     quill.on("text-change", (delta, oldDelta, source) => {
    //         descriptionInput.value = quill.root.innerHTML;
    //     });
    // }
    if (descriptionInput) {
        const isEditorEmpty = () => {
            const text = quill.getText().trim();
            return text.length === 0;
        };

        const updateDescriptionInput = () => {
            if (isEditorEmpty()) {
                descriptionInput.value = "";
            } else {
                descriptionInput.value = quill.root.innerHTML;
            }
        };

        // Initialize the description input with the current editor content
        updateDescriptionInput();

        quill.on("text-change", (delta, oldDelta, source) => {
            updateDescriptionInput();
        });
    }
};

// Quill on page
const intializeQuillOnPage = () => {
    const editor = document.getElementById("editor");
    if (!editor) {
        return;
    }
    const quill = new Quill(editor, {
        modules: {
            toolbar: [
                [
                    {
                        font: [],
                    },
                    {
                        size: [],
                    },
                ],
                ["bold", "italic", "underline"],
                [
                    {
                        color: [],
                    },
                    {
                        background: [],
                    },
                ],
                [
                    {
                        script: "super",
                    },
                    {
                        script: "sub",
                    },
                ],
                [
                    {
                        header: [!1, 1, 2, 3, 4, 5, 6],
                    },
                ],
                [
                    {
                        list: "ordered",
                    },
                    {
                        list: "bullet",
                    },
                    {
                        indent: "-1",
                    },
                    {
                        indent: "+1",
                    },
                ],
                [
                    {
                        align: [],
                    },
                ],
            ],
        },
        theme: "snow",
    });
    const parent = editor.parentNode;
    const descriptionInput = parent.querySelector("#description");
    // if (descriptionInput) {
    //     descriptionInput.value = quill.root.innerHTML;
    //     quill.on("text-change", (delta, oldDelta, source) => {
    //         descriptionInput.value = quill.root.innerHTML;
    //     });
    // }
    if (descriptionInput) {
        const isEditorEmpty = () => {
            const text = quill.getText().trim();
            return text.length === 0;
        };

        const updateDescriptionInput = () => {
            if (isEditorEmpty()) {
                descriptionInput.value = "";
            } else {
                descriptionInput.value = quill.root.innerHTML;
            }
        };

        // Initialize the description input with the current editor content
        updateDescriptionInput();

        quill.on("text-change", (delta, oldDelta, source) => {
            updateDescriptionInput();
        });
    }
};
const handlePasswordBox = (e) => {
    let icon = e.querySelector("i");
    let input = e.parentElement.querySelector(".passbox");
    icon.classList.toggle("ri-eye-off-fill");
    input.type = input.type === "password" ? "text" : "password";
};

/**
 * Generate a thumbnail preview for a selected file input element.
 *
 * @param {HTMLInputElement} input - The file input element for which to generate a thumbnail.
 */
const generateThumb = (input) => {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const container = input.closest("div");
            const preview = container.querySelector(".thumbnail");
            if (preview) {
                preview.src = e.target.result;
            }
        };

        reader.readAsDataURL(input.files[0]);
    }
};
/**
 * Handles form submission events.
 * @param {Event} e - The form submission event.
 */
document.addEventListener("submit", function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonHTML = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = settings.loading;
    const isValid = validateForm(form); // Implement the validateForm function
    if (!isValid) {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonHTML;
        return;
    }
    const noAjax = form.hasAttribute("noajax");
    if (noAjax) {
        form.submit();
    } else {
        fetch(form.action, {
            headers: settings.headers,
            method: form.method,
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "200") {
                    toastr.options = {
                        positionClass: "toast-top-center",
                    };
                    toastr.success(data.msg);
                    form.reset();
                    const modal = form.closest(".modal");
                    const dataTable = document.getElementById("DataTable");
                    if (modal) $(modal).modal("hide");
                    if (dataTable) {
                        $(dataTable).DataTable().ajax.reload(null, false);
                    }
                    if (
                        window.saarni &&
                        typeof window.saarni.ajaxReload === "function"
                    ) {
                        window.saarni.ajaxReload();
                    }
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
                    }
                    return;
                }
                if (data.status === "400") {
                    const errors = data.errors;
                    const errorElements = form.querySelectorAll("[data-error]");
                    errorElements.forEach(
                        (element) => (element.textContent = "")
                    );
                    const errorDisplayStyle =
                        form.getAttribute("data-error-style");
                    Object.keys(errors).forEach((key) => {
                        const error = errors[key];
                        if (errorDisplayStyle === "default") {
                            const errorElement = form.querySelector(
                                `[data-error="${key}"]`
                            );
                            if (errorElement) errorElement.textContent = error;
                            else toastr.error(error);
                        } else {
                            toastr.error(error);
                        }
                    });
                    return;
                }
                if (data.status === "500") {
                    toastr.error(data.msg);
                    return;
                }
                throw new Error("Unknown status code");
            })
            .catch((error) => {
                toastr.error(error);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHTML;
            });
    }
});

/**
 * Handles form submission events.
 * @param {Event} e - The form submission event.
 */
document.addEventListener("click", (e) => {
    let target = e.target;
    if (target.matches(".actionHandler, .actionHandler *")) {
        target = target.closest(".actionHandler");
        const handlerName = target.dataset.handler || null;
        if (handlerName) {
            if (typeof window[handlerName] === "function") {
                window[handlerName](target);
            } else {
                toastr.error(`Function with name '${handlerName}' not found.`);
            }
        } else actionHandler(target);
    }
    if (target.matches("button.password-addon, button.password-addon *")) {
        target = target.closest("button.password-addon");
        handlePasswordBox(target);
    }
});

/**
 * Handles Change events on Dependent Dropdown.
 * @param {Event} e - The click event.
 */

document.addEventListener("change", function (event) {
    const target = event.target;
    if (target.classList.contains("dependent-dropdown")) {
        const parentId = target.value;
        const dataUrl = target.dataset.url;
        const ch = document.getElementById(target.dataset.dependentChild);
        if (parentId) {
            fetch(`${dataUrl}?data_id=${parentId}`, {
                method: "GET",
                headers: {
                    ...settings.headers,
                    "Content-Type": "application/json",
                },
            })
                .then((response) => response.json())
                .then((res) => {
                    if (res.status === "200") {
                        ch.innerHTML =
                            '<option value="">Please Select One</option>';
                        Object.entries(res.data).forEach(([key, value]) => {
                            const option = document.createElement("option");
                            option.value = key;
                            option.textContent = value;
                            ch.appendChild(option);
                        });
                        if (res.jsFunction) {
                            if (typeof window[res.jsFunction] === "function") {
                                if (window[res.jsFunction].length === 0) {
                                    window[res.jsFunction]();
                                } else {
                                    window[res.jsFunction](...res.parameters);
                                }
                                return;
                            }
                        }
                    } else {
                        toastr["warning"](data.msg, "Opps..!");
                    }
                })
                .catch((error) => console.error(error));
        } else {
            ch.innerHTML = '<option value="">Please Select One</option>';
        }
    }
    if (target && target.classList.contains("has-thumbnail")) {
        generateThumb(target);
    }
});

document.addEventListener("input", function (e) {
    const target = e.target;

    if (
        target.tagName === "INPUT" &&
        target.getAttribute("inputmode") === "numeric"
    ) {
        target.value = target.value.replace(/\D/g, ""); // Remove non-digits
    }
});
document.addEventListener("show.bs.modal", (e) => {
    console.log("hello");
    const target = e.target;
    if (target.classList.contains("modal")) {
        intializeQuill(target);
    }
});

document.addEventListener("DOMContentLoaded", () => {
    intializeQuillOnPage();
});

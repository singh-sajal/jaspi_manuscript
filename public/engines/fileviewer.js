window.openInEditMode = (target) => {
    const url = new URL(target.dataset.url);
    swal({
        title: "Are you sure you want to proceed?",
        text: "You are about to open this Excel file in edit mode. Please note that if you save the file, certain elements such as charts, formatting, and advanced features may be lost or altered",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            const newTab = window.open(url, "_blank");
            if (!newTab) {
                alert("Popup blocked! Please allow popups for this site.");
            }
        } else {
            button.disabled = false;
            button.innerHTML = originalHTML;
        }
    });
};

window.openFilePreview = (button) => {
    const url = new URL(button.dataset.url);
    const fileUrl = button.dataset.fileUrl;
    const frameType = button.dataset.frame || "modal";
    url.searchParams.set("fileUrl", fileUrl);
    url.searchParams.set("frameOption", frameType);
    button.disabled = true;
    const originalButtonHTML = button.innerHTML;
    button.innerHTML = settings.spinner;

    fetch(url, {
        method: "GET",
        headers: {
            ...settings.headers,
            Accept: "text/html, application/json",
        },
    })
        .then((response) => response.text())
        .then((data) => {
            if ("500" === data.status) {
                toastr.error(data.msg);
                return;
            }

            if (frameType === "offcanvas") {
                const offcanvas = document.getElementById("FileOffcanvas");
                if (offcanvas) {
                    $(offcanvas).html(data);
                    const bsOffcanvas = new bootstrap.Offcanvas(offcanvas);
                    bsOffcanvas.show();
                }
            } else {
                const modal = document.getElementById(`AjaxModal`);
                if (modal) $(modal).html(data).modal("show");
            }
        })
        .catch((error) => {
            toastr.error(error);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalButtonHTML;
        });
};

function setupZoomControls(container) {
    let zoomLevel = 1;
    const iframe = container.querySelector("#fileViewerIframe");
    const zoomInBtn = container.querySelector("#zoom-in-btn");
    const zoomOutBtn = container.querySelector("#zoom-out-btn");

    if (iframe) {
        function zoomIframeContent(zoom) {
            if (iframe.contentWindow && iframe.contentWindow.document) {
                iframe.contentWindow.document.body.style.zoom = zoom;
            }
        }

        zoomInBtn?.addEventListener("click", function () {
            zoomLevel += 0.1;
            zoomIframeContent(zoomLevel);
        });

        zoomOutBtn?.addEventListener("click", function () {
            if (zoomLevel > 0.5) {
                zoomLevel -= 0.1;
                zoomIframeContent(zoomLevel);
            }
        });

        iframe.onload = function () {
            zoomIframeContent(zoomLevel);
        };
    }
}

document.addEventListener("shown.bs.modal", (e) => {
    setupZoomControls(e.target);
});

document.addEventListener("shown.bs.offcanvas", (e) => {
    setupZoomControls(e.target);
});

// document.addEventListener("shown.bs.modal", (e) => {
//     const targetModal = e.target;
//     let zoomLevel = 1;
//     const iframe = targetModal.querySelector("#fileViewerIframe");
//     const zoomInBtn = targetModal.querySelector("#zoom-in-btn");
//     const zoomOutBtn = targetModal.querySelector("#zoom-out-btn");
//     if (iframe) {
//         function zoomIframeContent(zoom) {
//             if (iframe.contentWindow && iframe.contentWindow.document) {
//                 iframe.contentWindow.document.body.style.zoom = zoom;
//             }
//         }

//         zoomInBtn.addEventListener("click", function () {
//             zoomLevel += 0.1;
//             zoomIframeContent(zoomLevel);
//         });

//         zoomOutBtn.addEventListener("click", function () {
//             if (zoomLevel > 0.5) {
//                 zoomLevel -= 0.1;
//                 zoomIframeContent(zoomLevel);
//             }
//         });

//         // Apply zoom once iframe is loaded
//         iframe.onload = function () {
//             zoomIframeContent(zoomLevel);
//         };
//     }
// });

// --------------Handling the refresh of the window once the window is focused and editing is complted
// function setRefreshFlag() {
//     localStorage.setItem("shouldRefresh", "true");
// }
// // ---------------Handling the refresh of the window once the window is focused and editing is complted
// window.addEventListener("focus", function () {
//     if (localStorage.getItem("refreshParent") === "true") {
//         localStorage.removeItem("refreshParent"); // Remove flag
//         location.reload(); // Reload page
//     }
// });

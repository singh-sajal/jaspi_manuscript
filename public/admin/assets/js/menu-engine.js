document.addEventListener("DOMContentLoaded", () => {
    const currentUrl = document.URL;
    const menuItems = document.querySelectorAll(".navbar-nav a.nav-link");
    menuItems.forEach((menuItem) => {
        const href = menuItem.getAttribute("href");
        if (href == currentUrl) {
            menuItem.classList.add("active");
            const parent = menuItem.closest("div");
            const top_parent = $(parent).parent().closest("div");
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const currentUrl = new URL(document.URL);
    const baseUrl = currentUrl.origin + currentUrl.pathname;

    const links = document.querySelectorAll(".navbar-nav a.nav-link");

    links.forEach((link) => {
        const href = link.getAttribute("href");

        if (href === baseUrl) {
            link.classList.add("active");
            const parent = link.closest("div");
            const topParent = parent?.parentElement.closest("div");

            if (parent?.classList.contains("menu-dropdown") && topParent?.classList.contains("menu-dropdown")) {
                topParent.classList.add("show");
                parent.classList.add("show");
                const previousSiblingLink = topParent.previousElementSibling;
                if (previousSiblingLink && previousSiblingLink.tagName === "A") {
                    previousSiblingLink.setAttribute("aria-expanded", "true");
                }
            } else {
                parent?.classList.add("show");
                const previousSiblingLink = parent.previousElementSibling;
                if (previousSiblingLink && previousSiblingLink.tagName === "A") {
                    previousSiblingLink.setAttribute("aria-expanded", "true");
                }
            }
        }
    });
});

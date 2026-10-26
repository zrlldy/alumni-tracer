const landingPage = document.querySelector(".landing-page");

if (landingPage) {
    const menuButton = landingPage.querySelector("[data-landing-menu]");
    const navigation = landingPage.querySelector("#landing-mobile-navigation");
    const menuLabel = landingPage.querySelector("[data-landing-menu-label]");
    const themeButton = landingPage.querySelector("[data-landing-theme]");
    const preferredTheme = window.matchMedia("(prefers-color-scheme: dark)");

    const closeMenu = () => {
        navigation.hidden = true;
        menuButton.setAttribute("aria-expanded", "false");
        menuLabel.textContent = "Menu";
    };

    closeMenu();
    menuButton.hidden = false;
    menuButton.addEventListener("click", () => {
        const expanded = menuButton.getAttribute("aria-expanded") !== "true";
        navigation.hidden = !expanded;
        menuButton.setAttribute("aria-expanded", String(expanded));
        menuLabel.textContent = expanded ? "Close" : "Menu";
    });

    navigation.addEventListener("click", (event) => {
        if (event.target.closest("a")) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !navigation.hidden) {
            closeMenu();
            menuButton.focus();
        }
    });

    window
        .matchMedia("(min-width: 64rem)")
        .addEventListener("change", (event) => {
            if (event.matches) {
                closeMenu();
            }
        });

    const applyTheme = (dark) => {
        landingPage.dataset.theme = dark ? "dark" : "light";
        themeButton.setAttribute("aria-pressed", String(dark));
        themeButton.setAttribute(
            "aria-label",
            `Switch to ${dark ? "light" : "dark"} theme`,
        );
    };

    applyTheme(preferredTheme.matches);
    themeButton.hidden = false;
    preferredTheme.addEventListener("change", (event) =>
        applyTheme(event.matches),
    );
    themeButton.addEventListener("click", () =>
        applyTheme(landingPage.dataset.theme !== "dark"),
    );

    if ("IntersectionObserver" in window && "animate" in Element.prototype) {
        const reducedMotion = window.matchMedia(
            "(prefers-reduced-motion: reduce)",
        );
        const sections = new Map(
            [
                ...landingPage.querySelectorAll(
                    [
                        ".landing-hero-layout",
                        ".landing-tool-index",
                        ".landing-record-section",
                        ".landing-tracing-layout",
                        ".landing-report-section",
                        ".landing-workflow-layout",
                        ".landing-questions-section",
                        ".landing-footer-top",
                        ".landing-footer-bottom",
                    ].join(", "),
                ),
            ].map((section) => [section, { visible: false, animation: null }]),
        );

        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    const state = sections.get(entry.target);

                    if (
                        entry.isIntersecting &&
                        !state.visible &&
                        !reducedMotion.matches &&
                        !entry.target.contains(document.activeElement)
                    ) {
                        state.animation?.cancel();
                        state.animation = entry.target.animate(
                            [
                                {
                                    opacity: 0.35,
                                    transform: `translateY(${entry.boundingClientRect.top < 0 ? -20 : 20}px)`,
                                },
                                { opacity: 1, transform: "translateY(0)" },
                            ],
                            {
                                duration: 650,
                                easing: "cubic-bezier(0.2, 0.7, 0.3, 1)",
                            },
                        );
                    } else if (!entry.isIntersecting) {
                        state.animation?.cancel();
                        state.animation = null;
                    }

                    state.visible = entry.isIntersecting;
                }
            },
            { rootMargin: "0px 0px -32px 0px" },
        );

        const updateScrollMotion = () => {
            observer.disconnect();

            for (const [section, state] of sections) {
                state.animation?.cancel();
                state.animation = null;

                const bounds = section.getBoundingClientRect();
                state.visible =
                    bounds.top < window.innerHeight - 32 && bounds.bottom > 0;

                if (!reducedMotion.matches) {
                    observer.observe(section);
                }
            }
        };

        landingPage.addEventListener("focusin", (event) => {
            for (const [section, state] of sections) {
                if (section.contains(event.target)) {
                    state.animation?.cancel();
                    state.animation = null;
                }
            }
        });

        reducedMotion.addEventListener("change", updateScrollMotion);
        updateScrollMotion();
    }
}

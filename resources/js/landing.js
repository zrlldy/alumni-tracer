import "./scrollcraft";

const landingPage = document.querySelector(".landing-page");

if (landingPage) {
    const menuButton = landingPage.querySelector("[data-landing-menu]");
    const navigation = landingPage.querySelector("#landing-mobile-navigation");
    const menuLabel = landingPage.querySelector("[data-landing-menu-label]");
    const themeButton = landingPage.querySelector("[data-landing-theme]");
    const preferredTheme = window.matchMedia("(prefers-color-scheme: dark)");
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const entrance = landingPage.querySelector("[data-landing-loader]");
    const entranceStarted = performance.now();
    let entranceFinished = false;
    let entranceAnimation;
    let entranceTimer;
    let entranceDeadline;

    const finishEntrance = () => {
        entranceFinished = true;
        clearTimeout(entranceTimer);
        clearTimeout(entranceDeadline);
        entrance.hidden = true;
        landingPage.classList.remove("is-loading");
        for (const eventName of [
            "keydown",
            "pointerdown",
            "wheel",
            "touchstart",
        ]) {
            window.removeEventListener(eventName, skipEntrance);
        }
    };
    const dismissEntrance = (immediate = false) => {
        if (entranceFinished) {
            return;
        }
        if (immediate || reducedMotion.matches) {
            entranceAnimation?.cancel();
            finishEntrance();
        } else if (!entranceAnimation) {
            entranceAnimation = entrance.animate(
                [{ opacity: 1 }, { opacity: 0 }],
                {
                    duration: 360,
                    easing: "ease-out",
                    fill: "forwards",
                },
            );
            entranceAnimation.finished.then(finishEntrance, finishEntrance);
        }
    };
    const skipEntrance = () => dismissEntrance(true);

    if (reducedMotion.matches || location.hash || performance.now() > 2500) {
        finishEntrance();
    } else {
        landingPage.classList.add("is-loading");
        for (const eventName of [
            "keydown",
            "pointerdown",
            "wheel",
            "touchstart",
        ]) {
            window.addEventListener(eventName, skipEntrance, { passive: true });
        }
        entranceDeadline = setTimeout(() => dismissEntrance(), 1800);
        const entranceImages = [
            ...landingPage.querySelectorAll(
                "[data-landing-loader] img, .hero-graduates img",
            ),
        ];
        Promise.allSettled([
            document.fonts.ready,
            ...entranceImages.map((image) => image.decode()),
        ]).then(() => {
            if (!entranceFinished && !entranceAnimation) {
                entranceTimer = setTimeout(
                    () => dismissEntrance(),
                    Math.max(0, 850 - (performance.now() - entranceStarted)),
                );
            }
        });
    }
    reducedMotion.addEventListener("change", () => {
        if (reducedMotion.matches) {
            dismissEntrance(true);
        }
    });
    window.addEventListener("pageshow", (event) => {
        if (event.persisted) {
            dismissEntrance(true);
        }
    });

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

    if (!reducedMotion.matches) {
        landingPage
            .querySelectorAll("[data-landing-enter]")
            .forEach((element) => {
                element.setAttribute("data-sc-in", "");
            });
    }
    let scrollCraft = window.ScrollCraft.mount(landingPage);
    reducedMotion.addEventListener("change", () => {
        scrollCraft?.destroy();
        landingPage
            .querySelectorAll("[data-sc-in]")
            .forEach((element) => element.classList.add("sc-in"));
        const motionElements = landingPage.querySelectorAll(
            "[data-sc-cue], [data-sc-reveal], .sc-split__i",
        );
        motionElements.forEach((element) => {
            element.style.opacity = reducedMotion.matches ? "1" : "";
            element.style.transform = reducedMotion.matches ? "none" : "";
            element.style.clipPath = reducedMotion.matches ? "none" : "";
        });
        scrollCraft = reducedMotion.matches
            ? null
            : window.ScrollCraft.mount(landingPage);
    });
    landingPage.addEventListener("focusin", (event) => {
        event.target.closest("[data-sc-in]")?.classList.add("sc-in");
    });

    const chapters = {
        career: {
            path: "M72 180C190 180 200 72 320 72S450 72 566 72",
            description:
                "Employment status and tracing notes help your school understand how graduate careers develop.",
            summary: "Your career journey. Your school connection.",
        },
        study: {
            path: "M72 180H566",
            description:
                "Education history can sit alongside academic and employment details in the alumni record.",
            summary:
                "Your learning continues. Your school connection does too.",
        },
        community: {
            path: "M72 180C190 180 200 288 320 288S450 288 566 288",
            description:
                "Current contact information helps the alumni team follow up and keep records useful.",
            summary: "Different paths. A community you still belong to.",
        },
    };
    const chapterOptions = landingPage.querySelector("[data-chapter-options]");
    const connectionThread = landingPage.querySelector(
        "[data-connection-thread]",
    );
    const connectionDescription = landingPage.querySelector(
        "[data-connection-description]",
    );
    const chapterSummary = landingPage.querySelector("[data-chapter-summary]");
    let threadAnimation;
    chapterOptions.disabled = false;
    chapterOptions.addEventListener("change", (event) => {
        const chapter = chapters[event.target.value];
        if (!chapter) {
            return;
        }
        threadAnimation?.cancel();
        connectionThread.setAttribute("d", chapter.path);
        connectionDescription.textContent = chapter.description;
        chapterSummary.textContent = chapter.summary;
        landingPage
            .querySelectorAll("[data-connection-node]")
            .forEach((node) => {
                node.classList.toggle(
                    "is-selected",
                    node.dataset.connectionNode === event.target.value,
                );
            });
        if (!reducedMotion.matches) {
            threadAnimation = connectionThread.animate(
                [
                    { opacity: 0.3, clipPath: "inset(0 100% 0 0)" },
                    { opacity: 1, clipPath: "inset(0)" },
                ],
                { duration: 550, easing: "cubic-bezier(0.23, 1, 0.32, 1)" },
            );
        }
    });
    reducedMotion.addEventListener("change", () => threadAnimation?.cancel());

    const hero = landingPage.querySelector("[data-hero-scene]");
    const finePointer = window.matchMedia("(hover: hover) and (pointer: fine)");
    const phone = window.matchMedia("(max-width: 767px)");
    const layerSettings = {
        school: { scroll: -60, pointer: 5, turn: -3 },
        graduates: { scroll: -28, pointer: 9, turn: 2 },
        cap: { scroll: -115, pointer: 16, turn: 12 },
        diploma: { scroll: -82, pointer: 12, turn: -6 },
    };
    const layers = [...hero.querySelectorAll("[data-hero-layer]")];
    let targetX = 0;
    let targetY = 0;
    let pointerX = 0;
    let pointerY = 0;
    let heroVisible = true;
    let frame = 0;

    const renderHero = () => {
        frame = 0;
        const bounds = hero.getBoundingClientRect();
        const progress = Math.min(
            1,
            Math.max(0, -bounds.top / Math.max(bounds.height, 1)),
        );
        const motionScale = reducedMotion.matches
            ? 0
            : phone.matches
              ? 0.25
              : 1;
        pointerX += (targetX - pointerX) * 0.12;
        pointerY += (targetY - pointerY) * 0.12;
        const rendered = {};
        for (const layer of layers) {
            const settings = layerSettings[layer.dataset.heroLayer];
            const x = pointerX * settings.pointer * motionScale;
            const y =
                (progress * settings.scroll + pointerY * settings.pointer) *
                motionScale;
            const turn = progress * settings.turn * motionScale;
            layer.style.setProperty("--layer-x", `${x.toFixed(2)}px`);
            layer.style.setProperty("--layer-y", `${y.toFixed(2)}px`);
            layer.style.setProperty("--layer-turn", `${turn.toFixed(2)}deg`);
            rendered[layer.dataset.heroLayer] = [
                Math.round(x),
                Math.round(y),
                Math.round(turn),
            ];
        }
        if (heroVisible && !reducedMotion.matches) {
            hero.dataset.scVerifyState = JSON.stringify(rendered);
        } else {
            delete hero.dataset.scVerifyState;
        }
        if (
            heroVisible &&
            !document.hidden &&
            (Math.abs(targetX - pointerX) > 0.005 ||
                Math.abs(targetY - pointerY) > 0.005)
        ) {
            frame = requestAnimationFrame(renderHero);
        }
    };
    const scheduleHero = () => {
        if (!frame && heroVisible && !document.hidden) {
            frame = requestAnimationFrame(renderHero);
        }
    };
    hero.addEventListener(
        "pointermove",
        (event) => {
            if (!finePointer.matches || reducedMotion.matches) {
                return;
            }
            const bounds = hero.getBoundingClientRect();
            targetX = (event.clientX - bounds.left) / bounds.width - 0.5;
            targetY = (event.clientY - bounds.top) / bounds.height - 0.5;
            scheduleHero();
        },
        { passive: true },
    );
    hero.addEventListener("pointerleave", () => {
        targetX = 0;
        targetY = 0;
        scheduleHero();
    });
    window.addEventListener("scroll", scheduleHero, { passive: true });
    window.addEventListener("resize", scheduleHero, { passive: true });
    document.addEventListener("visibilitychange", scheduleHero);
    reducedMotion.addEventListener("change", () => {
        targetX = 0;
        targetY = 0;
        pointerX = 0;
        pointerY = 0;
        renderHero();
    });
    if ("IntersectionObserver" in window) {
        new IntersectionObserver(([entry]) => {
            heroVisible = entry.isIntersecting;
            if (heroVisible) {
                scheduleHero();
            } else {
                delete hero.dataset.scVerifyState;
            }
        }).observe(hero);
    }
    scheduleHero();
}

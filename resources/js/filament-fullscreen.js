
(() => {
    const getButton = () =>
        document.getElementById('fullscreen-toggle');

    const getIcon = () =>
        document.getElementById('fullscreen-icon');

    // Maximize icon: enter fullscreen
    const maximizeIcon = `
        <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
        <path d="M21 8V5a2 2 0 0 0-2-2h-3"/>
        <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
        <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
    `;

    // Minimize icon: exit fullscreen
    const minimizeIcon = `
        <path d="M8 3v5H3"/>
        <path d="M21 8h-5V3"/>
        <path d="M3 16h5v5"/>
        <path d="M16 21v-5h5"/>
    `;

    /**
     * Update icon based on actual fullscreen state.
     */
    function updateFullscreenButton() {
        const button = getButton();
        const icon = getIcon();

        if (!button || !icon) {
            return;
        }

        const isFullscreen = !!document.fullscreenElement;

        // Change SVG path directly.
        icon.innerHTML = isFullscreen
            ? minimizeIcon
            : maximizeIcon;

        // Update tooltip.
        const label = isFullscreen
            ? 'Exit Fullscreen'
            : 'Enter Fullscreen';

        button.title = label;
        button.setAttribute('aria-label', label);
        button.setAttribute('aria-pressed', String(isFullscreen));
    }

    /**
     * Toggle fullscreen.
     */
    window.toggleFullscreen = async function () {
        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
            } else {
                await document.documentElement.requestFullscreen();
            }
        } catch (error) {
            console.error('Fullscreen request failed:', error);
        } finally {
            updateFullscreenButton();
        }
    };

    // Listen for browser fullscreen changes.
    document.addEventListener(
        'fullscreenchange',
        updateFullscreenButton
    );

    // Handle fullscreen errors.
    document.addEventListener(
        'fullscreenerror',
        updateFullscreenButton
    );

    // Handle page restoration.
    window.addEventListener(
        'pageshow',
        updateFullscreenButton
    );

    // Initial state.
    updateFullscreenButton();
})();

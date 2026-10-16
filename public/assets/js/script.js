function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    // Toggle sidebar translation
    sidebar.classList.toggle("-translate-x-full");

    // Toggle overlay visibility
    if (overlay.classList.contains("hidden")) {
        overlay.classList.remove("hidden");
        // Small delay to allow display:block to apply before animating opacity
        setTimeout(() => {
            overlay.classList.add("opacity-100");
            overlay.classList.remove("opacity-0");
        }, 10);
    } else {
        overlay.classList.remove("opacity-100");
        overlay.classList.add("opacity-0");
        // Wait for animation to finish before hiding element
        setTimeout(() => {
            overlay.classList.add("hidden");
        }, 300);
    }
}

function toggleModal(modalID) {
    const modal = document.getElementById(modalID);
    if (modal.classList.contains("hidden")) {
        modal.classList.remove("hidden");
        // Cegah scroll pada body saat modal terbuka
        document.body.style.overflow = "hidden";
    } else {
        modal.classList.add("hidden");
        // Kembalikan scroll pada body saat modal ditutup
        document.body.style.overflow = "auto";
    }
}

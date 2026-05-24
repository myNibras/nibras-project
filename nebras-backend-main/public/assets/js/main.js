$('[data-plugins="dropify"]').dropify({
	messages: {
		default: "Drag and drop a file here or click",
		replace: "Drag and drop or click to replace",
		remove: "Remove",
		error: "Ooops, something wrong appended."
	},
	error: {
		fileSize: "The file size is too big (1M max)."
	}
});
function togglePassword(fieldId, el) {
    const input = document.getElementById(fieldId);
    const icon = el.querySelector("i");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}
document.addEventListener("DOMContentLoaded", function () {
    const currentUrl = window.location.href.split(/[?#]/)[0]; // ignore query/hash
    const links = document.querySelectorAll("#sidenav-main .nav-link[href]");

    links.forEach(link => {
        if (link.classList.contains("menu")) {
            return; // just skip this iteration
        }
        const linkUrl = link.href.split(/[?#]/)[0];
        
        // Exact match only
        if (currentUrl === linkUrl) {
            link.classList.add("active");

            const parentLi = link.closest(".nav-item");
            if (parentLi) {
                parentLi.classList.add("active-item");
            }

            // Check if inside a collapse menu
            const collapseMenu = link.closest(".collapse");
            if (collapseMenu) {
                // Open the collapse
                collapseMenu.classList.add("show");

                // Add active to the toggler
                const toggler = document.querySelector(
                    `[data-bs-toggle="collapse"][href="#${collapseMenu.id}"], 
                     [data-bs-toggle="collapse"][data-bs-target="#${collapseMenu.id}"]`
                );
                if (toggler) {
                    toggler.classList.add("active");
                }
            }
        }
    });
});

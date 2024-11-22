document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const dumpsMessage = urlParams.get('dumps_message'); // New dumps message parameter
    const section = urlParams.get('section') || 'news';
    const redirect = urlParams.get('redirect');

    if (message) {
        showMessage(decodeURIComponent(message));
        showSection(section);
        scrollToSection(section);

        if (redirect) {
            setTimeout(() => {
                showSection(redirect);
                scrollToSection(redirect);
            }, 3000);
        }

        window.history.replaceState(null, null, window.location.pathname);
    }

    // Check for the dumps-specific message
    if (dumpsMessage) {
        showDumpsMessage(decodeURIComponent(dumpsMessage));
        showSection(section);
        scrollToSection(section);

        if (redirect) {
            setTimeout(() => {
                showSection(redirect);
                scrollToSection(redirect);
            }, 3000);
        }

        window.history.replaceState(null, null, window.location.pathname);
    }
});

function showDumpsMessage(message) {
    const dumpsMessageBox = document.getElementById('dumpsMessageBox');
    const dumpsMessageText = document.getElementById('dumpsMessageText');
    const dumpsOverlay = document.getElementById('dumpsOverlay');

    dumpsMessageText.textContent = message;
    dumpsMessageBox.style.display = 'block';
    dumpsOverlay.style.display = 'block';

    // Lock scroll position while message is displayed
    const scrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';

    // Auto-hide message and re-enable scrolling after 3 seconds
    setTimeout(() => {
        dumpsMessageBox.style.display = 'none';
        dumpsOverlay.style.display = 'none';
        document.body.style.position = '';
        document.body.style.top = '';
        window.scrollTo(0, scrollY);
    }, 3000);
}

// Example call to show the dumps message
// showDumpsMessage("Dumps purchase successful!");

function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.style.display = 'none';
    });

    // Show the specified section
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.display = 'block';
    } else {
        console.warn(`Section ${sectionId} not found, defaulting to 'news'`);
        document.getElementById('news').style.display = 'block';
    }

    // Update the active link in the sidebar
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.classList.remove('active');
    });

    const activeLink = document.querySelector(`[href="#${sectionId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    // Update the hash in the URL to reflect the active section
    window.history.replaceState(null, null, `#${sectionId}`);
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Listen for hash changes to handle manual section changes
window.addEventListener('hashchange', () => {
    const sectionId = window.location.hash.substring(1);
    if (sectionId) {
        showSection(sectionId);
    }
});

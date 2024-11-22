function showToolMessage(message) {
    const toolMessageBox = document.getElementById('toolMessageBox');
    const toolMessageText = document.getElementById('toolMessageText');
    const toolOverlay = document.getElementById('toolOverlay');

    toolMessageText.textContent = message;
    toolMessageBox.style.display = 'block';
    toolOverlay.style.display = 'block';

    // Lock scroll position while message is displayed
    const scrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';

    // Auto-hide message and unlock scroll after 3 seconds
    setTimeout(() => {
        toolMessageBox.style.display = 'none';
        toolOverlay.style.display = 'none';
        document.body.style.position = '';
        document.body.style.top = '';
        window.scrollTo(0, scrollY);
    }, 3000);
}

function showMessage(message) {
    const messageBox = document.getElementById('messageBox');
    const messageText = document.getElementById('messageText');
    const overlay = document.getElementById('overlay');

    messageText.textContent = message;
    messageBox.style.display = 'block';
    overlay.style.display = 'block';

    // Lock scroll position while message is displayed
    const scrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';

    // Auto-hide message and unlock scroll after 3 seconds
    setTimeout(() => {
        messageBox.style.display = 'none';
        overlay.style.display = 'none';
        document.body.style.position = '';
        document.body.style.top = '';
        window.scrollTo(0, scrollY);
    }, 3000);
}

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

    // Auto-hide message and unlock scroll after 3 seconds
    setTimeout(() => {
        dumpsMessageBox.style.display = 'none';
        dumpsOverlay.style.display = 'none';
        document.body.style.position = '';
        document.body.style.top = '';
        window.scrollTo(0, scrollY);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const dumpsMessage = urlParams.get('dumps_message');
    const toolMessage = urlParams.get('tool_message');
    const section = urlParams.get('section') || 'news';
    const redirect = urlParams.get('redirect');

    // Check for the general message
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

    // Check for the tool-specific message
    if (toolMessage) {
        showToolMessage(decodeURIComponent(toolMessage));
        showSection(section);
        scrollToSection(section);

        if (redirect === 'my-orders') {
            setTimeout(() => {
                showSection(redirect);
                scrollToSection(redirect);
            }, 3000);
        }
        window.history.replaceState(null, null, window.location.pathname);
    }
});

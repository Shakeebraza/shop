    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        if (message) {
            showMessage(decodeURIComponent(message));

            // Check if the message is a success message related to purchasing a card
            if (message.includes("Purchase successful!")) {
                // Scroll to and activate the #credit-cards section
                showSection('credit-cards');
                scrollToSection('credit-cards');
                
                // After the success message disappears, redirect to #my-cards and display that section
                setTimeout(() => {
                    window.location.hash = '#my-cards';
                    showSection('my-cards'); // Explicitly show the #my-cards section
                }, 3000); // Redirect after 3 seconds
            }

            // Clear the message from the URL after displaying it
            window.history.replaceState(null, null, window.location.pathname);
        }
    });

//    function showMessage(message) {
//     const messageBox = document.getElementById('messageBox');
//     const messageText = document.getElementById('messageText');
//     const overlay = document.getElementById('overlay');

//     messageText.textContent = message;
//     messageBox.style.display = 'block';
//     overlay.style.display = 'block';

//     // Lock the scroll position
//     const scrollY = window.scrollY; // Capture the current scroll position
//     document.body.style.position = 'fixed';
//     document.body.style.top = `-${scrollY}px`;
//     document.body.style.width = '100%';

//     // Auto-hide after 3 seconds and release the scroll lock
//     setTimeout(() => {
//         messageBox.style.display = 'none';
//         overlay.style.display = 'none';

//         // Re-enable scrolling
//         document.body.style.position = '';
//         document.body.style.top = '';
//         window.scrollTo(0, scrollY); // Return to the original scroll position
//     }, 3000);
// }

    function showSection(sectionId) {
        document.querySelectorAll('.section').forEach(section => {
            section.style.display = 'none';
        });
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = 'block';
        }

        document.querySelectorAll('.sidebar a').forEach(link => {
            link.classList.remove('active');
        });
        const activeLink = document.querySelector(`[href="#${sectionId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        // Update the URL hash without reloading the page
        window.history.replaceState(null, null, `#${sectionId}`);
    }

    function scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Listen for hash changes and show the correct section
    window.addEventListener('hashchange', () => {
        const sectionId = window.location.hash.substring(1); // Get the hash without #
        if (sectionId) {
            showSection(sectionId);
        }
    });
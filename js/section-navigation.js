// Define sidebarLinks at the top so it's accessible throughout the script
const sidebarLinks = document.querySelectorAll('.sidebar ul li a');

document.addEventListener('DOMContentLoaded', function () {
    // Handle section navigation
    const hash = window.location.hash.substring(1);
    if (hash) {
        showSection(hash);
    } else {
        showSection('news');
    }

    // Attach event listeners to sidebar links
  

    // Dropdown toggle functionality for the user menu
    const userDropdownToggle = document.getElementById('userDropdownToggle');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    userDropdownToggle.addEventListener('click', function (event) {
        event.stopPropagation(); // Prevent click from bubbling up
        userDropdownMenu.classList.toggle('show');
    });

    // Close the dropdown if clicking outside
    document.addEventListener('click', function (event) {
        if (!userDropdownMenu.contains(event.target) && !userDropdownToggle.contains(event.target)) {
            userDropdownMenu.classList.remove('show');
        }
    });
});

// Show section function for navigation
function showSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');  // Hide all sections
    });

    // Show the selected section
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.classList.add('active');  // Show the clicked section
    }

    // Remove active class from all sidebar links
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
    });

    // Add active class to the clicked link
    const activeLink = document.querySelector(`a[href="#${sectionId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    // Prevent automatic scrolling
    window.scrollTo(0, 0);

    // Update the URL hash without scrolling the page
    history.pushState(null, null, '#' + sectionId);
}

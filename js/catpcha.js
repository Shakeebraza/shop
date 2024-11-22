document.addEventListener('DOMContentLoaded', function () {
    // Example of simple client-side validation for the CAPTCHA field
    const form = document.querySelector('form');
    const captchaInput = document.querySelector('input[name="captcha"]');
    form.addEventListener('submit', function (event) {
        if (!captchaInput.value) {
            alert('Please fill in the CAPTCHA.');
            event.preventDefault(); // Stop form submission if CAPTCHA is empty
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Check if the modal exists (account was successfully created)
    const modal = document.getElementById('successModal');

    if (modal) {
        // Show the modal
        modal.style.display = 'flex';

        // After 5 seconds, redirect to login.php
        setTimeout(function () {
            window.location.href = 'login.php';
        }, 5000);
    }
});


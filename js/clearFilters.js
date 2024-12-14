// clearFilters.js
function clearFilters(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset(); // Reset all form fields
        form.submit(); // Submit the form to refresh the page with cleared filters
    } else {
        console.error(`Form with ID ${formId} not found.`);
        alert(`Form with ID ${formId} not found.`); // Add alert to ensure visibility
    }
}

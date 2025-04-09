// Get all navigation links
const navLinks = document.querySelectorAll('.navbar a');

// Add click event listener to each link
navLinks.forEach(link => {
    link.addEventListener('click', function () {
        // Remove 'active' class from all links
        navLinks.forEach(nav => nav.classList.remove('active'));
        // Add 'active' class to the clicked link
        this.classList.add('active');
    });
});


//--------------------------print product--------------------------
function printContent() {
    // Get the content to print
    var printContents = document.querySelector('.show').innerHTML;
    var originalContents = document.body.innerHTML;

    // Replace the body content with the content to print
    document.body.innerHTML = printContents;

    // Print the page
    window.print();

    // Restore the original body content
    document.body.innerHTML = originalContents;
}

//--------------------------password show--------------------------


document.addEventListener("DOMContentLoaded", function () {
    function togglePasswordVisibility(toggleIcon, passwordField) {
        toggleIcon.addEventListener("click", function () {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("bx-hide");
                toggleIcon.classList.add("bx-show", "active"); // Icon grows
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("bx-show", "active");
                toggleIcon.classList.add("bx-hide");
            }
        });
    }

    // Get elements
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("confirm_password");
    const togglePasswordIcon = document.getElementById("togglePassword");
    const toggleConfirmPasswordIcon = document.getElementById("toggleConfirmPassword");

    // Attach event listeners
    togglePasswordVisibility(togglePasswordIcon, passwordField);
    togglePasswordVisibility(toggleConfirmPasswordIcon, confirmPasswordField);
});

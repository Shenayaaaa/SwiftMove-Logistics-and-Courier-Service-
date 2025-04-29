// File: public/js/form-validation.js

document.getElementById("registrationForm").addEventListener("submit", function(e) {
    let errorMessage = "";
  
    // Validate name, email, phone, password, confirm password
    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
  
    if (name === "") {
        errorMessage += "Name is required.\n";
    }
  
    // Validate email using regex
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        errorMessage += "Enter a valid email address.\n";
    }
  
    // Validate phone (digits only, 7 to 15 characters)
    const phonePattern = /^[0-9]{7,15}$/;
    if (!phonePattern.test(phone)) {
        errorMessage += "Enter a valid phone number.\n";
    }
  
    // Validate password length
    if (password.length < 6) {
        errorMessage += "Password must be at least 6 characters long.\n";
    }
  
    // Validate confirm password match
    if (password !== confirmPassword) {
        errorMessage += "Passwords do not match.\n";
    }
  
    // If there are errors, alert the user and prevent form submission
    if (errorMessage !== "") {
        alert(errorMessage);
        e.preventDefault();
    }
});
  
// AJAX: Check if email already exists
document.getElementById("email").addEventListener("blur", function() {
    const email = this.value.trim();
    if (email !== "") {
        fetch(`../check_user.php?email=${encodeURIComponent(email)}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    document.getElementById("emailFeedback").textContent = "Email already exists!";
                } else {
                    document.getElementById("emailFeedback").textContent = "";
                }
            })
            .catch(err => console.error(err));
    }
});
  
// AJAX: Check if phone already exists
document.getElementById("phone").addEventListener("blur", function() {
    const phone = this.value.trim();
    if (phone !== "") {
        fetch(`../check_user.php?phone=${encodeURIComponent(phone)}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    document.getElementById("phoneFeedback").textContent = "Phone already exists!";
                } else {
                    document.getElementById("phoneFeedback").textContent = "";
                }
            })
            .catch(err => console.error(err));
    }
});

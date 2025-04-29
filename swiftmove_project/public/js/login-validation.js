// File: public/js/login-validation.js

document.getElementById("loginForm").addEventListener("submit", function(e) {
    let errorMessage = "";
  
    // Validate login field (email or phone)
    const login = document.getElementById("login").value.trim();
    const password = document.getElementById("password").value;
  
    if (login === "") {
        errorMessage += "Email or phone is required.\n";
    }
  
    // Validate password field
    if (password === "") {
        errorMessage += "Password is required.\n";
    }
  
    // If errors exist, alert and prevent submission
    if (errorMessage !== "") {
        alert(errorMessage);
        e.preventDefault();
    }
});

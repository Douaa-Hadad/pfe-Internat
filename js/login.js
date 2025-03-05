function togglePasswordVisibility() {
    let passwordInput = document.getElementById("password");
    let toggleText = document.querySelector(".show-hide");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleText.innerText = "Hide";
    } else {
        passwordInput.type = "password";
        toggleText.innerText = "Show";
    }
}

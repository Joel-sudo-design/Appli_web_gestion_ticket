import './styles/app.css';

//Active jQuery
const $ = require('jquery');
window.$ = window.jQuery = $;

//Active Bootstrap
require('bootstrap');
require('bootstrap/dist/js/bootstrap.bundle');
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

//Autoload images
const imagesContext = require.context('../assets/images', true, /\.(png|jpg|jpeg|gif|ico|svg|webp)$/);
imagesContext.keys().forEach(imagesContext);

// Form validation
const inputs = document.querySelectorAll("input");
const password = document.getElementsByClassName("inputPassword")[0];
const confirmPassword = document.getElementsByClassName("inputConfirmPassword")[0];
const matchPassword = document.getElementById("match");
const emailAddress = document.getElementById("emailAddress");
const matchEmail = document.getElementById("matchEmail");
const eyePassword = document.getElementById("eye-password");
const eyeConfirmPassword = document.getElementById("eye-confirm-password");
const form = document.querySelector("form");
const submit = document.querySelector("button[type='submit']");

inputs.forEach((input) => {
    if (input.value) {
        input.classList.add("is-valid");
    }
    input.addEventListener("blur", (event) => {
        if (event.target.value) {
            input.classList.add("is-valid");
        } else {
            input.classList.remove("is-valid");
        }
    });
});

const updateRequirement = (id, valid) => {
    const requirement = document.getElementById(id);
    if (requirement) {
        if (valid) {
            requirement.classList.add("valid");
        } else {
            requirement.classList.remove("valid");
        }
    }
};

// Password validation
if (password) {
    password.addEventListener("input", (event) => {
        const value = event.target.value;

        updateRequirement("length", value.length >= 8);
        updateRequirement("lowercase", /[a-z]/.test(value));
        updateRequirement("uppercase", /[A-Z]/.test(value));
        updateRequirement("number", /\d/.test(value));
        updateRequirement("characters", /[#.?!@$%^&*-]/.test(value));
    });
}

// Email validation
if (emailAddress && matchEmail) {
    emailAddress.addEventListener("focus", (event) => {
        matchEmail.classList.add("hidden");
    });

    emailAddress.addEventListener("blur", (event) => {
        const characters = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

        if (!characters.test(event.target.value) && event.target.value.length > 0) {
            matchEmail.classList.remove("hidden");
        } else {
            matchEmail.classList.add("hidden");
        }
    });
}

// Confirm password validation
if (confirmPassword && matchPassword && password) {
    confirmPassword.addEventListener("focus", (event) => {
        matchPassword.classList.add("hidden");
    });

    confirmPassword.addEventListener("blur", (event) => {
        const value = event.target.value;

        if (value.length && value !== password.value) {
            matchPassword.classList.remove("hidden");
        } else {
            matchPassword.classList.add("hidden");
        }
    });
}

// Form validation handler
const handleFormValidation = () => {
    if (!password || !confirmPassword || !emailAddress || !submit) {
        return false;
    }

    const value = password.value;
    const confirmValue = confirmPassword.value;
    const characters = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    const email = emailAddress.value;

    if (
        value.length >= 8 &&
        /[a-z]/.test(value) &&
        /[A-Z]/.test(value) &&
        /\d/.test(value) &&
        /[#.?!@$%^&*-]/.test(value) &&
        value === confirmValue &&
        email.match(characters)
    ) {
        submit.removeAttribute("disabled");
        return true;
    }

    submit.setAttribute("disabled", true);
    return false;
};

if (form) {
    form.addEventListener("change", () => {
        handleFormValidation();
    });
}

// Button show/hide password
function password_show_hide() {
    const x = document.getElementsByClassName("inputPassword")[0];
    const show_eye = document.getElementById("show_eye");
    const hide_eye = document.getElementById("hide_eye");

    if (x && show_eye && hide_eye) {
        show_eye.classList.remove("d-none");
        if (x.type === "password") {
            x.type = "text";
            show_eye.style.display = "block";
            hide_eye.style.display = "none";
        } else {
            x.type = "password";
            show_eye.style.display = "none";
            hide_eye.style.display = "block";
        }
    }
}

function confirm_password_show_hide() {
    const x2 = document.getElementsByClassName("inputConfirmPassword")[0];
    const show_eye2 = document.getElementById("show_eye2");
    const hide_eye2 = document.getElementById("hide_eye2");

    if (x2 && show_eye2 && hide_eye2) {
        show_eye2.classList.remove("d-none");
        if (x2.type === "password") {
            x2.type = "text";
            show_eye2.style.display = "block";
            hide_eye2.style.display = "none";
        } else {
            x2.type = "password";
            show_eye2.style.display = "none";
            hide_eye2.style.display = "block";
        }
    }
}

if (eyePassword) {
    eyePassword.addEventListener("click", password_show_hide);
}

if (eyeConfirmPassword) {
    eyeConfirmPassword.addEventListener("click", confirm_password_show_hide);
}
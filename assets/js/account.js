import '../styles/app.css'

//Active jQuery
const $ = require('jquery');
window.$ = window.jQuery = $;

//Active Bootstrap
require('bootstrap');
require('bootstrap/dist/js/bootstrap.bundle');
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

//Autoload images
const imagesContext = require.context('../../assets/images', true, /\.(png|jpg|jpeg|gif|ico|svg|webp)$/);
imagesContext.keys().forEach(imagesContext);

// Form validation
const inputs = document.querySelectorAll("input");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");
const matchPassword = document.getElementById("match");

// Translate label
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

// Password validation
const updateRequirement = (id, valid) => {
    const requirement = document.getElementById(id);

    if (valid) {
        requirement.classList.add("valid");
    } else {
        requirement.classList.remove("valid");
    }
};
password.addEventListener("input", (event) => {
    const value = event.target.value;

    updateRequirement("length", value.length >= 8);
    updateRequirement("lowercase", /[a-z]/.test(value));
    updateRequirement("uppercase", /[A-Z]/.test(value));
    updateRequirement("number", /\d/.test(value));
    updateRequirement("characters", /[#.?!@$%^&*-]/.test(value));
});
confirmPassword.addEventListener("focus", () => {
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

// Button show/hide password
const eyePassword = document.getElementById("eye-password");
const eyeConfirmPassword = document.getElementById("eye-confirm-password");
eyePassword.addEventListener("click", password_show_hide);
eyeConfirmPassword.addEventListener("click", confirm_password_show_hide);
function password_show_hide() {
    const x = document.getElementsByClassName("inputPassword")[0];
    const show_eye = document.getElementById("show_eye");
    const hide_eye = document.getElementById("hide_eye");
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
function confirm_password_show_hide() {
    const x2 = document.getElementsByClassName("inputConfirmPassword")[0];
    const show_eye2 = document.getElementById("show_eye2");
    const hide_eye2 = document.getElementById("hide_eye2");
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

// Button show/hide modify username and password
const OpenNewName = document.getElementById("pen_username")
const username = document.getElementById("username");
const pencil = document.getElementsByClassName("bi-pencil-square")[0];
const x = document.getElementsByClassName("bi-x-square")[0];
const submitModify = document.getElementsByClassName("submitModify")[0];
const OpenNewPassword = document.getElementById("pen_password");
const pencil2 = document.getElementsByClassName("bi-pencil-square 2")[0];
const x2 = document.getElementsByClassName("bi-x-square 2")[0];
const lien = document.getElementsByClassName("lien")[0];

function Open_new_name() {
    if (username.disabled && submitModify.classList.contains('is-visible') && $('#newName').css("display","none")) {
        username.disabled = false;
        x.classList.remove("d-none");
        pencil.classList.add("d-none");
        $('#newName').css("display","block").toggleClass("is-visible");
        $('#appUsername').toggleClass("translated");
        lien.style.visibility = "hidden";
    }
    else if (username.disabled && !submitModify.classList.contains('is-visible') && $('#newName').css("display","none")) {
        username.disabled = false;
        x.classList.remove("d-none");
        pencil.classList.add("d-none");
        $('#newName').css("display","block").toggleClass("is-visible");
        $('#appUsername').toggleClass("translated");
        $('.submitModify').toggleClass('is-visible');
        lien.style.visibility = "hidden";

    }else{
        username.disabled = true;
        x.classList.add("d-none");
        pencil.classList.remove("d-none");
        $('#appUsername').toggleClass("translated");
        $('#newName').toggleClass("not-visible");
        setTimeout(function() {$('#newName').css("display","none").toggleClass("not-visible").toggleClass("is-visible");}, 400);
        if (password.disabled && confirmPassword.disabled) {
            $('.submitModify').toggleClass('is-visible');
            lien.style.visibility = "visible";
        }
    }
}
function Open_new_password() {
    if (password.disabled && confirmPassword.disabled && submitModify.classList.contains('is-visible')) {
        $('.collapse').collapse('show');
        x2.classList.remove("d-none");
        pencil2.classList.add("d-none");
        lien.style.visibility = "hidden";
        function Open1() {
            password.disabled = false;
            confirmPassword.disabled = false;
            $('.new-password').toggleClass("is-visible");
            $('.new-confirmPassword').toggleClass('is-visible');
            $('.requirementPassword').toggleClass('is-visible');
        }
        setTimeout(Open1, 400)
    }
    else if (password.disabled && confirmPassword.disabled && !submitModify.classList.contains('is-visible')) {
        $('.collapse').collapse('show');
        x2.classList.remove("d-none");
        pencil2.classList.add("d-none");
        lien.style.visibility = "hidden";
        function Open2() {
            password.disabled = false;
            confirmPassword.disabled = false;
            $('.new-password').toggleClass("is-visible");
            $('.new-confirmPassword').toggleClass('is-visible');
            $('.requirementPassword').toggleClass('is-visible');
            $('.submitModify').toggleClass('is-visible');
        }
        setTimeout(Open2, 400)
    }
    else {
        pencil2.classList.remove("d-none");
        x2.classList.add("d-none");
        $('.new-password').toggleClass("is-visible");
        $('.new-confirmPassword').toggleClass('is-visible');
        $('.requirementPassword').toggleClass('is-visible');
        if (username.disabled) {
            $('.submitModify').toggleClass('is-visible');
            lien.style.visibility = "visible";
        }
        function hide() {
            $('.collapse').collapse('hide');
            password.disabled = true;
            confirmPassword.disabled = true;
        }
        setTimeout(hide, 400)
    }
}

OpenNewPassword.addEventListener("click", Open_new_password);
OpenNewName.addEventListener("click", Open_new_name);
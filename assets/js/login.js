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

//Animation login avec ScrollReveal
import ScrollReveal from "scrollreveal";

// Initialisation globale avec opacity: 0 par défaut
const sr = ScrollReveal({
    opacity: 0,
    reset: false
});

sr.reveal('.img-fluid', {
    delay: 50,
    duration: 1200,
    scale: 0.5,
    distance: '200px',
    origin: 'top',
});

sr.reveal("#name", {
    origin: 'left',
    distance: '300px',
    duration: 600,
    delay: 900,
    easing: "ease-in-out",
});

sr.reveal("#pass", {
    origin: 'right',
    distance: '300px',
    duration: 600,
    delay: 900,
    easing: "ease-in-out",
});

sr.reveal(".mdp", {
    origin: 'top',
    distance: '10px',
    duration: 600,
    delay: 2150,
    easing: "ease-in-out",
});

sr.reveal(".new", {
    origin: 'bottom',
    distance: '10px',
    duration: 600,
    delay: 2150,
    easing: 'ease-in-out',
});

sr.reveal("#button", {
    delay: 1400,
    rotate: { x: 180, y: 0, z: 0 },
    duration: 900,
    scale: 0.5,
    easing: 'ease-in-out',
});

// Form validation
const inputs = document.querySelectorAll("input");
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

// Button show/hide password
const eye = document.getElementById("eye-password");
eye.addEventListener("click", password_show_hide);
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

// Calcule 1% de la hauteur du viewport et stocke-le dans la variable CSS --vh
let vh = window.innerHeight * 0.01;
document.documentElement.style.setProperty('--vh', `${vh}px`);


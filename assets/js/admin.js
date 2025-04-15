import '../styles/home.css';

//Active jQuery
const $ = require('jquery');
window.$ = window.jQuery = $;

//Active Bootstrap
require('bootstrap');
require('bootstrap/dist/js/bootstrap.bundle');
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.min.js';

//Active Axios
const axios = require('axios');

//Active ScrollReveal
import ScrollReveal from "scrollreveal";

// Import images
const imagesContext = require.context('../images', true, /\.(png|jpg|jpeg|gif|ico|svg|webp)$/);
imagesContext.keys().forEach(imagesContext);

// Modal
const open = document.getElementById('buttonTicketOpen');
const close = document.getElementsByClassName('buttonTicketClose');
open.addEventListener("click", function() {
    document.getElementById('modalTicket').classList.add("is-visible");
});
close[0].addEventListener("click", function() {
    document.getElementById('modalTicket').classList.remove("is-visible");
});

const input = document.getElementsByClassName('UploadImg')[0];
const MAX_IMG_SIZE = 1
input.addEventListener('change', () => {
    const {files} = input
    if (files.length > 0) {
        const fileSize = files.item(0).size;
        const fileSizeMb = fileSize / 1024 ** 2;
        const ext = input.value.split('.').pop().toLowerCase()
        if (!['jpg', 'jpeg', 'png'].includes(ext) && (fileSizeMb > MAX_IMG_SIZE)) {
            alert('Seuls les fichiers .jpeg et .png sont autorisés avec une taille maximale de 1 Mo!')
            input.value = ""
            return false
        }
        else if (fileSizeMb > MAX_IMG_SIZE) {
            alert(`Taille maximale autorisée 1 Mo!`)
            input.value = ""
            return false
        }
        else if (!['jpg', 'jpeg', 'png'].includes(ext)) {
            alert('Seuls les fichiers .jpeg et .png sont autorisés!')
            input.value = ""
            return false
        }
    }
});

// Focus Ticket en attente au chargement de la page
document.getElementById('waitingTicket').classList.add('active');

// Animation Focus button Ticket en attente / en cours / résolu
const btnWaitingTicket = document.getElementById('waitingTicket');
const btnInProgressTicket = document.getElementById('inProgressTicket');
const btnResolvedTicket = document.getElementById('resolvedTicket');
btnWaitingTicket.addEventListener('click', function() {
    if (!btnWaitingTicket.classList.contains('active') && btnInProgressTicket.classList.contains('active')) {
        btnWaitingTicket.classList.add('active');
        btnInProgressTicket.classList.remove('active');
    }
    else if (!btnWaitingTicket.classList.contains('active') && btnResolvedTicket.classList.contains('active')) {
        btnWaitingTicket.classList.add('active');
        btnResolvedTicket.classList.remove('active');
    }
});
btnInProgressTicket.addEventListener('click', function() {
    if (!btnInProgressTicket.classList.contains('active') && btnWaitingTicket.classList.contains('active')) {
        btnInProgressTicket.classList.add('active');
        btnWaitingTicket.classList.remove('active');
    }
    else if (!btnInProgressTicket.classList.contains('active') && btnResolvedTicket.classList.contains('active')) {
        btnInProgressTicket.classList.add('active');
        btnResolvedTicket.classList.remove('active');
    }
});
btnResolvedTicket.addEventListener('click', function() {
    if (!btnResolvedTicket.classList.contains('active') && btnInProgressTicket.classList.contains('active')) {
        btnResolvedTicket.classList.add('active');
        btnInProgressTicket.classList.remove('active');
    }
    else if (!btnResolvedTicket.classList.contains('active') && btnWaitingTicket.classList.contains('active')) {
        btnResolvedTicket.classList.add('active');
        btnWaitingTicket.classList.remove('active');
    }
});

// Affichage des tickets en attente à l'ouverture de la page
$(document).ready(function() {
    $("#waitingTicket").click();
});

// Fonctions pour les tickets + filtres
function formatTicketDate(jsonDate) {
    const dateStr = JSON.stringify(jsonDate).slice(9, 19);
    const [year, month, day] = dateStr.split("-");
    return `${day}/${month}/${year}`;
}
function generateImgButton(obj, formatImg) {
    if (formatImg) {
        return `
      <button id="ticketImg-${obj.id}" style="all: unset; cursor:pointer; width: 35%" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal-${obj.id}">
        <img src="${formatImg}" class="img-fluid w-100" alt="image_ticket">
      </button>
      <div class="modal fade" id="exampleModal-${obj.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content">
            <button type="button" data-bs-dismiss="modal" aria-label="Close">
              <img style="width: 100%" src="${formatImg}" alt="image_ticket">
            </button>
          </div>
        </div>
      </div>
    `;
    } else {
        return `
      <button id="ticketImg-${obj.id}" style="all: unset; cursor:pointer; width: 35%" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal-${obj.id}"></button>
    `;
    }
}
function generateTicketHeader(obj, formatDate) {
    return `
    <div class="d-flex flex-wrap col-12 align-items-center justify-content-between mt-2">
      <div class="d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-center mb-1">
          <img src="/build/images/mail.png" alt="mail" id="mail-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <img src="/build/images/serveur.png" alt="serveur" id="serveur-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <img src="/build/images/dolibarr.png" alt="dolibarr" id="dolibarr-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <img src="/build/images/internet.png" alt="internet" id="internet-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <img src="/build/images/printer.png" alt="printer" id="printer-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <img src="/build/images/software.png" alt="software" id="software-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <img src="/build/images/hardware.png" alt="hardware" id="hardware-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <img src="/build/images/other.png" alt="other" id="other-${obj.id}" class="img-fluid d-none" style="width: 3rem; height: auto">
          <div id="ticketCat-${obj.id}" class="ticketSize fs-6 fw-bold p-2 text-start" style="margin-left: 0.5rem">
            Ticket N°${obj.id} - ${obj.category}
          </div>
          <div id="PJ-${obj.id}" style="color: #0F75AD; margin-left: 1.5rem" class="fs-4 bi bi-paperclip position-relative"></div>
          <div id="priority-${obj.id}" class="d-flex justify-content-center">
            <i style="margin-left: 1.5rem" class="d-flex align-items-center fs-4 bi bi-exclamation-triangle-fill">
              <span class="priority fst-normal mx-2 fs-5">Priorité haute</span>
            </i>
          </div>
          <div id="new-${obj.id}" style="margin-left: 1.5rem" class="badge text-bg-info fs-5">NEW</div>
        </div>
      </div>
      <div>${obj.email}&nbsp;&nbsp;-&nbsp;&nbsp;${formatDate}</div>
    </div>
    <div class="fs-6 fw-bold mt-2">${obj.title}</div>
  `;
}
function generateTicketAccordion(obj, formatImg, type) {
    const imgButton = generateImgButton(obj, formatImg);
    return `
    <div id="accordion-body-${obj.id}" class="accordion-body align-item-center">
          <div class="row">
              <div class="col-7 d-flex align-content-center">
                <textarea id="description-${obj.id}" class="form-control p-3 fs-6" style="height: 150px" disabled>${obj.description}</textarea>
                <label for="description-${obj.id}"></label>
              </div>
            <div class="col-5 align-content-center">
                <div id="row-pj-${obj.id}" class="row align">
                    <div class="col-12 text-center">${imgButton}</div>
                </div>
            </div>
          </div>
          ${generateAnswersRows(obj)}  
          <div id="row-accordion-answer-${obj.id}" class="row justify-content-end">
              <div class="col-12 d-flex justify-content-center">
                <button type="button" id="answerButton-${obj.id}" class="submit fs-6 mt-2 d-none">REPONDRE</button>
                <button type="button" id="sendAnswerButton-${obj.id}" class="submit fs-6 mt-2 d-none">ENVOYER</button>
                ${type === "waiting" ? `<button type="button" id="submitInProgress-${obj.id}" 
                    class="submit fs-6 mt-2">EN COURS</button>` : ""}
                ${type === "inProgress" ? `<button type="button" id="submitResolved-${obj.id}" 
                    class="submit fs-6 mt-2">RESOLU</button>` : ""}
              </div>  
          </div>
    </div>
  `;
}
function generateAnswerRow(obj, index) {
    return `
    <div id="row-answer-${obj.id}-${index}" class="row mt-3 justify-content-end">
      <div class="col-7 d-flex">
        <textarea id="answer-${obj.id}-${index}" class="answer form-control p-3 fs-6" style="height: 150px"></textarea>
        <label for="answer-${obj.id}-${index}"></label>
      </div>
    </div>
  `;
}
function generateAnswersRows(obj) {
    if (!obj.answers || !obj.answers.length) return "";

    return obj.answers.map((answer, index) => {
        const role = Object.keys(answer)[0];
        const contenu = answer[role];
        const justifyClass = role === "admin" ? "justify-content-end" : "justify-content-start";
        const user = role ==="user" ? "<div style='font-size:0.8rem'>Réponse de l'utilisateur</div>" : '';
        return `
              <div id="row-answer${obj.id}-${index}" class="row mt-3 ${justifyClass}">
                <div class="col-7 d-flex flex-column">
                  <div>${user}</div>
                  <textarea id="answer${obj.id}-${index}" class="answer form-control p-3 fs-6" style="height: 150px" disabled>${contenu}</textarea>
                  <label for="answer${obj.id}-${index}"></label>
                </div>  
              </div>
            `;
    }).join('');
}
function createTicketTemplate(obj, formatDate, formatImg, type) {
    return `
        <div id="ticket-template-${obj.id}" class="row mt-4">
          <div class="col-12 p-0">
            <div class="accordion accordion-flush">
              <div class="accordion-item border">
                <h2 class="accordion-header">
                  <button class="pt-0 pb-0 flex-column accordion-button RotateArrow collapsed align-items-start" id="button-ticket-${obj.id}" type="button">
                    ${generateTicketHeader(obj, formatDate)}
                  </button>
                </h2>
                <div id="flush-collapseOne-${obj.id}" class="collapse">
                  ${generateTicketAccordion(obj, formatImg, type)}  
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }
function attachTicketEvents(obj, type) {
    const $button = $(`#button-ticket-${obj.id}`);
    const $content = $(`#flush-collapseOne-${obj.id}`);
    const $accordionBody = $(`#accordion-body-${obj.id}`);
    const $rowPj = $(`#row-pj-${obj.id}`);
    const $row = $(`#row-${obj.id}`);
    const $badgeNew = $(`#new-${obj.id}`);
    const buttonStatusInProgress = document.getElementById(`submitInProgress-${obj.id}`);
    const buttonStatusResolved = document.getElementById(`submitResolved-${obj.id}`);
    const $buttonAnswer = $(`#answerButton-${obj.id}`);
    const $buttonSendAnswer = $(`#sendAnswerButton-${obj.id}`);
    const categories = {
        'Messagerie': `#mail-${obj.id}`,
        'Serveur': `#serveur-${obj.id}`,
        'Dolibarr': `#dolibarr-${obj.id}`,
        'Internet': `#internet-${obj.id}`,
        'Imprimante': `#printer-${obj.id}`,
        'Logiciel': `#software-${obj.id}`,
        'Matériel': `#hardware-${obj.id}`,
        'Autre': `#other-${obj.id}`
    };
    const $priority = $(`#priority-${obj.id}`);

    // Événements spécifiques pour les tickets en cours
    if (type === "inProgress") {

        $buttonAnswer.removeClass('d-none');
        let answerIndex = 0;

        $buttonAnswer.on("click", function () {
            answerIndex++;
            $buttonSendAnswer.removeClass('d-none');
            $buttonAnswer.addClass('d-none');

            // Génération du textaréa avant les boutons
            const answerRowHtml = generateAnswerRow(obj, answerIndex);
            $(`#row-accordion-answer-${obj.id}`).before(answerRowHtml);

            // Animation avec ScrollReveal du textarea
            const $rowAnswer = $(`#row-answer-${obj.id}-${answerIndex}`);
            const $answer = $(`#answer-${obj.id}-${answerIndex}`);
            ScrollReveal().reveal($rowAnswer[0], {
                origin: 'left',
                distance: '400px',
                duration: 500,
                delay: 0,
                easing: "ease-in-out",
                beforeReveal: function () {
                    $answer.addClass('active');
                    document.getElementById(`accordion-body-${obj.id}`).scrollIntoView({ block: "end", behavior: "smooth" })
                },
                afterReveal: function () {
                    $answer.focus();
                    document.getElementById(`accordion-body-${obj.id}`).scrollIntoView({ block: "end", behavior: "smooth" })
                }
            });

            // Envoyer la réponse
            $buttonSendAnswer.one("click", function () {
                const $answer = $(`#answer-${obj.id}-${answerIndex}`);
                $buttonAnswer.removeClass('d-none');
                const answerValue = $answer.val();
                $answer.prop("disabled", true);
                $buttonSendAnswer.addClass('d-none');
                $answer.removeClass('active')
                const data = { id: obj.id, answer: answerValue };
                const url = "/admin/send_answer";
                axios.post(url, JSON.stringify(data))
                    .then((response) => console.log(response))
                    .catch((error) => console.error(error));
            });

            // Suppression de la classe active lors du clic en dehors du textarea de réponse
            $(document).on('click', function (event) {
                if (!$answer.is(event.target)) {
                    $answer.removeClass('active');
                }
            });
        });
    }

    // Gestion de l'ouverture/fermeture de l'accordéon, animation de la flèche, envoi de l'événement d'ouverture de ticket
    $button.on("click", function () {
        $badgeNew.hide();
        $rowPj.css("display", "flex");
        $row.css("display", "flex");

        // Envoi de l'événement d'ouverture de ticket
        const data = { id: obj.id };
        const url = "/admin/open";
        axios.post(url, JSON.stringify(data))
            .then((response) => console.log(response))
            .catch((error) => console.error(error));

        $content.collapse('show');
        $button.toggleClass('RotateArrow');
    });

    // Gestion lors du clic en dehors
    $(document).on('click', function (event) {
        if (!$content.is(event.target) && $content.has(event.target).length === 0 && $content.hasClass('show')) {
            $content.collapse('hide');
            $button.addClass('RotateArrow');
        }
    });

    // Scroll vers le bas si le contenu dépasse la fenêtre
    $content.on('shown.bs.collapse', function () {
        const rect = $accordionBody[0].getBoundingClientRect();
        if (rect.bottom > window.innerHeight) {
            $accordionBody[0].scrollIntoView({ block: "end", behavior: "smooth" });
        }
    });

    // Passage en cours
    $(`#submitInProgress-${obj.id}`).on("click", function () {
        const data = { id: obj.id };
        const url = "/admin/status_inProgress_ticket";
        axios.post(url, JSON.stringify(data))
            .then((response) => {
                console.log(response);
                $("#waitingTicket").click(); // recharge les tickets en attente
            })
            .catch((error) => console.error(error));
    });

    // Passage en résolu
    $(`#submitResolved-${obj.id}`).on("click", function () {
        const data = { id: obj.id };
        const url = "/admin/status_resolved_ticket";
        axios.post(url, JSON.stringify(data))
            .then((response) => {
                console.log(response);
                $("#inProgressTicket").click(); // recharge les tickets en cours
            })
            .catch((error) => console.error(error));
    });

    // Affichage de l'icône de catégorie
    if (categories[obj.category]) {
        $(categories[obj.category]).removeClass('d-none');
    }

    // Affichage de la priorité
    if (obj.priority === 'Haute') {
        $priority.addClass('text-danger');
    } else if (obj.priority === 'Basse') {
        $priority.addClass('d-none');
    }

    // Si aucune image n'est présente, on masque l'icône et le bouton image
    if (!obj.image) {
        $(`#ticketImg-${obj.id}`).addClass('d-none');
        $(`#PJ-${obj.id}`).addClass('d-none');
    }

    // Si le ticket est ouvert, on masque le badge "NEW"
    if (obj.open) {
        $badgeNew.hide();
    }

    // Gestion des boutons
    if (obj.status === 'En attente') {
        buttonStatusInProgress.classList.remove('d-none');
    }
    if (obj.status === 'En cours') {
        buttonStatusResolved.classList.remove('d-none');
    }
    if (obj.status === 'Résolu') {
        $buttonAnswer.addClass('d-none');
    }
}
function loadFilteredTickets(url, type, filterFn, sortOrder = 'normal') {
    $('.spinner-border').show();
    $('#appTicket').empty();
    const $nothing = $('.nothing').first();
    if (!$nothing.hasClass('d-none')) {
        $nothing.addClass('d-none');
    }

    axios.get(url)
        .then((response) => {
            let tickets = response.data;
            // Si on souhaite afficher les tickets dans l'ordre "ancien", on inverse le tableau
            if (sortOrder === 'ancien') {
                tickets = tickets.reverse();
            }
            // Appliquer un filtre si défini
            if (typeof filterFn === 'function') {
                tickets = tickets.filter(filterFn);
            }
            if (tickets.length === 0) {
                $nothing.removeClass('d-none');
            }
            $.each(tickets, function (index, obj) {
                const formatDate = formatTicketDate(obj.date);
                const ticket_Img = '/ticket_image';
                const formatImg = obj.image ? `${ticket_Img}/${obj.image}` : '';
                const ticketTemplate = createTicketTemplate(obj, formatDate, formatImg, type);
                $('#appTicket').append(ticketTemplate);
                attachTicketEvents(obj, type);
            });
        })
        .catch((error) => console.error(error))
        .finally(() => $('.spinner-border').hide());
}
function getActiveTypeAndUrl() {
    let type, url;
    if ($("#waitingTicket").hasClass("active")) {
        type = "waiting";
        url = "/admin/waiting_tickets";
    } else if ($("#inProgressTicket").hasClass("active")) {
        type = "inProgress";
        url = "/admin/inProgress_tickets";
    } else if ($("#resolvedTicket").hasClass("active")) {
        type = "resolved";
        url = "/admin/resolved_tickets";
    }
    return { type, url };
}
function loadTickets(url, type) {
    // Chargement sans filtre (ordre par défaut : récent)
    loadFilteredTickets(url, type, null, 'normal');
}

// Affichage des tickets
$("#waitingTicket").click(function () {
    const url = "/admin/waiting_tickets";
    loadTickets(url, "waiting");
});
$("#inProgressTicket").click(function () {
    const url = "/admin/inProgress_tickets";
    loadTickets(url, "inProgress");
});
$("#resolvedTicket").click(function () {
    const url = "/admin/resolved_tickets";
    loadTickets(url, "resolved");
});

// Filtres
$("#dropRécent").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    // 'normal' correspond à l'ordre récent (par défaut)
    loadFilteredTickets(url, type, null, 'normal');
});
$("#dropAncien").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    // Inverse l'ordre pour afficher les plus anciens en premier
    loadFilteredTickets(url, type, null, 'ancien');
});
$("#dropPriorité").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.priority === "Haute");
});
$("#dropMail").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Messagerie");
});

$("#dropServeur").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Serveur");
});
$("#dropDolibarr").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Dolibarr");
});
$("#dropInternet").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Internet");
});
$("#dropImprimante").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Imprimante");
});
$("#dropLogiciel").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Logiciel");
});
$("#dropMatériel").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Matériel");
});
$("#dropAutre").click(function () {
    const { type, url } = getActiveTypeAndUrl();
    loadFilteredTickets(url, type, ticket => ticket.category === "Autre");
});

// Barre recherche
$(document).ready(function(){
    $("#search").on("keyup", function() {
        const value = $(this).val().toLowerCase();
        $("#appTicket .accordion-item").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
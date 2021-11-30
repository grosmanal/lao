import httpClient from 'axios';
import $ from 'jquery';
import { modal } from './components/modal';
import { submitCommentMenu, submitComment, transformToSummernote } from './comment';
import nullFieldConverter from './utils/nullFieldConverter';
import confirm from './utils/confirm';
import removeDomElement from './utils/removeDomElement';

import './styles/patient.scss'

/**
 * Composant Vue des disponibilités
 */
import Vue from 'vue';
import Weekvailability from './components/availability/AvailabilityWeek.vue';
import Translator from 'bazinga-translator';

new Vue({
    render(h) {
        return h(Weekvailability, {
            props: {
                middleOfDay: this.$el.getAttribute('data-middle-of-day'),
                initAvailability: JSON.parse(this.$el.getAttribute('data-availability')),
                urlPutPatientAvailability: this.$el.getAttribute('data-url-put-patient-availability'),
            }
        });
    }
}).$mount('#week-availability')

jQuery(function ($) {
    // Composant summernote sur chaque textarea d'ajout de commentaire
    $('.comments textarea').each(function () {
        transformToSummernote(this);
    })

    // Clic sur checkbox variableSchedule
    $('#variable-schedule-form input[type="checkbox"]').on('change', function () {
        updateVariableSchedule(this)
    });

    // Bouton d'ajout de care request
    $('.care-request-create-button').on('click', function () {
        insertCareRequestCreationForm(this, $);
    });
});

function doSubmitPatient(url, data) {
    httpClient({
        method: 'put',
        url: url,
        data: data
    }).then(function (response) {
        // https://manal.xyz/gitea/origami_informatique/lao/issues/85
    }).catch(function (error) {
        modal('patient.error.updating)');
    });
}

/**
 * Enregistrement des infos du patient
 */
function submitPatient(event) {
    event.preventDefault();

    const form = event.target;

    const data = {
        firstname: nullFieldConverter(form['patient[firstname]'].value),
        lastname: nullFieldConverter(form['patient[lastname]'].value),
        birthdate: nullFieldConverter(form['patient[birthdate]'].value),
        contact: nullFieldConverter(form['patient[contact]'].value),
        phone: nullFieldConverter(form['patient[phone]'].value),
        mobilePhone: nullFieldConverter(form['patient[mobilePhone]'].value),
        email: nullFieldConverter(form['patient[email]'].value),
    };
    
    doSubmitPatient(form['patient[apiPutUrl]'].value, data);

    return false;
};


function updateVariableSchedule(input) {
    const data = {
        variableSchedule: input.checked,
    }

    doSubmitPatient(input.form['variable_schedule[apiPutUrl]'].value, data);
}


/**
 * Appel ajax de la mise à jour (via API) de la care request
 * puis mise à jour de l'html de la care request avec les nouvelle données
 * @param {object} form 
 * @param {object} data 
 */
function doSubmitCareRequest(form, data) {
    httpClient({
        method: form['care_request[apiAction]'].value,
        url: form['care_request[apiUrl]'].value,
        data: data
    }).then(function (response) {
        httpClient
            .get(response.data.relatedUri.getHtmlForm)
            .then(function (response) {
                // Recherche du parent de la form pour y injecter le nouveau HTML
                let formParent = $(form).parentsUntil('#care-requests-accordion', '.accordion-item');

                // Injection du nouveau HTML
                formParent.html(response.data);

                // Transformation de l'éventuel textarea de création de commentaire en summernote
                const textarea = formParent.find('.comments textarea');
                if (textarea.length) {
                    transformToSummernote(textarea.get(0));
                }

                // https://manal.xyz/gitea/origami_informatique/lao/issues/85
            }).catch(function (error) {
                modal('care_request.error.reread');
            });
    }).catch(function (error) {
        let errorMessage = '';
        if (error.response.data) {
            const responseData = error.response.data;
            if (responseData['@type'] == 'ConstraintViolationList') {
                responseData.violations.forEach(function (violation) {
                    errorMessage += '<br />' + violation.message;
                });
            }
        }
        modal('care_request.error.update', { errorMessage });
    });
}

/**
 * Modification d'une demande
 */
function submitCareRequest(event) {
    event.preventDefault();

    const form = event.target;
    const data = {
        creationDate: nullFieldConverter(form['care_request[creationDate]'].value),
        priority: nullFieldConverter(form['care_request[priority]'].checked),
        customComplaint: nullFieldConverter(form['care_request[customComplaint]'].value),
        acceptDate: nullFieldConverter(form['care_request[acceptDate]'].value),
        abandonDate: nullFieldConverter(form['care_request[abandonDate]'].value),
        abandonReason: nullFieldConverter(form['care_request[abandonReason]'].value),
        doctorCreator: nullFieldConverter(form['care_request[doctorCreator]'].value),
        complaint: nullFieldConverter(form['care_request[complaint]'].value),
        acceptedByDoctor: nullFieldConverter(form['care_request[acceptedByDoctor]'].value),
    };

    if (form['care_request[patientUri]']) {
        // Le formulaire contient le champ (caché) patientUri, il faut l'ajouter
        // aux data pour création de la care request
        data['patient'] = form['care_request[patientUri]'].value;
    }

    doSubmitCareRequest(form, data);

    return false;
};


/**
 * Réactivation d'une care request
 */
function reactivateCareRequest(event) {
    event.preventDefault();

    const form = event.target;
    const data = {
        acceptDate: null,
        abandonDate: null,
        abandonReason: null,
        acceptedByDoctor: null,
    };

    doSubmitCareRequest(form, data);

    return false;
}


/**
 * Abandon de la demande de prise en charge
 */
function abandonCareRequest(event) {
    const form = event.target.form
    const button = event.target;

    const data = {
        abandonReason: nullFieldConverter(form['care_request[abandonReason]'].value),
        abandonDate: 'now',
    };

    const abandonReason = form['care_request[abandonReason]'];
    
    if (abandonReason.value == '') {
        const popoverAttributes = {
            element: abandonReason,
            title: Translator.trans('care_request.confirm_abandon.title'),
            content: Translator.trans('care_request.confirm_abandon.content'),
            placement: 'top',
        }
        
        confirm(
            button,
            function() { doSubmitCareRequest(form, data) },
            'btn-warning',
            popoverAttributes,
        );
    } else {
        // La raison d'abandon est rensignée
        doSubmitCareRequest(form, data);
    }
}

/**
 * Acceptation de la demande de prise en charge
 */
function acceptCareRequest(event) {
    const form = event.target.form;
    const data = {
        acceptDate: 'now',
        acceptedByDoctor: nullFieldConverter(form['care_request[doctorUri]'].value),
    };

    doSubmitCareRequest(form, data);
}


/**
 * Suppression de la care request
 * @param {Event} event 
 */
function deleteCareRequest(event) {
    event.preventDefault();
    
    const apiUrlDelete = event.target.dataset.apiUrlDelete;
    const elementToRemove = $(event.target).parentsUntil('#care-requests-accordion', '.accordion-item');

    confirm(event.target, function() {
        // Suppression de la care request
        httpClient.delete(apiUrlDelete)
            .then(function (response) {
                // Suppression de la care request du DOM
                removeDomElement(elementToRemove.get(0));
            }).catch(function (error) {
                modal('care_request.error.delete');
            })
        ;
    });
}


/**
 * Insertion d'un formulaire de création de care request dans
 * la liste des care request
 * @param {Event} event 
 */
function insertCareRequestCreationForm(event, $) {
    // Recherche de l'URL du formulaire de création de le care request
    const urlCareRequestForm = $(event).data('urlCareRequestForm');

    httpClient
        .get(urlCareRequestForm)
        .then(function (response) {
            // Recherche du parent de la form pour y injecter le nouveau HTML
            const careRequestsAccordion = $('#care-requests-accordion');

            // Fermeture (collapse) de toutes les care request existantes affichée
            careRequestsAccordion.find('.accordion-collapse.collapse.show').removeClass('show');

            const careRequestAccordionItem = $('<div></div>').addClass('accordion-item')

            // Injection du nouveau HTML dans l'item
            careRequestAccordionItem.append(response.data);

            // Ajout de l'item au début de l'accordion
            careRequestsAccordion.prepend(careRequestAccordionItem);

        }).catch(function (error) {
            modal('care_request.error.reread');
        });
}


/**
 * Permettre de valider le formulaire de création de commentaire
 * avec Ctrl + Entrée
 */
const formsComment = document.querySelectorAll('.comment-form');
formsComment.forEach(function (element) {
    element.addEventListener('keydown', function (event) {
        if (event.getModifierState('Control') && event.key == 'Enter') {
            element.dispatchEvent(new Event('submit', {
                'bubbles': true,
                'cancelable': true,
            }));
        }
    });
});

// Ces fonctions sont appelées depuis les forms care request.
// Elles doivent donc être globale
window.submitPatient = submitPatient;
window.submitCareRequest = submitCareRequest;
window.reactivateCareRequest = reactivateCareRequest;
window.deleteCareRequest = deleteCareRequest;
window.submitComment = submitComment;
window.submitCommentMenu = submitCommentMenu;
window.abandonCareRequest = abandonCareRequest;
window.acceptCareRequest = acceptCareRequest;
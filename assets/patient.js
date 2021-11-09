import httpClient from 'axios';
import $ from 'jquery';
import 'summernote/dist/summernote-lite';
import 'summernote/dist/summernote-lite.css';
//import 'summernote/dist/summernote-bs5';
//import 'summernote/dist/summernote-bs5.css';
import modal from './components/modal';
import { submitCommentMenu, submitComment, transformToSummernote } from './comment';
import nullFieldConverter from './utils/nullFieldConverter';
import apiFieldConverter from './utils/apiFieldConverter';

import './styles/patient.scss'

/**
 * Composant Vue des disponibilités
 */
import Vue from 'vue';
import Weekvailability from './components/availability/weekAvailability.vue';

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

/**
 * Composant summernote sur chaque textarea d'ajout de commentaire
 */
jQuery(function($) {
    $('.comments textarea').each(function() {
        transformToSummernote(this);
    })
});

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
    httpClient({
        method: 'put',
        url: patientParams.urlApiPatientPut,
        data: data
    }).then(function (response) {
        // TODO voir quoi faire
        // on pourrait mettre un check dans le bouton de validation qui s'effacerait après un timer
    }).catch(function (error) {
        modal('patient_error.updating)');
    });

    return false;
};


/**
 * Appel ajax de la mise à jour (via API) de la care request
 * puis mise à jour de l'html de la care request avec les nouvelle données
 * @param {object} form 
 * @param {int} careRequestId 
 * @param {object} data 
 */
function doSubmitCareRequest(form, careRequestId, data) {
    httpClient({
        method: 'put',
        url: patientParams.urlApiCareRequestPut.replace('%id%', careRequestId),
        data: data
    }).then(function(response) {
        httpClient
            .get(patientParams.urlCareRequestForm.replace('%id%', careRequestId))
            .then(function(response) {
                // Recherche du parent de la form pour y injecter le nouveau HTML
                let formParent = $(form).parentsUntil('#care-requests-accordion', '.accordion-item');

                // Injection du nouveau HTML
                formParent.html(response.data);
                
                // Transformation de l'éventuel textarea de création de commentaire en summernote
                transformToSummernote(formParent.find('.comments textarea').get(0));
            }).catch(function(error) {
                modal('care_request_error.reread');
            });
    }).catch(function(error) {
        modal('care_request_error.update');
    });
}

/**
 * Modification d'une demande
 */
function submitCareRequest(event) {
    event.preventDefault();

    const form = event.target;
    const careRequestId = form['care-request-id'].value;

    const data = {
        creationDate: nullFieldConverter(form['care_request[creationDate]'].value),
        customComplaint: nullFieldConverter(form['care_request[customComplaint]'].value),
        acceptDate: nullFieldConverter(form['care_request[acceptDate]'].value),
        abandonDate: nullFieldConverter(form['care_request[abandonDate]'].value),
        abandonReason: nullFieldConverter(form['care_request[abandonReason]'].value),
        doctorCreator: apiFieldConverter(form['care_request[doctorCreator]'].value, 'Doctor'),
        complaint: apiFieldConverter(form['care_request[complaint]'].value, 'Complaint'),
        acceptedByDoctor: apiFieldConverter(form['care_request[acceptedByDoctor]'].value, 'Doctor'),
    };
    
    doSubmitCareRequest(form, careRequestId, data);

    return false;
};


/**
 * Réactivation d'une care request
 */
function reactivateCareRequest(event) {
    event.preventDefault();

    const form = event.target;
    const careRequestId = form['care-request-id'].value;

    const data = {
        acceptDate: null,
        abandonDate: null,
        abandonReason: null,
        acceptedByDoctor: null,
    };
    
    doSubmitCareRequest(form, careRequestId, data);
    
    return false;
}


/**
 * Abandon de la demande de prise en charge
 */
function abandonCareRequest(event) {
    const form = event.target.form
    const careRequestId = form['care-request-id'].value;
    

    const data = {
        abandonDate: 'now',
    };
    
    doSubmitCareRequest(form, careRequestId, data);
}

/**
 * Acceptation de la demande de prise en charge
 */
 function acceptCareRequest(event) {
    const form = event.target.form
    const careRequestId = form['care-request-id'].value;

    const data = {
        acceptDate: 'now',
        acceptedByDoctor: patientParams.currentDoctorId ? apiFieldConverter(patientParams.currentDoctorId, 'Doctor') : null,
    };
    
    doSubmitCareRequest(form, careRequestId, data);
 }


/**
 * Permettre de valider le formulaire de création de commentaire
 * avec Ctrl + Entrée
 */
const formsComment = document.querySelectorAll('.comment-form');
formsComment.forEach(function(element) {
    element.addEventListener('keydown', function(event) {
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
window.submitComment = submitComment;
window.submitCommentMenu = submitCommentMenu;
window.abandonCareRequest = abandonCareRequest;
window.acceptCareRequest = acceptCareRequest;
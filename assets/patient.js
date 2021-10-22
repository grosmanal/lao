import httpClient from 'axios';
import $ from 'jquery';
import modal from './components/modal';
import nullFieldConverter from './components/nullFieldConverter';
import apiFieldConverter from './components/apiFieldConverter';

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
        doctorCreator: apiFieldConverter(form['care_request[doctorCreator]'].value, 'doctors'),
        complaint: apiFieldConverter(form['care_request[complaint]'].value, 'complaints'),
        acceptedByDoctor: apiFieldConverter(form['care_request[acceptedByDoctor]'].value, 'doctors'),
    };

    httpClient({
        method: 'put',
        url: patientParams.urlApiCareRequestPut.replace('%id%', careRequestId),
        data: data
    }).then(function (response) {
        httpClient
            .get(patientParams.urlCareRequestForm.replace('%id%', careRequestId))
            .then(function (response) {
                // Recherche du parent de la form pour y injecter le nouveau HTML
                let formParent = $(form).parentsUntil('.accordion-item').parent();
                // Injection du nouveau HTML
                formParent.html(response.data);
            }).catch(function(error) {
                modal('care_request_error.reread');
            });
    }).catch(function(error) {
        modal('care_request_error.update');
    });
    
    return false;
};


/**
 * Annulation de l'abandon de la demande de prise en charge
 */
function abandonCareRequest() {
    alert('in func abandon');  // TODO
}

/**
 * Annulation de l'acceptation de la demande de prise en charge
 */
 function acceptCareRequest() {
    alert('in func accept');  // TODO
 }


/**
 * Permettre de valider le formulaire de création de commentaire
 * avec Ctrl + Entrée
 */
const formComment = document.querySelector('.comment-form');
formComment.addEventListener('keydown', function(event) {
    if (event.getModifierState('Control') && event.key == 'Enter') {
        formComment.dispatchEvent(new Event('submit', {
            'bubbles': true,
            'cancelable': true,
        }));
    }
});

/**
 * Ajout d'un commentaire une care request
 */
function submitComment(event) {
    event.preventDefault();

    const form = event.target;
    const careRequestId = form['care-request-id'].value;
    const doctorId = form['doctor-id'].value;

    const comment = nullFieldConverter(form['comment'].value);
    
    const data = {
        author: patientParams.uriApiDoctor.replace('%id%', doctorId),
        creationDate: new Date().toISOString(),
        careRequest: patientParams.uriApiCareRequest.replace('%id%', careRequestId),
        content: comment,
    }
    
    httpClient({
        method: 'post',
        url: patientParams.urlApiCommentPost,
        data: data
    }).then(function (response) {
        httpClient
            .get(patientParams.urlCommentPart.replace('%id%', response.data.id))
            .then(function(response) {
                // Recherche de l'élément liste
                let listElement = $(`#care-request-body-${careRequestId} ul.comments`);
                
                console.log(response.data);
                
                // Injection du nouveau HTML
                listElement.prepend(response.data);
                
                // Vidage du contenu de formulaire
                form['comment'].value = '';

                setTimeout(function() {
                    listElement.find('li').first().removeClass('opacity-0');
                }, 100)
            })
            .catch(function(error) {
                modal('comment_error.reread');
            })
    }).catch(function(error) {
        modal('comment_error.add');
    });
    
    return false;
 }

// Ces fonctions sont appelées depuis les forms care request.
// Elles doivent donc être globale
window.submitPatient = submitPatient;
window.submitCareRequest = submitCareRequest;
window.submitComment = submitComment;
window.abandonCareRequest = abandonCareRequest;
window.acceptCareRequest = acceptCareRequest;
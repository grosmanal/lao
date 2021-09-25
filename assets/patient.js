import httpClient from 'axios';
import $ from 'jquery';
import modal from './components/modal';
import nullFieldConverter from './components/nullFieldConverter';
import apiFieldConverter from './components/apiFieldConverter';

/**
 * Composant Vue des disponibilités
 */
import Vue from 'vue';
import Weekvailability from './components/availability/weekAvailability.vue';

const vm = new Vue({
    el: '#week-availability',
    render: h => h(Weekvailability)
});

/**
 * Enregistrement des infos du patient
 */
//$('#patient_validate').on('click', function() {

function submitPatient(event) {
    event.preventDefault();

    const form = event.target;
    const patientId = form['patient[id]'].value;

    const url = '/api/patients/' + patientId;

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
        url: url,
        data: data
    }).then(function (response) {
        // TODO voir quoi faire
        // on pourrait mettre un check dans le bouton de validation qui s'effacerait après un timer
    }).catch(function (error) {
        modal('Erreur lors de la mise à jour du patient'); // TODO traduction
    });

    return false;
};

/**
 * Modification d'une demande
 */
function submitCareRequest(event) {
    const form = event.target;
    
    const careRequestId = form['care-request-id'].value;
    
    const url = '/api/care_requests/' + careRequestId;
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
        url: url,
        data: data
    }).then(function (response) {
        httpClient
            .get('/care_request_form/' + careRequestId)
            .then(function (response) {
                // Recherche du parent de la form pour y injecter le nouveau HTML
                let formParent = $(form).parentsUntil('.accordion-item').parent();
                // Injection du nouveau HTML
                formParent.html(response.data);
            }).catch(function(error) {
                modal('Erreur lors du ré-affichage de la demande') // TODO traduction
            });

    }).catch(function(error) {
        modal('Erreur lors de la mise à jour de la demande de soin'); // TODO traduction
    });
    
    return false;
};


/**
 * Annulation de l'abandon de la demande de prise en charge
 */
function abandonCareRequest() {
    alert('in func abandon');
}

/**
 * Annulation de l'acceptation de la demande de prise en charge
 */
 function acceptCareRequest() {
    alert('in func accept');
 }

// Ces fonctions sont appelées depuis les forms care request.
// Elles doivent donc être globale
window.submitPatient = submitPatient;
window.submitCareRequest = submitCareRequest;
window.abandonCareRequest = abandonCareRequest;
window.acceptCareRequest = acceptCareRequest;
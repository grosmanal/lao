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
$('#patient_validate').on('click', function() {
    const url = '/api/patients/' + $('#patient_id').val();
    const data = {
        firstname: $('#patient_firstname').val(),
        lastname: $('#patient_lastname').val(),
        birthdate: $('#patient_birthdate').val(),
        contact: $('#patient_contact').val(),
        phone: $('#patient_phone').val(),
        mobilePhone: $('#patient_mobilePhone').val(),
        email: $('#patient_email').val()
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
});

/**
 * Modification d'une demande
 */
function submitCareRequest() {
    const form = this;
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
                
                // Recherche de la nouvelle form dans le nouveau HTML
                // pour ré affectation de cette fonction en tant que submit
                let newFrom = $(formParent).find('form').on('submit', submitCareRequest);
            }).catch(function(error) {
                modal('Erreur lors du ré-affichage de la demande') // TODO traduction
            });

    }).catch(function(error) {
        modal('Erreur lors de la mise à jour de la demande de soin'); // TODO traduction
    });
    
    return false;
};

$('#care-requests-accordion form').on('submit', submitCareRequest);
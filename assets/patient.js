import httpClient from 'axios';
import $ from 'jquery';

/**
 * Composant Vue des disponibilités
 */
import Vue from 'vue';
import Weekvailability from './components/availability/weekAvailability.vue';

const vm = new Vue({
    el: '#week-availability',
    render: h => h(Weekvailability)
})

/**
 * Enregistrement des infos du patient
 */
$('#patient_validate').on('click', function(){
    var url = '/api/patients/' + $('#patient_id').val();
    var data = {
        firstname: $('#patient_firstname').val(),
        lasttname: $('#patient_lasttname').val(),
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
        $('#validation_message')
            .html('Patient mis à jour') // TODO traduction
            .toggle()
            .removeClass('alert-danger')
            .addClass('alert-success')
            ;
    }).catch(function (error) {
        $('#validation_message')
            .html('Erreur lors de la mise à jour') // TODO traduction
            .toggle()
            .removeClass('alert-success')
            .addClass('alert-danger')
            ;
    });

    return false;
});
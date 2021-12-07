import httpClient from 'axios';
import $ from 'jquery';
import { modal } from './components/modal';
import nullFieldConverter from './utils/nullFieldConverter';
import confirm from './utils/confirm';
import showCheckFlag from './utils/showCheckFlag';

import { submitCareRequest, insertCareRequestCreationForm } from './careRequest';
import { submitCommentMenu, submitComment, transformToSummernote } from './comment';

import './styles/patient.scss'

/**
 * Composant Vue des disponibilités
 */
import Vue from 'vue';
import Weekvailability from './components/availability/AvailabilityWeek.vue';
import Translator from 'bazinga-translator';


jQuery(function ($) {
    // Composant summernote sur chaque textarea d'ajout de commentaire
    $('.comments textarea').each(function () {
        transformToSummernote(this);
    })
    
    // Clic sur checkbox variableSchedule
    $('#variable-schedule-form input[type="checkbox"]').on('change', function () {
        updateVariableSchedule(this)
    });

    // Composant availability
    if ($('#week-availability').length > 0) {
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
    }
    
    // Scroll jusqu'à l'élément (fait ici car certains éléments sont créés dynamiquement)
    const currentUrl = new URL(window.location.href)
    if (currentUrl.hash) {
        $(currentUrl.hash).get(0).scrollIntoView();
    }
});

    
function collectPatientData(form) {
    return {
        firstname: nullFieldConverter(form['patient[firstname]'].value),
        lastname: nullFieldConverter(form['patient[lastname]'].value),
        birthdate: nullFieldConverter(form['patient[birthdate]'].value),
        contact: nullFieldConverter(form['patient[contact]'].value),
        phone: nullFieldConverter(form['patient[phone]'].value),
        mobilePhone: nullFieldConverter(form['patient[mobilePhone]'].value),
        email: nullFieldConverter(form['patient[email]'].value),
    };
}

    
function updateVariableSchedule(input) {
    const data = {
        variableSchedule: input.checked,
    }

    httpClient({
        method: 'PUT',
        url: input.form['variable_schedule[apiUrl]'].value,
        data: data
    }).then(function (response) {
        // rien à faire
    }).catch(function (error) {
        console.error(error);
        modal('patient.error.updating');
    });
}


function deletePatient(deleteButton, apiUrl) {
    confirm(deleteButton, function() {
        httpClient.delete(apiUrl)
        .then(function (response) {
            window.location = '/';
        }).catch(function (error) {
            console.error(error);
            modal('patient.error.deleting');
        });
    }, null, {
        element: null, // placement du popover sur le bouton de confirmation
        title: Translator.trans('patient.info.confirm_delete.title'),
        content: Translator.trans('patient.info.confirm_delete.content'),
        placement: 'top'
    });
}


function submitPatient(event) {
    const form = event.target;
    
    const apiUrl = $(event.submitter).data('apiUrl');
    if (apiUrl !== undefined) {
        // On utilisera l'API : ne pas continuer le submit vers l'action du formulaire
        event.preventDefault();
    }
    
    if (event.submitter.name == 'patient[update]') {
        httpClient({
            method: 'PUT',
            url: apiUrl,
            data: collectPatientData(form)
        }).then(function(response) {
            httpClient.get(response.data.relatedUri.getHtmlForm)
            .then(function(response) {
                const formParent = $(form).parent();
                $(form).replaceWith(response.data);

                showCheckFlag(
                    formParent.find('form button[name="patient[update]"]').get(0)
                )
            })
        }).catch(function(error) {
            console.error(error);
            modal('patient.error.updating');
        });
    } else if (event.submitter.name == 'patient[delete]') {
        deletePatient(event.submitter, apiUrl);
    } else if (event.submitter.name == 'patient[create]') {
        // Rien à faire : le submit se poursuit par
        // un appel en POST de /patient_new
    }
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
window.submitComment = submitComment;
window.submitCommentMenu = submitCommentMenu;
window.insertCareRequestCreationForm = insertCareRequestCreationForm;
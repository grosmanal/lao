import httpClient from 'axios';
import $ from 'jquery';
import Translator from 'bazinga-translator';
import nullFieldConverter from './utils/nullFieldConverter';
import confirm from './utils/confirm';
import removeDomElement from './utils/removeDomElement';
import showCheckFlag from './utils/showCheckFlag';
import { modal } from './components/modal';
import { transformToSummernote } from './comment';

export {
    submitCareRequest,
    insertCareRequestCreationForm,
};

jQuery(function ($) {
    $('.accept-show-details').on('click', function () {
        handleShowAcceptDetails($, this);
    })
});

/**
 * Appel ajax de la mise à jour ou création (via API) de la care request
 * puis mise à jour de l'html de la care request avec les nouvelle données
 * @param {object} form 
 * @param {object} data 
 * @param {boolean} checkFlag Afficher le check flag sur le bouton update
 */
function doSubmitCareRequest(form, data, checkFlag = true) {
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

                // bind de la fonction de gestion d'affichage des détails de prise en charge
                $('.accept-show-details').on('click', function () {
                    handleShowAcceptDetails($, this);
                })
                
                // Affichage d'un check sur le bouton du nouveau formulaire
                const updateButton = formParent.find('#care_request_upsert');
                if (checkFlag && updateButton.length) {
                    showCheckFlag(updateButton.get(0));
                }
            }).catch(function (error) {
                console.error(error);
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
        console.error(error);
        modal('care_request.error.update', { errorMessage });
    });
}

/**
 * Modification d'une demande
 */
function upsertCareRequest(form) {

    const data = {
        contactedBy: nullFieldConverter(form['care_request[contactedBy]'].value),
        contactedAt: nullFieldConverter(form['care_request[contactedAt]'].value),
        requestedDoctor: nullFieldConverter(form['care_request[requestedDoctor]'].value),
        priority: nullFieldConverter(form['care_request[priority]'].checked),
        complaint: nullFieldConverter(form['care_request[complaint]'].value),
        customComplaint: nullFieldConverter(form['care_request[customComplaint]'].value),
    };
        
    let checkFlag = undefined;
    if (form['care_request[patientUri]']) {
        // Le formulaire contient le champ (caché) patientUri, il faut l'ajouter
        // aux data pour création de la care request
        // Cas d'une création de care request
        data['patient'] = form['care_request[patientUri]'].value;
        checkFlag = false;
    } else {
        // Cas d'une modification de care request
        checkFlag = true;
    }

    doSubmitCareRequest(form, data, checkFlag);
};


/**
 * Réactivation d'une care request
 */
function reactivateCareRequest(form) {
    const data = {
        acceptedBy: null,
        acceptedAt: null,
        abandonedBy: null,
        abandonedAt: null,
        abandonedReason: null,
    };

    doSubmitCareRequest(form, data, false);
}


/**
 * Abandon de la demande de prise en charge
 */
function abandonCareRequest(form, button) {
    const data = {
        abandonedReason: nullFieldConverter(form['care_request[abandonedReason]'].value),
        abandonedBy: nullFieldConverter(form['care_request[doctorUri]'].value),
        abandonedAt: 'now',
    };

    const abandonedReason = form['care_request[abandonedReason]'];
    
    if (abandonedReason.value == '') {
        const popoverAttributes = {
            element: abandonedReason,
            title: Translator.trans('care_request.confirm_abandon.title'),
            content: Translator.trans('care_request.confirm_abandon.content'),
            placement: 'bottom',
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
function acceptCareRequest(form, htmlButton) {
    let data;

    if ($(htmlButton).data('detailsShown') == 1) {
        // Les détails de la prise en charge sont affichés
        data = {
            acceptedBy: nullFieldConverter(form['care_request[acceptedBy]'].value),
            acceptedAt: nullFieldConverter(form['care_request[acceptedAt]'].value)
        };
    } else {
        // Les détails de la prise en charge sont cachés
        // La prise en charge se fait par le docteur en cours ce jour
        data = {
            acceptedBy: nullFieldConverter(form['care_request[doctorUri]'].value),
            acceptedAt: 'now',
        };
    }

    doSubmitCareRequest(form, data);
}


/**
 * Suppression de la care request
 * @param {HTMLButtonElement} button 
 */
function deleteCareRequest(button) {
    const apiUrlDelete = button.dataset.apiUrlDelete;
    const elementToRemove = $(button).parentsUntil('#care-requests-accordion', '.accordion-item');

    confirm(button, function() {
        // Suppression de la care request
        httpClient.delete(apiUrlDelete)
            .then(function (response) {
                // Suppression de la care request du DOM
                removeDomElement(elementToRemove.get(0));
            }).catch(function (error) {
                console.error(error);
                modal('care_request.error.delete');
            })
        ;
    });
}


function submitCareRequest(event) {
    event.preventDefault();

    const form = event.target;
    
    if (event.submitter.name == 'care_request[upsert]') {
        upsertCareRequest(form);
    } else if (event.submitter.name == 'care_request[reactivate]') {
        reactivateCareRequest(form);
    } else if (event.submitter.name == 'care_request[abandon]') {
        abandonCareRequest(form, event.submitter);
    } else if (event.submitter.name == 'care_request[accept]') {
        acceptCareRequest(form, event.submitter);
    } else if (event.submitter.name == 'care_request[delete]') {
        deleteCareRequest(event.submitter);
    }
}


/**
 * Insertion d'un formulaire de création de care request dans
 * la liste des care request
 * @param {Event} event 
 */
function insertCareRequestCreationForm(event) {
    event.preventDefault();

    const form = event.target;

    // Recherche de l'URL du formulaire de création de le care request
    const urlCareRequestForm = form['careRequestCreationFormUrl'].value;

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
            console.error(error);
            modal('care_request.error.reread');
        });
}

/**
 * 
 * @param {JQueryStatic} $ jQuery
 * @param {HTMLAnchorElement} el 
 */
function handleShowAcceptDetails($, htmlEl) {
    const el = $(htmlEl);
    const buttonAccept = $(el.data('acceptButtonId'));

    const detailsFields = $(el.attr('href')).find('[name^="care_request"]');

    if (el.hasClass('collapsed')) {
        // Label du lien qui collapse
        el.text(el.data('labelDetailsHidden'));

        // Label du bouton d'acceptation
        buttonAccept.text(buttonAccept.data('labelDetailsHidden'));

        // Marquage des data du bouton
        buttonAccept.data('detailsShown', 0);

        // Disable des champs du détail (pour annuler le required)
        detailsFields.prop('disabled', true);
    } else {
        // Label du lien qui collapse
        el.text(el.data('labelDetailsShown'));

        // Label du bouton d'acceptation
        buttonAccept.text(buttonAccept.data('labelDetailsShown'));

        // Marquage des data du bouton
        buttonAccept.data('detailsShown', 1);

        // Enable des champs du détail (pour activer le required)
        detailsFields.prop('disabled', false);
    }
}
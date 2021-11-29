import $ from 'jquery';
import Translator from 'bazinga-translator';

function doConfirmAction(cancelConfirmationTimeout, confirmCallback, confirmCallbackParameters) {
    // Suppression du timeout d'annulation de la confirmation
    clearTimeout(cancelConfirmationTimeout);

    confirmCallback(...confirmCallbackParameters);
}

/**
 * 
 * @param {HTMLElement} domActionButton 
 * @param {function} confirmCallback 
 * @param {Iterable} confirmCallbackParameters
 * @param {string} confirmLabel 
 */
export default function (
    domActionButton,
    confirmCallback,
    confirmCallbackParameters = {},
    additionalCancelConfrimCallback = null,
    warningClass = '',
    confirmLabel = undefined
) {
    // Changement de l'apparence du bouton
    const actionButton = $(domActionButton);
    
    // Création du bouton de confirmation
    const confirmButton = $(document.createElement('button'));
    const confirmButtonContent =
        '<i class="bi bi-exclamation-diamond"></i> ' +
        (confirmLabel !== undefined ? confirmLabel : Translator.trans('confirm.button_label'));
        
    
    // Ajout des classe du bouton d'origine
    // et transfomation du danger en warning
    domActionButton.classList.forEach(function(actionButtonClass) {
        const dangerClass = actionButtonClass.match(/^([a-z\-]*)\-danger$/)
        if (dangerClass != null) {
            confirmButton.addClass(dangerClass[1] + '-warning');
        } else {
            confirmButton.addClass(actionButtonClass);
        }
    });
    
    if (warningClass != '') {
        confirmButton.addClass(warningClass);
    }

    confirmButton
        .html(confirmButtonContent)
    ;
    
    // Timeout pour annuler la demande de confirmation
    const cancelConfirmationTimeout = setTimeout(function() {
        confirmButton.replaceWith(actionButton);
        additionalCancelConfrimCallback();
    }, 2000);

    confirmButton
        .on('click', function(event) {
            event.preventDefault();
            doConfirmAction(cancelConfirmationTimeout, confirmCallback, confirmCallbackParameters);
        })
    ;

    actionButton.replaceWith(confirmButton);
}
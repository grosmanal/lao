import $ from 'jquery';
import Translator from 'bazinga-translator';

function doConfirmAction(cancelConfirmationTimeout, confirmCallback) {
    // Suppression du timeout d'annulation de la confirmation
    clearTimeout(cancelConfirmationTimeout);

    confirmCallback();
}

export default function (domActionButton, confirmCallback, confirmLabel = undefined) {
    // Changement de l'apparence du bouton
    const actionButton = $(domActionButton);
    
    // Création du bouton de confirmation
    const confirmButton = $(document.createElement('button'));
    const confirmButtonContent =
        '<i class="bi bi-exclamation-diamond"></i> ' +
        (confirmLabel !== undefined ? confirmLabel : Translator.trans('confirm.button_label'));

    confirmButton
        .addClass('btn') // TODO si le actionButton a btn
        .addClass('btn-outline-warning')
        .html(confirmButtonContent)
    ;
    
    
    // Timeout pour annuler la demande de confirmation
    const cancelConfirmationTimeout = setTimeout(function() {
        confirmButton.replaceWith(actionButton);
    }, 2000);

    confirmButton
        .on('click', function(event) { event.preventDefault(); doConfirmAction(cancelConfirmationTimeout, confirmCallback); })
    ;

    actionButton.replaceWith(confirmButton);
}
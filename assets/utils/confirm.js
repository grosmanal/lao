import $ from 'jquery';
import Translator from 'bazinga-translator';
import { Popover } from 'bootstrap';

function doConfirmAction(cancelConfirmationTimeout, confirmCallback) {
    // Suppression du timeout d'annulation de la confirmation
    clearTimeout(cancelConfirmationTimeout);

    confirmCallback();
}

/**
 * 
 * @param {HTMLElement} domActionButton Bouton concerné par la confirmation
 * @param {function} confirmCallback Callback a appeler si confirmation
 * @param {string} warningClass Classe à ajouter à l'élément de confimation
 * @param {Object} popoverAttributes Attributs pour création d'un popover
 * @param {Element|null} popoverAttributes.element Élement DOM qui recevra le popover
 * @param {string} popoverAttributes.title Titre du popover
 * @param {string} popoverAttributes.content Contenu du popover
 * @param {string} popoverAttributes.placement Placement du popover
 * @param {string} confirmLabel Libellé demandant la confirmation
 */
export default function (
    domActionButton,
    confirmCallback,
    warningClass = '',
    popoverAttributes = null,
    confirmLabel = null,
) {
    // Changement de l'apparence du bouton
    const actionButton = $(domActionButton);
    
    // Création du bouton de confirmation
    const confirmButton = $(document.createElement('button'));
    const confirmButtonContent =
        '<i class="bi bi-exclamation-diamond"></i> ' +
        (confirmLabel != null ? confirmLabel : Translator.trans('confirm.button_label'));
        
    
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
    
    // Création du popover
    let popover = null;
    if (popoverAttributes !== null) {
        if (popoverAttributes.element == null) {
            // L'élément sur lequel positionner le popover n'est pas précisé
            // On place donc le popover sur le bouton de confirmation
            popoverAttributes.element = confirmButton.get(0);
        }

        popover = Popover.getOrCreateInstance(popoverAttributes.element, {
            title: popoverAttributes.title,
            content: popoverAttributes.content,
            placement: popoverAttributes.placement,
            trigger: 'manual',
        });
    }

    // Timeout pour annuler la demande de confirmation
    const cancelConfirmationTimeout = setTimeout(function() {
        if (popover != null) {
            popover.hide();
        }

        confirmButton.replaceWith(actionButton);
    }, 4000);

    // Déclenchement de l'action si click sur le bouton de confirmation
    confirmButton
        .on('click', function(event) {
            event.preventDefault();
            
            if (popover != null) {
                // Destruction du popover
                popover.dispose();
            }
            doConfirmAction(cancelConfirmationTimeout, confirmCallback);
        })
    ;

    actionButton.replaceWith(confirmButton);
    if (popover != null) {
        popover.show();
    }
}
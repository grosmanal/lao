import $ from 'jquery';

/**
 * Affiche un check sur le bouton qui a permis de mettre à jour des données
 * @param {HTMLButtonElement} button 
 */
export default function(button) {
    const previousContent = $(button).html();
    
    const check = document.createElement('i');
    $(check)
        .addClass('bi')
        .addClass('bi-check-lg')
    ;
    
    $(button).html(previousContent + '&nbsp;');
    $(button).append(check);    
    
    setTimeout(function() {
        $(button).html(previousContent);
    }, 4000);
}
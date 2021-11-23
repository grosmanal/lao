import $ from 'jquery';
import Translator from 'bazinga-translator';
import {Modal as bootstrapModal} from 'bootstrap';

function modal (message, messageParameters = {}, title = 'modal.title.alert') {
    // Alimentation du titre
    $('#modal-window .modal-title').html(Translator.trans(title));
    
    // Alimentation du contenu
    $('#modal-window .modal-body p').html(Translator.trans(message, messageParameters));

    const modalWindow = new bootstrapModal($('#modal-window'));
    modalWindow.show();
}

function modalOrConsole(message, messageParameters = {}, title = 'modal.title.alert') {
    if (typeof message === 'string') {
        modal(message, messageParameters, title);
    }
    else {
        console.error(error);
    }
}

export { modal, modalOrConsole };
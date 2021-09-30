import $ from 'jquery';
import {Modal as bootstrapModal} from 'bootstrap';
import Translator from 'bazinga-translator';

export default function(message, title = 'Alert') {
    // Alimentation du titre
    $('#modal-window .modal-title').html(Translator.trans(title));
    
    // Alimentation du contenu
    $('#modal-window .modal-body p').html(Translator.trans(message));

    const modalWindow = new bootstrapModal($('#modal-window'));
    modalWindow.show();
}
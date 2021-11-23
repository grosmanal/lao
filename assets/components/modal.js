import $ from 'jquery';
import {Modal as bootstrapModal} from 'bootstrap';

export default function(message, messageParameters = {}, title = 'modal.title.alert') {
    // Alimentation du titre
    $('#modal-window .modal-title').html(Translator.trans(title));
    
    // Alimentation du contenu
    $('#modal-window .modal-body p').html(Translator.trans(message, messageParameters));

    const modalWindow = new bootstrapModal($('#modal-window'));
    modalWindow.show();
}
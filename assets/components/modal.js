import $ from 'jquery';
import {Modal as bootstrapModal} from 'bootstrap';

export default function(message, title = 'Alerte') {
    // Alimentation du titre
    $('#modal-window .modal-title').html(title);
    
    // Alimentation du contenu
    $('#modal-window .modal-body p').html(message);

    const modalWindow = new bootstrapModal($('#modal-window'));
    modalWindow.show();
}
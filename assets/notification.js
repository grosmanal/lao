import httpClient from 'axios';
import $ from 'jquery';
import { modal } from './components/modal';
import removeDomElement from './utils/removeDomElement';

function setNotificationRead(form) {
    const apiUrl = form['apiUrl'].value;

    return httpClient.put(apiUrl, {
        readAt: 'now',
    })
    .catch(function(error) {
        console.error(error);
        modal('notification.error.updating');
    })
    ;
}

function submitNotification(event) {
    event.preventDefault();
    
    const form = event.target;
    
    let afterMarkCallback;
    if (event.submitter.name == 'mark') {
        afterMarkCallback = function() {
            // Suppression de la notification dans le DOM
            const notificationItem = $(form).parentsUntil("ul.unread-notifications", "li.notification");
            const notificationsList = notificationItem.parent();
            
            removeDomElement(notificationItem.get(0))
            .then(() => {
                if (notificationsList.children('li.notification').length == 0) {
                    // Il n'y a plus de notification : suppression du badge dans la navbar
                    $('#notificationIndicator').hide();
                }
            });
        };
    }
    else if (event.submitter.name == 'markAndGo') {
        afterMarkCallback = function() {
            // Redirection vers la fiche du patient
            window.location = form['commentUrl'].value;
        };
    }

    setNotificationRead(form)
    .then(afterMarkCallback)
    ;

}

window.submitNotification = submitNotification;
import httpClient from 'axios';

import './styles/home.scss';
import removeDomElement from './utils/removeDomElement';

jQuery(function ($) {
    $('section.articles article .btn-close').on('click', function(event) {
        event.preventDefault();
        markArticleAsRead($, event.currentTarget)
    })
})

function markArticleAsRead($, closeButton) {
    httpClient({
        method: 'POST',
        url: $(closeButton).data('markReadUrl'),
        data: null
    }).then(function (response) {
        // Suppression de l'article marqué
        const articleItem = $(closeButton).parentsUntil('ul.articles-list', 'li');
        const nextArticleItem = articleItem.next();
        const articleSection = articleItem.parents('section');
        
        removeDomElement(articleItem.get(0), function() {
            if (nextArticleItem.length > 0) {
                // Affichage d'un éventuel autre article
                nextArticleItem.removeClass('d-none');
            } else {
                // Il n'y a pas d'autre article : on supprime la section
                console.log(articleSection.remove());
            }
        });

    }).catch(function (error) {
        console.error(error);
        modal('article.error.markRead');
    });

}
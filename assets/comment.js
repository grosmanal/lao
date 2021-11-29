import httpClient from 'axios';
import $ from 'jquery';
import Translator from 'bazinga-translator';

import nullFieldConverter from './utils/nullFieldConverter';
import removeDomElement from './utils/removeDomElement';
import { modal } from './components/modal';

import 'summernote/dist/summernote-lite';
import 'summernote/dist/summernote-lite.css';
//import 'summernote/dist/summernote-bs5';
//import 'summernote/dist/summernote-bs5.css';
import 'summernote/dist/lang/summernote-fr-FR';

import './styles/comment.scss'

export {
    submitComment,
    submitCommentMenu,
    transformToSummernote,
};


/**
 * Transformation d'un textarea en summernote
 * @param {HTMLTextAreaElement} commentElement Élément textarea à transformer
 */
function transformToSummernote(commentElement)
{
    const formElement = $(commentElement).parents('form');
    let doctors
    try {
        doctors = JSON.parse(formElement.get(0)['comment[doctors]'].value);
    } catch (e) {
        if (e instanceof SyntaxError) {
            doctors = [];
        } else {
            throw e;
        }
    } 
    
    $(commentElement).summernote({
        lang: 'fr-FR',
        height: 200,
        placeholder: Translator.trans('care_request.comment.placeholder'),
        toolbar: [
            ['style', ['style']],
            ['character', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
        ],
        hintSelect: 'next',
        hint: {
            mentions: doctors,
            match: /\B@(\w*)$/,
            search: function (keyword, callback) {
                callback($.grep(this.mentions, function(item) {
                    return item.displayName.toLowerCase().indexOf(keyword.toLowerCase()) == 0;
                }));
            },
            template: function(item) {
                return item.displayName;
            },
            content: function(item) {
                return $('<span>')
                    .attr('data-mention-doctor-id', item.id)
                    .addClass('mention')
                    .text(item.displayName)
                    .get(0)
                    ;
            }    
        }
    });

}

    
/**
 * Transormation d'un commentaire affiché en textarea éditable via summernote
 * @param {HTMLFormElement} form Formulaire contenant le bouton d'édition
 */
function editComment(form)
{
    // Récupération du formulaire de modification
    httpClient
        .get(form['url-get-form'].value)
        .then(function(response) {
            // Recherche de l'item de la liste des commentaires pour y injecter le nouveau HTML
            const commentListItem = $(form).parentsUntil('ul.comments', 'li.comment');

            // Cache du commentaire actuel et de la barre de bouton
            commentListItem.children().hide();
            commentListItem.children().filter('.d-flex').removeClass('d-flex');

            // Injection du nouveau HTML
            const newTextArea = commentListItem.append(response.data).find('textarea');
            
            // Transformation en summernote
            transformToSummernote(newTextArea.get(0));
        }).catch(function(error) {
            modal('care_request.error.reread');
        });
}


/**
 * Suppression d'un commentaire
 * @param {HTMLFormElement} form Formulaire contenant le bouton de suppression
 */
function deleteComment(form)
{
    httpClient({
        method: 'delete',
        url: form['url-api-delete'].value,
    }).then(function (response) {
        // Recherche de l'item de la liste des commentaires pour y le supprimer
        const commentListItem = $(form).parentsUntil('ul.comments', 'li.comment');
        removeDomElement(commentListItem.get(0));
    }).catch(function(error) {
        console.log(error);
        modal('comment.error.delete');
    });
}

function submitCommentMenu(event) {
    event.preventDefault();
    
    const form = event.target;
    
    switch(event.submitter.name) {
        case 'edit':
            editComment(form);
            break;
        case 'delete':
            deleteComment(form);
            break;
    }

    return false;
}



/**
 * Annulation de l'édition d'un commentaire
 * @param {HTMLFormElement} form Formulaire contenant textarea d'édition
 */
function cancelEditComment(form)
{
    const commentListItemJq = $(form).parent();

    // Suppression du formulaire d'édition
    $(form).remove();

    // Démasquage du commentaire précédement caché
    commentListItemJq.children().show();
    commentListItemJq.children().filter('article').addClass('d-flex');
}


/**
 * Ajout d'un commentaire une care request
 */
function submitComment(event) {
    event.preventDefault();

    const form = event.target;

    switch(event.submitter.name) {
        case 'comment[cancel]':
            cancelEditComment(form);
            break;
        case 'comment[upsert]':
            upsertComment(form);
            break;
    }
    
    return false;
}

/**
 * Création ou mise à jour d'un commentaire
 * @param {HTMLFormElement} form Formulaire contenant textarea d'édition
 */
function upsertComment(form)
{
    let updating
    if (form['comment[apiAction]'].value === 'PUT') {
        updating = true;
    } else {
        updating = false;
    }

    const data = {
        author: form['comment[authorApiUri]'].value,
        careRequest: form['comment[careRequestApiUri]'].value,
        content: nullFieldConverter(form['comment[content]'].value),
    }
    
    if (updating) {
        data.modificationDate = 'now';
    } else {
        data.creationDate = 'now';
    }
    
    httpClient({
        method: form['comment[apiAction]'].value,
        url: form['comment[apiUrl]'].value,
        data,
    }).then(function (response) {
        if (updating) {
            updateCommentInList(form, response.data.relatedUri.getHtmlContent);
        } else {
            prependCommentInList(form, response.data.relatedUri.getHtmlContent);
        }
    }).catch(function(error) {
        modal('comment.error.add');
    });
    
    return false;
 }


function updateCommentInList(form, urlHtmlContent)
{
    const commentListItem = $(form).parent();
    
    // Suppression du contenu de l'élément de liste
    commentListItem.empty();

    // Alimentation de la liste avec le nouveau contenu du commentaire
    httpClient
        .get(urlHtmlContent)
        .then(function(response) {
            // Suppression du li père avec append de chacun de ses enfant
            // Je n'utilise pas replaceWith car cette fonction ne me permet pas
            // de récupéré le nouvel élément et donc de supprimer sa class opacity-0
            const newCommentListItem = $(response.data)
            newCommentListItem.children().each(function() {
                commentListItem.append($(this));
            })
        }) 
        .catch(function(error) {
            modal('comment.error.reread');
        })
        ;
}


function prependCommentInList(form, urlHtmlContent)
{
    // Insertion du commentaire dans la liste des commentaires existants
    httpClient
        .get(urlHtmlContent)
        .then(function(response) {
            // Réinitialisation du summernote
            $(form['comment[content]']).summernote('reset');

            // Recherche de l'élément liste
            const listElement = $(form).siblings('ul.comments');
            
            // Injection du nouveau HTML
            listElement.prepend(response.data);
            
            // Vidage du contenu de formulaire
            form['comment[content]'].value = '';

            setTimeout(function() {
                listElement.find('li').first().removeClass('opacity-0');
            }, 100)
        })
        .catch(function(error) {
            modal('comment.error.reread');
        })
        ;
}
import httpClient from 'axios';
import $ from 'jquery';
import nullFieldConverter from './utils/nullFieldConverter';
import apiFieldConverter from './utils/apiFieldConverter';
import modal from './components/modal';

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
        doctors = JSON.parse(formElement.get(0)['doctors'].value);
    } catch (e) {
        if (e instanceof SyntaxError) {
            doctors = [];
        } else {
            throw e;
        }
    } 
    
    $(commentElement).summernote({
        height: 200,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['style']],
            ['character', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
        ],
        hint: {
            mentions: doctors.map(doctor => doctor.fullname),
            match: /\B@(\w*)$/,
            search: function (keyword, callback) {
                callback($.grep(this.mentions, function(item) {
                    return item.indexOf(keyword) == 0;
                }));
            },
            template: function(item) {
                return item;
            },
            content: function(item) {
                // Problème : le curseur est à l'intérieur de l'élément créé
                // Si l'utilisateur continue de taper, cela agrandit le span
                // cf https://github.com/summernote/summernote/issues/4178
                /*
                return $('<span>')
                    .attr('data-mention', item)
                    .css('background-color', 'silver')
                    .text(this.template(item))
                    .get(0)
                    ;
                */
                return '@' + doctors.find(doctor => doctor.fullname === item).email;
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
            // Recherche du parent de la form pour y injecter le nouveau HTML
            let commentListItemJq = $(form).parent();

            // Cache du commentaire actuel et de la barre de bouton
            commentListItemJq.children().hide();
            commentListItemJq.children().filter('.d-flex').removeClass('d-flex');

            // Injection du nouveau HTML
            let newTextArea = commentListItemJq.append(response.data).find('textarea');
            
            // Transformation en summernote
            transformToSummernote(newTextArea.get(0));
        }).catch(function(error) {
            modal('care_request_error.reread');
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
        const comment = $(form).parent();
        const commentListItem = comment.get(0);
        
        commentListItem.addEventListener('transitionend', function(event) {
            if (event.target !== commentListItem) {
                return;
            }

            this.remove();
        });

        // Visibility pour transition jolie
        comment.addClass('opacity-0');
    }).catch(function(error) {
        modal('comment_error.delete');
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
        case 'cancel':
            cancelEditComment(form);
            break;
        case 'upsert':
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
    let commentId, updating
    if (form['comment-id'] === undefined) {
        updating = false;
    } else {
        updating = true;
        commentId = form['comment-id'].value;
    }

    const careRequestId = form['care-request-id'].value;
    const authorId = form['user-id'].value;
    const comment = nullFieldConverter(form['comment'].value);
    
    const data = {
        author: apiFieldConverter(authorId, 'Doctor'),
        careRequest: apiFieldConverter(careRequestId, 'CareRequest'),
        content: comment,
    }
    
    if (updating) {
        data.modificationDate = 'now';
    } else {
        data.creationDate = 'now';
    }
    
    let method, url
    if (updating) {
        method = 'put';
        url = form['url-api-put'].value;
    } else {
        method = 'post';
        url = form['url-api-post'].value;
    }
    
    httpClient({
        method,
        url,
        data,
    }).then(function (response) {
        if (updating) {
            updateCommentInList(form);
        } else {
            prependCommentInList(form, response.data.relatedUri.getHtmlContent);
        }
    }).catch(function(error) {
        modal('comment_error.add');
    });
    
    return false;
 }


function updateCommentInList(form)
{
    const commentListItem = $(form).parent();
    
    // Suppression du contenu de l'élément de liste
    commentListItem.empty();

    // Alimentation de la liste avec le nouveau contenu du commentaire
    httpClient
        .get(form['url-get-content'].value)
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
            modal('comment_error.reread');
        })
        ;
}


function prependCommentInList(form, urlHtmlContent)
{
    // Réinitialisation du summernote
    $(form['comment']).summernote('reset');

    // Insertion du commentaire dans la liste des commentaires existants
    httpClient
        .get(urlHtmlContent)
        .then(function(response) {
            // Recherche de l'élément liste
            const listElement = $(form).siblings('ul.comments');
            
            // Injection du nouveau HTML
            listElement.prepend(response.data);
            
            // Vidage du contenu de formulaire
            form['comment'].value = '';

            setTimeout(function() {
                listElement.find('li').first().removeClass('opacity-0');
            }, 100)
        })
        .catch(function(error) {
            modal('comment_error.reread');
        })
        ;
}
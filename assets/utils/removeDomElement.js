/**
 * Suppression d'un élément du DOM après
 * avoir transformé son opacité
 * @param {Element} domElement 
 */
export default function(domElement) {
    // Ajout d'un event listener pour supprimer
    // l'élément après la transition

    // Modification de l'effet de transition
    domElement.style.transition = 'all 0.5s linear';

    domElement.addEventListener('transitionend', function(event) {
        if (event.target !== domElement) {
            return;
        }

        this.remove();
    })
    
    domElement.classList.add('opacity-0');
}
/**
 * Suppression d'un élément du DOM après
 * avoir transformé son opacité
 * @param {Element} domElement 
 * @returns {Promise}
 */
export default function(domElement) {
    // Modification de l'effet de transition
    domElement.style.transition = 'all 0.5s linear';

    return transitionPromise(domElement, 'opacity', '0')
    .then(() => {
        domElement.remove();
    });
}

// Inspiré de https://vaggrippino.github.io/blog/transitionend-Promise/
function transitionPromise(el, property, value) {
    return new Promise((resolve, reject) => {
        el.style[property] = value;
    
        let transitionHandler = function (e) {
            if (e.propertyName !== property) return;
            el.removeEventListener('transitionend', transitionHandler);
            resolve();
        };
    
        el.addEventListener('transitionend', transitionHandler);
    })
}
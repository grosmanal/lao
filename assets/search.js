import './styles/search.scss';

// Permettre de cliquer sur les rows du tableau de résultat
jQuery(function($) {
    $('.search-result table > tbody > tr').on('click', function(event) {
        // Clic sur une row => chargement de la page patient
        window.location.assign($(event.currentTarget).data('patient-url'));
    });
});
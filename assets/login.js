import $ from 'jquery';

import './styles/login.scss';

function toggleShowPassword(event) {
    const cb = $(event.target);
    const password = cb.parents('form').find('input[name="password"]')
    
    password.attr('type', cb.is(':checked') ? 'text' : 'password');
}

window.toggleShowPassword = toggleShowPassword;
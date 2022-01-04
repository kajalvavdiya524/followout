window._ = require('lodash');

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap-sass');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
// window.axios = require('axios');
// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    // window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
window.toastr = require('toastr');
toastr.options.newestOnTop = false;
toastr.options.timeOut = 10000;
toastr.options.extendedTimeOut = 15000;

window.selectize = require('selectize');

window.Cookies = require('js-cookie');

window.jstz = require('jstz');

window.owlCarousel = require('owl.carousel');

window.moment = require('moment');

window.Cleave = require('cleave.js');

require('textarea-autosize');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': token.content,
    }
});

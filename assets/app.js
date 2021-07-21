/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './styles/app.scss';
import bsCustomFileInput from 'bs-custom-file-input';

const $ = require('jquery');
require('bootstrap');
// require('bootstrap-star-rating');
// // require 2 CSS files needed
// require('bootstrap-star-rating/css/star-rating.css');
// require('bootstrap-star-rating/themes/krajee-svg/theme.css');


// start the Stimulus application
import './bootstrap';

bsCustomFileInput.init();

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();

    const oldModalTitle = $('#confirmModal .modal-title').text();

    $('#confirmModal').on('show.bs.modal', function (e) {
        let button = $(e.relatedTarget) // Button that triggered the modal
        let entityId = button.data('entity-id') // Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        let modal = $(this)
        let titleBlock = modal.find('.modal-title')
        let title = titleBlock.text()
        let newText = title + ' #' + entityId
        titleBlock.text(newText)
        modal.find('.modal-body input').val(entityId)

        modal.find('#deleteAgreeButton').attr({'href': button.attr('data-href')})

    })


    $('#confirmModal').on('hide.bs.modal', function (e) {
        let modal = $(this)
        let titleBlock = modal.find('.modal-title')
        titleBlock.text(oldModalTitle)
        $('#deleteAgreeButton').attr({'href': '#'})
    })
});

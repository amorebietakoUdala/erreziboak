import Translator from 'bazinga-translator';
const translations = require('../../../public/translations/' + Translator.locale + '.json');

import Swal from 'sweetalert2';

export function showAlert(title, html, confirmationButtonText, cancelButtonText, confirmURL) {
    Swal.fire({
        title: title,
        html: html,
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: cancelButtonText,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmationButtonText,
    }).then(function(result) {
        if (result.value) {
            document.location.href = confirmURL;
        }
    });
}

export function createAlert(e, confirmURL) {
    Translator.fromJSON(translations);
    var numeroRecibo = typeof($(e.currentTarget).data('numerorecibo')) === 'undefined' ? $(e.currentTarget).data('referencia') : $(e.currentTarget).data('numerorecibo');
    var concepto = $(e.currentTarget).data('concepto');
    var importe = $(e.currentTarget).data('importe');
    var ultimodiapago = $(e.currentTarget).data('ultimodiapago');
    var email = $(e.currentTarget).data('email');
    var html = Translator.trans('receipt.numeroRecibo') + ": " + numeroRecibo + '<br/>' +
    Translator.trans('receipt.concepto') + ": " + concepto + '<br/>' +
    Translator.trans('receipt.importe') + ": " + importe + '<br/>' +
    Translator.trans('receipt.ultimoDiaPago') + ": " + ultimodiapago + '<br/>';
    if (email !== '') {
        html = html + Translator.trans('receipt.email') + ": " + email + '<br/>';
    }

    showAlert(Translator.trans('confirmation.title'), html, Translator.trans('confirmation.pay'), Translator.trans('confirmation.cancel'), confirmURL);
}

export function createConfirmationAlert(confirmURL) {
    Translator.fromJSON(translations);
    showAlert(
        Translator.trans('messages.confirmacion'),
        Translator.trans('messages.confirmationDetail'),
        Translator.trans('messages.si'),
        Translator.trans('messages.no'),
        confirmURL
    );
}
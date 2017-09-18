function disableTransactionButtons() {
    $('*[data-url-transaction-capture], *[data-url-transaction-void], *[data-url-transaction-refund]')
        .attr('disabled', true);
}

function enableTransactionButtons() {
    $('*[data-url-transaction-capture], *[data-url-transaction-void], *[data-url-transaction-refund]')
        .attr('disabled', false);
}

function modalConfirm(url, text, ajax) {
    var modal = '#squareup-confirm-modal';
    var content = '#squareup-confirm-modal-content';
    var button = '#squareup-confirm-ok';

    $(content).html(text);
    $(button).unbind().click(function() {
        disableTransactionButtons();

        $(modal).modal('hide');

        if (ajax) {
            transactionRequest('GET', url);
        } else {
            document.location = url;
        }
    });

    $(modal).modal('show');
}

function refundInputValidate(reason_input, amount_input) {
    var result = true;

    if (!$(reason_input)[0].checkValidity()) {
        $(reason_input).closest('.form-group').addClass('has-error');
        result = false;
    } else {
        $(reason_input).closest('.form-group').removeClass('has-error');
    }

    if (!$(amount_input)[0].checkValidity()) {
        $(amount_input).closest('.form-group').addClass('has-error');
        result = false;
    } else {
        $(amount_input).closest('.form-group').removeClass('has-error');
    }

    return result;
}

function modalRefund(url, text_reason, text_amount, ajax) {
    var modal = '#squareup-refund-modal';
    var content_reason = '#squareup-refund-modal-content-reason';
    var content_amount = '#squareup-refund-modal-content-amount';
    var button = '#squareup-refund-ok';
    var reason_input = '#squareup-refund-reason';
    var amount_input = '#squareup-refund-amount';

    $(content_reason).html(text_reason);
    $(content_amount).html(text_amount);

    $(reason_input).val('');
    $(amount_input).val('');

    $(button).unbind().click(function() {
        if (!refundInputValidate(reason_input, amount_input)) {
            return;
        }

        disableTransactionButtons();

        $(modal).modal('hide');

        if (ajax) {
            transactionRequest('POST', url, {
                reason: $(reason_input).val(),
                amount: $(amount_input).val()
            });
        } else {
            $('#refund_reason').val($(reason_input).val());
            $('#refund_amount').val($(amount_input).val());
            $('#transaction_form').submit();
        }
    });

    $(modal).modal('show');
}

<?php if ($sandbox_message) : ?>
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-circle"></i>&nbsp;<?php echo $sandbox_message; ?>
    </div>
<?php endif; ?>
<div id="squareup-notification">
    <div class="text-center alert alert-info"><i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;<?php echo $text_loading; ?></div>
</div>
<form class="form-horizontal" action="<?php echo $action; ?>" method="POST" id="squareup_card_details_form" style="display: none;">
    <fieldset>
        <legend><?php echo $text_card_details; ?></legend>
        <?php if (!empty($cards)) : ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="squareup_select_card"><?php echo $text_saved_card; ?></label>
            <div class="col-sm-10">
                <select class="form-control" name="squareup_select_card" id="squareup_select_card">
                    <option <?php echo !$has_selected_card ? 'selected' : ''; ?> value><?php echo $text_new_card; ?></option>
                    <?php foreach ($cards as $card) { ?>
                        <option value="<?php echo $card['id'];?>" <?php echo $card['selected'] ? 'selected' : ''; ?>><?php echo $card['text']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <?php endif; ?>
    </fieldset>
    <fieldset id="hide-card-details" <?php echo $has_selected_card ? 'style="display: none;"' : ''; ?>>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="id_card_number"><?php echo $text_card_number; ?></label>
            <div class="col-sm-10"><div id="id_card_number"></div></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="id_card_expiry"><?php echo $text_card_expiry; ?></label>
            <div class="col-sm-10"><div id="id_card_expiry"></div></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="id_cvc"><?php echo $text_card_cvc; ?></label>
            <div class="col-sm-10"><div id="id_cvc"></div></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="id_card_zip_code"><?php echo $text_card_zip; ?></label>
            <div class="col-sm-10"><div id="id_card_zip_code"></div></div>
        </div>
        <?php if ($is_logged) : ?>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="squareup_save_card"><?php echo $text_card_save; ?></label>
                <div class="col-sm-10">
                    <input type="checkbox" id="squareup_save_card" value="1" name="squareup_save_card"/>
                </div>
            </div>
        <?php endif; ?>
    </fieldset>
    <div class="buttons">
        <div class="pull-right">
            <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm-order" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary">
        </div>
    </div>
    <input type="hidden" name="squareup_nonce" id="squareup_nonce"/>
</form>
<style>
    .control-label {
        cursor: pointer;
    }

    .form-control--focus {
        /* Indicates how form inputs should appear when they have focus */
        outline: 5px auto rgb(59, 153, 252);
    }
    .form-control--error {
        /* Indicates how form inputs should appear when they contain invalid values */
        outline: 5px auto rgb(255, 97, 97);
    }

    #squareup_save_card {
        margin-top: 10px;
        cursor: pointer;
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        var app_id = '<?php echo $app_id; ?>';

        var paymentFormSettings = {
            applicationId: app_id,
            inputClass: 'form-control',
            inputStyles: [{
                fontSize: '15px'
            }],
            cardNumber: {
                elementId: 'id_card_number',
                placeholder: '•••• •••• •••• ••••'
            },
            cvv: {
                elementId: 'id_cvc',
                placeholder: 'CVV'
            },
            expirationDate: {
                elementId: 'id_card_expiry',
                placeholder: 'MM/YY'
            },
            postalCode: {
                elementId: 'id_card_zip_code'
            },
            callbacks: {
                // Called when the SqPaymentForm completes a request to generate a card
                // nonce, even if the request failed because of an error.
                cardNonceResponseReceived: function(errors, nonce, cardData) {
                    if (errors) {
                        var message = '';

                        errors.forEach(function(error) {
                            message += error.message + '. ';
                        });

                        squareupError(message);
                    } else {
                        // No errors occurred. Extract the card nonce.
                        $('#squareup_nonce').val(nonce);
                        submitForm();
                    }
                },

                unsupportedBrowserDetected: function() {
                    squareupError("<?php echo $error_browser_not_supported; ?>");
                    // Fill in this callback to alert buyers when their browser is not supported.
                },

                paymentFormLoaded: function() {
                    // Fill in this callback to perform actions after the payment form is
                    // done loading (such as setting the postal code field programmatically).
                    paymentForm.setPostalCode('<?php echo $payment_zip; ?>');

                    $('#squareup-notification').empty();
                    $('#squareup_card_details_form').slideDown();
                }
            }
        };

    function submitForm() {
        var form = '#squareup_card_details_form';

        $.ajax({
            url : $(form).attr('action'),
            dataType : 'json',
            type : $(form).attr('method'),
            data : $(form).serialize(),
            beforeSend : function() {
                $('#button-confirm-order').button('loading');
            },
            success : function(json) {
                if (json.error) {
                    squareupError(json.error);
                } else if (json.redirect) {
                    document.location = json.redirect;
                }
            },
            error : function(jqXHR, ajaxSettings, thrownError) {
                squareupError(thrownError);
            },
            complete : function() {
                $('#button-confirm-order').button('reset');
            }
        });
    }

    function onScriptLoad() {
        // paymentForm must be global
        paymentForm = new SqPaymentForm(paymentFormSettings);
        paymentForm.build();
    }

    function onConfirmCheckout() {
        if ($('#squareup_select_card').val()) {
            submitForm();
        } else {
            paymentForm.requestCardNonce();
        }
    }

    function squareupError(error) {
        $('#button-confirm-order').button('reset');

        $('#squareup-notification').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="X"><span aria-hidden="true">&times;</span></button><i class="fa fa-exclamation-circle"></i>&nbsp;' + error + '</div>');
    }

    function toggleAjaxSetup(status) {
        $.ajaxSetup({
            cache: status
        });
    }

    function initSquareup(init_ajax_setup) {
        if (typeof paymentForm != 'undefined') {
            paymentForm.destroy();
            onScriptLoad();
            return;
        }
        toggleAjaxSetup(false);
        
        var script = document.createElement('script');
        script.type="text/javascript";
        $("head").append(script);
        script.onload = onScriptLoad;
        script.src="<?php echo $squareup_js_api; ?>";
        script.async = true;
        toggleAjaxSetup(init_ajax_setup);
    }

    $('#button-confirm-order').unbind().click(function(e) {
        e.preventDefault();
        e.stopPropagation();

        $('#squareup-notification').empty();
        $('#button-confirm-order').button('loading');

        onConfirmCheckout();
    });

    $('#squareup_select_card').change(function() {
        if ($(this).val()) {
            $('#hide-card-details').slideUp();
        } else {
            $('#hide-card-details').slideDown();
        }
    });

    window.addEventListener("error", function (e) {
       return false;
    });

    initSquareup($.ajaxSetup().cache);
});
</script>
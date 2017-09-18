<div id="transaction-alert" data-message="<?php echo $text_loading; ?>"></div>
<div class="text-right margin-bottom">
</div>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-left hidden-xs"><?php echo $column_transaction_id; ?></th>
                <th class="text-left hidden-xs"><?php echo $column_type; ?></th>
                <th class="text-left hidden-xs"><?php echo $column_amount; ?></th>
                <th class="text-left hidden-xs"><?php echo $column_refunds; ?></th>
                <th class="text-left hidden-xs hidden-sm"><?php echo $column_ip; ?></th>
                <th class="text-left"><?php echo $column_date_created; ?></th>
                <th class="text-right"><?php echo $column_action; ?></th>
            </tr>
        </thead>
        <tbody id="transactions">
        </tbody>
    </table>
    <div id="transactions_pagination"></div>
</div>
<div class="modal fade" id="squareup-confirm-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $text_confirm_action; ?></h4>
            </div>
            <div class="modal-body">
                <h4 id="squareup-confirm-modal-content"></h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $text_close; ?></button>
                <button id="squareup-confirm-ok" type="button" class="btn btn-primary"><?php echo $text_ok; ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="squareup-refund-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $text_refund_details; ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label" id="squareup-refund-modal-content-reason"></label>
                    <textarea class="form-control" id="squareup-refund-reason" required></textarea>
                </div>
                <div class="form-group">
                    <label class="control-label" id="squareup-refund-modal-content-amount"></label>
                    <input class="form-control" type="text" id="squareup-refund-amount" required />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $text_close; ?></button>
                <button id="squareup-refund-ok" type="button" class="btn btn-primary"><?php echo $text_ok; ?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    var disableTransactionButtons = function() {
        $('*[data-url-transaction-capture], *[data-url-transaction-void], *[data-url-transaction-refund]')
            .attr('disabled', true);
    }

    var enableTransactionButtons = function() {
        $('*[data-url-transaction-capture], *[data-url-transaction-void], *[data-url-transaction-refund]')
            .attr('disabled', false);
    }

    var modalConfirm = function(url, text, ajax) {
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

    var refundInputValidate = function(reason_input, amount_input) {
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

    var modalRefund = function(url, text_reason, text_amount, ajax) {
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

    var listTransactions = function(page) {
        $.ajax({
          url : '<?php echo $url_list_transactions; ?>'.replace('{PAGE}', page ? page : transactionListPage),
          dataType : 'json',
          beforeSend : function() {
            $('#refresh_transactions').button('loading');
            $('#transactions_pagination').empty();
            $('#transactions').html('<tr><td colspan="9" class="text-center"><i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;<?php echo $text_loading; ?></td></tr>');
          },
          success : function(data) {
            var html = '';

            if (data.transactions.length) {
              for (var i in data.transactions) {
                var row = data.transactions[i];

                html += '<tr>';
                html += '<td class="text-left hidden-xs">' + row.transaction_id + '</td>';
                html += '<td class="text-left hidden-xs">' + row.status + '</td>';
                html += '<td class="text-left hidden-xs">' + row.amount + '</td>';
                html += '<td class="text-left hidden-xs">' + row.amount_refunded + '</td>';
                html += '<td class="text-left hidden-xs hidden-sm">' + row.ip + '</td>';
                html += '<td class="text-left">' + row.date_created + '</td>';
                html += '<td class="text-right">';

                switch (row.type) {
                    case "AUTHORIZED" : {
                        html += '<a class="btn btn-success" data-url-transaction-capture="' + row.url_capture + '" data-confirm-capture="' + row.confirm_capture + '"><?php echo $text_capture; ?></a> ';
                        html += '<a class="btn btn-warning" data-url-transaction-void="' + row.url_void + '" data-confirm-void="' + row.confirm_void + '"><?php echo $text_void; ?></a> ';
                    } break;

                    case "CAPTURED" : {
                        if (row.is_fully_refunded) {
                            break;
                        }
                        html += '<a class="btn btn-danger" data-url-transaction-refund="' + row.url_refund + '" data-confirm-refund="' + row.confirm_refund + '" data-insert-amount="' + row.insert_amount + '"><?php echo $text_refund; ?></a> ';
                    } break;
                }

                html += ' <a class="btn btn-info" href="' + row.url_info + '"><?php echo $text_view; ?></a>';
                html += '</td>';
                html += '</tr>';
              }
            } else {
              html += '<tr>';
              html += '<td class="text-center" colspan="7"><?php echo $text_no_transactions; ?></td>';
              html += '</tr>';
            }

            $('#transactions').html(html);
            
            $('#transactions_pagination').html(data.pagination).find('a[href]').each(function(index,element) {
              $(this).click(function(e) {
                e.preventDefault();

                transactionListPage = isNaN($(this).attr('href')) ? 1 : $(this).attr('href');

                listTransactions();
              })
            });

            $('#history').load('index.php?route=sale/order/history&token=<?php echo $token; ?>&order_id=<?php echo $order_id; ?>');

            $('select[name="order_status_id"]').val(data.order_status_id);
          },
          complete : function() {
            $('#refresh_transactions').button('reset');
          }
        });
    }

    var transactionLoading = function() {
        var message = $('#transaction-alert').attr('data-message');

        $('#transaction-alert').html('<div class="text-center alert alert-info"><i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;' + message + '</div>');
    }

    var transactionError = function(message) {
        $('#transaction-alert').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="X"><span aria-hidden="true">&times;</span></button><i class="fa fa-exclamation-circle"></i>&nbsp;' + message + '</div>');
    }

    var transactionSuccess = function(message) {
        $('#transaction-alert').html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="X"><span aria-hidden="true">&times;</span></button><i class="fa fa-exclamation-circle"></i>&nbsp;' + message + '</div>');
    }

    var transactionRequest = function(type, url, data) {
        $.ajax({
            url : url,
            dataType : 'json',
            type : type,
            data : data,
            beforeSend : transactionLoading,
            success : function(json) {
                if (json.error) {
                    transactionError(json.error);
                    enableTransactionButtons();
                }

                if (json.success) {
                    transactionSuccess(json.success);
                    listTransactions();
                }
            },
            error : function(xhr, ajaxSettings, thrownError) {
                transactionError(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                enableTransactionButtons();
            }
        });
    }

    var transactionListPage = 1;

    $(document).on('click', '*[data-url-transaction-capture]', function() {
        if ($(this).attr('disabled')) return;

        modalConfirm(
            $(this).attr('data-url-transaction-capture'),
            $(this).attr('data-confirm-capture'),
            true
        );
    });
        
    $(document).on('click', '*[data-url-transaction-void]', function() {
        if ($(this).attr('disabled')) return;

        modalConfirm(
            $(this).attr('data-url-transaction-void'),
            $(this).attr('data-confirm-void'),
            true
        );
    });

    $(document).on('click', '*[data-url-transaction-refund]', function() {
        if ($(this).attr('disabled')) return;

        modalRefund(
            $(this).attr('data-url-transaction-refund'),
            $(this).attr('data-confirm-refund'),
            $(this).attr('data-insert-amount'),
            true
        );
    });

    listTransactions();
});
</script>
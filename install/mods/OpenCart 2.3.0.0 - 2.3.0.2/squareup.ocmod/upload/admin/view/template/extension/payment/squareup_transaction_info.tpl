<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <?php if ($is_authorized) : ?>
            <a id="transaction_capture" data-url-transaction-capture="<?php echo $url_capture; ?>" data-confirm-capture="<?php echo $confirm_capture; ?>" class="btn btn-success"><?php echo $button_capture; ?></a>
            <a id="transaction_void" data-url-transaction-void="<?php echo $url_void; ?>" data-confirm-void="<?php echo $confirm_void; ?>" class="btn btn-warning"><?php echo $button_void; ?></a>
        <?php endif; ?>

        <?php if ($is_captured && !$is_fully_refunded) : ?>
            <a id="transaction_refund" data-url-transaction-refund="<?php echo $url_refund; ?>" data-confirm-refund="<?php echo $confirm_refund; ?>" data-insert-amount="<?php echo $insert_amount; ?>" class="btn btn-danger"><?php echo $button_refund; ?></a>
        <?php endif; ?>

        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php foreach ($squareup_alerts as $alert) { ?>
        <div class="alert alert-<?php echo $alert['type']; ?>"><i class="fa fa-<?php echo $alert['icon']; ?>"></i>&nbsp;<?php echo $alert['text']; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
          <form class="form-horizontal" method="POST" id="transaction_form" action="<?php echo $url_refund; ?>">
            <input type="hidden" name="reason" id="refund_reason" value="" />
            <input type="hidden" name="amount" id="refund_amount" value="" />

              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_transaction_id; ?></label>
                <div class="col-sm-10">
                  <div class="form-info"><a href="<?php echo $url_transaction; ?>" target="_blank"><?php echo $transaction_id; ?></a></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_merchant; ?></label>
                <div class="col-sm-10">
                  <div class="form-info"><?php echo $merchant; ?></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_order_id; ?></label>
                <div class="col-sm-10">
                  <div class="form-info"><a href="<?php echo $url_order; ?>" target="_blank"><?php echo $order_id; ?></a></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_transaction_status; ?></label>
                <div class="col-sm-10">
                  <div class="form-info"><?php echo $status; ?></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_amount; ?></label>
                <div class="col-sm-10">
                <div class="form-info"><?php echo $amount; ?></div>
                </div>
              </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $entry_billing_address_company; ?></label>
              <div class="col-sm-10">
                <div class="form-info"><?php echo $billing_address_company; ?></div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $entry_billing_address_street; ?></label>
              <div class="col-sm-10">
                <div class="form-info"><?php echo $billing_address_street; ?></div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $entry_billing_address_city; ?></label>
              <div class="col-sm-10">
                <div class="form-info"><?php echo $billing_address_city; ?></div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $entry_billing_address_postcode; ?></label>
              <div class="col-sm-10">
                <div class="form-info"><?php echo $billing_address_postcode; ?></div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $entry_billing_address_province; ?></label>
              <div class="col-sm-10">
                <div class="form-info"><?php echo $billing_address_province; ?></div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $entry_billing_address_country; ?></label>
              <div class="col-sm-10">
                <div class="form-info"><?php echo $billing_address_country; ?></div>
              </div>
            </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_browser; ?></label>
                <div class="col-sm-10">
                  <div class="form-info"><?php echo $browser; ?></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_ip; ?></label>
                <div class="col-sm-10">
                  <div class="form-info"><?php echo $ip; ?></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_date_created; ?></label>
                <div class="col-sm-10">
                  <div class="form-info"><?php echo $date_created; ?></div>
                </div>
              </div>
            <?php if ($has_refunds) : ?>
                <hr />
                <h3><?php echo $text_refunds; ?></h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <th><?php echo $column_date_created; ?></th>
                        <th><?php echo $column_reason; ?></th>
                        <th><?php echo $column_status; ?></th>
                        <th><?php echo $column_amount; ?></th>
                        <th><?php echo $column_fee; ?></th>
                    </thead>
                    <tbody>
                        <?php foreach ($refunds as $refund) : ?>
                            <tr>
                                <td><?php echo $refund['date_created']; ?></td>
                                <td><?php echo $refund['reason']; ?></td>
                                <td><?php echo $refund['status']; ?></td>
                                <td><?php echo $refund['amount']; ?></td>
                                <td><?php echo $refund['fee']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
          </form>
      </div>
    </div>
  </div>
</div>
<?php echo $squareup_modals; ?>
<script type="text/javascript">
    $(document)
        .on('click', '*[data-url-transaction-capture]', function() {
            if ($(this).attr('disabled')) return;

            modalConfirm(
                $(this).attr('data-url-transaction-capture'),
                $(this).attr('data-confirm-capture')
            );
        })
        .on('click', '*[data-url-transaction-void]', function() {
            if ($(this).attr('disabled')) return;

            modalConfirm(
                $(this).attr('data-url-transaction-void'),
                $(this).attr('data-confirm-void')
            );
        })
        .on('click', '*[data-url-transaction-refund]', function() {
            if ($(this).attr('disabled')) return;
            
            modalRefund(
                $(this).attr('data-url-transaction-refund'),
                $(this).attr('data-confirm-refund'),
                $(this).attr('data-insert-amount')
            );
        });
</script>
<?php echo $footer; ?> 
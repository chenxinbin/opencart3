<div class="buttons clearfix">
  <?php if ($order_recurring_id) { ?>
  <div class="pull-left">
    <button type="button" id="button-cancel" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-danger"><?php echo $button_cancel; ?></button>
  </div>
  <?php } ?>
  <div class="pull-right"><a href="<?php echo $continue; ?>" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary"><?php echo $button_continue; ?></a></div>
</div>
<style type="text/css">
    .squareup_spacer_10 {
        margin: 10px 0;
    }
</style>
<script type="text/javascript"><!--
$(document).delegate('#button-cancel', 'click', function() {
    if (!confirm("<?php echo $text_confirm_cancel; ?>")) {
        return false;
    }

    $.ajax({
        url: '<?php echo $cancel_url; ?>',
        dataType: 'json',
        beforeSend: function() {
             $('#button-cancel').button('loading');
        },
        complete: function() {
            $('#button-cancel').button('reset');
        },        
        success: function(json) {
            $('.alert').remove();
            
            if (json['success']) {
                $('#content').prepend('<div class="alert success squareup_spacer_10">' + json['success'] + '</div>');

                $('#button-cancel').hide();
            }
            
            if (json['error']) {
                $('#content').prepend('<div class="alert warning text-danger squareup_spacer_10">' + json['error'] + '</div>');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});
//--></script>
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
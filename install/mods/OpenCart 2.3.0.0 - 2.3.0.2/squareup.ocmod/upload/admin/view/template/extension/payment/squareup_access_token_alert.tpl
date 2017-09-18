<?php foreach ($alerts as $alert) : ?>
    <div class="alert alert-<?php echo $alert['type']; ?>"><i class="fa fa-<?php echo $alert['icon']; ?>"></i>&nbsp;<?php echo $alert['text']; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endforeach; ?>
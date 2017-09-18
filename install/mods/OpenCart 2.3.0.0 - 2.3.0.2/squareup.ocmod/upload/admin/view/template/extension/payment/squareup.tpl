<?php
echo $header;
echo $column_left;
?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <?php if ($squareup_merchant_id != '') : ?>
                    <button type="submit" form="form-square-checkout" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <?php else: ?>
                    <span data-toggle="tooltip" title="<?php echo $text_please_connect; ?>">
                        <a href="javascript:void(0)" disabled class="btn btn-primary"><i class="fa fa-save"></i></a>
                    </span>
                <?php endif; ?>
                <a href="<?php echo $help; ?>" data-toggle="tooltip" title="<?php echo $button_help; ?>" class="btn btn-info" target="_blank"><i class="fa fa-question-circle"></i></a>
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
                <?php if (empty($alert['non_dismissable'])) : ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php endif; ?>
            </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i>&nbsp;<?php echo $text_edit_heading; ?></h3>
            </div>

            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-square-checkout" class="form-horizontal">
                    <input type="hidden" name="squareup_admin_url" value="<?php echo $admin_url; ?>" />
                    <ul class="nav nav-tabs">
                        <li><a href="#tab-setting" data-toggle="tab"><i class="fa fa-gear"></i>&nbsp;<?php echo $tab_setting; ?></a></li>
                        <li><a href="#tab-transaction" data-toggle="tab"><i class="fa fa-list"></i>&nbsp;<?php echo $tab_transaction; ?></a></li>
                        <li><a href="#tab-recurring" data-toggle="tab"><i class="fa fa-hourglass-half"></i>&nbsp;<?php echo $tab_recurring; ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane" id="tab-setting">
                            <fieldset>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="<?php echo 'input_squareup_client_id'; ?>">
                                        <span data-toggle="tooltip" title="<?php echo $text_client_id_help; ?>"><?php echo $text_client_id_label; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="squareup_client_id" value="<?php echo $squareup_client_id; ?>" placeholder="<?php echo $text_client_id_placeholder; ?>" id="input_squareup_client_id" class="form-control"/>
                                        <?php if ($err_client_id !== false && $err_client_id != '') { ?>
                                        <div class="text-danger"><?php echo $err_client_id; ?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input_squareup_client_secret">
                                        <span data-toggle="tooltip" title="<?php echo $text_client_secret_help; ?>"><?php echo $text_client_secret_label; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="squareup_client_secret" value="<?php echo $squareup_client_secret; ?>" placeholder="<?php echo $text_client_secret_placeholder; ?>" id="input_squareup_client_secret" class="form-control"/>
                                        <?php if ($err_client_secret !== false && $err_client_secret != '') { ?>
                                        <div class="text-danger"><?php echo $err_client_secret; ?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input_squareup_redirect_uri_static">
                                        <span data-toggle="tooltip" title="<?php echo $text_redirect_uri_help; ?>"><?php echo $text_redirect_uri_label; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input readonly type="text" id="input_squareup_redirect_uri_static" name="squareup_redirect_uri_static" class="form-control" value="<?php echo $squareup_redirect_uri ?>" />
                                    </div>
                                </div>
                                <?php if ($squareup_merchant_id != '') { ?>
                                    <legend>
                                        <span>1. <?php echo $text_connection_section . ' - ' .  $text_connected; ?></span>
                                        <div class="pull-right">
                                            <a target="_blank" href="<?php echo $url_video_help; ?>" data-toggle="tooltip" title="<?php echo $tooltip_video_help; ?>" class="btn btn-info btn-sm"><i class="fa fa-video-camera"></i>&nbsp;&nbsp;<?php echo $text_installation_video; ?></a>
                                            <a target="_blank" href="<?php echo $url_integration_settings_help; ?>" data-toggle="tooltip" title="<?php echo $tooltip_integration_settings_help; ?>" class="btn btn-info btn-sm"><i class="fa fa-question-circle"></i></a>
                                        </div>
                                    </legend>
                                    <div class="form-group">
                                        <div class="col-sm-2"></div>
                                        <div class="col-sm-10">
                                            <div class="row margin-bottom">
                                                <div class="col-sm-12">
                                                    <div class="button-wrapper" data-toggle="tooltip" title="<?php echo $text_disabled_connect_help_text; ?>"><a id="reconnect-button" href="javascript:void(0)" class="btn btn-primary btn-lg btn-connect"<?php echo (empty($squareup_client_id) || empty($squareup_client_secret))?' disabled':''; ?>><?php echo $button_reconnect; ?></a></div>
                                                    <!--div class="button-wrapper" data-toggle="tooltip" title="<?php echo $text_disabled_connect_help_text; ?>"><a id="refresh-button" href="<?php echo $squareup_refresh_link; ?>" class="btn btn-primary btn-lg btn-connect"<?php echo (empty($squareup_client_id) || empty($squareup_client_secret))?' disabled':''; ?>><?php echo $button_refresh; ?></a></div-->
                                                    <span id="connect-error"></span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <p><?php echo $text_connected_info; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <legend>
                                        <span>1. <?php echo $text_connection_section . ' - ' .  $text_not_connected; ?></span>
                                        <div class="pull-right">
                                            <a target="_blank" href="<?php echo $url_video_help; ?>" data-toggle="tooltip" title="<?php echo $tooltip_video_help; ?>" class="btn btn-info btn-sm"><i class="fa fa-video-camera"></i>&nbsp;&nbsp;<?php echo $text_installation_video; ?></a>
                                            <a target="_blank" href="<?php echo $url_integration_settings_help; ?>" data-toggle="tooltip" title="<?php echo $tooltip_integration_settings_help; ?>" class="btn btn-info btn-sm"><i class="fa fa-question-circle"></i></a>
                                        </div>
                                    </legend>
                                    <div class="form-group">
                                        <div class="col-sm-2"></div>
                                        <div class="col-sm-10">
                                            <div class="row margin-bottom">
                                                <div class="col-sm-12">
                                                    <div class="button-wrapper" data-toggle="tooltip" title="<?php echo $text_disabled_connect_help_text; ?>"><a id="connect-button" href="javascript:void(0)" class="btn btn-primary btn-lg btn-connect<?php echo (empty($squareup_client_id) || empty($squareup_client_secret))?' disabled':''; ?>"><?php echo $button_connect; ?></a></div>
                                                    <span id="connect-error"></span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <p><?php echo $text_not_connected_info; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </fieldset>
                            <fieldset>
                                <legend>2. <?php echo $text_cron_settings; ?></legend>

                                <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $text_recurring_info; ?></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-original-title="<?php echo $help_local_cron; ?>"><?php echo $text_local_cron; ?></span></label>
                                    <div class="col-sm-10">
                                        <input readonly type="text" class="form-control" value="<?php echo $squareup_cron_command; ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-original-title="<?php echo $help_remote_cron; ?>"><?php echo $text_remote_cron; ?></span></label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <input readonly type="text" name="squareup_cron_url" class="form-control" value="" />
                                            <div data-toggle="tooltip" data-original-title="<?php echo $text_refresh_token; ?>" class="input-group-addon btn btn-primary" id="refresh-cron-token">
                                                <i class="fa fa-refresh"></i>
                                            </div>
                                        </div>
                                        <input type="hidden" name="squareup_cron_token" value="<?php echo $squareup_cron_token; ?>" />
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label"><?php echo $entry_setup_confirmation; ?></label>
                                    <div class="col-sm-10">
                                        <input type="checkbox" value="1" <?php if (!empty($squareup_cron_acknowledge)) echo 'checked'; ?> name="squareup_cron_acknowledge" />
                                        <?php echo $text_acknowledge_cron; ?>
                                        <?php if (!empty($err_cron_acknowledge)) : ?>
                                            <div class="text-danger"><?php echo $err_cron_acknowledge; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label col-sm-2"><span data-toggle="tooltip" data-original-title="<?php echo $help_cron_email_status; ?>"><?php echo $text_cron_email_status; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_cron_email_status" class="form-control">
                                            <option value="1" <?php echo $squareup_cron_email_status === '1' ? 'selected' : ''; ?>><?php echo $text_enabled; ?></option>
                                            <option value="0" <?php echo $squareup_cron_email_status === '0' ? 'selected' : ''; ?>><?php echo $text_disabled; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-original-title="<?php echo $help_cron_email; ?>"><?php echo $text_cron_email; ?></span></label>
                                    <div class="col-sm-10">
                                        <input name="squareup_cron_email" type="text" class="form-control" value="<?php echo $squareup_cron_email; ?>" />
                                        <?php if (!empty($err_cron_email)) : ?>
                                            <div class="text-danger"><?php echo $err_cron_email; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend>3. <?php echo $text_basic_settings; ?></legend>

                                <div class="form-group required"<?php echo ($squareup_enable_sandbox)?' style="display: none;"':''; ?>>
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_location_id"><span data-toggle="tooltip" title="<?php echo $text_location_help; ?>"><?php echo $text_location_label; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_location_id" id="dropdown_squareup_location_id" class="form-control"<?php echo (count($squareup_locations)==0)?' disabled':'' ?>>
                                        <?php if ($text_select_location != null) { ?>
                                            <option value="" style="display: none;"<?php if (empty($squareup_location_id)) echo ' selected'; ?>><?php echo (count($squareup_locations)!=0)?$text_select_location:$text_no_locations_label; ?></option>
                                        <?php } ?>
                                        <?php for($i=0;$i<count($squareup_locations);++$i) { $_opt = $squareup_locations[$i]; ?>
                                            <option value="<?php echo $_opt['id']; ?>"<?php if ($_opt['id'] == $squareup_location_id) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if ($err_location !== false && !empty($err_location)) { ?>
                                        <div class="text-danger"><?php echo $err_location; ?></div>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_delay_capture"><span data-toggle="tooltip" title="<?php echo $text_delay_capture_help; ?>"><?php echo $text_delay_capture_label; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_delay_capture" id="dropdown_squareup_delay_capture" class="form-control">
                                            <option value="1"<?php if ($squareup_delay_capture == 1) echo ' selected="selected"'; ?>><?php echo $text_authorize_label; ?></option>
                                            <option value="0"<?php if ($squareup_delay_capture == 0) echo ' selected="selected"'; ?>><?php echo $text_sale_label; ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_status_authorized"><span data-toggle="tooltip" title="<?php echo $squareup_status_comment_authorized; ?>"><?php echo $entry_status_authorized; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_status_authorized" id="dropdown_squareup_status_authorized" class="form-control">
                                        <option value><?php echo $text_select_status; ?></option>
                                        <?php for($i=0;$i<count($order_statuses);++$i) { $_opt = $order_statuses[$i]; ?>
                                            <option value="<?php echo $_opt['order_status_id']; ?>"<?php if ($_opt['order_status_id'] == $squareup_status_authorized) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if (!empty($err_status_authorized)) : ?>
                                            <div class="text-danger"><?php echo $err_status_authorized; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_status_captured"><span data-toggle="tooltip" title="<?php echo $squareup_status_comment_captured; ?>"><?php echo $entry_status_captured; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_status_captured" id="dropdown_squareup_status_captured" class="form-control">
                                        <option value><?php echo $text_select_status; ?></option>
                                        <?php for($i=0;$i<count($order_statuses);++$i) { $_opt = $order_statuses[$i]; ?>
                                            <option value="<?php echo $_opt['order_status_id']; ?>"<?php if ($_opt['order_status_id'] == $squareup_status_captured) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if (!empty($err_status_captured)) : ?>
                                            <div class="text-danger"><?php echo $err_status_captured; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_status_voided"><span data-toggle="tooltip" title="<?php echo $squareup_status_comment_voided; ?>"><?php echo $entry_status_voided; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_status_voided" id="dropdown_squareup_status_voided" class="form-control">
                                        <option value><?php echo $text_select_status; ?></option>
                                        <?php for($i=0;$i<count($order_statuses);++$i) { $_opt = $order_statuses[$i]; ?>
                                            <option value="<?php echo $_opt['order_status_id']; ?>"<?php if ($_opt['order_status_id'] == $squareup_status_voided) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if (!empty($err_status_voided)) : ?>
                                            <div class="text-danger"><?php echo $err_status_voided; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_status_failed"><span data-toggle="tooltip" title="<?php echo $squareup_status_comment_failed; ?>"><?php echo $entry_status_failed; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_status_failed" id="dropdown_squareup_status_failed" class="form-control">
                                        <option value><?php echo $text_select_status; ?></option>
                                        <?php for($i=0;$i<count($order_statuses);++$i) { $_opt = $order_statuses[$i]; ?>
                                            <option value="<?php echo $_opt['order_status_id']; ?>"<?php if ($_opt['order_status_id'] == $squareup_status_failed) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if (!empty($err_status_failed)) : ?>
                                            <div class="text-danger"><?php echo $err_status_failed; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_status_partially_refunded"><span data-toggle="tooltip" title="<?php echo $squareup_status_comment_partially_refunded; ?>"><?php echo $entry_status_partially_refunded; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_status_partially_refunded" id="dropdown_squareup_status_partially_refunded" class="form-control">
                                        <option value><?php echo $text_select_status; ?></option>
                                        <?php for($i=0;$i<count($order_statuses);++$i) { $_opt = $order_statuses[$i]; ?>
                                            <option value="<?php echo $_opt['order_status_id']; ?>"<?php if ($_opt['order_status_id'] == $squareup_status_partially_refunded) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if (!empty($err_status_partially_refunded)) : ?>
                                            <div class="text-danger"><?php echo $err_status_partially_refunded; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_status_fully_refunded"><span data-toggle="tooltip" title="<?php echo $squareup_status_comment_fully_refunded; ?>"><?php echo $entry_status_fully_refunded; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_status_fully_refunded" id="dropdown_squareup_status_fully_refunded" class="form-control">
                                        <option value><?php echo $text_select_status; ?></option>
                                        <?php for($i=0;$i<count($order_statuses);++$i) { $_opt = $order_statuses[$i]; ?>
                                            <option value="<?php echo $_opt['order_status_id']; ?>"<?php if ($_opt['order_status_id'] == $squareup_status_fully_refunded) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if (!empty($err_status_fully_refunded)) : ?>
                                            <div class="text-danger"><?php echo $err_status_fully_refunded; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend>4. <?php echo $text_extension_status; ?></legend>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_status"><span data-toggle="tooltip" title="<?php echo $text_extension_status_help; ?>"><?php echo $text_extension_status; ?></span></label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <select name="squareup_status" id="dropdown_squareup_status" class="form-control">
                                                <option value="1"<?php if ($squareup_status == 1) echo ' selected="selected"'; ?>><?php echo $text_extension_status_enabled; ?></option>
                                                <option value="0"<?php if ($squareup_status == 0) echo ' selected="selected"'; ?>><?php echo $text_extension_status_disabled; ?></option>
                                            </select>
                                            <span class="input-group-addon input-group-addon-btn">
                                            <?php if ($squareup_merchant_id != '') : ?>
                                                <button type="submit" form="form-square-checkout" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                                            <?php else: ?>
                                                <a href="javascript:void(0)" data-toggle="tooltip" title="<?php echo $text_please_connect; ?>" disabled class="btn btn-primary"><i class="fa fa-save"></i></a>
                                            <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                            <legend>5. <?php echo $text_advanced_settings; ?></legend>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        <span data-toggle="tooltip" title="<?php echo $text_payment_method_name_help; ?>"><?php echo $text_payment_method_name_label; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <?php foreach($languages as $language) : ?>
                                            <div class="input-group">
                                                <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>" /></span>
                                                <input type="text" name="squareup_display_name[<?php echo $language['language_id']; ?>]" value="<?php echo !empty($squareup_display_name[$language['language_id']])?$squareup_display_name[$language['language_id']]:$text_payment_method_name_placeholder; ?>" placeholder="<?php echo $text_payment_method_name_placeholder; ?>" class="form-control"/>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input_squareup_total">
                                        <span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="squareup_total" value="<?php echo $squareup_total; ?>" placeholder="<?php echo $entry_total; ?>" id="squareup_total" class="form-control"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                  <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
                                  <div class="col-sm-10">
                                    <select name="squareup_geo_zone_id" id="input-geo-zone" class="form-control">
                                      <option value="0"><?php echo $text_all_zones; ?></option>
                                      <?php foreach ($geo_zones as $geo_zone) { ?>
                                      <?php if ($geo_zone['geo_zone_id'] == $squareup_geo_zone_id) { ?>
                                      <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                      <?php } else { ?>
                                      <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                      <?php } ?>
                                      <?php } ?>
                                    </select>
                                  </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input_squareup_sort_order">
                                        <?php echo $entry_sort_order; ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="squareup_sort_order" value="<?php echo $squareup_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input_squareup_sort_order" class="form-control"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_debug"><span data-toggle="tooltip" title="<?php echo $text_debug_help; ?>"><?php echo $text_debug_label; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_debug" id="dropdown_squareup_debug" class="form-control">
                                            <option value="1"<?php if ($squareup_debug == 1) echo ' selected="selected"'; ?>><?php echo $text_debug_enabled; ?></option>
                                            <option value="0"<?php if ($squareup_debug == 0) echo ' selected="selected"'; ?>><?php echo $text_debug_disabled; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_enable_sandbox"><span data-toggle="tooltip" title="<?php echo $text_enable_sandbox_help; ?>"><?php echo $text_enable_sandbox_label; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_enable_sandbox" id="dropdown_squareup_enable_sandbox" class="form-control">
                                            <option value="1"<?php if ($squareup_enable_sandbox == 1) echo ' selected="selected"'; ?>><?php echo $text_sandbox_enabled_label; ?></option>
                                            <option value="0"<?php if ($squareup_enable_sandbox == 0) echo ' selected="selected"'; ?>><?php echo $text_sandbox_disabled_label; ?></option>
                                        </select>
                                    </div>
                                </div>
                            <div id="sandbox_settings"<?php if (!$squareup_enable_sandbox) echo ' style="display: none;"'; ?>> 
                                <div class="form-group required"<?php echo (!$squareup_enable_sandbox)?' style="display: none;"':''; ?>>
                                    <label class="col-sm-2 control-label" for="dropdown_squareup_sandbox_location_id"><span data-toggle="tooltip" title="<?php echo $text_location_help; ?>"><?php echo $text_sandbox_location_label; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_sandbox_location_id" id="dropdown_squareup_sandbox_location_id" class="form-control"<?php echo (count($squareup_sandbox_locations)==0)?' disabled':'' ?>>
                                        <?php if ($text_select_location != null) { ?>
                                            <option value="" style="display: none;"<?php if (empty($squareup_sandbox_location_id)) echo ' selected'; ?>><?php echo (count($squareup_sandbox_locations)!=0)?$text_select_location:$text_no_locations_label; ?></option>
                                        <?php } ?>
                                        <?php for($i=0;$i<count($squareup_sandbox_locations);++$i) { $_opt = $squareup_sandbox_locations[$i]; ?>
                                            <option value="<?php echo $_opt['id']; ?>"<?php if ($_opt['id'] == $squareup_sandbox_location_id) echo ' selected="selected"'; ?>><?php echo $_opt['name']; ?></option>
                                        <?php } ?>
                                        </select>
                                        <?php if ($err_location !== false && !empty($err_location)) { ?>
                                        <div class="text-danger"><?php echo $err_location; ?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input_squareup_sandbox_client_id">
                                        <span data-toggle="tooltip" title="<?php echo $text_sandbox_client_id_help; ?>"><?php echo $text_sandbox_client_id_label; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="squareup_sandbox_client_id" value="<?php echo $squareup_sandbox_client_id; ?>" placeholder="<?php echo $text_sandbox_client_id_placeholder; ?>" id="input_squareup_sandbox_client_id" class="form-control"/>
                                        <?php if ($err_sandbox_client_id !== false && $err_sandbox_client_id != '') { ?>
                                        <div class="text-danger"><?php echo $err_sandbox_client_id; ?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input_squareup_sandbox_token">
                                        <span data-toggle="tooltip" title="<?php echo $text_sandbox_access_token_help; ?>"><?php echo $text_sandbox_access_token_label; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="squareup_sandbox_token" value="<?php echo $squareup_sandbox_token; ?>" placeholder="<?php echo $text_sandbox_access_token_placeholder; ?>" id="input_squareup_sandbox_token" class="form-control"/>
                                        <?php if ($err_sandbox_token !== false && $err_sandbox_token != '') { ?>
                                        <div class="text-danger"><?php echo $err_sandbox_token; ?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input_merchant_name">
                                        <?php echo $text_merchant_name_label; ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="merchant_name" value="<?php echo $squareup_merchant_name; ?>" placeholder="<?php echo $text_merchant_name_placeholder; ?>" id="input_merchant_name" class="form-control" readonly/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input_access_token_expires">
                                        <?php echo $text_access_token_expires_label; ?>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="text" name="access_token_expires" value="<?php echo (!empty($squareup_access_token_expires))?$access_token_expires_time:''; ?>" placeholder="<?php echo $text_access_token_expires_placeholder; ?>" id="input_access_token_expires" class="form-control" readonly/>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="tab-pane" id="tab-transaction">
                            <div id="transaction-error" data-message="<?php echo $text_loading; ?>"></div>
                            <div class="text-right margin-bottom">
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-left hidden-xs"><?php echo $column_transaction_id; ?></th> 
                                            <th class="text-left"><?php echo $column_customer; ?></th>
                                            <th class="text-left hidden-xs"><?php echo $column_order_id; ?></th>
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
                        </div>
                        <div class="tab-pane" id="tab-recurring">
                            <div class="form-group">
                                <label class="control-label col-sm-2"><span data-toggle="tooltip" data-original-title="<?php echo $help_recurring_status; ?>"><?php echo $text_recurring_status; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="squareup_recurring_status" class="form-control">
                                        <option value="1" <?php echo $squareup_recurring_status === '1' ? 'selected' : ''; ?>><?php echo $text_enabled; ?></option>
                                        <option value="0" <?php echo $squareup_recurring_status === '0' ? 'selected' : ''; ?>><?php echo $text_disabled; ?></option>
                                    </select>
                                </div>
                            </div>
                            <fieldset>
                                <legend><?php echo $text_customer_notifications; ?></legend>
                                <div class="form-group">
                                    <label class="control-label col-sm-2"><span data-toggle="tooltip" data-original-title="<?php echo $help_notify_recurring_success; ?>"><?php echo $text_notify_recurring_success; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_notify_recurring_success" class="form-control">
                                            <option value="1" <?php echo $squareup_notify_recurring_success === '1' ? 'selected' : ''; ?>><?php echo $text_enabled; ?></option>
                                            <option value="0" <?php echo $squareup_notify_recurring_success === '0' ? 'selected' : ''; ?>><?php echo $text_disabled; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-2"><span data-toggle="tooltip" data-original-title="<?php echo $help_notify_recurring_fail; ?>"><?php echo $text_notify_recurring_fail; ?></span></label>
                                    <div class="col-sm-10">
                                        <select name="squareup_notify_recurring_fail" class="form-control">
                                            <option value="1" <?php echo $squareup_notify_recurring_fail === '1' ? 'selected' : ''; ?>><?php echo $text_enabled; ?></option>
                                            <option value="0" <?php echo $squareup_notify_recurring_fail === '0' ? 'selected' : ''; ?>><?php echo $text_disabled; ?></option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $squareup_modals; ?>
<script type="text/javascript">
    $('.nav-tabs a[href="#<?php echo $tab ?>"]').tab('show');

    function onSandboxToggle() {
        enabled = $('#dropdown_squareup_enable_sandbox').val() == 1;
        if (enabled) {
            // show sandbox specific settings and switch locations to the sandbox ones
            $('#sandbox_settings').show();
            $('#dropdown_squareup_sandbox_location_id').closest('.form-group').show();
            $('#dropdown_squareup_location_id').closest('.form-group').hide();
        } else {
            // hide sandbox specific settings and switch locations to the live ones
            $('#sandbox_settings').hide();
            $('#dropdown_squareup_location_id').closest('.form-group').show();
            $('#dropdown_squareup_sandbox_location_id').closest('.form-group').hide();
        }
    }

    function validateClientIdAndSecret() {
        let clientId = $('#input_squareup_client_id').val();
        let secret = $('#input_squareup_client_secret').val();
        
        return (clientId.length > 0 && clientId.length <= 32 && secret.length > 0 && secret.length <= 50);
    }

    function disableButton(btnId) {
        $('#'+btnId).closest('.button-wrapper').tooltip('enable');
        $('#'+btnId).addClass('disabled');
    }

    function enableButton(btnId) {
        $('#'+btnId).closest('.button-wrapper').tooltip('disable');
        $('#'+btnId).removeClass('disabled');
    }

    function onConnectionRequiredFieldChange() {
        let valid = validateClientIdAndSecret();
        if (!valid) { disableButton('connect-button'); disableButton('refresh-button'); disableButton('reconnect-button'); }
        if (valid) { enableButton('connect-button'); enableButton('refresh-button'); enableButton('reconnect-button'); }
    }

    function onConnectClick(event) {
        event.preventDefault();
        //$('form').attr('action','<?php echo str_replace('&amp;', '&', $action . '&save_and_auth=1'); ?>');
        //$('form').submit();

        $.ajax({
            url: '<?php echo $url_accept_credentials; ?>',
            dataType: 'json',
            type: 'POST',
            data : {
                squareup_client_id: $('input[name="squareup_client_id"]').val(),
                squareup_client_secret: $('input[name="squareup_client_secret"]').val()
            },
            beforeSend : function() {
                $('#connect-error').empty(); //@todo - continue
            },
            success: function(json) {
                if (json.redirect) {
                    document.location = json.redirect;
                }

                if (json.error) {
                    $('#connect-error').html('<span class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="X"><span aria-hidden="true">&times;</span></button><i class="fa fa-exclamation-circle"></i>&nbsp;' + json.error + '</span>');
                }
            }
        });
    }

    transactionListPage = 1;

    function listTransactions(page) {
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
                html += '<td class="text-left hidden-xs">' + row.customer + '</td>';
                html += '<td class="text-left"><a target="_blank" href="' + row.url_order + '">' + row.order_id + '</td>';
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
              html += '<td class="text-center" colspan="9"><?php echo $text_no_transactions; ?></td>';
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
          },
          complete : function() {
            $('#refresh_transactions').button('reset');
          }
        });
      }

    function transactionLoading() {
        var message = $('#transaction-error').attr('data-message');

        $('#transaction-error').html('<div class="text-center alert alert-info"><i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;' + message + '</div>');
    }

    function transactionError(message) {
        $('#transaction-error').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="X"><span aria-hidden="true">&times;</span></button><i class="fa fa-exclamation-circle"></i>&nbsp;' + message + '</div>');
    }

    function transactionSuccess(message) {
        $('#transaction-error').html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="X"><span aria-hidden="true">&times;</span></button><i class="fa fa-exclamation-circle"></i>&nbsp;' + message + '</div>');
    }

    function transactionRequest(type, endpoint, data) {
        $.ajax({
            url : endpoint,
            dataType : 'json',
            type : type,
            data : data,
            beforeSend : transactionLoading,
            success : function(json) {
                if (json.error) {
                    transactionError(json.error);
                }

                if (json.success) {
                    transactionSuccess(json.success);
                }

                listTransactions();
            },
            error : function(jqXHR, ajaxSettings, thrownError) {
                transactionError(thrownError);
                enableTransactionButtons();
            }
        });
    }

    function setCronUrl() {
        $('input[name="squareup_cron_url"]').val(
            "<?php echo $squareup_cron_url; ?>".replace('{CRON_TOKEN}', $('input[name="squareup_cron_token"]').val())
        );
    }

    function randomString() {
        return (Math.random() * 100).toString(16).replace('.', '');
    }

    function scrollToConnect() {
        $('.nav-tabs a[href="#tab-setting"]').tab('show');

        $('html, body').animate({
            scrollTop: $("#input_squareup_client_id").closest('fieldset').offset().top
        }, 2000);
    }

    $(document)
        .on('click', '#cron_click', function() {
            $('.nav-tabs a[href="#tab-setting"]').tab('show');

            $('html, body').animate({
                scrollTop: $('input[name="squareup_cron_acknowledge"]').closest('fieldset').offset().top
            }, 2000);
        })
        .on('click', '*[data-url-transaction-capture]', function() {
            if ($(this).attr('disabled')) return;

            modalConfirm(
                $(this).attr('data-url-transaction-capture'),
                $(this).attr('data-confirm-capture'),
                true
            );
        })
        .on('click', '*[data-url-transaction-void]', function() {
            if ($(this).attr('disabled')) return;

            modalConfirm(
                $(this).attr('data-url-transaction-void'),
                $(this).attr('data-confirm-void'),
                true
            );
        })
        .on('click', '*[data-url-transaction-refund]', function() {
            if ($(this).attr('disabled')) return;
            
            modalRefund(
                $(this).attr('data-url-transaction-refund'),
                $(this).attr('data-confirm-refund'),
                $(this).attr('data-insert-amount'),
                true
            );
        })
        .on('click', '#refresh-cron-token', function() {
            $('input[name="squareup_cron_token"]').val(randomString() + randomString());
            setCronUrl();
        })
        .on('click', '#focus_connect', scrollToConnect);

    <?php if ($scroll_to_connect) : ?>
        scrollToConnect();
    <?php endif; ?>

    // Transactions list
    $(document).ready(function() {
        listTransactions();
        <?php if (!empty($squareup_client_id) && !empty($squareup_client_secret) && isset($squareup_cron_acknowledge) && $squareup_cron_acknowledge != '') { ?>
        enableButton('connect-button');
        enableButton('reconnect-button');
        enableButton('refresh-button');
        <?php } ?>

        $('#connect-button').click(onConnectClick);
        $('#reconnect-button').click(onConnectClick);
        $('#dropdown_squareup_enable_sandbox').change(onSandboxToggle);
        // onkeyup handles keyed in values, while change is needed for copy/pasting and auto filled browser values
        $('#input_squareup_client_id').keyup(onConnectionRequiredFieldChange);
        $('#input_squareup_client_secret').keyup(onConnectionRequiredFieldChange);
        $('#input_squareup_client_id').change(onConnectionRequiredFieldChange);
        $('#input_squareup_client_secret').change(onConnectionRequiredFieldChange);
        $('[name="squareup_cron_acknowledge"]').change(onConnectionRequiredFieldChange);

        setCronUrl();
    });
</script>
<?php echo $footer; ?>

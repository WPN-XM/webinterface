<h2>
    PHP Extensions
    <small>(<?php echo $number_enabled_extensions; ?> of <?php echo $number_available_extensions; ?> loaded)</small>
    <div id="ajax-status" class="floatright hide btn btn-small btn-success">Updating Extensions.</div>
</h2>

To enable an extension, check it's checkbox. To disable an extension uncheck it's checkbox. <small>Surprise, surprise!</small>
<form id="phpExtensionsForm" class="phpextensions form-horizontal"
      action="/webinterface/index.php?page=config&amp;action=update_phpextensions"
      method="post">

<fieldset id="phpExtensionsFormContent">
<?php echo $form; ?>
</fieldset>

<div class="right">
    <button type="submit" class="btn btn-success">Submit</button>
    <button type="reset" class="btn btn-danger">Reset</button>
</div>
</form>

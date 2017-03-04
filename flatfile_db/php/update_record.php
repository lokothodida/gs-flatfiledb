<h3><?php echo $page_title; ?></h3>

<form method="post">
  <?php
  foreach ($fields as $name => $field) {
    self::template('field_record', array(
      'name'  => $field->name,
      'label' => $field->label,
      'type'  => $field->type,
      'value' => $record->{$name},
    ));
  }
  ?>

  <p>
    <input class="submit" type="submit" name="update" value="<?php i18n('BTN_SAVECHANGES'); ?>"/>&nbsp;&nbsp;/
    <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL . '&db=' . $db_name; ?>"><?php i18n('CANCEL'); ?></a>
  </p>
</form>
<?php
self::loadCSS('https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', false);
self::loadJS('template/js/ckeditor/ckeditor.js', false);
self::loadJS('record_form.js');
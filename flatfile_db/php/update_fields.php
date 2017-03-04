<h3 class="floated"><?php echo $page_title; ?></h3>

<div class="edit-nav clearfix">
  <a class="add-field" href="#"><?php FlatFileDBPlugin::i18n('ADD_FIELD'); ?></a>
</div>

<form method="post">
  <table>
    <thead>
      <tr>
        <th><?php FlatFileDBPlugin::i18n('NAME'); ?></th>
        <th><?php FlatFileDBPlugin::i18n('LABEL'); ?></th>
        <th><?php FlatFileDBPlugin::i18n('TYPE'); ?></th>
        <th><?php FlatFileDBPlugin::i18n('DEFAULT'); ?></th>
        <th><?php FlatFileDBPlugin::i18n('HIDDEN'); ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody class="fields">
      <?php
      foreach ($fields as $field_name => $field) {
        self::template('field_row', array(
          'field' => $field,
          'types' => $field_types,
        ));
      }
      ?>
    </tbody>
  </table>

  <p>
    <input class="submit" type="submit" name="update" value="<?php i18n('BTN_SAVECHANGES'); ?>"/>&nbsp;&nbsp;/
    <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL . '&db=' . $db_name; ?>"><?php i18n('CANCEL'); ?></a>
  </p>
</form>

<template name="field-template">
  <?php
  self::template('field_row', array(
    'field' => $field_defaults,
    'types' => $field_types,
  ));
  ?>
</template>

<?php
self::loadJS('fields_form.js');
self::loadCSS('fields_form.css');
<h3><?php echo $page_title; ?></h3>

<form action="<?php echo FLATFILEDB_ADMINURL . '&db=' . $db_name; ?>" method="post">
  <p>
    <?php FlatFileDBPlugin::i18n('DELETE_RECORD_SURE', array('%id%' => '<b>' . $record_id . '</b>'));
    ?>
  </p>
  <p>
    <input type="hidden" name="_id" value="<?php echo $record_id; ?>"/>
    <input class="submit" type="submit" name="delete" value="<?php FlatFileDBPlugin::i18n('CONFIRM'); ?>"/>&nbsp;&nbsp;/
    <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL . '&db=' . $db_name; ?>"><?php i18n('CANCEL'); ?></a>
  </p>
</form>
<h3><?php FlatFileDBPlugin::i18n('DROP_DATABASE'); ?>: <?php echo $db_name; ?></h3>

<form action="<?php echo FLATFILEDB_ADMINURL; ?>" method="post">
  <p><?php FlatFileDBPlugin::i18n('DROP_DATABASE_SURE', array('%name%' => '<b>' . $db_name . '</b>')); ?></p>
  <p>
    <input type="hidden" name="name" value="<?php echo $db_name; ?>"/>
    <input class="submit" type="submit" name="drop" value="<?php FlatFileDBPlugin::i18n('CONFIRM'); ?>"/>&nbsp;&nbsp;/
    <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL; ?>"><?php i18n('CANCEL'); ?></a>
  </p>
</form>
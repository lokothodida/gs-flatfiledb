<h3><?php FlatFileDBPlugin::i18n('CREATE_DATABASE'); ?></h3>

<form method="post" action="<?php echo FLATFILEDB_ADMINURL;?>">
  <p>
    <input class="text title" name="name" placeholder="<?php FlatFileDBPlugin::i18n('NAME'); ?>" required/>
  </p>

  <p>
    <input class="submit" type="submit" name="create" value="<?php FlatFileDBPlugin::i18n('CREATE'); ?>"/>&nbsp;&nbsp;/
    <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL; ?>"><?php i18n('CANCEL'); ?></a>
  </p>
</form>
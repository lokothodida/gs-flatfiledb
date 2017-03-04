<h3 class="floated"><?php echo $page_title; ?></h3>

<?php include('admin_nav.php'); ?>

<div class="loading">
  <?php FlatFileDBPlugin::i18n('LOADING'); ?>
</div>

<table class="database hidden edittable highlight">
  <thead>
    <tr>
      <th>_id</th>
      <?php foreach ($fields as $fname => $field) : ?>
        <?php if (@$field->hidden !== 'y') : ?>
        <th><?php echo $field->label; ?><th>
        <?php endif; ?>
      <?php endforeach ;?>

      <th width="1%"></th>
    </tr>
  </thead>
  <tbody class="records">
    <?php foreach ($records as $record) : ?>
    <tr>
      <td>
        <?php if ($can_update): ?>
          <a href="<?php echo FLATFILEDB_ADMINURL . '&db=' . $db_name . '&update=' . $record->_id; ?>">
            <?php echo $record->_id; ?>
          </a>
        <?php else : ?>
          <?php echo $record->_id; ?>
        <?php endif; ?>
      </td>

      <?php foreach ($fields as $fname => $field) : ?>
        <?php if (@$field->hidden !== 'y') : ?>
        <td><?php echo self::getExcerpt(@$record->{$fname}); ?><td>
        <?php endif; ?>
      <?php endforeach ;?>

      <td>
        <?php if ($can_delete) : ?>
        <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL . '&db=' . $db_name . '&delete=' . $record->_id; ?>">&times;</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<p>
  <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL; ?>"><?php i18n('CANCEL'); ?></a>
</p>

<script src="<?php echo FLATFILEDB_JSURL; ?>jquery.datatables.min.js"></script>
<script src="<?php echo FLATFILEDB_JSURL; ?>view_database.js"></script>
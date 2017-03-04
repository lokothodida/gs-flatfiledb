<h3 class="floated"><?php echo $page_title; ?></h3>

<?php include('admin_nav.php'); ?>

<table class="edittable highlight">
  <thead>
    <tr>
      <th width="100%"><?php $label_name; ?></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($databases as $database) : ?>
    <tr>
      <td>
        <a href="<?php echo FLATFILEDB_ADMINURL . '&db=' . $database . '&view'; ?>">
          <?php echo $database; ?>
        </a>
      <td>
      <td>
        <?php if ($can_drop) : ?>
        <a class="cancel" href="<?php echo FLATFILEDB_ADMINURL . '&db=' . $database . '&drop'; ?>">&times;</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
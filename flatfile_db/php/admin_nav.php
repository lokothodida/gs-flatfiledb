<div class="edit-nav clearfix">
  <?php foreach ($nav as $item) : ?>
    <a class="<?php echo $item->classes; ?>" href="<?php echo $item->url; ?>" target="<?php echo $item->target; ?>"><?php echo $item->title; ?></a>
  <?php endforeach; ?>
</div>
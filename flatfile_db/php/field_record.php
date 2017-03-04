<p class="field field-<?php echo $name; ?>">
  <label><?php echo $label; ?></label>
  <?php if ($type === 'text') : ?>
    <input class="text" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
  <?php elseif ($type === 'textlong') :?>
    <input class="text title" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
  <?php elseif ($type === 'textarea') :?>
    <textarea name="<?php echo $name; ?>"><?php echo $value; ?></textarea>
  <?php elseif ($type === 'wysiwyg') :?>
    <textarea class="wysiwyg" name="<?php echo $name; ?>"><?php echo $value; ?></textarea>
  <?php elseif ($type === 'date') : ?>
    <input class="text date" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
  <?php elseif ($type === 'checkbox') : ?>
    <input class="checkbox" type="checkbox" name="<?php echo $name; ?>" <?php if ($value) echo 'checked'; ?> value="y"/>
  <?php else : ?>
    <input class="text" name="<?php echo $name; ?>" value="<?php echo $value; ?>" readonly/>
  <?php endif; ?>
</p>
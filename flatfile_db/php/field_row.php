<tr class="field editable">
  <td>
    <input class="text name" name="name[]" value="<?php echo $field->name; ?>" required/>
  </td>
  <td>
    <input class="text label" name="label[]" value="<?php echo $field->label; ?>" required/>
  </td>
  <td>
    <select class="text type" name="type[]">
      <?php foreach ($types as $type) : ?>
      <option <?php if ($type == $field->type) echo 'selected'; ?>><?php echo $type; ?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td>
    <input class="text default" name="default[]" value="<?php echo $field->default; ?>"/>
  </td>
  </td>
  <td>
    <select class="text _hidden type" name="hidden[]">
      <option <?php if ($field->hidden === 'n') echo 'selected'; ?>>n</option>
      <option <?php if ($field->hidden === 'y') echo 'selected'; ?>>y</option>
    </select>
  </td>
  <td>
    <a class="cancel delete-field" href="#">&times;</a>
  </td>
</tr>
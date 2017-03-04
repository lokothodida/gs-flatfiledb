<script>
  jQuery(function($) {
    $('div.bodycontent').before('<div class="' + <?php echo $status; ?> + '" style="display:block;">'+<?php echo $message; ?>+'</div>');
  }); // ready
</script>
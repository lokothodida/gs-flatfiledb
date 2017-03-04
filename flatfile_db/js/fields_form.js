/* global jQuery */
jQuery(function($) {
  function getTemplate() {
    return $($("[name='field-template']").html());
  }

  function addFieldCallback(evt) {
    var $template = getTemplate();
    var $fields   = $(".fields");
    $fields.append($template);

    evt.preventDefault();
  }

  function deleteFieldCallback(evt) {
    var $target = $(evt.target);
    var $field  = $target.closest(".field");
    $field.remove();

    evt.preventDefault();
  }

  function init() {
    var $maincontent = $("#maincontent");

    $maincontent.find(".fields").sortable({
      cancel: "input, textarea, button, select, option, .cancel",
    });

    $maincontent.on("click", ".add-field", addFieldCallback);
    $maincontent.on("click", ".delete-field", deleteFieldCallback);
  }

  init();
});
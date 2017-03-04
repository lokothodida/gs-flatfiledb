/* global jQuery, CKEDITOR */
jQuery(function($) {
  function init() {
    var $maincontent = $("#maincontent");

    CKEDITOR.replaceAll("wysiwyg");

    $maincontent.find(".date").datepicker({
      dateFormat: "yy/mm/dd",
    });
  }

  init();
});
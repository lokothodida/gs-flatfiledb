/* global jQuery */
jQuery(function($) {
  function init() {
    var $maincontent = $("#maincontent");
    var $table = $maincontent.find(".database");
    var $loading = $maincontent.find(".loading");

    $table.DataTable({
      fnInitComplete: function(oSettings, jso) {
        $loading.remove();
        $table.removeClass("hidden");
      }
    });
  }

  init();
});
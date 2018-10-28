(function ($) {
    var link = document.createElement("link");
    link.setAttribute("rel", "stylesheet");
    link.setAttribute("type", "text/css");
    link.setAttribute("href", "https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css");
    document.head.appendChild(link);

    var script = document.createElement("script");
    script.setAttribute("type", "text/javascript");
    script.setAttribute("src", "https://code.jquery.com/ui/1.12.1/jquery-ui.js");
    document.head.appendChild(script);

    function datepicker() {
        var pickers = $('div.container.icinga-module.module-bridgedays input[type="datetime-local"]');

        if (typeof pickers.datepicker !== "undefined") {
            pickers.datepicker({"dateFormat": "yy-mm-ddT00:00:00"});
        }

        window.setTimeout(datepicker, 100);
    }

    $(datepicker);
})(jQuery);

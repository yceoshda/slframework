; sl_utils = (function ($) {

return {
    /**
    *   triggerNoticeEvent(message)
    *       sends a notification in the notification area
    */
    triggerNoticeEvent : function(data) {
        var $notif = $('#notif-area');
        var id = 'a' + Date.now();
        $notif.addClass('sl-notif-show').append('<div id="' + id + '" class="alert alert-' + data.type + ' sl-notif-hide" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>' + data.message + '</div>');
        $('#' + id).fadeIn('slow');
        setTimeout(function(){
                $('#' + id).fadeOut('slow', function (){ $(this).remove(); });
                if ($notif.html() == '') {
                    $notif.removeClass('sl-notif-show');
                };
             }, data.timeout);
    }

}


}(jQuery));
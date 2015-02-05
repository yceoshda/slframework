<script type="text/javascript">
<?php if ($_config->googleAnalytics->cnil): ?>

    (function ($){
        if ($.cookie('sl_cookie_bar') == undefined) {
            $('body').append('<div class="cookie-bar" id="cookie-bar"><?=$_translate->layout->cnil_compliance->text; ?> <a href="http://www.google.com/analytics/"><?=$_translate->layout->cnil_compliance->link; ?></a> <div class="cookie-btn" id="cookie-btn-ok"><?=$_translate->layout->cnil_compliance->btn_ok; ?></div>' + '<?=($_config->googleAnalytics->can_deny ? '<div class="cookie-btn cookie-btn-deny" id="cookie-btn-deny">'.$_translate->layout->cnil_compliance->btn_deny.'</div>' : ''); ?>' + '</div>');

            $('#cookie-btn-ok').click(function (e){
                e.preventDefault();
                $('#cookie-bar').fadeOut();
                $.cookie('sl_cookie_bar', 'viewed', { expires: <?=$_config->googleAnalytics->cookie_duration; ?> });
            });

            <?php if ($_config->googleAnalytics->can_deny): ?>

              $('#cookie-btn-deny').click(function (e){
                  e.preventDefault();
                  $('#cookie-bar').fadeOut();
                  $.cookie('sl_cookie_bar', 'viewed', { expires: <?=$_config->googleAnalytics->cookie_duration; ?> });
                  $.cookie('sl_cookie_deny', 'denied', { expires: <?=$_config->googleAnalytics->cookie_duration; ?> });
              });

            <?php endif ?>

        }

        if ($.cookie('sl_cookie_deny') == undefined) {
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
              ga('create', '<?=$_config->googleAnalytics->id; ?>', '<?=$_config->googleAnalytics->url; ?>');
              ga('send', 'pageview');
        };
    }(jQuery));

<?php else: ?>

    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
              ga('create', '<?=$_config->googleAnalytics->id; ?>', '<?=$_config->googleAnalytics->url; ?>');
              ga('send', 'pageview');

<?php endif ?>

</script>


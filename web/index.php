<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>SnapGameSearch</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="base.css">
        <link rel="stylesheet" href="index.css">
    </head>
    <body>
        <div id="content">
            <img class="logo floatLeft" src="img/pad.png" border="0" alt="" />
            <div id="div-search" class="floatLeft">
                <div id="ruwlerTitle">RUWLER</div>
                <form action="search.php" method="GET">
                    <input class="searchValue" type="text" name="search" style="width: 400px;" /> &nbsp; <input class="btn-search" type="submit" value="Rechercher" />
                </form>
            </div>
        </div>

        <script>
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-49631045-1']);
            _gaq.push(['_trackPageview']);
            _gaq.push(['_trackEvent', 'searchGames', 'search', '<?php echo $searchValue; ?>']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>
    </body>
</html>
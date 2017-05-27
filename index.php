<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Wiki Readscore</title>
        <meta name="author" content="Sarah German">
        <meta name="description" content="Analyzes readability of Wikipedia articles in a given category.">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Candal" type="text/css">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="css/normalize.min.css">
        <link rel="stylesheet" href="css/main.css">

        <!--[if lt IE 9]>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script>window.html5 || document.write('<script src="js/vendor/html5shiv.js"><\/script>')</script>
        <![endif]-->
    </head>
    <body>

        <div class="header-container">
            <header class="wrapper clearfix">
                <h1 class="title">wiki readscore</h1>
            </header>
        </div>

        <div class="main-container">
            <div class="main wrapper clearfix">

                <p>
                  Enter a Wikipedia Category in the box below to view articles in
                  that category, sorted by readability.
                </p>

                <form id="search-category" method="post" action="">
                  <label for="wiki-category" class="hidden">Category</label>
                  <input type="text" id="wiki-category" name="wiki-category" value="" placeholder="" required="required" autofocus="autofocus" />
                  <input type="submit" value="Search" id="submit-category" class="btn" />
                </form>

                <div class="results"></div>

            </div> <!-- #main -->
        </div> <!-- #main-container -->

        <div class="footer-container">
            <footer class="wrapper">
              <p>View source on <a href="https://github.com/sarahg/wiki-readscore">Github</a>.</p>
            </footer>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

        <script src="js/main.js"></script>
    </body>
</html>

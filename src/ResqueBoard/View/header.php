<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?php echo $pageTitle . TITLE_SEP . APPLICATION_NAME?></title>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js"></script>
        <script type="text/javascript" src="/js/jquery.jsrender.js"></script>
        <script type="text/javascript" src="/js/moment.js"></script>
        <script type="text/javascript" src="/js/jquery.cookie.js"></script>
        <script type="text/javascript" src="/js/d3.v2.min.js"></script>
        <script type="text/javascript" src="/js/bootstrap-2.0.4.min.js"></script>
        <script type="text/javascript" src="/js/app.js"></script>
        <link href="/css/bootstrap.css" rel="stylesheet" type="text/css">
        <link href="/css/main.css" rel="stylesheet" type="text/css">
        <link href="/img/resqueboard.png" rel="shortcut icon">
        
    </head>
    <body>
        <script type="text/javascript">serverIp = '<?php echo $_SERVER['SERVER_ADDR'] ?>'</script>
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="/"><img src="/img/resqueboard_24.png" alt="<?php echo APPLICATION_NAME ?>" /> <?php echo APPLICATION_NAME ?></a>
                        <ul class="nav">
                            <?php
                                $navs = array(
                                        '/' => 'Home',
                                        '/logs' => 'Logs',
                                        '/workers' => 'Workers',
                                		'/jobs' => 'Jobs'
                                        );
    
                                foreach ($navs as $link => $nav) {
                                    echo '<li'. ($link == $_SERVER['REQUEST_URI'] ? ' class="active"' : '').'>'.
                                    '<a href="'.$link.'">'.$nav.'</a></li>';
                                }
                            ?>
                        </ul>
                </div>
            </div>
        </div>
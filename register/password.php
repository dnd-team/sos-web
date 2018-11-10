<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Passwort für das School Organising System zurücksetzen.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>SOS - Passwort zur&uuml;cksetzen</title>

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="../favicons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="../favicons/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="../favicons/manifest.json">
    <link rel="mask-icon" href="../favicons/safari-pinned-tab.svg" color="#ff5900">
    <link rel="shortcut icon" href="../favicons/favicon.ico">
    <meta name="msapplication-config" content="../favicons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="SOS">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.1.3/material.deep_orange-blue.min.css" />
    <link rel="stylesheet" href="../css/getmdl-select.min.css">
    <link rel="stylesheet" href="../css/mdl-jquery-modal-dialog.css">
    <link rel="stylesheet" href="../css/jquery.modal.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="../css/snackbar.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="../css/material.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="../css/register.css">
</head>

<body>
    <div class="demo-layout mdl-layout mdl-js-layout mdl-color--grey-100">
        <header class="demo-header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
                <div class="mdl-layout__header-row">
                    <span class="mdl-layout-title">SOS - Passwort zur&uuml;cksetzen</span>
                    <div class="mdl-layout-spacer"></div>
                    <a class="mdl-button mdl-js-button mdl-button--icon" href="" target="_blank">
                        <i class="material-icons">help</i>
                    </a>
                </div>
        </header>
        <div class="demo-ribbon"></div>
        <main class="demo-main mdl-layout__content">
            <div class="demo-container mdl-grid">
                <div class="mdl-cell mdl-cell--2-col mdl-cell--hide-tablet mdl-cell--hide-phone"></div>
                <div class="demo-content mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--4-col" id="main-card">
                    <h3 class="title">Passwort zur&uuml;cksetzen</h3><br>
                    <form id="reset-pw" method="post">
                        <div class="mdl-card__supporting-text">
                            <p>Gib die Email Addresse ein, mit der du dich bei uns angemeldet hast und wir schicken dir einen Link zum Zur&uuml;cksetzen. Alternativ kannst du auch unseren Support kontaktieren unter support@schoolos.de.</p>
                            <div class="mdl-grid">

                                <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col">
                                    <input class="mdl-textfield__input" type="text" id="email" name="email">
                                    <label class="mdl-textfield__label" for="email">Email</label>
                                </div>
                            </div>
                            <?php
                                    if(isset($_GET["success"])){
                                        echo('<div class="success-message"><p>Erfolg! Email erfolgreich versendet!</p></div>');
                                    }
                                    if(isset($_GET["problem"])){
                                        echo('<div class="error-message"><p>Es ist ein Fehler aufgetreten. Hmm wir konnten deine Email in der Datenbank leider nicht finden.</p></div>');
                                    }

                                    ?>
                        </div>
                        <div class="mdl-card__actions">
                            <button type="submit" class="mdl-button">Email verschicken</button>
                            <a href="../login" target="_self" class="mdl-button">Zur&uuml;ck zum Login</a>
                        </div>
                    </form>
                </div> 
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>

    <!-- jQuery UI 1.12 -->
    <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="../js/validator.min.js"></script>
    <script src="../js/snackbar.min.js" type="text/javascript"></script>
    <script src="../js/mdl-jquery-modal-dialog.js"></script>
    <script src="../js/material.js"></script>
    <script src="../js/mfb.min.js" type="text/javascript"></script>
    <script src="../js/getmdl-select.min.js" type="text/javascript"></script>
    <script src="../js/app.main.js"></script>
    <script src="app.register.js"></script>
</body>

</html>

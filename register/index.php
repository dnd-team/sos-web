<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Registriere dich für das School Organising System">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>SOS - Registrieren</title>

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
    <!-- Captcha Code-->
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body id="register">
    <div class="demo-layout mdl-layout mdl-js-layout mdl-color--grey-100">
        <header class="demo-header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
                <div class="mdl-layout__header-row">
                    <span class="mdl-layout-title">SOS - Registrieren</span>
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
                <div class="demo-content mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--8-col" id="main-card">
                    <h3>Erstelle dein kostenloses SOS Konto</h3><br>
                    <form class="form" role="form" id="register-form">
                        <div class="mdl-card__supporting-text">
                            <div class="error-message" id="log-messages">
                                <p></p>
                            </div>
                            <div class="mdl-grid">
                                <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--6-col">
                                    <input class="mdl-textfield__input" type="text" id="first-name" name="surname" required>
                                    <label class="mdl-textfield__label" for="first-name">Vorname *</label>
                                </div>
                                <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--6-col">
                                    <input class="mdl-textfield__input" type="text" id="name" name="name" required>
                                    <label class="mdl-textfield__label" for="name">Name *</label>
                                </div>
                                <p>Bitte trage deinen richtigen Namen ein, damit dich deine Lehrer identifizieren können!</p>
                            </div>

                            <div class="mdl-grid">
                                <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--6-col">
                                    <input class="mdl-textfield__input" type="password" id="pwd" name="password" required>
                                    <label class="mdl-textfield__label" for="pwd">Passwort *</label>
                                </div>

                                <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--6-col">
                                    <input class="mdl-textfield__input" type="password" id="pwd-retry" name="password_retry" required>
                                    <label class="mdl-textfield__label" for="pwd-retry">Passwort wiederholen *</label>
                                </div>
                            </div>
                            <div class="mdl-grid">
                                <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--6-col">
                                    <input class="mdl-textfield__input" type="email" id="email" name="email" required>
                                    <label class="mdl-textfield__label" for="email">Email *</label>
                                </div>
                                <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--6-col">
                                    <input class="mdl-textfield__input" type="email" id="email-retry" name="email_retry" required>
                                    <label class="mdl-textfield__label" for="email-retry">Email wiederholen *</label>
                                </div>
                            </div>
                            <div class="mdl-grid">
                                <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="checkbox-1">
                                    <input type="checkbox" id="checkbox-1" name="accept_terms" class="mdl-checkbox__input" checked>
                                    <span class="mdl-checkbox__label">Ich akzeptiere die <a href="http://schoolos.de/agb-user" target="_blank">Allgemeinen Gesch&auml;ftsbedingungen</a>.</span>
                                </label>
                            </div>
                        </div>
                        <div class="mdl-card__actions">
                            <button type="submit" id="register-user" class="mdl-button">Registrieren</button>
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
    <script src="../js/snackbar.min.js" type="text/javascript"></script>
    <script src="../js/mdl-jquery-modal-dialog.js"></script>
    <script src="../js/material.js"></script>
    <script src="../js/mfb.min.js" type="text/javascript"></script>
    <script src="../js/getmdl-select.min.js" type="text/javascript"></script>
    <script src="../js/app.main.js"></script>
    <script src="app.register.js"></script>
</body>

</html>

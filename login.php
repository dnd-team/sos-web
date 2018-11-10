    <!doctype html>
    <html lang="de">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="Anmelden für SOS.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <title>SOS - Login</title>
        <!-- Add to homescreen for Chrome on Android -->
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="apple-touch-icon" sizes="180x180" href="../favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="../favicons/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="../favicons/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="../favicons/manifest.json">
        <link rel="mask-icon" href="../favicons/safari-pinned-tab.svg" color="#ff5900">
        <meta name="msapplication-config" content="../favicons/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">

        <!-- Add to homescreen for Safari on iOS -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="SOS">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="https://code.getmdl.io/1.1.3/material.deep_orange-blue.min.css" />
        <link rel="stylesheet" href="css/getmdl-select.min.css">
        <link rel="stylesheet" href="css/mdl-jquery-modal-dialog.css">
        <link rel="stylesheet" href="css/snackbar.css" type="text/css" media="screen"/>
        <link rel="stylesheet" href="css/material.css" type="text/css" media="screen"/>
        <link rel="stylesheet" href="../css/register.css">
    </head>
    <body>
        <div class="mdl-layout mdl-js-layout">
            <main class="mdl-layout__content">
                <div class="mdl-grid">
                    <div class="mdl-cell mdl-cell--4-col" id="login">
                        <form id="login-form" method="POST">
                                <div class="mdl-grid center">
                                    <div class="mdl-layout-spacer"></div>
                                    <div class="mdl-cell mdl-cell--4-col logo-wrapper"><img id="login-logo" src="images/sos-logo-login.png"/></div>
                                    <div class="mdl-layout-spacer"></div>
                                </div>
                                <div class="mdl-grid center">
                                    <div class="mdl-layout-spacer"></div>
                                    <div class="mdl-cell mdl-cell--8-col"><h2>SOS Login</h2></div>
                                    <div class="mdl-layout-spacer"></div>
                                </div>
                                <div class="mdl-grid center">
                                    <div class="mdl-layout-spacer"></div>
                                    <div class="mdl-cell mdl-cell--8-col register-btn-wrapper"><a href="/register/" class="register-btn">Noch kein Account? Jetzt registrieren</a></div>
                                    <div class="mdl-layout-spacer"></div>
                                </div>
                                <div class="mdl-grid">

                                    <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col">
                                        <input class="mdl-textfield__input" type="text" id="email" name="email" required>
                                        <label class="mdl-textfield__label" for="email">Email *</label>
                                    </div>
                                </div>
                                <div class="mdl-grid">
                                    <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col">
                                        <input class="mdl-textfield__input" type="password" id="pwd" name="password" required>
                                        <label class="mdl-textfield__label" for="pwd">Passwort *</label>
                                    </div>
                                </div>

                                <div class="mdl-grid">

                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="checkbox-1">
                                        <input type="checkbox" id="checkbox-1" class="mdl-checkbox__input" checked>
                                        <span class="mdl-checkbox__label">Angemeldet bleiben</span>
                                    </label>
                                </div>
                            <div class="mdl-card__actions">
                                <button type="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored">Anmelden</button><a href="/register/password" class="mdl-button mdl-js-button mdl-js-ripple-effect">Passwort vergessen?</a>
                            </div>
                        </form>
                    </div>
                    <div class="mdl-cell mdl-cell--8-col image-holder"><img id="login-image" src="images/sos-web.png"></div>
                </div>
            </main>
        </div>
        <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>

        <!-- jQuery UI 1.12 -->
        <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="js/validator.min.js"></script>
        <script src=js/snackbar.min.js type="text/javascript"></script>
        <script src="js/mdl-jquery-modal-dialog.js"></script>
        <script src="js/material.js"></script>
        <script src="js/mfb.min.js" type="text/javascript"></script>
        <script src="js/getmdl-select.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/store.js/1.3.20/store.min.js" type="text/javascript"></script>
        <script src="js/app.main.js"></script>
        <script src="js/app.login.js"></script>
    </body>

    </html>

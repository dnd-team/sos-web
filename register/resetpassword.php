<?php
     $e = "";

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $e = test_input($_GET["e"]);
    }
    function test_input($data) {
          $data = trim($data);
          $data = stripslashes($data);
          $data = htmlspecialchars($data);
          return $data;
    }
?>
    <!doctype html>
    <html lang="de">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="Passwort für das School Organising System zurücksetzen.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <title>SOS - Passwort &auml;ndern</title>

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
        <link rel="stylesheet" href="../css/register.css">
    </head>

    <body>
        <div class="demo-layout mdl-layout mdl-js-layout mdl-color--grey-100">
            <header class="demo-header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
                <div class="mdl-layout__header-row">
                    <span class="mdl-layout-title">SOS - Passwort &auml;ndern</span>
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
                        <h3 class="title">Passwort &auml;ndern</h3>
                        <form method="post" id="update-password">
                            <div class="mdl-card__supporting-text">
                                <div class="mdl-grid">

                                    <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col">
                                        <input class="mdl-textfield__input" type="password" id="p" name="password" required>
                                        <label class="mdl-textfield__label" for="p">Neues Passwort *</label>
                                    </div>
                                    <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col">
                                        <input class="mdl-textfield__input" type="password" id="r" name="password_retry" required>
                                        <input type="hidden" name="verification_code" value="<?php echo $e ?>" />
                                        <label class="mdl-textfield__label" for="r">Passwort wiederholen *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mdl-card__actions">
                                <button type="submit" class="mdl-button">Zur&uuml;cksetzen</button>
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
        <script src=../js/snackbar.min.js type="text/javascript"></script>
        <script src="../js/mdl-jquery-modal-dialog.js"></script>
        <script src="../js/material.js"></script>
        <script src="../js/mfb.min.js" type="text/javascript"></script>
        <script src="../js/getmdl-select.min.js" type="text/javascript"></script>
        <script src="../js/app.main.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(function() {
                "use strict";
                var e = getURLParameter("e");
                if (e != "" && e != " " && e != null) {
                    $(document).on('submit', '#update-password', function(e) {
                        e.preventDefault(); // avoid to execute the actual submit of the form.
                        $.ajax({
                            url: apiURL + "/password",
                            data: $("#update-password").serialize(),
                            type: 'PUT',
                            success: function(data) {
                                if (data["message"] == "Problem while updating password.") {
                                    var error =  '<h3>Fehler beim Zurücksetzen!</h3><br><p>Bitte versuche es erneut oder wende Dich an unseren Support: support@schoolos.de</p><br><a href="../login" target="_self" class="mdl-button">Zum Login</a>';
                                    $('#main-card').html(error).promise().done(function() {
                                        componentHandler.upgradeDom();
                                    });
                                } else if (data["message"] == "Password updated successfully.") {
                                    var success =  '<h3>Passwort erfolgreich zurückgesetzt!</h3><br><a href="../login" target="_self" class="mdl-button">Zum Login</a>';
                                    $('#main-card').html(success).promise().done(function() {
                                        componentHandler.upgradeDom();
                                        setTimeout(function() {
                                            window.location.href = "../login.php";
                                        }, 2000);

                                    });
                                }
                            },
                            error: function(xhr, desc, err) {
                                console.log(xhr);
                                console.log("Details: " + desc + "\nError:" + err);
                            }
                        });
                    });
                } else {
                    window.location.href = "../login.php";
                }

            });

        </script>
    </body>

    </html>

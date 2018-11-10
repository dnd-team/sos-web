<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="School Organising System">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta name="Author" content="Dominik Scherm">
    <title>SOS - Anmeldung zum Elternsprechtag</title>
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
    <link rel="stylesheet" href="../css/jquery.stickyalert.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="../css/material.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="styles.css">

    <style>
        #overview {
            overflow-x: scroll !important;
        }
        /* Overwrite style (colors and bg colors)*/

        @media only screen and (max-width: 480px) {
            #overview table {
                width: auto;
            }
            #overview {
                width: 100% !important;
                padding-bottom: 60px;
                margin-bottom: 5%;
                text-align: left;
            }
        }

        .consultation {
            width: 100% !important;
            margin 0;
            left: 0;
            position: relative;
        }

        .demo-header {
            z-index: 100;
        }

        .mdl-layout__header-row {
            background-color: #EFF2F3 !important;
            color: #546e7a;
        }

        .demo-ribbon {
            background-color: #FF5900 !important;
        }

        #add-task,
        .new-task {
            background-color: #536DFE !important;
        }

        #task-list,
        #calendar,
        #chats,
        #members {
            background-color: #F2F2F2 !important;
        }

        .mdl-navigation {
            background-color: #fff !important;
            color: #4A4A4A !important;
        }

        .demo-drawer-header {
            background-color: #FF5900 !important;
        }

        .mdl-navigation__link {
            color: #4A4A4A !important;
        }

        #new-course-btn {
            background-color: #536DFE !important;
            /*            background-color: #FF5900 !important;*/
        }

        #selected-course {
            background-color: #536DFE;
            color: #fff !important;
        }

        .getmdl-select {
            width: 100% !important;
        }

    </style>
</head>

<body>
    <div class="demo-layout mdl-layout mdl-layout--fixed-header mdl-js-layout mdl-color--grey-100">

        <header class="demo-header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
            <div id="alert-container"></div>
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title">SOS - Anmeldung zum Elternsprechtag</span>
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
                <div class="demo-content mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--8-col" id="overview">
                </div>
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>

    <!-- jQuery UI 1.12 -->
    <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
    <script src=../js/snackbar.min.js type="text/javascript"></script>
    <script src="../js/mdl-jquery-modal-dialog.js"></script>
    <script src="../js/material.js"></script>
    <script src="../js/jquery.modal.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="../js/mfb.min.js" type="text/javascript"></script>
    <script src="../js/getmdl-select.min.js" type="text/javascript"></script>
    <script src="../js/jquery.stickyalert.js" type="text/javascript"></script>
    <script src="app.consultations.js"></script>
</body>

</html>

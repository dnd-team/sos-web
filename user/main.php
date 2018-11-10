    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="School Organising System">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <meta name="Author" content="Dominik Scherm">
        <title>SOS - School Organising System</title>

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

        <!--    <link rel="shortcut icon" href="../favicons/apple-touch-icon-60x60.png">-->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <link rel="stylesheet" href="../css/bootstrap.min.css">

        <!--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />-->
        <link rel="stylesheet" href="../css/material-cards.css">
        <!-- core CSS of SnackbarJS, find it in /dist -->
        <link href=../css/snackbar.css rel=stylesheet>
        <link rel='stylesheet' href='../css/fullcalendar.css' />
        <link rel="stylesheet" href="../css/jquery.modal.css" type="text/css" media="screen" />
        <!--     <link rel='stylesheet' href='css/mdl-calendar.css' />-->
        <!-- the default theme of SnackbarJS, find it in /themes-css -->
        <link rel="stylesheet" href="../css/mdl-tooltip.css" type="text/css" media="screen" />
        <link href="../css/mfb.min.css" type="text/css" rel=stylesheet>
        <link rel="stylesheet" href="venobox/venobox.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="../css/mdl-jquery-modal-dialog.css">
        <link rel="stylesheet" href="https://code.getmdl.io/1.1.3/material.deep_orange-blue.min.css" />
        <link href=../css/material.css rel=stylesheet>
        <link rel="stylesheet" href="../css/main.css">
        <link rel="stylesheet" href="Dropzone/dropzone.css" type="text/css" />
        <link rel="stylesheet" href="../css/bootstrap-material-datetimepicker.css" />
        <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>

        <!--[if IE]>
        <script src="//cdnjs.cloudflare.com/ajax/libs/es5-shim/4.2.0/es5-shim.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/classlist/2014.01.31/classList.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js"></script>
        <![endif]-->

        <style>
            .jquery-modal {
                z-index: 10000000 !important;
            }

            .dtp {
                z-index: 100000000 !important;
            }

            .button-overlay {
                -webkit-box-shadow: inset 0px 80px 79px 0px rgba(255, 255, 255, 0.3);
                -moz-box-shadow: inset 0px 80px 79px 0px rgba(255, 255, 255, 0.3);
                box-shadow: inset 0px 80px 79px 0px rgba(255, 255, 255, 0.3);
            }
            /* Style for aushang cards */

            .demo-card-wide > .mdl-card__title {
                background-repeat: no-repeat !important;
                background-size: cover !important;
            }
              
            .mdl-card__actions.mdl-card {
                margin-top: -2.7% !important;
            }

            .dialog-container .mdl-card {
                margin: 10% auto !important;
            }

            .homework-section .mdl-card__actions .mdl-button,.aushang-section .mdl-card__actions .mdl-button,.setting-section .mdl-card__actions .mdl-button{
                color: #536DFE !important;
            }

            .new-btn {
                position: fixed;
                display: block;
                right: 0;
                bottom: 0;
                margin-right: 40px;
                margin-bottom: 40px;
                z-index: 900;
                /*border-radius: 4px;*/
            }

            #selected-course {
                background-color: #00BCD4;
                color: #37474F;
            }

            #selected-course .course-label {
                color: #fff;
            }

            .file-upload-dropzone {
                display: none;
                height: 1000px;
            }

            .file-preview-card,
            .members-card {
                display: block;
                width: 860px;
                margin: auto;
            }
            /* Overwrite style (colors and bg colors)*/

            .mdl-layout__header-row {
                background-color: #FF5900 !important;
                color: #fff;
            }

            #action-btn,
            .new-task {
                background-color: #536DFE !important;
            }

            .mdl-demo .mdl-layout__tab-panel:not(#overview) {
                background-color: #f2f2f2 !important;
            }

            #main {
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

            #new-course-btn,
            .mfb-component__button--child {
                background-color: #536DFE !important;
                /*            background-color: #FF5900 !important;*/
            }

            #selected-course {
                background-color: #536DFE;
                color: #fff !important;
            }

            #header-progress {
                height: 30px;
                width: 30px;
            }

            .custom-btn-save,
            .custom-btn-print {
                width: 150px;
            }

            .header-console {
                color: white;
                line-height: 50px;
                text-align: center;
                transition: all 1s;
                padding-right: 20px;
            }
            /**/
            .custom-btn-cancel {
                float: left;
                opacity: 0.5;
                border: none;
            }

            .custom-btn-submit {
                float: right;
                background-color: #2ecc71;
                color: white;
                margin-bottom: 7px;
            }

            .active {
                font-weight: 900 !important;
                color: black !important;
            }
            /* Autocompletion style */

            .autocomplete-suggestions {
                border: 1px solid #999;
                background: #fff;
                cursor: default;
                overflow: auto;
            }

            .autocomplete-suggestion {
                padding: 10px 5px;
                font-size: 1.2em;
                white-space: nowrap;
                overflow: hidden;
            }

            .autocomplete-selected {
                background: #f0f0f0;
            }

            .autocomplete-suggestions strong {
                font-weight: normal;
                color: #3399ff;
            }

            .demo-layout .demo-navigation .mdl-navigation__link:hover,
            .demo-layout .demo-navigation .mdl-navigation__link:focus {
                text-transform: none;
                text-decoration: none;
                color: white !important;
                background-color: #536DFE !important;
            }

            .demo-layout .demo-navigation .mdl-navigation__link:hover i,
            .demo-layout .demo-navigation .mdl-navigation__link:focus i {
                color: white !important;
            }

            .navigation-disabled {
                color: darkgray !important;
            }

            .active {
                color: #536DFE !important;
            }

            .active i {
                color: #536DFE !important;
            }

            .android-drawer-separator {
                height: 1px;
                background-color: #dcdcdc;
                margin: 8px 0;
            }

            .homework-done {
                opacity: 0.8;
            }

            .course-item {
                margin-left: 7% !important;
                height: 50px !important;

            }
            .folder-icon{
                color: #757575 !important;
            }
            .dialog-center{
                position: static;
                margin-top: 20%;
                margin-left: 25%;
                width: 50%;
                text-align: center;
                color: darkgray;
                display:block;
            }

            #consultation-table{
                width: 100% !important;
            }

            .isHidden{
                display: none !important;
            }

            .info-alert{
                background: #eeff41 !important;
                border-radius: 2px;
            }

            .active-user{
                color: #536DFE;
            }

            .icon-holder-tutorial{
                margin-top: 5%;
                margin-left: 30%;
            }
            
            .icon-holder-tutorial img{
                width: 70%;
            }

            #user-onboarding{
                padding: 0%;
                min-width: 60% !important;
                width: 60% !important;
            }

            #text-wrapper{
                padding-top: 5%;
                height: 300px;
                color: white;
            }
            
            #course-selection{
                min-width: 40% !important;
                width: 40% !important;
            }
            #course-selection .course-list{
                width: 100% !important;
            }

            body > div.mdl-layout__container > div > header > div.mdl-layout__drawer-button{
                background-color: #ff5900 !important;
                color: white !important;
            }

            .mdl-list__item-secondary-action{
                color: black;
            }

            /* Media Queries */

            @media screen and (max-width: 1000px) {


                .icon-holder-tutorial{
                    margin-left: 1%;
                    width: 70%;
                }

                .icon-holder-tutorial img{
                    width: 60%;
                    margin-left: auto;
                }

                #user-onboarding{
                    padding: 0%;
                    min-width: 90% !important;
                    width: 90% !important;
                }

                #text-wrapper{
                    padding-top: 5%;
                    color: white;
                    height: 300px;
                }

                .mdl-layout-title{
                    margin-left: 3% !important;
                }

                #course-selection{
                    min-width: 100% !important;
                    width: 100% !important;
                }
                #course-selection .course-list{
                    width: 100% !important;
                }
                .members-card{
                    width: 100% !important;
                }
            }


            @media screen and (max-width: 840px) {

                .icon-holder-tutorial{
                    margin-left: 30%;
                }

                .icon-holder-tutorial img{
                    width: 50%;
                    margin-left: auto;
                }

                #user-onboarding{
                    padding: 0%;
                    min-width: 100% !important;
                    width: 100% !important;
                }

                #text-wrapper{
                    padding-top: 5%;
                    color: white;
                    height: 400px;
                }
            }

            @media screen and (max-width: 480px) {

                .mdl-layout-title{
                    margin-left: 9% !important;
                }
                .mdl-layout__tab{
                    font-size: 0.94em;
                    text-transform: none;
                }

                #course-selection{
                    min-width: 95% !important;
                    width: 95% !important;
                }
                #course-selection .course-list{
                    width: 100% !important;
                }
                .members-card{
                    width: 100% !important;
                }
            }

            .warning-dialog{
                background-color: #fff9c4;
                
            }
            .warning-dialog p{
                display: block;
                text-align: center;
                margin: 0.8%;
                margin-left: 25%;
                width: 50%;
            }
            .mdl-spinner{
                width: 50px;
                height: 50px;
            }
        </style>
        <script defer src="../js/getmdl-select.min.js"></script>
        <link rel="stylesheet" href="../css/getmdl-select.min.css">
        <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />

    </head>

    <body class="mdl-demo mdl-color--grey-100 isHidden">
        <div class="demo-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header mdl-layout--fixed-tabs">

            <header class="demo-header mdl-layout__header mdl-color--grey-100 mdl-color-text--grey-600">
                <div class="mdl-layout__header-row">
                    <span class="mdl-layout-title"><?php if($_GET["site"] == "home"){
                                                    echo "Home";
                                                }
                                                else if($_GET["site"] == "plan"){
                                                    echo "Stundenplan";
                                                }
                                                else if($_GET["site"] == "timetable"){
                                                     echo "Stundenplan";
                                                }
                                                else if($_GET["site"] == "aushang"){
                                                    echo "Aushang";
                                                }
                                                else if($_GET["site"] == "courses"){
                                                    echo "Deine Kurse";
                                                }
                                                else if($_GET["site"] == "homework"){
                                                     echo "Aufgaben";
                                                }
                                                else if($_GET["site"] == "calendar"){
                                                    echo "Kalendar";
                                                }
                                                else if($_GET["site"] == "bugreport"){
                                                    echo "Bug Report";
                                                }
                                                else if($_GET["site"] == "settings"){
                                                    echo "Einstellungen";
                                                }
                                        ?></span>
                    <div class="mdl-layout-spacer"></div>
                    <span id="option-menu"></span>
                </div>
                <div id="tab-bar-controller"></div>
            </header>
            <div class="demo-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">
                <header class="demo-drawer-header">
                    <img src="../images/sos-logo.png" width="90">
                    <br>
                    <div class="demo-avatar-dropdown">
                        <span id='username-label'><?php echo $_SESSION["username"]; ?><br><span id='role-label'><?php echo ucfirst(strtolower($_SESSION["role"])); ?></span></span>
                        <br>
                        <div class="mdl-layout-spacer"></div>
                        <button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                            <i class="material-icons" role="presentation">arrow_drop_down</i>
                            <span class="visuallyhidden">Accounts</span>
                        </button>
                         <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu" for="accbtn" id="user-list">

                        </ul>
                    </div>
                </header>
                <nav class="demo-navigation mdl-navigation mdl-color--blue-grey-800">
                    <!--              <a class="mdl-navigation__link <?php if($_GET["site"]=="home"){echo('active');}?>" href="main?site=home"  data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons mdl-badge mdl-badge--overlap" role="presentation" data-badge="1">home</i>Home</a>-->
                    <a class="mdl-navigation__link <?php if($_GET["site"]=="plan"){echo('active');}?>" href="main?site=plan" data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">schedule</i>Stundenplan</a>
                     <span class="mdl-navigation__link navigation-disabled" id="course-btn"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">dns</i>Kurse<i class="mdl-color-text--blue-grey-400 material-icons" id="course-menu-icon">&#xE5C7;</i></span><span id="course-list"></span>
                        <div class="android-drawer-separator"></div>
                        <a class="mdl-navigation__link <?php if($_GET["site"]=="aushang"){echo('active');}?>" href="main?site=aushang" id=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">dashboard</i>Aushang</a>
                        <a id="homework" class="mdl-navigation__link <?php if($_GET["site"]=="homework"){echo('active');}?>" href="main?site=homework" data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">today</i><span id="homework-label">Aufgaben</span></a>
                        <!--            <a class="mdl-navigation__link <?php if($_GET["site"]=="calendar"){echo('active');}?>" href="main?site=calendar" data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">event</i>Kalendar</a>-->
                        <!-- <a class="mdl-navigation__link vpt <?php if($_GET["site"]=="consultations"){echo('active');}?>" href="main?site=consultations"  data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">people</i>Elternsprechtag</a> -->
                        <!--            <a class="mdl-navigation__link vpt <?php if($_GET["site"]=="messages"){echo('active');}?>" href="main?site=messages" data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">message</i>Nachrichten</a>-->
<!--                        <a class="mdl-navigation__link <?php if($_GET["site"]=="bugreport"){echo('active');}?>" href="main?site=bugreport" data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">bug_report</i>Bugreport</a>-->
                        <a class="mdl-navigation__link vpt <?php if($_GET["site"]=="settings"){echo('active');}?>" href="main?site=settings" data-intro=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">settings</i>Einstellungen</a>

                </nav>
            </div>
            <main class="mdl-layout__content mdl-color--grey-100" id="main">


                <section class="mdl-layout__tab-panel is-active" id="main-section">

                    <div class="page-content" id="overview">

                    </div>

                </section>
            </main>

        </div>
        <ul id="menu" class="mfb-component--br mfb-zoomin" data-mfb-toggle="hover">
            <li class="mfb-component__wrap">
                <a href="#" class="mfb-component__button--main" id="action-btn">
                    <i class="mfb-component__main-icon--resting ion-plus-round"></i>
                    <i class="mfb-component__main-icon--active ion-close-round"></i>
                </a>
                <ul class="mfb-component__list">
                </ul>
            </li>
        </ul>
        <!-- jQuery 2.2.4 -->
        <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
        <!-- jQuery UI 1.12 -->
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script type="text/javascript" src="../js/jquery.popup.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment-with-locales.js"></script>
        <script type="text/javascript" src="../js/bootstrap-material-datetimepicker.js"></script>
        <script type="text/javascript" src="../js/validator.min.js"></script>
        <script type="text/javascript" src="venobox/venobox.min.js"></script>
        <!-- Dropzone.js -->
        <script src="Dropzone/dropzone.js" type="text/javascript"></script>
        <script type="text/javascript" src="../js/jquery.popup.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="../js/jQuery.print.js"></script>
        <script src="../js/fullcalendar.js" type="text/javascript"></script>
        <script src=../js/snackbar.min.js type="text/javascript"></script>
        <script src=../js/jquery.query-object.js type="text/javascript"></script>
        <script src="../js/mdl-jquery-modal-dialog.js"></script>
        <script src="../js/jquery.modal.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="../js/material.js"></script>
        <script type="text/javascript" src="../js/jquery.autocomplete.min.js"></script>
        <script src="../js/mfb.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.debug.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/2.0.16/jspdf.plugin.autotable.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.13/clipboard.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.4/jquery.lazy.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.4/jquery.lazy.plugins.min.js"></script>        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/store.js/1.3.20/store.min.js" type="text/javascript"></script>
        <script src="../js/app.main.js"></script>  
        <script src="app.user.js"></script> 
    </body>

    </html>

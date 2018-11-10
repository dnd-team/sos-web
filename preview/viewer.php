<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sign up for the SOS System.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>SOS - Preview</title>

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
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">

    <style>

        /* Overwrite style (colors and bg colors)*/
        
        html::-webkit-scrollbar { 
            display: none; 
        }

        
        .mdl-layout__container{
            position: relative !important;
        }

        #main-card {
            width: 100%;
            padding-bottom: 60px;
            margin-bottom: 5%;
            
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
        /*********************
            VP Style
        *********************/
        .button-overlay {
          -webkit-box-shadow: inset 0px 80px 79px 0px rgba(255,255,255,0.3);
        -moz-box-shadow: inset 0px 80px 79px 0px rgba(255,255,255,0.3);
        box-shadow: inset 0px 80px 79px 0px rgba(255,255,255,0.3);
        }

        .mdl-layout__header-row{
            background-color: #FF5900 !important;
            color: #fff;
        }
	.custom-btn-save, .custom-btn-print {width: 150px;}
    .header-console {color: white; line-height: 50px; text-align: center; transition: all 1s; padding-right: 20px;}

	/* VP Interface */
	.vptable {position: relative; width: auto; margin: 0 auto; margin-top: -3%; top: 0px; padding-right: 15px; padding-left: 15px; z-index: 1; font-family: 'Roboto','Avenir Next',sans-serif; border-radius: 5px;}
    .vptable table{background-color: #fff; border-radius: 5px; }
    #vp-builder{ -webkit-box-shadow: 10px 22px 40px 10px rgba(0,0,0,0.15);
-moz-box-shadow: 10px 22px 40px 10px rgba(0,0,0,0.15);
box-shadow: 10px 22px 40px 10px rgba(0,0,0,0.15);}
	.vpheadline>td {font-weight: 500; text-align: left; height: 50px; color: black; background-color: #fff; line-height: 50px; font-size: 18px; }
    .vpheadline-wrapper{margin-top: 1%; margin-bottom: 1%;}
    .vpheadline h4{display: inline-block; margin: 0; margin-left:4%; color: #ff5900; width: 60%; font-size: 
        1.6em; letter-spacing: 0em;}
    .pagination{margin: 0; margin-left: 18%; padding: 0};
    .pager li.active > a, .pager li.active > span{
        background-color:#ff5900;
        pointer-events:none;
        border-color: #ffffff;
    }
    .active > a{background-color: #ff5900 !important; color: white; border-color: #fff !important;}
    .pagination>.deactive>a{background-color: #fff !important; color: #ff5900 !important;}
	.vpheader {height: 10px; color: #fff; font-weight: 500; background-color: #ff5900; opacity: 1; }
	.vpheader>td {border: 3px black;}
	.table > tbody > tr > .vpcell {padding: 0px; margin: 0px; width: 175px;}
	.vprow {height: auto; text-align: center; border-collapse: collapse;}

	.largerow {height: auto;}
	.smallrow {height: 20px;}
	.aweek, .bweek {cursor: pointer;}

	.vprow > td > ul {list-style: none; padding: 0px; margin: 0px;}
	.vpcontent {background-color: transparent; transition: 0.25s; height: auto; width: 100%;}
	.vpcontent:hover {background-color: #f2f2f2;}

	.vpdata {padding: 5px; width: 100%; position: relative; height: auto;}
	.vpdata_new {
        display: none;

	}

    .vpdata table td{
        padding: 0.5%;
        padding-left: 3%;
    }
    .vpdata table .lesson-label,.teacher-label,.description-label{
        word-wrap:break-word;
        max-width: 50px;
        font-size: 0.9em;
    }

        
    .vpdata table .description-label{
        max-width: 60px;
    }
        
    
    .vpdata table .lesson-label,.grade-label{
        font-weight: 700;
        font-size: 1.1em;
    }
        
    .vpdata table{
    
       width: 100%;
       height: auto;
        
       box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    }  
        
    .blurred{filter: blur(5px); }

    .vp-alert{display: none; position: absolute; z-index: 100000; left: 15%; width: auto; color: black;}
    .main-footer{position: fixed; width: 100%; z-index: 1000;}
	.vpdata_new:hover {opacity: 1;}
	.vptime {width: 30px;}
	.input-break>span
	.input-break>p {margin: 0px;}

	.input-group {padding-bottom: 7px;}
	#input_description {height: 50px; resize: vertical;}
	.custom-popup-addon {width: 100px; text-align: right;}
	.custom-popup-input {width: 300px; text-align: left;}
	.testroman {width: 300px; text-align: left;}
	.custom-btn-cancel {float: left; opacity: 0.5; border: none;}
	.custom-btn-submit {float: right; background-color: #ff5900; color: white; margin-bottom: 7px;}

        .active{font-weight: 900 !important;
    color: black !important;}
        
    .table>tbody>tr.info>td, .table>tbody>tr.info>th, .table>tbody>tr>td.info, .table>tbody>tr>th.info, .table>tfoot>tr.info>td, .table>tfoot>tr.info>th, .table>tfoot>tr>td.info, .table>tfoot>tr>th.info, .table>thead>tr.info>td, .table>thead>tr.info>th, .table>thead>tr>td.info, .table>thead>tr>th.info {
        background-color: #f8f9fb;
    }


    .demo-layout .demo-navigation .mdl-navigation__link:hover,.demo-layout .demo-navigation .mdl-navigation__link:focus{
            text-transform: none;
            text-decoration: none;
            color: white !important;
            background-color: #536DFE !important;
        }
    .homework-section .mdl-card__actions .mdl-button,.aushang-section .mdl-card__actions .mdl-button,.setting-section .mdl-card__actions .mdl-button{
                color: #536DFE !important;
            }
    .demo-layout .demo-navigation .mdl-navigation__link:hover i,.demo-layout .demo-navigation .mdl-navigation__link:focus i{
          color: white !important;
        }

        .active{
            color: #536DFE !important;
        }
        .active i{
            color: #536DFE !important;
        }
        
        .lesson-type{
            display: none !important;
        }

        @media print {
          .lesson-type{
            display: inline !important;
         }
        }

        @media screen and (max-width: 1000px) {
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
            }

        @media screen and (max-width: 480px) {
                .mdl-layout-title{
                    margin-left: 8% !important;
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
            }

    </style>
    <!-- Captcha Code-->
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body>
    <div class="demo-layout mdl-layout mdl-layout--fixed-header mdl-js-layout mdl-color--grey-100">
        <header class="demo-header mdl-layout__header mdl-layout__header--scroll mdl-color--grey-100 mdl-color-text--grey-800">
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title">SOS - VP Viewer</span>
                <div class="mdl-layout-spacer"></div>
                <a class="mdl-button mdl-js-button mdl-button--icon" href="" target="_blank">
                    <i class="material-icons">help</i>
                </a> 
            </div>
        </header>
        <div class="demo-ribbon"></div>
        <main class="demo-main mdl-layout__content">
                          <div class="vptable">
                                                        <table class="table table-bordered" id="vp-builder">

                                                            <tbody>

                                                                <!-- Headline -->
                                                                <tr class="vpheadline">
                                                                    <td colspan="9">

                                                                        <!-- Current date -->
                                                                        <div class="vpheadline-wrapper">
                                                                        <h4>
                                                                        <span id="week_print">A-Woche</span>
                                                                        <?php

                                        //error_reporting(-1); ini_set('display_errors', 'On');

                                        getVPDate();
                                                                            
                                       function vpdate($vp) {
                                            $german = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");

                                            // Get date
                                            date_default_timezone_set('Europe/Berlin'); $today = getdate();
                                            $date = new DateTime($today["year"]."-".$today["mon"]."-".$today["mday"]);

                                            $weekday = $german[date( "N", strtotime($date->format('Y-m-d')))-1];

                                            // Format and return
                                            $datef = $date->format('d.m.Y');
                                            return '<span id="vp_weekday">'.$weekday.'</span>, der <span id="vp_date">'.$datef.'</span>';

                                        }
                                    
                                        function getVPDate(){
                                            $vp_day = $_GET["daynumber"]; 
                                            $vpDay = test_input($vp_day);
                                            if(!isset($vpDay) || empty($vpDay)){
                                                $vpDay = date('N', strtotime($date));
                                            }
                                            echo(vpdate($vpDay)); 
                                           
                                        }
                                        
                                                                            
                                        function test_input($data) {
                                          $data = trim($data);
                                          $data = stripslashes($data);
                                          $data = htmlspecialchars($data);
                                          return $data;
                                        }
                                                                            
                                        ?>

                                                                            <!-- A/B Week -->
                                                                            </h4>


                                                                            <ul class="pagination">
                                                                                    <li class="aweek active" week="A"><a>A-Woche <span class="sr-only"></span></a></li>
                                                                                    <li class="bweek deactive" week="B"><a>B-Woche <span class="sr-only"></span></a></li>
                                                                                </ul>

</div>
                                                                    </td>
                                                                </tr>
                                                                

                                                                <!-- Tableheads -->
                                                                
                                                                <tr class="vprow">
                                                                    <td id="abwesend" class="info">Abwesend:</td>
                                                                    <td colspan=8 class="input-break" id="absent" break="absent">
                                                                        <div class="break-text"></div></td>

                                                                </tr>
                                                                <tr class="vpheader">
                                                                    <td class="vptime">Zeit</td>
                                                                    <td>5/6</td>
                                                                    <td>7</td>
                                                                    <td>8</td>
                                                                    <td>9</td>
                                                                    <td>10</td>
                                                                    <td>11</td>
                                                                    <td>12</td>
                                                                    <td>Lehrer</td>
                                                                </tr>

                                                                
                                                                
                                                                <!-- Row 1 -->
                                                                <tr class="vprow table-striped largerow">
                                                                    <td class="vptime">8:00 bis 9:30 Uhr</td>
                                                                    <td class="vpcell" grade="5-6" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new lesson_full" grade="5-6" id="5-6_1" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="7" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="7_1" grade="7" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="8" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="8_1" grade="8" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="9" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="9_1" grade="9" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="10" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="10_1" grade="10" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="11" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="11_1" grade="11" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="12" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="12_1" grade="12" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="L" period="1">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="L_1" grade="L" period="1">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <span class="dialog-center vp-alert "><h2>Ferienmodus aktiviert!</h2></span>
                                                                </tr>

                                                                <!-- Row 2 -->
                                                                <tr class="vprow smallrow">
                                                                    <td class="vptime">Pause</td>
                                                                    <td colspan=8 class="input-break" id="break_1" break="1">
                                                                        <div class="break-text"></div></td>
                                                                </tr>

                                                                <!-- Row 3 -->
                                                                <tr class="vprow largerow" id="second-hour">
                                                                    <td class="vptime">10:00 bis 11:30 Uhr</td>
                                                                    <td class="vpcell" grade="5-6" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="5-6_2" grade="5-6" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="7" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="7_2" grade="7" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="8" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="8_2" grade="8" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="9" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="9_2" grade="9" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="10" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="10_2" grade="10" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="11" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="11_2" grade="11" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="12" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="12_2" grade="12" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="L" period="2">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="L_2" grade="L" period="2">+</li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>

                                                                <!-- Row 4 -->
                                                                <tr class="vprow smallrow">
                                                                    <td class="vptime">Pause</td>
                                                                    <td colspan=8 class="input-break" id="break_2" break="2">
                                                                        <div class="break-text"></div></td>
                                                                </tr>

                                                                <!-- Row 5 -->
                                                                <tr class="vprow">
                                                                    <td class="vptime">12:00 bis 13:30 Uhr</td>
                                                                    <td class="vpcell" grade="5-6" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="5-6_3" grade="5-6" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="7" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="7_3" grade="7" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="8" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="8_3" grade="8" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="9" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="9_3" grade="9" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="10" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="10_3" grade="10" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="11" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="11_3" grade="11" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="12" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="12_3" grade="12" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="L" period="3">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="L_3" grade="L" period="3">+</li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>

                                                                <!-- Row 6 -->
                                                                <tr class="vprow smallrow">
                                                                    <td class="vptime">Pause</td>
                                                                    <td colspan=8 class="input-break" id="break_3" break="3">
                                                                        <div class="break-text"></div></td>
                                                                </tr>

                                                                <!-- Row 7 -->
                                                                <tr class="vprow">
                                                                    <td class="vptime">13:50 bis 15:20 Uhr</td>
                                                                    <td class="vpcell" grade="5-6" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="5-6_4" grade="5-6" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="7" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="7_4" grade="7" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="8" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="8_4" grade="8" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="9" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="9_4" grade="9" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="10" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="10_4" grade="10" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="11" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="11_4" grade="11" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="12" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="12_4" grade="12" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="L" period="4">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="L_4" grade="L" period="4">+</li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>

                                                                <!-- Row 8 -->
                                                                <tr class="vprow smallrow">
                                                                    <td class="vptime">Pause</td>
                                                                    <td colspan=8 class="input-break" id="break_4" break="4">
                                                                        <div class="break-text"></div></td>
                                                                </tr>

                                                                <!-- Row 9 -->
                                                                <tr class="vprow">
                                                                    <td class="vptime">15:30 bis 17:00 Uhr</td>
                                                                    <td colspan=5></td>
                                                                    <td class="vpcell" grade="11" period="5">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="11_5" grade="11" period="5">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="12" period="5">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="12_5" grade="12" period="5">+</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td class="vpcell" grade="L" period="5">
                                                                        <ul class="vpcontent">
                                                                            <li class="vpdata_new" id="L_5" grade="L" period="5">+</li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                    

                                                            </tbody>

                                                        </table>
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
    <script src="../js/app.main.js"></script>
    <script src="viewer-js.js"></script>
</body>

</html>

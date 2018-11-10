<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sign up for the SOS System.">
    <meta name="viewport" content="width=device-width, initial-scale=0.5, minimum-scale=0.1">
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
    body{background-color: #484c4f;}
        
	/* VP Interface */
	.vptable {position: relative; width: auto; margin: 10% auto; padding-right: 15px; padding-left: 15px; z-index: 1; font-family: 'Roboto','Avenir Next',sans-serif; overflow-x: scroll;}
    .vptable table{background-color: #ffffff; margin-bottom: 
        2%; border-bottom: 1px solid lightgray; background-size: cover;}
	.vpheadline>td {font-weight: 500; text-align: left; height: 30px; color: black; background-color: #fff; line-height: 50px; font-size: 15px; }
    .vpheadline-wrapper{margin-top: 2%; margin-bottom: 1%;}
    .vpheadline h4{display: inline-block; margin: 0; margin-left:4%; color: #ff5900; width: 60%; font-size: 
        1.5em; letter-spacing: 0em; padding: 0 !important;}
    .pagination{margin: -1%; margin-left: 70%; padding: 0 !important; display: inline-block; width: 30%;};
    .pager li.active > a, .pager li.active > span{
        background-color:#ff5900;
        pointer-events:none;
        border-color: #ffffff;
    }
    .active > a{background-color: #ff5900 !important; color: white; border-color: #fff !important;}
    .pagination>.deactive>a{background-color: #fff !important; color: #ff5900 !important;}
	.vpheader {height: 10px; color: #fff; font-weight: 500; background-color: #ff5900; opacity: 1; }
	.vpheader>td {border: 3px black;}
	.table > tbody > tr > .vpcell {padding: 0px; margin: 0px; width: 150px; background-color: #ffffff !important;}
	.vprow {height: auto; text-align: center; border-collapse: collapse;}

        
    .input-break{background-color: #ffffff !important;}    
	.largerow {height: auto;}
	.smallrow {height: 20px; background-color: #f2f2f2;}

	.vprow > td > ul {list-style: none; padding: 0px; margin: 0px;}
    .vpcontent {background-color: transparent; transition: 0.25s; height: auto; width: 100% !important;}
    .vpdata {min-height: 20px; height: auto; border-bottom: 2px #ddd solid; width: 100%; position: relative; padding: 0px; border: none;}
    .vptime {width: 15px !important; height: 100%; line-height: 15px; font-size: 10pt; font-weight: 500;}
    .largerow {height: auto;} .smallrow {height: 14px; line-height: 14px;}
    #absent, #jobs {text-align: left;}

    /* Invisible */
    .vpdata_new,
    .week,
    .popover,
    .arrow,
    .popover-title,
    .popover-content,
    .arrow,
    .input-break-edit,
    .vpdata .remove,
    .vpdata .edit,
    .input-break-edit,
    .lesson-type-input,
    .reset-break {display: none;} 
        
    .blurred{filter: blur(5px); }    
    .vp-alert{display: none; position: absolute; z-index: 100000; left: 15%; width: auto; color: black;}
        
    .vpdata table {
        min-width: 100% !important;
    }
        
     .vpdata table .lesson-label,.teacher-label,.description-label{
        width: auto;
        font-size: 0.7em;
        padding-left: 4% !important;
    }

    .vpdata table .description-label{
        max-width: 60px;
    }
        
    .vpdata table .lesson-label,.grade-label{
        font-weight: 700;
        padding-left: 4% !important;
    }
        
    </style>
    <!-- Captcha Code-->
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body>
        <main>
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

                                            // Difference to specified date
                                            $diff = $vp - date( "N", strtotime($date->format('Y-m-d')));

                                            // Calculate date using diff
                                            if ($diff<0) {$di = new DateInterval("P".(7-(abs($diff))."D"));}
                                            else {$di = new DateInterval("P".(abs($diff)."D"));}
                                            $date->add($di)->format('d.m.Y');
                                            $weekday = $german[date( "N", strtotime($date->format('Y-m-d')))-1];

                                            // Format and return
                                            $datef = $date->format('d.m.Y');
                                            return '<span id="vp_weekday">'.$weekday.'</span>, der <span id="vp_date">'.$datef.'</span>';

                                        }
                                    
                                        function getVPDate(){
                                            $vp_day = $_GET["daynumber"]; 
                                            $vpDay = test_input($vp_day); 
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
                                                                <tr class="vprow largerow">
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
                                                                 <tr class="vprow">
                                                                    <td id="abwesend" class="info">Abwesend:</td>
                                                                    <td colspan=8 class="input-break" id="absent" break="absent">
                                                                        <div class="break-text"></div></td>

                                                                </tr>

<!--
                                                                <tr class="vprow">
                                                                    <td id="auftraege" class="info">Auftr&auml;ge:</td>
                                                                    <td colspan=8 class="input-break" id="jobs" break="jobs">
                                                                        <div class="break-text"></div>
                                                                    </td>
                                                                </tr>
-->

                                                            </tbody>

                                                        </table>
                                                    </div>

        </main>
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
    <script src="vp.js"></script>
</body>

</html>

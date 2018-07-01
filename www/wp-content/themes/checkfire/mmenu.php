

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="author" content="www.frebsite.nl" />
    <meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />

    <title>jQuery.mmenu - Demo</title>

    <link rel="stylesheet" href="css/demo.css?v=7.0.1" />
    <link rel="stylesheet" href="css/hamburgers.css" />
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../mmenu/jquery.mmenu.all.css?v=7.0.1" />
    <link rel="stylesheet" href="../mhead-plugin/mhead/jquery.mhead.css?v=7.0.1" />

    <style>
        .mm-menu {
            height: calc( 100% - 40px ) !important;
            background: #4bb5ef;
        }
        .mm-navbar_size-2 {
            text-align: center;
            position: relative;
            border-bottom: none;
            display: block !important;
        }
        .mm-navbar_size-2:before {
            content: "";
            display: inline-block;
            vertical-align: middle;
            height: 100%;
            width: 1px;
        }
        .mm-navbar_size-2 > * {
            color: #fff !important;
            display: inline-block;
            vertical-align: middle;
        }
        .mm-navbar_size-2 img {
            opacity: 0.6;
            border: 1px solid #fff;
            border-radius: 60px;
            width: 60px;
            height: 60px;
            padding: 10px;
            margin: 0 10px;
        }
        .mm-navbar_size-2 a {
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 40px;
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 16px !important;
            line-height: 40px;
            width: 40px;
            height: 40px;
            padding: 0;
        }
        .mm-navbar_size-2 a:hover {
            border-color: #fff;
            color: #fff !important;
        }

        .mm-panels > .mm-panel:after {
            content: none;
            display: none;
        }
        .mm-panels > .mm-panel > .mm-listview {
            margin: 0;
        }

        .mm-listview {
            text-transform: uppercase;
        }
        .mm-listitem:last-child:after {
            content: none;
            display: none;
        }
        .mm-listitem a,
        .mm-listitem span {
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            padding-right: 20px !important;
        }
        .mm-listitem a:hover,
        .mm-listitem a:hover + span {
            color: #fff;
        }
    </style>
</head>
<body>
<div id="page">

    <div class="mh-head Sticky">
				<span class="mh-btns-left">
					<a href="#menu" class="fa fa-bars"></a>
					<a href="#page" class="fa fa-close"></a>
				</span>
        <span class="mh-text">demo</span>
    </div>

    <div class="content">
        <p><strong>This is a demo.</strong><br />
            Click the menu icon to open a custom styled menu.</p>
    </div>
</div>

<nav id="menu">
    <ul id="panel-menu">
        <li><a href="#/">Home</a></li>
        <li><a href="#/work">Our work</a></li>
        <li><span>About us</span>
            <ul>
                <li><a href="#/about/history">History</a></li>
                <li><span>The team</span>
                    <ul>
                        <li><a href="#/about/team/management">Management</a></li>
                        <li><a href="#/about/team/sales">Sales</a></li>
                        <li><a href="#/about/team/development">Development</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li><span>Services</span>
            <ul>
                <li><a href="#/services/design">Design</a></li>
                <li><a href="#/services/development">Development</a></li>
                <li><a href="#/services/marketing">Marketing</a></li>
            </ul>
        </li>
        <li><a href="#/contact">Contact</a></li>
    </ul>
    <ul id="panel-language">
        <li><a href="#/en">English</a></li>
        <li><a href="#/de">Deutsch</a></li>
        <li><a href="#/nl">Nederlands</a></li>
    </ul>
</nav>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="../mmenu/jquery.mmenu.all.js?v=7.0.1"></script>
<script src="../mhead-plugin/mhead/jquery.mhead.js?v=7.0.1"></script>
<script src="js/playground.js"></script>
<script>
    $(function() {

        $("#menu").mmenu({
            extensions 	: [ "position-bottom", "fullscreen", "theme-black", "listview-50", "fx-panels-slide-up", "fx-listitems-drop", "border-offset" ],
            navbar 		: {
                title 		: ""
            },
            navbars		: [{
                height 	: 2,
                content : [
                    '<a href="#/" class="fa fa-phone"></a>',
                    '<img src="img/profile-2-w.png" />',
                    '<a href="#/" class="fa fa-envelope"></a>'
                ]
            }, {
                content : ["prev","title"]
            }]}, { });
        $(".mh-head.mm-sticky").mhead({
            scroll: {
                hide: 200
            }
        });
        $(".mh-head:not(.mm-sticky)").mhead({
            scroll: false
        });



        $('body').on( 'click',
            'a[href^="#/"]',
            function() {
                alert( "Thank you for clicking, but that's a demo link." );
                return false;
            }
        );
    });
</script>
</body>
</html>
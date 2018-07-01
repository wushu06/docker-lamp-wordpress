/*
 *	JavaScript Wordpress editor
 *	Author: 		Ante Primorac
 *	Author URI: 	http://anteprimorac.from.hr
 *	Version: 		1.1
 *	License:
 *		Copyright (c) 2013 Ante Primorac
 *		Permission is hereby granted, free of charge, to any person obtaining a copy
 *		of this software and associated documentation files (the "Software"), to deal
 *		in the Software without restriction, including without limitation the rights
 *		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *		copies of the Software, and to permit persons to whom the Software is
 *		furnished to do so, subject to the following conditions:
 *
 *		The above copyright notice and this permission notice shall be included in
 *		all copies or substantial portions of the Software.
 *
 *		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *		THE SOFTWARE.
 *	Usage:
 *		server side(WP):
 *			js_wp_editor( $settings );
 *		client side(jQuery):
 *			$('textarea').wp_editor( options );
 */

!function(e,t){e.fn.wp_editor=function(t){if(e(this).is("textarea")||console.warn("Element must be a textarea"),("undefined"==typeof tinyMCEPreInit||"undefined"==typeof QTags||"undefined"==typeof ap_vars)&&console.warn("js_wp_editor( $settings ); must be loaded"),!e(this).is("textarea")||"undefined"==typeof tinyMCEPreInit||"undefined"==typeof QTags||"undefined"==typeof ap_vars)return this;var i={mode:"html",mceInit:{theme:"modern",skin:"lightgray",language:"en",formats:{alignleft:[{selector:"p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",styles:{textAlign:"left"},deep:!1,remove:"none"},{selector:"img,table,dl.wp-caption",classes:["alignleft"],deep:!1,remove:"none"}],aligncenter:[{selector:"p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",styles:{textAlign:"center"},deep:!1,remove:"none"},{selector:"img,table,dl.wp-caption",classes:["aligncenter"],deep:!1,remove:"none"}],alignright:[{selector:"p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",styles:{textAlign:"right"},deep:!1,remove:"none"},{selector:"img,table,dl.wp-caption",classes:["alignright"],deep:!1,remove:"none"}],strikethrough:{inline:"del",deep:!0,split:!0}},relative_urls:!1,remove_script_host:!1,convert_urls:!1,browser_spellcheck:!0,fix_list_elements:!0,entities:"38,amp,60,lt,62,gt",entity_encoding:"raw",keep_styles:!1,paste_webkit_styles:"font-weight font-style color",preview_styles:"font-family font-size font-weight font-style text-decoration text-transform",wpeditimage_disable_captions:!1,wpeditimage_html5_captions:!1,plugins:"charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,image",content_css:ap_vars.includes_url+"css/dashicons.css?ver=3.9,"+ap_vars.includes_url+"js/mediaelement/mediaelementplayer.min.css?ver=3.9,"+ap_vars.includes_url+"js/mediaelement/wp-mediaelement.css?ver=3.9,"+ap_vars.includes_url+"js/tinymce/skins/wordpress/wp-content.css?ver=3.9",selector:"#apid",resize:"vertical",menubar:!1,wpautop:!0,indent:!1,toolbar1:"bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv",toolbar2:"formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",toolbar3:"",toolbar4:"",tabfocus_elements:":prev,:next",body_class:"apid"}},s=new RegExp("apid","g");tinyMCEPreInit.mceInit.apid&&(i.mceInit=tinyMCEPreInit.mceInit.apid);var t=e.extend(!0,i,t);return this.each(function(){if(e(this).is("textarea")){var i=e(this).attr("id");e.each(t.mceInit,function(a,n){"string"==e.type(n)&&(t.mceInit[a]=n.replace(s,i))}),t.mode="tmce"==t.mode?"tmce":"html",tinyMCEPreInit.mceInit[i]=t.mceInit,e(this).addClass("wp-editor-area").show();var a=this;if(e(this).closest(".wp-editor-wrap").length){var n=e(this).closest(".wp-editor-wrap").parent();e(this).closest(".wp-editor-wrap").before(e(this).clone()),e(this).closest(".wp-editor-wrap").remove(),a=n.find('textarea[id="'+i+'"]')}var r=e('<div id="wp-'+i+'-wrap" class="wp-core-ui wp-editor-wrap '+t.mode+'-active" />'),l=e('<div id="wp-'+i+'-editor-tools" class="wp-editor-tools hide-if-no-js" />'),o=e('<div class="wp-editor-tabs" />'),d=e('<a id="'+i+'-html" class="wp-switch-editor switch-html" data-wp-editor-id="'+i+'">Text</a>'),p=e('<a id="'+i+'-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="'+i+'">Visual</a>'),c=e('<div id="wp-'+i+'-media-buttons" class="wp-media-buttons" />'),m=e('<a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="'+i+'" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>'),h=e('<div id="wp-'+i+'-editor-container" class="wp-editor-container" />'),w=!1;m.appendTo(c),c.appendTo(l),d.appendTo(o),p.appendTo(o),o.appendTo(l),l.appendTo(r),h.appendTo(r),h.append(e(a).clone().addClass("wp-editor-area")),0!=w&&e.each(w,function(){e('link[href="'+this+'"]').length||e(a).before('<link rel="stylesheet" type="text/css" href="'+this+'">')}),e(a).before('<link rel="stylesheet" id="editor-buttons-css" href="'+ap_vars.includes_url+'css/editor.css" type="text/css" media="all">'),e(a).before(r),e(a).remove(),new QTags(i),QTags._buttonsInit(),switchEditors.go(i,t.mode),e(r).on("click",".insert-media",function(t){var i=e(t.currentTarget),s=i.data("editor"),a={frame:"post",state:"insert",title:wp.media.view.l10n.addMedia,multiple:!0};t.preventDefault(),i.blur(),i.hasClass("gallery")&&(a.state="gallery",a.title=wp.media.view.l10n.createGalleryTitle),wp.media.editor.open(s,a)})}else console.warn("Element must be a textarea")})}}(jQuery,window);
jQuery(document).ready(function ($) {

    /** Drop down menu making link active (check nav walker) **/

    $('.navbar-nav.dropdown').hover(function () {
        $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
    }, function () {
        $(this).find('.dropdown-menu').first().stop(true, true).delay(100).slideUp();
    });
    $('.navbar-nav .dropdown > a').click(function () {
        location.href = this.href;
    });
    // Bootstrap menu magic
    $(window).resize(function () {
        if ($(window).width() < 768) {
            $(".dropdown-toggle").attr('data-toggle', 'dropdown');
        } else {
            $(".dropdown-toggle").removeAttr('data-toggle dropdown');
        }
    });
    /*
     * -/ show search bar -/
     */
    $('#wooShowBar, #wooShowBarMobile').on('click', function () {
       $('.woo-search-container').show();
    });
    $('#WooCloseBar,#WooCloseBarMobile ').on('click', function () {
        $('.woo-search-container').hide();
    });

    /*
     * -/ init mmenu -/
   */




    // Call Mobile Menu & Get Menu API
  /* $('#menu-toggle').on('click', function(){
        $('.js-hamburger').toggleClass('is-active');
       $('body').addClass('mmenu-opened');
    });
    $('.mmenu-opened').on('click', function(){
        $('.js-hamburger').toggleClass('is-active');
    });*/


    /*
     * Detect mobile
     */
    var isMobile = false; //initiate as false
// device detection
    if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
        || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;



    if( isMobile === true ) {

        $("#menu").mmenu({
            extensions 	: [ "position-bottom", "fullscreen", "theme-black", "listview-50", "fx-panels-slide-up", "fx-listitems-drop", "border-offset" ],
            navbar 		: {
                title 		: ""
            },
            "autoHeight": true
        });
        $(".mh-head.mm-sticky").mhead({
            scroll: {
                hide: 200
            }
        });
        $(".mh-head:not(.mm-sticky)").mhead({
            scroll: false
        });
        $('.header-mobile').css({
            'bottom' : '0',
            'top': 'unset'


        });
        $('#page').css({
            'margin-top': '30px'
        });
        $('.mobile-logo').hide();
        $('.woo-nav-mobile').css({
            'width': '100%'
        });
        $('.woo-nav-mobile ul').css({
            'width': '100%'
        });

        $('.block_blog').css({
            'padding' : '0'
        });
        if ($(window).width() < 1200) {

            $('ul').removeClass("dropdown-menu");
            $(".dropdown-toggle").removeAttr('data-toggle dropdown');
        }
        $('.wrapper').css({
            'margin-top': '-100px'
        });


    }else {
        $('#menu').css({
            'height': 'height: calc(100% - 100px);'
        })
        $("#menu").mmenu({
            extensions 	: [ "position-bottom", "fullscreen", "theme-black", "listview-50", "fx-panels-slide-up", "fx-listitems-drop", "border-offset" ],
            navbar 		: {
                title 		: ""
            },
            "autoHeight": true
        });
        $(".mh-head.mm-sticky").mhead({
            scroll: {
                hide: 200
            }
        });
        $(".mh-head:not(.mm-sticky)").mhead({
            scroll: false
        });
        $('.header-mobile').css({
            'top' : '0',


        })
    }






    var api = $("#menu").data( "mmenu" );
    $('#menu').removeAttr('style');
    $('.mobile-menu-btn').click(function (e) {
        mmChange();
        $('.search-mobile').removeClass('active');
    });
    api.bind('close:finish', function () {
        $('.js-hamburger').removeClass('is-active');
        $('.extra-bar').show("slide", { direction: "left" }, 1000);
        $('.extra-bar').animate({"left": '0'},500);
        $('.wrapper').css({
            'margin-top': '-100px'
        });
    });
    api.bind('open:finish', function () {
        $('.js-hamburger').addClass('is-active');
        $('.extra-bar').animate({"left": '200'},200);
        $('.wrapper').css({
            'margin-top': '30px'
        });
    });
    function mmChange() {

            $('.mobile-menu-btn').toggleClass('mobile-menu-close');
            if(isMobile === true) {
                $('#menu').css({
                    'margin-top' : '-40px'

                });
            }

         if($('.mobile-menu-btn').hasClass('mobile-menu-close')) {
             $('.js-hamburger').addClass('is-active');

        }else {
                $('.js-hamburger').removeClass('is-active');
                api.close();
        }

        }




   $('body').on( 'click',
        'a[href^="#/"]',
        function() {

            return false;
        }
    );





    /*
    * -/ init carousel for home page -/
  */


    $('.hero-slider').slick({
        arrows: false,
        infinite: false,
        autoplay: true,
        autoplaySpeed: 3000,
        fade: true,
        pauseOnHover:false,
        cssEase: 'linear'
    });




    /* fix the menu on scroll*/
    var lastScrollTop = 500;
    $(window).scroll(function(event){
        var st = $(this).scrollTop();
        if (st > 50 ){


            $('#mainHeader').css({
                'top' : '-100px',
                'transition' : '0.5s linear'

            });
            if( isMobile === true ) {
                $('.header-mobile').css({
                    'bottom': '-100px',
                    'transition': '0.5s linear'

                });
            }else{
                $('.header-mobile').css({
                    'top': '-100px',
                    'transition': '0.5s linear'

                });
            }


        }
        if(st < lastScrollTop ) {
            if( isMobile === true ) {
                $('.header-mobile').css({
                    'bottom': '0',
                    'transition': '0.5s linear'
                });
            }else{
                $('.header-mobile').css({
                    'top': '0',
                    'transition': '0.5s linear'

                });
            }
        }
        if(st < 50) {

        $('#mainHeader').css({
            'top' : '0',
            'transition' : '0.5s linear'
        });




        }
        lastScrollTop = st;
    });





    /** Drop down menu making link active (check nav walker) **/
    $('.navbar .dropdown').hover(function() {
        $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
    }, function() {
        $(this).find('.dropdown-menu').first().stop(true, true).delay(100).slideUp();
    });
    $('.navbar .dropdown > a').click(function() {
        location.href = this.href;
    });




    // masonry
    //le masonry
    var freeMasonry = $('.masonry');

    freeMasonry.imagesLoaded()
        .done(function(){
            $('.masonry').masonry({
                columnWidth: '.grid-sizer',
                gutter: '.gutter-sizer',
                itemSelector: '.item'
            });
        });



    // popup for masonry
    $("[data-fancybox]").fancybox({
        // Options will go here
        selector : '[data-fancybox="images"]',
        loop     : true
    });

    /* Viewport animation */

   /* $('h1').viewportChecker({
        classToAdd: 'visible animated fadeInUp',
        offset: 100
    });*/
    if ($(window).width() > 991) {
        $('.main-title').viewportChecker({
            classToAdd: 'animated fadeInUp',
            offset: 0,
            removeClassAfterAnimation: false,
            repeat: false,
        });
        $('.js-fadeUpx').addClass("hideme").viewportChecker({
            classToAdd: 'visible animated fadeInUp',
            offset: 100
        });
        $('.title-separator').addClass("hideme").viewportChecker({
            classToAdd: 'visible animated addwidth',
            offset: 100
        });
        $('h1').addClass("hideme").viewportChecker({
            classToAdd: 'animate-title sa-visible',
            offset: 100
        });
    }else {
        $('h1').addClass('animate-title sa-visible');
        $('.title-separator').addClass('visible animated addwidth');
        $('.js-fadeUpx').addClass('visible animated fadeInUp');
        $('.main-title').addClass('animated fadeInUp');
    }
    // loading animation
    $(".animsition").animsition({
        inClass: 'zoom-in-sm',
        outClass: 'zoom-out-sm',
        inDuration: 1000,
        outDuration: 500,
        linkElement: '.animsition-link',
        // e.g. linkElement: 'a:not([target="_blank"]):not([href^="#"])'
        loading: true,
        loadingParentElement: 'body', //animsition wrapper element
        loadingClass: 'animsition-loading',
        loadingInner: '', // e.g '<img src="loading.svg" />'
        timeout: false,
        timeoutCountdown: 5000,
        onLoadEvent: true,
        browser: [ 'animation-duration', '-webkit-animation-duration'],
        // "browser" option allows you to disable the "animsition" in case the css property in the array is not supported by your browser.
        // The default setting is to disable the "animsition" in a browser that does not support "animation-duration".
        overlay : false,
        overlayClass : 'animsition-overlay-slide',
        overlayParentElement : 'body',
        transition: function(url){ window.location.href = url; }
    });





    /* Validate booking form */

    $('#gform_submit_button_2').on('click', function(e) {
        var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
        if ($('#input_2_1').val() !== "" && $('#input_2_2').val() !== "" && $('#input_2_5').val() !== "" && $('#input_2_6').val() !== "" && $('#input_2_7').val() !== ""  ) {
            $('#gform_2')[0].submit();
        } else {
            e.preventDefault();
            $(":input").css({
                'border': '1px solid red'
            });
            $(":input").attr('placeholder','Required Field!');
        }



    });

    $('#gform_2 :input' ).bind('blur focus ', function(){
        if($(this).val() !== ''){
            $(this).css({
                'border': '1px solid green'
            });
        }else {
            $(this).css({
                'border': '1px solid red'
            });
        }
    });
    if($("#gform_confirmation_message_2" + name).length !== 0) {
        $('.book-form').animate({
            'right': '0'
        });
        $('.book-form_content').hide();
        $('.book-form_form').addClass('show-content-form');
    }


    // make images same height


    var maxHeightName = 0;
    $('.woo-prduct-list').each(function(){
        var currheightName = $(this).height();

        if (currheightName > maxHeightName) {
            maxHeightName = currheightName;
        }
    });
    $('.woo-prduct-list').each(function(){
        $(this).css({'height' : maxHeightName});
    });






    $('.loadmore').on('click', function(event) {

        event.preventDefault(); //Prevent the default submit

        var url = $('#bar').data('link')
        var values = $('#bar').val();
        values++;

        var that = $('.loadmore');
        var page = $('.loadmore').data('page');
        var newPage = page+1;
        var ajaxurl = that.data('url');

        var loaderSpinner = '<div class="loader-spinner">' +
            '<span class="square"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '<span class="square clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '<span class="square clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '</div>';

        $.ajax({

            url : ajaxurl,
            type : 'post',
            data : {

                page : page,
                action: 'tbb_load_more'

            },
            error : function( response ){
                console.log(response);
            },
            beforeSend: function(xhr) {
                $('.loadmore').hide();
                $('.loader-wrapper').append(loaderSpinner);
                $('.container-dots').show();
               // console.log('sending...');
            },
            success : function( response ){
                if( response == 0 ) {
                    $('.loadmore').show();
                    $('.loadmore').text('NO MORE PRODUCTS');
                    $('.loadmore').fadeOut(1000);
                    $('.loader-spinner').remove();
                    $('.container-dots').fadeOut(2000);
                    console.log('empty');
                }else {
                    that.data('page', newPage);
                    $('#lazyload').append(response);
                    page++;
                    $('.loadmore').show();
                    $('.loadmore').text('LOAD MORE PRODUCTS');
                   $('.loader-spinner').remove();
                    $('.container-dots').fadeOut(2000);



                }



            }

        });


        var maxHeightName = 0;
        $('.woo-prduct-list').each(function(){
            var currheightName = $(this).height();

            if (currheightName > maxHeightName) {
                maxHeightName = currheightName;
            }
        });
        $('.woo-prduct-list').each(function(){
            $(this).css({'height' : maxHeightName});
        });

    });


    /*
     * -/ ajax filter posts by category -/
   */

    $('#select').change(function(){

        $('.wrapper').css({'opacity': '0.5'});
        var filter = $('#filter');
        var loaderSpinner = '<div class="loader-spinner">' +
            '<span class="square"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '<span class="square clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '<span class="square clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '</div>';
        $('.wrapper').append(loaderSpinner);


        $.ajax({
            url:filter.attr('action'),
            data:filter.serialize(), // form data
            type:filter.attr('method'), // POST
            beforeSend:function(xhr){

                filter.find('button').text('Applying Filters...');          },
            error:function (data) {
                console.log('ERROR');
            },
            success:function(data){
              //  console.log(data);
                $('.loader-spinner').hide();
                $('.wrapper').css({'opacity': '1'});
                filter.find('button').text('Apply filters');
                $('#lazyload').empty().html(data);

                // $('#lazyload').empty();
            }
        });
        return false;
    });

    /*
  * -/ ajax filter products by category -/
*/

    $('#selectCat').change(function(){
        var loaderSpinner = '<div class="loader-spinner">' +
            '<span class="square"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '<span class="square clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square last clear"></span>'+
            '<span class="square"></span>'+
            '<span class="square"></span>'+
            '<span class="square last"></span>'+
            '</div>';

        $('.wrapper').append(loaderSpinner);
        $('.wrapper').css({'opacity': '0.5'});
        var filter = $('#wooFilter');;
        $.ajax({
            action: 'woocustomfilter',
            url:filter.attr('action'),
            data:filter.serialize(), // form data
            type: 'POST',

            beforeSend:function(xhr){
                filter.find('button').text('Applying Filters...');          },

            success:function(data){

                console.log(data);
                $('.loader-spinner').hide();
                $('.wrapper').css({'opacity': '1'});
                filter.find('button').text('Apply filters');
                //$('.products').empty().html(data);

                // $('#lazyload').empty();
            }
        });
        return false;
    });


    var arrowLeft = '<i class="fas fa-arrow-left"></i>',
    arrowRight ='<i class="fas fa-arrow-right"></i>'
    $slick_slider = $(' .mobile-wrapper-slick');
    settings_slider = {
        dots: false,
        prevArrow:arrowLeft,
        nextArrow:arrowRight,
        // more settings
    }





// slick on mobile
    function slick_on_mobile(slider, settings){
        $(window).on('load resize', function() {
            if ($(window).width() > 991) {
                if (slider.hasClass('slick-initialized')) {
                    slider.slick('unslick');
                }
                return
            }
            if (!slider.hasClass('slick-initialized')) {
                return slider.slick(settings);
            }
        });
    };
    slick_on_mobile( $slick_slider, settings_slider);






    var maxHeightName = 0;
    jQuery('.height-fix').each(function(){
        var currheightName = jQuery(this).height();
        if (currheightName > maxHeightName) {
            maxHeightName = currheightName;
        }
    });
    //make product Name the same height
    jQuery('.height-fix').each(function(){
        jQuery(this).css({'height' : maxHeightName});
    })


    // animation for input submit
    $(window).on('load', function() {
        var gfromWidth = $('.gform_button').outerWidth(),
            inputClass = $('input.red-button');

        $('.gform_button').wrap('<div class="btn-input"></div>');
        $('.btn-input').css({'width': gfromWidth});

        inputClass.wrap('<div class="btn-input"></div>');
        $('.btn-input').css({
            'width': inputClass.outerWidth(),
            'height': inputClass.outerHeight()
        });

    });


    /*
     * anime.js
     */


    $('.crosshairs').viewportChecker({
        classToAdd: 'visible',
        callbackFunction: function(elem, action){
          /* var rotate = anime({
                targets: '.rotate',

                scale: {
                    value: 1.2,
                    duration: 1000,
                    delay: 800,
                    easing: 'easeInOutQuart'
                },
                delay: 550 // All properties except 'scale' inherit 250ms delay
            });*/
          $('#svg').append(styleSvg);



        }


    });
    $(window).on('load', function() {
        $('#svg').append(styleSvg);
        setInterval(function(){
            $('#svg').remove('#style');
        }, 3000)
        /* setInterval(function(){
            $('#svg').append(styleSvg);
        }, 4000);*/
    });

var styleSvg = '<style >.sKLUyLVI_0 {\n' +
    '  stroke-dasharray: 400 402;\n' +
    '  stroke-dashoffset: 401;\n' +
    '  animation: sKLUyLVI_draw 200ms linear 0ms forwards;\n' +
    '}\n' +
    '.sKLUyLVI_1 {\n' +
    '  stroke-dasharray: 65 67;\n' +
    '  stroke-dashoffset: 66;\n' +
    '  animation: sKLUyLVI_draw 1000ms linear 14ms forwards;\n' +
    '}\n' +
    '.sKLUyLVI_2 {\n' +
    '  stroke-dasharray: 136 138;\n' +
    '  stroke-dashoffset: 137;\n' +
    '  animation: sKLUyLVI_draw 1000ms linear 28ms forwards;\n' +
    '}\n' +
    '.sKLUyLVI_3 {\n' +
    '  stroke-dasharray: 0 2;\n' +
    '  stroke-dashoffset: 1;\n' +
    '  animation: sKLUyLVI_draw 1000ms linear 42ms forwards;\n' +
    '}\n' +
    '.sKLUyLVI_4 {\n' +
    '  stroke-dasharray: 52 54;\n' +
    '  stroke-dashoffset: 53;\n' +
    '  animation: sKLUyLVI_draw 1500ms linear 57ms forwards;\n' +
    '}\n' +
    '.sKLUyLVI_5 {\n' +
    '  stroke-dasharray: 55 57;\n' +
    '  stroke-dashoffset: -56;\n' +
    '  animation: sKLUyLVI_draw 1500ms linear 71ms forwards;\n' +
    '}\n' +
    '.sKLUyLVI_6 {\n' +
    '  stroke-dasharray: 50 52;\n' +
    '  stroke-dashoffset: -50;\n' +
    '  animation: sKLUyLVI_draw 1500ms linear 85ms forwards;\n' +
    '}\n' +
    '.sKLUyLVI_7 {\n' +
    '  stroke-dasharray: 50 52;\n' +
    '  stroke-dashoffset: 50;\n' +
    '  animation: sKLUyLVI_draw 1500ms linear 100ms forwards;\n' +
    '  float:left;\n' +
    '\n' +
    '}\n' +
    '@keyframes sKLUyLVI_draw {\n' +
    '  100% {\n' +
    '    stroke-dashoffset: 0;\n' +
    '  }\n' +
    '}\n' +
    '@keyframes sKLUyLVI_fade {\n' +
    '  0% {\n' +
    '    stroke-opacity: 1;\n' +
    '  }\n' +
    '  94.44444444444444% {\n' +
    '    stroke-opacity: 1;\n' +
    '  }\n' +
    '  100% {\n' +
    '    stroke-opacity: 0;\n' +
    '  }\n' +
    '}\n</style>';






});
/*
 * Fixing elements on scroll
 */
jQuery(document).ready(function ($) {

    var elementPosition = $('.stick-scroll').offset();
    var crosshairs = $('.crosshairs').offset();
    var bogpost = $('.block_blog').offset();

    $(window).scroll(function(){
        if ( $(window).width() > 991 ) {
            if ($('.stick-scroll').length) {

                if ($(window).scrollTop() > (elementPosition.top - 50 )) {

                    $('.stick-scroll').css('position', 'fixed').css('top', '50px');
                } else {
                    $('.stick-scroll').css('position', 'static');
                }
            }


            if( $('.crosshairs').length ) {
                 if ($(window).scrollTop() > $('.block_hero-carousel_images').outerHeight()) {
                 $('.crosshairs').css({
                 'position': 'fixed',
                 'top': '150px',
                     'margin-top': '0'
                 });
                 } else {
                     $('.crosshairs').css({
                         'position': 'absolute',
                         'top': '0',
                         'margin-top':$('.block_hero-carousel_images').outerHeight() + 150
                     });

                 }
             }

            if ($('.block_blog').length) {
                if ($(window).scrollTop() > (bogpost.top -100) ) {
                    $('.stick-scroll').css({
                        'position': 'absolute',
                        'top': bogpost.top - $('.stick-scroll').outerHeight()

                    })
                }

            }
        }

    });
    $('.crosshairs').css({
        'position': 'absolute',
        'top': '0',
        'margin-top':$('.block_hero-carousel_images').outerHeight() + 150
    });



    $(document).on('scroll', function() {

      if( $('.block_three-col').length ) {
          if ($(this).scrollTop() >= $('.block_three-col').offset().top) {
              var numb = Math.round(($('.block_two-col_image ').height() + $(window).scrollTop()) * 0.18);
              $('.home-image').css({'top': numb - 400 + 'px'});


          } else {
              $('.home-image').css({'top': numb - 400 + 'px'});
          }
      }
    })

    $(window).on('load resize', function() {
        if ($(window).width() < 1200) {
            $("[role=menu]").removeClass("dropdown-menu");
        }
    });




});

jQuery(document).ready(function (){

    jQuery('.up').wp_editor();

});

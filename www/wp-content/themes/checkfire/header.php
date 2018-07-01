<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9"> <![endif]-->
<!--[if IE 9]>    <html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 8]> <html class="no-js"> <![endif]-->

<head id="header">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <meta name="theme-color" content="#000000">
    <meta name="msapplication-navbutton-color" content="#000000">
    <meta name="apple-mobile-web-app-status-bar-style" content="#000000">

    <title>
		<?php if(is_front_page() ) : ?>
			<?php echo get_bloginfo()?>
		<?php else : ?>
			<?php echo get_bloginfo()?> | <?php wp_title(''); ?>
		<?php endif; ?>
    </title>
    <!--<link rel="shortcut icon" href="<?php /*echo get_stylesheet_directory_uri(); */?>/assets/images/favicon.ico" />-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri();?>/assets/images/favicon.ico" />

    <link rel="stylesheet" href="https://use.typekit.net/bvg4rxy.css">
    <style>
        @import url("https://use.typekit.net/bvg4rxy.css");
    </style>
    <script>
        (function(d) {
            var config = {
                    kitId: 'bvg4rxy',
                    scriptTimeout: 3000,
                    async: true
                },
                h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='https://use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
        })(document);
    </script>


	<?php wp_head();?>

</head>

<body <?php body_class('');?>>

<header id="mainHeader" class="p-fixed">
    <!-- Static navbar -->
	<?php if( is_front_page() || is_page('Commander') || is_page('Commander Edge') || is_page('Contempo') ) {
		$navbarhome = 'navbar-nav-home';
	}else {
		$navbarhome ='';
	}
	?>
    <nav class="navbar navbar-default <?php echo $navbarhome ?>">
        <div class="container-fluid">
            <div class="navbar-header">


                <a class="navbar-brand" href="<?php echo site_url() ?>">
                    <img src="<?php echo get_template_directory_uri() ; ?>/assets/images/logo.svg" alt="" width="200">

                </a>
            </div>
            <div id="navbar" class="navbar-collapse collapse wp-navbar">


				<?php
				wp_nav_menu( array(
						'menu'              => 'primary',
						'theme_location'    => 'primary',
						'depth'             => 4,
						'container'         => 'div',
						'container_class'   => 'collapse navbar-collapse',
						'container_id'      => 'bs-example-navbar-collapse-1',
						'menu_class'        => 'nav navbar-nav yamm',
						'fallback_cb'       => 'Yamm_Nav_Walker_menu_fallback',
						'walker'            => new Yamm_Nav_Walker())
				);

				?>
                <div class="woo-nav pull-right">
                    <ul class="nav navbar-nav" id="menu-woo-menu">
                        <li  class="woo-basket">
							<?php if( is_front_page() || is_page('Commander') || is_page('Commander Edge') || is_page('Contempo') ): ?>
                                <a href="<?php echo site_url() ?>/cart/"><img src="<?php echo get_template_directory_uri() ?>/assets/images/basket-white.svg" width="35"></a>
							<?php else: ?>
                                <a href="<?php echo site_url() ?>/cart/"><img src="<?php echo get_template_directory_uri() ?>/assets/images/basket.svg" width="35"></a>
							<?php endif; ?>
                            <span class="woo-basket_count">
                                    <a class="woo-basket_count_a" href="<?php echo wc_get_cart_url(); ?>"
                                       title="<?php _e( 'View your shopping cart' ); ?>">
                                        <?php echo WC()->cart->get_cart_contents_count()  ; ?>
                                    </a>
                                </span>
                        </li>
                        <li>
							<?php if( is_front_page() || is_page('Commander') || is_page('Commander Edge') || is_page('Contempo') ): ?>
                                <a href="<?php echo site_url() ?>/my-account/" ><img src="<?php echo get_template_directory_uri() ?>/assets/images/account-white.svg" width="35"></a>
							<?php else: ?>
                                <a href="<?php echo site_url() ?>/my-account/" ><img src="<?php echo get_template_directory_uri() ?>/assets/images/account.svg" width="35"></a>
							<?php endif; ?>
                        </li>
                        <li>
							<?php if( is_front_page() || is_page('Commander') || is_page('Commander Edge') || is_page('Contempo') ): ?>
                                <a href="#" ><img id="wooShowBar" src="<?php echo get_template_directory_uri() ?>/assets/images/search-white.svg" width="35"></a>
							<?php else: ?>
                                <a href="#" class="woo-search" ><img id="wooShowBar" src="<?php echo get_template_directory_uri() ?>/assets/images/search.svg" width="35"></a>
							<?php endif; ?>

                        </li>
                    </ul>
                </div>

            </div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->
    </nav>




</header>

<div class="header-mobile">

    <div class="mobile-logo">
        <a class="navbar-brand" href="<?php echo site_url() ?>">
            <img class="img-responsive" src="<?php echo get_template_directory_uri() ; ?>/assets/images/logo.svg" alt="" >

        </a>
    </div>



    <div class="woo-nav-mobile">
        <ul class="nav navbar-nav" id="menu-woo-menu">
            <li  class="woo-basket">
                <a  href="<?php echo site_url() ?>/cart/"><img src="<?php echo get_template_directory_uri() ?>/assets/images/basket.svg" width="35"></a>
                <span class="woo-basket_count">
                                    <a class="woo-basket_count_a" href="<?php echo wc_get_cart_url(); ?>"
                                       title="<?php _e( 'View your shopping cart' ); ?>">
                                        <?php echo WC()->cart->get_cart_contents_count()  ; ?>
                                    </a>
                                </span>
            </li>
            <li>
                <a href="<?php echo site_url() ?>/my-account/" ><img src="<?php echo get_template_directory_uri() ?>/assets/images/account.svg" width="35"></a>
            </li>
            <li class="mobile-search">
                <a href="#" ><img id="wooShowBarMobile" src="<?php echo get_template_directory_uri() ?>/assets/images/search.svg" width="35"></a>

            </li>
            <li>

                    <a href="#menu" class="mobile-menu-btn">
                        <div class="hamburger hamburger--spring js-hamburger">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                                <span class="extra-bar"></span>
                            </div>
                        </div>

                    </a>

            </li>
        </ul>
    </div>










</div><!-- /- end mobile header -/ -->

<div class="clearfix"></div>


<nav id="menu">


	<?php wp_nav_menu( array(
		'theme_location' => 'primary',
		'container'         => false,
			'menu_class'        => false,
			'fallback_cb'       => false,
			'walker'            => new wp_bootstrap_navwalker()

	) ); ?>

</nav><!-- end mobile menu -->



<div class="clearfix"></div>

<section class="wrapper animsitionx" id="page"> <!-- website wrapper -->

    <div class="woo-search-container">
        <form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <ul class="search-top">
                <li class="search-label"> Search for product or code</li>
                <li id="WooCloseBarMobile"><i class="cross-icon pull-left"></i><span class="pull-left">Close</span></li>

            </ul>

            <input type="search" id="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>" class="search-field pull-left" placeholder="<?php echo esc_attr__( 'Search products&hellip;',
				'woocommerce' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
            <div class="woo-search-submit-wrapper">
                <input class="woo-search-submit" type="submit" value=""style="" />
            </div>
            <input type="hidden" name="post_type" value="product" />
        </form>
    </div>


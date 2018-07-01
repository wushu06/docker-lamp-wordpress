<?php
/**
 * Tbb
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * Documentation standards:
 * https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/
 *
 * @package        WordPress
 * @subpackage    tbb
 * @since        1.0.2
 *
 * @author        The Bigger Boat
 */
/**
 * Increase memory and processing time.
 *
 * @since  1.0.1
 */
ini_set('upload_max_size', '64M');
ini_set('post_max_size', '64M');
ini_set('max_execution_time', '300');
add_action('after_setup_theme', 'setup_woocommerce_support');

function setup_woocommerce_support()
{
    add_theme_support('woocommerce');
}

/**
 * Define theme path for quicker referencing.
 *
 * @since  1.0.1
 */
define('THEME_DIR', get_template_directory_uri());
/**
 * Load our ACF configuration information.
 *
 * This is required to set up ACF Local JSON.
 *
 * @since 1.0.1
 */
require_once get_template_directory() . '/inc/acf/config.php';


/**
 * Load our helpers file.
 *
 * This contains a number of useful functions used throughout the theme.
 *
 * @since 1.0.2
 */
require_once get_template_directory() . '/inc/helpers/tbb.php';
require_once get_template_directory() . '/inc/helpers/woocommerce.php';
require_once get_template_directory() . '/inc/helpers/home-loop.php';
require_once get_template_directory() . '/inc/helpers/theme.php';
require_once get_template_directory() . '/inc/acf/metabox.php';

/**
 * Core theme class.
 *
 * Sets up WordPress hooks for actions and filters that are used in the theme.
 *
 * @since 1.0.1
 */
class tbb

{

    /**
     * Set up our action and filter hooks.
     */
    public function __construct()
    {

        /**
         * Remove Generator Meta Tag.
         *
         * @since 1.0.1
         */
        remove_action('wp_head', 'wp_generator');
        /**
         * Set up stylesheets and scripts.
         *
         * @since 1.0.1
         */
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        /**
         * Set up image sizes and menu assignment.
         *
         * @since 1.0.1
         */
        add_action('init', array($this, 'tbb_init'));
        /**
         * Additional active menu classes.
         *
         * @since 1.0.1
         */
        //  add_filter('nav_menu_css_class', array($this, 'add_active_class'), 10, 2);
        /*
         * Google Api
         */
        //add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');
        /*
     * Excerpt
     */
    }
    /* public function my_acf_google_map_api($api)
     {
         $api['key'] = 'AIzaSyCwXwrp-FcHELStKoqx8ZyzQkEW5zVSPEc';
         return $api;
     }*/
    /**
     * Enqueue scripts and styles for the front end.
     *
     * @since 1.0.1
     * @access public
     */
    public function enqueue_styles()
    {
        wp_enqueue_style('fontawesome-style', 'https://use.fontawesome.com/releases/v5.0.6/css/all.css', array(), '1.0.1');
        //  wp_enqueue_style('font-style', 'https://use.typekit.net/kxz6ket.css', array(), '1.0.1');

        wp_enqueue_style('mmenu-style-all', THEME_DIR . '/assets/stylesheets/jquery.mmenu.all.css', array(), '1.0.1');


        /*       wp_enqueue_style('jquery.fancybox-style', THEME_DIR . '/assets/stylesheets/jquery.fancybox.css', array(), '1.0.1');
               wp_enqueue_style('animate-style', THEME_DIR . '/assets/stylesheets/animate.css', array(), '1.0.1');
               wp_enqueue_style('animsition.min-style', THEME_DIR . '/assets/stylesheets/animsition.min.css', array(), '1.0.1');*/


        wp_enqueue_style('app-style', THEME_DIR . '/assets/dist/css/app.min.css', array(), '1.0.1');


        // Add latest jQuery.
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', '//code.jquery.com/jquery-3.1.1.min.js', array(), '3.1.1', true);
        /*  wp_enqueue_script('bootstrap-script', THEME_DIR . '/assets/js/bootstrap.min.js', array('jquery'), '1.0.1', false);
          wp_enqueue_script('sick-script', THEME_DIR . '/assets/js/slick.js', array('jquery'), '1.0.1', false);
          wp_enqueue_script('mmenu-script', THEME_DIR . '/assets/js/jquery.mmenu.js', array('jquery'), '1.0.1', false);
          wp_enqueue_script('masonry-script', THEME_DIR . '/assets/js/masonry.js', array('jquery'), '1.0.1', false);
          wp_enqueue_script('magnific-popup.min-script', THEME_DIR . '/assets/js/jquery.fancybox.min.js', array('jquery'), '1.0.1', false);
          wp_enqueue_script('viewport-script', THEME_DIR . '/assets/js/jquery.viewportchecker.js', array('jquery'), '1.0.1', false);
          wp_enqueue_script('animsition.min-script', THEME_DIR . '/assets/js/animsition.min.js', array('jquery'), '1.0.1', false);*/
        wp_enqueue_script('app-script', THEME_DIR . '/assets/dist/js/app.js', array('jquery'), '1.0.1', false);


        // send template url to js file
        $translation_array = array('templateUrl' => get_stylesheet_directory_uri());
        //after wp_enqueue_script
        wp_localize_script('app-script', 'path', $translation_array);


    }
    /**
     * Set up the theme information.
     *
     * This assigns image sizes, registers nav menus and enables HTML5 components.
     *
     * @since 1.0.1
     * @access public
     */
    // Register Custom Navigation Walker
    public function tbb_init()
    {
        // This theme uses wp_nav_menu().
        register_nav_menus(array(
            // Main navigation
            'primary' => __('Primary Menu', 'Tbb'),
            // WOO navigation
            'Secondary' => __('Woo Menu', 'Tbb'),
            // Mobile navigation
            'mobile_nav' => __('Main Menu - Mobile', 'tbb'),

        ));
        // Register our image sizes.
        add_theme_support('post-thumbnails');
        // Additional image sizes.
        add_image_size('technical', 384, 344, array('center', 'center'));
        add_image_size('key-feature', 440, 440, array('center', 'center'));
        // Add RSS feed links to <head> for posts and comments.
        add_theme_support('automatic-feed-links');
        add_theme_support('customize-selective-refresh-widgets');
    }

    /**
     * Add additional classes to active menu items.
     *
     * @since 1.0.1
     * @access public
     */
    /* public function add_active_class($classes, $item)
     {
         if ($item->menu_item_parent == 0 &&
             in_array('current-menu-item', $classes) ||
             in_array('current-menu-ancestor', $classes) ||
             in_array('current-menu-parent', $classes) ||
             in_array('current_page_parent', $classes) ||
             in_array('current_page_ancestor', $classes)
         ) {
             $classes[] = 'active';
         }
         return $classes;
     }*/
}

new tbb;
require_once 'yamm-nav-walker.php';
require_once 'wp-bootstrap-navwalker.php';


// ajax function for the filter
function my_filters()
{

    $args = array(
        'orderby' => 'date',
        'order' => $_POST['date']
    );

    if (isset($_POST['categoryfilter']))
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $_POST['categoryfilter'],

            )
        );

    $query = new WP_Query($args);
    $i = 0;

    if ($query->have_posts()) :
        while ($query->have_posts()): $query->the_post();

            if ($i % 3 == 0) {
                echo '</div><div class=" row" >';
            }
            ?>
            <div class="col-md-4 col-xs-12  block_blog_content">
                <div class="block_blog_wrapper_image">
                    <a href="<?php the_permalink() ?>" class="animsition-link " data-animsition-out-class="zoom-out-sm">

                        <img src="<?php echo get_the_post_thumbnail_url() ?>" class="img-responsive" alt="">
                        <div class="block_blog_wrapper_image_overlay">
                            <span>Find out more</span>
                        </div>
                    </a>
                </div>
                <h2><?php the_title() ?></h2>
                <div class="row block_blog_meta">
                    <div class="col-md-4">
                        <?php
                        // Retrieve The Post's Author ID
                        $user_id = get_the_author_meta('ID');
                        // Set the image size. Accepts all registered images sizes and array(int, int)
                        $size = 'thumbnail';

                        // Get the image URL using the author ID and image size params
                        $imgURL = get_cupp_meta($user_id, $size);

                        // Print the image on the page
                        echo '<img src="' . $imgURL . '" alt="" width="95"> ';
                        ?>
                    </div>
                    <div class="col-md-8">
                        <p>POSTED <strong><?php the_date('Y.m.d') ?></strong><br/>
                            BY <strong><?php the_author() ?></strong></p>
                    </div>

                </div>


            </div>


            <?php
            $i++; endwhile;
        wp_reset_postdata();
    else :
        echo 'No posts found';
    endif;

    die();
}


add_action('wp_ajax_customfilter', 'my_filters');
add_action('wp_ajax_nopriv_customfilter', 'my_filters');


// ajax function for loading posts
function load_posts_by_ajax_callback()
{

    check_ajax_referer('load_more_posts', 'security');
    $paged = $_POST['page'];
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => '3',
        'paged' => $paged,
    );
    $my_posts = new WP_Query($args);
    if ($my_posts->have_posts()) :
        ?>
        <?php while ($my_posts->have_posts()) : $my_posts->the_post();

        ?>

        <div id="response" class="block_posts_single">
            <div class="block_posts_content">
                <a href="<?php the_permalink() ?>">
                    <div class="block_posts_content_image">

                        <?php echo get_the_post_thumbnail(); ?>
                        <div class="block_posts_content_image_overlay">

                            <span>Read the article</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="block_posts_title">
                <h6><?php the_date() ?></h6>
                <h2>
                    <?php the_title(); ?>
                </h2>
            </div>


        </div>
    <?php endwhile ?>
        <?php
    endif;

    wp_die();

}

add_action('wp_ajax_load_posts_by_ajax', 'load_posts_by_ajax_callback');
add_action('wp_ajax_nopriv_load_posts_by_ajax', 'load_posts_by_ajax_callback');

/**
 * Register our sidebars and widgetized areas.
 *
 */
function arphabet_widgets_init()
{

    register_sidebar(array(
        'name' => 'Home right sidebar',
        'id' => 'home_right_1',
        'before_widget' => '<div>',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="rounded">',
        'after_title' => '</h2>',
    ));

}

add_action('widgets_init', 'arphabet_widgets_init');


// svg function


function add_svg_to_upload_mimes($upload_mimes)
{
    $upload_mimes['svg'] = 'image/svg+xml';
    $upload_mimes['svgz'] = 'image/svg+xml';
    return $upload_mimes;
}

add_filter('upload_mimes', 'add_svg_to_upload_mimes', 10, 1);


function my_login_logo()
{ ?>
    <style type="text/css">
        .login {
            background-color: white;
        }

        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/logo.jpg);
            height: 65px;
            width: 320px;
            background-size: 320px 65px;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
    </style>
<?php }

add_action('login_enqueue_scripts', 'my_login_logo');


function add_custom_id_meta_box()
{
    add_meta_box(
        'custom_id_meta_box', // $id
        'Product ID', // $title
        'show_custom_id_meta_box', // $callback
        'product', // $screen
        'normal', // $context
        'high' // $priority
    );
}

add_action('add_meta_boxes', 'add_custom_id_meta_box');

function show_custom_id_meta_box()
{
    global $post;
    $meta = get_post_meta($post->ID, 'Custom_ID', true); ?>

    <input type="hidden" name="your_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)); ?>">
    <span><strong>DO NOT ALTER THESE VALUES!</strong></span>
    <p>
        <label for="custom_id[text]">Product wp ID</label>
        <br>
        <input type="text" name="custom_id[text]" id="custom_id[text]" class="regular-text"
               value="<?php echo $product_id = get_the_ID() ?>">
    </p>

    <?php
    global $wpdb;
    $count = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d AND post_type = 'product'", $product_id));
    $stdInstance = json_decode(json_encode($count), true);
    foreach ($stdInstance as $c) {
        ?>

        <p>
            <label for="custom_id[text]">Product custom ID</label>
            <br>
            <input type="text" name="custom_id[text]" id="custom_id[text]" class="regular-text"
                   value="<?php echo $c['custom_id']; ?>">
        </p>
    <?php }
    ?>


<?php }

/*function save_custom_id_meta( $post_id ) {
	// verify nonce
	if ( !wp_verify_nonce( $_POST['your_meta_box_nonce'], basename(__FILE__) ) ) {
		return $post_id;
	}
	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// check permissions
	if ( 'page' === $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	}

	$old = get_post_meta( $post_id, 'custom_id', true );
	$new = $_POST['custom_id'];
	if ( $new && $new !== $old ) {
		update_post_meta( $post_id, 'custom_id', $new );
	} elseif ( '' === $new && $old ) {
		delete_post_meta( $post_id, 'custom_id', $old );
	}
}*/
//Page Slug Body Class
function add_slug_body_class($classes)
{
    global $post;
    if (isset($post)) {
        $classes[] = $post->post_type . '-' . $post->post_name;
    }
    return $classes;
}

add_filter('body_class', 'add_slug_body_class');


/*
 *  send email on registration
 */

// Redefine user notification function
if (!function_exists('wp_new_user_notification')) {
    function wp_new_user_notification($user_id, $plaintext_pass = '')
    {
        $user = new WP_User($user_id);

        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);

        $message = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "rnrn";
        $message .= sprintf(__('Username: %s'), $user_login) . "rnrn";
        $message .= sprintf(__('E-mail: %s'), $user_email) . "rn";

        @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

        if (empty($plaintext_pass))
            return;

        $message = __('Hi there,') . "rnrn";
        $message .= sprintf(__("Welcome to %s! Here's how to log in:"), get_option('blogname')) . "rnrn";
        $message .= wp_login_url() . "rn";
        $message .= sprintf(__('Username: %s'), $user_login) . "rn";
        $message .= sprintf(__('Password: %s'), $plaintext_pass) . "rnrn";
        $message .= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) . "rnrn";
        $message .= __('Adios!');

        wp_mail($user_email, sprintf(__('[%s] Your username and password'), get_option('blogname')), $message);

    }
}
/* fix CROSS */
add_filter('allowed_http_origin', '__return_true');


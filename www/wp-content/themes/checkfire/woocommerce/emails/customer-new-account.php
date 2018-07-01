<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

    <p><?php printf( __( 'Dear %1$s,' ),  '<strong>' . esc_html( $user_login ) . '</strong>' ); ?></p>
	<p><?php printf( __( 'We wish you a very warm welcome to %1$s and very much look forward to working with you.' ), esc_html( $blogname ) ); ?></p>
    <p>Your online account is currently being set up, you will receive an email shortly with further instruction on how to access your account. </p>
    <p>Thanks</p>
    <p>Checkfire Team</p>

<?php if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $password_generated ) : ?>

	<p><?php printf( __( 'Your password has been automatically generated: %s', 'woocommerce' ), '<strong>' . esc_html( $user_pass ) . '</strong>' ); ?></p>

<?php endif; ?>


<?php  do_action( 'woocommerce_email_footer', $email );

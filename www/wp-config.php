<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'checkfire');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'db');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '`C[CEw|)DvJ>~VAZzDHzdVNn=LMi?/yXuT!Hu4V^)$Sv;WC{gM.=bA%1S]|[yrjQ');
define('SECURE_AUTH_KEY',  '0Vqlk<L5bs`L-p77e3]E<J;O;k;]^nT98`(dG2!ROMzvsPZEmr<6NX5hY6;n3ve$');
define('LOGGED_IN_KEY',    '|$dDlBZs9Z>{?H~ORYYkNmVeR8bYlDZ$aexZ~]yN;WKq/`fwqQ#blIqNjI%&hCi]');
define('NONCE_KEY',        '6N#Xdz],^IYcxCiL^14lH<Uopp(6?;6&ll&Ae,^i-ZU3=#!$Vh|4x.B^NOh/X5Z>');
define('AUTH_SALT',        '^K1L^xZa]f}{GkR+mEAZE]n<HeV0&/(-E9n6IV*yp.F8HK2+(pDC.MVkl~}25V5]');
define('SECURE_AUTH_SALT', 'jHF^mD}Z-79$,W*Xne6[u[61R1L79[upR6&g(oqNEXl7L)YM;NY3_%+z-f0<rBdI');
define('LOGGED_IN_SALT',   'Df4}7_thtv~`Z:hTPAc=#!-sf_s}Lfo=:DW`R+!Umw1C?&,kY?ql|8t30$@!W/t>');
define('NONCE_SALT',       ',~/Gu-7qqg<Gs^zMB=ZH.j&%wL6BXn^;xkEp@sWj-*e!DoV4#u<7,/%4?_J:=#&u');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

define('WP_CACHE', true);

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
define('DB_NAME', 'litiperf_phukienvtz');

/** MySQL database username */
define('DB_USER', 'litiperf_phukienvtz');

/** MySQL database password */
define('DB_PASSWORD', 'YO:2jJA1z0d6!b');

/** MySQL hostname */
define('DB_HOST', '198.252.106.70');

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
define('AUTH_KEY',         'w0%b$k?j[lJAMhR%%yJ&Qq4MH !3B>)h8oAiMMd%Kto.:w#gOJ%T+X`Q?lSU0-/0');
define('SECURE_AUTH_KEY',  '_X5Q/0H~PGmC)V3*x}ciLVhc(`lFgG#V,O;^/q;BSWYBAoTW7wbmbg=F#y}qWDS(');
define('LOGGED_IN_KEY',    'i2ktljcP1sn3~/[ln|nOt&w.CqTG-k@=+x7P@o?H+etl3b-LuO3/U669h-[&0_sB');
define('NONCE_KEY',        'KH |TMV5jSXeVl`@xse~`qtYTCvDxqDiS9GKA)z45atVuc8&U=IT4LcY<Ctq_wg2');
define('AUTH_SALT',        'RD`R+mUS0?{lql`IQ?:o;%*`MQl1xCsFfm9RCdhY$3vQ8/ocHlh/%7DXk:~K?j@B');
define('SECURE_AUTH_SALT', 'WL&6bL=$8>)BFY [,xxc1JcQn1^COfb~`BTS4+O7&w#&D~SaZDJd:@4WFjak|&/E');
define('LOGGED_IN_SALT',   'SZRtRh+`xks(6dg6dHC!I=+n6T(p0:-^*;);m#RB(J3NMy9QFr*#E&gwa9HZ_IJ0');
define('NONCE_SALT',       '$wGrAS?iWwI|,4QJXA-:>dd1KC6%$(>ig4pQH`E0{i11q#gqlW3MZ8E`/.+P,gat');

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

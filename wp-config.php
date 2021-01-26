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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'addweb' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'dh;%k=g88|KDu=(uooF@k&Pva.Mi.;T nh*?V!f<MYSvs+;By[03 Qm=Qj]A$@{+' );
define( 'SECURE_AUTH_KEY',  '}`4w ZejT3v)`B,k2gwC6,pWCudn0%C- AE0i>u{`H 8^M->#ljLpd@UNVJSV}&h' );
define( 'LOGGED_IN_KEY',    'fR79sT ZaRIKke~uE@W}x$Os=tMRf.xCWgh7/A]T$VPbfB]u{:!Cj?p{V]`|lxNY' );
define( 'NONCE_KEY',        '>JBk;;K]:]mq`Jp{MZZ$=[/dFb8FpY1N#aO|Dzc-vVfzuFW64Fo2N}CZ4D%%_Yt.' );
define( 'AUTH_SALT',        '7[T5e)OLivx2A?MmxL,!SO8v-AP^<w[-dKWCzTCyH)jBs:SGpoV7yYnjCv^59Rm_' );
define( 'SECURE_AUTH_SALT', 'Q/@H0,(C|b<8*|@aAmjo_a=^QGKCeLSf{$+V}/z{R)5_EDz[,OwHP{(B-j!9yb_}' );
define( 'LOGGED_IN_SALT',   ')=]tzg^@7ffM[}kw9;@jDA#TT9}N;8UO@(Te:xO^=,KdfAr|b[hD*Qyj=*UXeq;<' );
define( 'NONCE_SALT',       'Z`G6Pl>J-]A]Mp%&&/xp609I+I2}`][fsj<&6e5/Y d/C$W}0qbX442]f0>KxJVP' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

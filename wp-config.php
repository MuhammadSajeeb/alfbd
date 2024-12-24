<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'alfbd' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '-|w PH@Ma485VhN@!YOIE#(m@k^%#cnaq+r`WF@/CExAsnnbX<u|MJA9szol?u3Y' );
define( 'SECURE_AUTH_KEY',  'O8^@2J})Qa#@68Ct~1M)pKnQ!@Rb (_RWVKI(M:5HL_`6{$7rPCv+`iZ+ntCw7}o' );
define( 'LOGGED_IN_KEY',    'xAc0bbC T4>-93JWApJ4~46PQ[S{Qe:`q-XWSKn04V=jWg@W&n-<W1awQc[Lvobw' );
define( 'NONCE_KEY',        'XL%TABqp:MoAY-jMU@bIpc4.}XI5POW5_leJm :|!3).LU.=pgI!XFGQp7,LsT|I' );
define( 'AUTH_SALT',        '$)U3b:22mv6+%z-V2iD/>+AsC3=8:#z>p;:x;6Zs2:{?5Ag^KC 4o&H)MLNP0<F^' );
define( 'SECURE_AUTH_SALT', '^W>{BxlHBy=x`K | W/V^i#bt.^]+/#D@JK-`^RHLMV79.~Z&U!n{$GH~R~W6_Om' );
define( 'LOGGED_IN_SALT',   't}Nm*H#JjCdHl*CrGcASCA-EirHa-/G#XuDRPwu-ruhc&A-K^CGqW=(Rb+=g4n0q' );
define( 'NONCE_SALT',       'J6F?dsjHmfM%ooF&vE%b(/O]b%B{7+|flGQs9F?,S(-SwTpf&W5-0qn=>w|q5YLr' );

/**#@-*/

/**
 * WordPress database table prefix.
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

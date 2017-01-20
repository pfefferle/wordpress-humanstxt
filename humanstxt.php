<?php
/**
 * Plugin Name: simple humans.txt
 * Plugin URI: https://github.com/pfefferle/wordpress-humanstxt
 * Description: humans.txt for WordPress
 * Version: 1.0.0
 * Author: pfefferle
 * Author URI: http://notiz.blog
 */

function humanstxt_write() {
	header( 'Content-type: text/plain' );
?>
/* the humans responsible &amp; colophon */
/* humanstxt.org */

/* TEAM */
<?php
foreach ( get_users() as $user ) {
	$userdata = get_userdata( $user->ID );
	$data = apply_filters( 'humanstxt_team_data' , array( ucfirst( $userdata->roles[0] ) => $user->display_name ), $user );

	foreach ( $data as $key => $value ) {
		echo '  ' . $key . ': ' . $value . PHP_EOL;
	}
?>

<?php } ?>

/* SITE */
	Standards: <?php echo implode( ', ', array_unique( apply_filters( 'humanstxt_standards', array( 'XFN' ) ) ) ); ?>
	Components: <?php echo implode( ', ', array_unique( apply_filters( 'humanstxt_components', array( 'jQuery' ) ) ) ); ?>
	Software: WordPress <?php echo bloginfo( 'version' ); ?>
<?php
	exit;
}
add_action( 'well_known_humans.txt', 'humanstxt_write' );

/**
 * Add 'humanstxt' as a valid query variables.
 *
 * @param array $vars
 * @return array
 */
function humanstxt_query_vars( $vars ) {
	$vars[] = 'humanstxt';
	return $vars;
}
add_filter( 'query_vars', 'humanstxt_query_vars' );

/**
 * Add rewrite rules for .well-known.
 *
 * @param object $wp_rewrite WP_Rewrite object
 */
function humanstxt_rewrite_rules( $wp_rewrite ) {
	$rewrite_rules = array(
		'humans.txt' => 'index.php?humanstxt=true',
	);

	$wp_rewrite->rules = $rewrite_rules + $wp_rewrite->rules;
}
add_action( 'generate_rewrite_rules', 'humanstxt_rewrite_rules' );

/**
 * Parse the WordPress request.  If the request is for the humans.txt document, handle it accordingly.
 *
 * @param object $wp WP instance for the current request
 */
function humanstxt_parse_request( $wp ) {
	global $wp;
	if ( array_key_exists( 'humanstxt', $wp->query_vars ) ) {
		humanstxt_write();
	}
}
add_action( 'parse_request', 'humanstxt_parse_request' );

/**
 * add some default data for the "Team"-section
 *
 * @param array $data
 * @param object $user
 * @return array
 */
function humanstxt_default_team_data( $data, $user ) {
	$userdata = get_userdata( $user->ID );

	if ( ! empty( $userdata->user_url ) ) {
		$data['Site'] = $userdata->user_url;
	}
	if ( ! empty( $userdata->aim ) ) {
		$data['AIM'] = $userdata->aim;
	}
	if ( ! empty( $userdata->yim ) ) {
		$data['Y!'] = $userdata->yim;
	}
	if ( ! empty( $userdata->jabber ) ) {
		$data['Jabber'] = $userdata->jabber;
	}
	if ( ! empty( $userdata->country_name ) ) {
		$data['Location'] = $userdata->country_name;
	}
	if ( ! empty( $userdata->twitter ) ) {
		$data['Twitter'] = $userdata->twitter;
	}

	return $data;
}
add_filter( 'humanstxt_team_data', 'humanstxt_default_team_data', 1, 2 );

/**
 * reset rewrite rules
 */
function humanstxt_flush_rewrite_rules() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}
register_activation_hook( __FILE__, 'humanstxt_flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'humanstxt_flush_rewrite_rules' );

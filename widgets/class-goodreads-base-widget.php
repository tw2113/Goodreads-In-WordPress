<?php
/**
 * Base widget class that we add our extras to for all current and future widgets.
 *
 * @package Goodreads Widget.
 */

namespace tw2113;

/**
 * Class Goodreads_Base_Widget.
 *
 * @since 1.0.0
 */
class Goodreads_Base_Widget extends \WP_Widget {

	/**
	 * Render a form input for use in our form input.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of argus to use with the markup.
	 * @return void
	 */
	public function form_input( $args = [] ) {
		printf(
			'<p><label for="%s">%s</label><input type="%s" class="widefat" name="%s" id="%s" value="%s" /></p>',
			esc_attr( $args['id'] ),
			esc_attr( $args['label'] ),
			esc_attr( $args['type'] ),
			esc_attr( $args['name'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['value'] )
		);
	}

	/**
	 * Display errors if we're missing anything.
	 *
	 * @since 1.0.0
	 *
	 * @param string $user_id User ID to display content for.
	 * @param string $api_key API key needed for requests.
	 *
	 * @return bool
	 */
	public function maybe_display_errors( $user_id = '', $api_key = '' ) : bool {
		$error = false;
		if ( empty( $user_id ) ) {
			$error = true;
			if ( current_user_can( 'manage_options' ) ) {
				echo '<p>' . esc_html__( 'Please provide a user ID', 'mb_goodreads' ) . '</p>';
			}
		}
		if ( empty( $api_key ) ) {
			$error = true;
			if ( current_user_can( 'manage_options' ) ) {
				echo '<p>' . esc_html__( 'Please provide an API key provided by Goodreads', 'mb_goodreads' ) . '</p>';
			}
		}
		return $error;
	}
}

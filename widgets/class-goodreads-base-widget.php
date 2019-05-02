<?php
/**
 * Base widget class that we add our extras to for all current and future widgets.
 *
 * @package Goodreads in WordPress.
 */

namespace tw2113;

/**
 * Class Goodreads_Base_Widget.
 *
 * @since 1.0.0
 */
class Goodreads_Base_Widget extends \WP_Widget {

	/**
	 * Saved settings.
	 *
	 * @var array|mixed|void
	 */
	protected $goodreads_settings = [];

	/**
	 * Set our settings property.
	 *
	 * @since 1.0.0
	 */
	protected function set_settings() {
		$this->goodreads_settings = get_option( 'mb_goodreads_settings', [] );
	}

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
		$error_key = 'user';

		if ( empty( $user_id ) ) {
			$error     = true;
			$error_key = 'user';
		}

		if ( empty( $api_key ) ) {
			$error     = true;
			$error_key = 'apikey';
		}

		if ( ! $error ) {
			return $error;
		}

		$errors = [
			'user'   => esc_html__( 'Please provide a user ID', 'mb_goodreads' ),
			'apikey' => esc_html__( 'Please provide an API key provided by Goodreads', 'mb_goodreads' ),
		];

		if ( current_user_can( 'manage_options' ) ) {
			echo wpautop( $errors[ $error_key ] );
		}

		return $error;
	}
}

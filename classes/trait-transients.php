<?php
/**
 * Transient management.
 *
 * @since 1.0.0
 * @package Goodreads in WordPress
 */

namespace tw2113;

trait transients {

	/**
	 * Retrieve our Goodreads API data, from a transient first, if available.
	 *
	 * @since 1.0.0
	 *
	 * @param array $trans_args Array of transient data.
	 *
	 * @return array|\WP_Error Data from Goodreads
	 */
	public function get_transient( $trans_args = [] ) {
		$content = get_transient( $trans_args['transient_name'] );

		if ( false === $content ) {

			$mycontent = $trans_args['object']->get();

			/**
			 * Filters the duration to store our transients.
			 *
			 * @since 1.0.0
			 *
			 * @param int $value Time in seconds.
			 */
			$duration = apply_filters( 'goodreads_transient_duration', 60 * 10 );

			if ( 200 === wp_remote_retrieve_response_code( $mycontent ) ) {
				$content = wp_remote_retrieve_body( $mycontent );
				set_transient( $trans_args['transient_name'], $content, $duration );
			} else {
				$message = esc_html__( 'Error in getting book data.', 'mb_goodreads' );
				if ( is_array( $mycontent ) && isset( $mycontent['error'] ) ) {
					$message = $mycontent['error'];
				} elseif ( 404 === wp_remote_retrieve_response_code( $mycontent ) ) {
					$message = $mycontent->get_error_message();
				}

				if ( current_user_can( 'manage_options' ) ) {
					return new \WP_Error(
						'admin_only_error',
						sprintf(
							/* Translators: placeholder will hold Goodreads API error message. */
							esc_html__( 'Admin-only error: %s', 'mb_goodreads' ),
							esc_html( $message )
						)
					);
				}
			}
		}

		return $content;
	}

	/**
	 * Clear a transient.
	 *
	 * @since 1.0.0
	 */
	public function clear_transient( $transient_name = '' ) {
		delete_transient( $transient_name );
		$user_id = ! empty( $this->goodreads_settings['user_id'] ) ? trim( strip_tags( $this->goodreads_settings['user_id'] ) ) : '';
		if ( ! empty( $user_id ) ) {
			delete_transient( apply_filters( 'profile_filter', 'goodreads_user_profile_' . $user_id ) );
		}

		$user_id = ! empty( $this->goodreads_settings['user_id'] ) ? trim( strip_tags( $this->goodreads_settings['user_id'] ) ) : '';
		if ( ! empty( $user_id ) ) {
			delete_transient( apply_filters( 'current_reading_filter', 'goodreads_current_reading_' . $user_id ) );
		}

		if ( ! empty( $isbn ) ) {
			delete_transient( apply_filters( 'book_filter', 'goodreads_book_' . $isbn ) );
		}
	}
}

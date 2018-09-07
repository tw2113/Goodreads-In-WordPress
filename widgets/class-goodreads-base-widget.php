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
}

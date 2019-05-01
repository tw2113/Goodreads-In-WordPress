<?php
/**
 * Class for fetching our user profile.
 *
 * @package Goodreads WordPress Widgets
 * @since 1.0.0
 */

namespace tw2113;

/**
 * Class Goodreads_Profile_API
 *
 * @since 1.0.0
 */
class Goodreads_Profile_API extends Goodreads_API implements goodreads {

	/**
	 * Goodreads API endpoint to query.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $endpoint = '/user/show/';

	/**
	 * Retrieve latest user badge.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get() : array {
		$url = sprintf( '%s%s%s.xml',
			$this->base_uri,
			$this->endpoint,
			$this->user_id
		);

		$results = wp_remote_get(
			add_query_arg(
				[
					'key' => $this->api_key,
				],
				$url
			)
		);

		return $results;
	}
}

<?php
/**
 * Class for fetching current reading.
 *
 * @package Goodreads WordPress Widgets
 * @since 1.0.0
 */

namespace tw2113;

/**
 * Class Current_Reading_Shelf_API
 *
 * @since 1.0.0
 */
class Current_Reading_Shelf_API extends Goodreads_API {

	/**
	 * Goodreads API endpoint to query.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $endpoint = '/review/list/';

	/**
	 * What shelf to retrieve.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $shelf = 'currently-reading';

	/**
	 * Retrieve latest user badge.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_books() {
		$url = sprintf( '%s%s%s.xml',
			$this->base_uri,
			$this->endpoint,
			$this->user
		);

		$results = wp_remote_get(
			add_query_arg(
				[
					'key'      => $this->api_key,
					'v'        => 2,
					'shelf'    => $this->shelf,
					'per_page' => $this->limit,
				],
				$url
			)
		);

		return $results;
	}
}

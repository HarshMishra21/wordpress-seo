<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Frontend\Schema
 */

/**
 * Class WPSEO_Schema
 *
 * Outputs schema code specific for Google's JSON LD stuff.
 *
 * @since 1.8
 */
class WPSEO_Schema implements WPSEO_WordPress_Integration {
	/**
	 * Registers the hooks.
	 */
	public function register_hooks() {
		add_action( 'wpseo_head', array( $this, 'json_ld' ), 91 );
		add_action( 'wpseo_json_ld', array( $this, 'output' ), 1 );
	}

	/**
	 * JSON LD output function that the functions for specific code can hook into.
	 *
	 * @since 1.8
	 */
	public function json_ld() {
		if ( ! is_404() ) {
			do_action( 'wpseo_json_ld' );
		}
	}

	/**
	 * Outputs the JSON LD code in a valid JSON+LD wrapper.
	 *
	 * @since 10.2
	 *
	 * @return void
	 */
	public function output() {
		$graph  = array();

		foreach ( $this->get_graph_pieces() as $piece ) {
			$graph_piece = $piece->add_to_graph();
			if ( is_array( $graph_piece ) ) {
				$graph[] = $graph_piece;
			}
		}

		if ( is_array( $graph ) && ! empty( $graph ) ) {
			$output = array(
				"@context" => "https://schema.org",
				"@graph"   => $graph,
			);

			echo "<script type='application/ld+json'>", $this->format_data( $output ), '</script>', "\n";
		}
	}

	/**
	 * Prepares the data for outputting.
	 *
	 * @param array $data The data to format.
	 *
	 * @return false|string The prepared string.
	 */
	public function format_data( $data ) {
		if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
			// @codingStandardsIgnoreLine
			return wp_json_encode( $data, JSON_UNESCAPED_SLASHES ); // phpcs:ignore PHPCompatibility.Constants.NewConstants.json_unescaped_slashesFound -- Version check present.
		}

		return wp_json_encode( $data );
	}

	/**
	 * Gets all the graph pieces we need.
	 *
	 * @return array A filtered array of graph pieces.
	 */
	private function get_graph_pieces() {
		$pieces = array(
			new WPSEO_Schema_Organization(),
			new WPSEO_Schema_Person(),
			new WPSEO_Schema_Website(),
			new WPSEO_Schema_WebPage(),
			new WPSEO_Schema_Breadcrumb(),
		);

		/**
		 * Filter: 'wpseo_schema_graph_pieces' - Allows adding pieces to the graph.
		 *
		 * @api array $pieces The schema pieces.
		 */
		return apply_filters( 'wpseo_schema_graph_pieces', $pieces );
	}
}
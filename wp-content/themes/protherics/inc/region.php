<?php

class Protherics_Region {

	private $cookie_name = 'protherics_region';
	private $popup_is_enable;
	private $all_regions;


	public function __construct() {

		$this->popup_is_enable = get_field( 'regions_enable_popup', 'option' );
		// get all regions
		$temp_region = new WP_Query( array(
			'post_type'      => 'region',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		$this->all_regions = $temp_region->posts ?? array();

		if ( $this->popup_is_enable ) {
			add_action( 'wp_head', array( $this, 'add_style' ) );
			add_action( 'wp_body_open', array( $this, 'add_script' ) );
			add_action( 'wp_footer', array( $this, 'modal_contetnt' ) );
			add_filter( 'nav_menu_item_classes', array( $this, 'nav_menu_item_classes' ), 10, 3 );

			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		}

	}

	function pre_get_posts( $query ) {

		$post_type = $query->get( 'post_type' );
		$request_uri = $_SERVER['REQUEST_URI'] ?? '';

		if (
			in_array( $post_type, array( 'insights', 'news') )
			&&
			(
				(
					wp_doing_ajax()
					|| in_array( $request_uri, array( '/wp-json/news/v1/all' ) )
					|| ( ! is_admin() && $query->is_main_query() )
				)
				||
				(
					! wp_doing_ajax()
					&& ! is_admin()
					&& ! $query->is_main_query()
				)
			)
		) {

			$region_id = $_COOKIE[ $this->cookie_name ] ?? '';

			if ( $region_id ) {

				$query->set( 'meta_query', array(
					'relation'      => 'OR',
					array(
						'key'       => 'show_in_region',
						'value'     => $region_id,
						'compare'   => 'LIKE',
					),
					array(
						'key'       => 'show_in_region',
						'compare'   => 'NOT EXISTS',
					),
				) );

			}

		}

	}

	function nav_menu_item_classes( $classes, $item, $depth ) {

		$show_in_region = get_field( 'show_in_region', $item->object_id );

		if ( $show_in_region && $this->all_regions ) {

			$id_to_hide = array_diff( $this->all_regions, $show_in_region );

			foreach ( $id_to_hide as $region_id ) {
				$classes[] = "region-hide-$region_id";
			}
		}
		return $classes;
	}

	function modal_contetnt() {
		get_template_part( 'template-parts/regions-modal' );
	}

	function add_style() {

		$style = '';

		foreach ( $this->all_regions as $region_id ) :
			$style .= "body[region-id=\"{$region_id}\"] .region-hide-{$region_id} { display: none; visibility: hidden; }\n";
		endforeach;

		if ( $style ) {
			echo "<style>$style</style>";
		}

	}

	function add_script () {

		?>
		<script>
			( function( $ ) {
				function getCookie(name) {
					const value = `; ${document.cookie}`;
					const parts = value.split(`; ${name}=`);
					if (parts.length === 2) return parts.pop().split(";").shift();
				}
				const cookieRegion = getCookie("<?php echo $this->cookie_name; ?>");
				if ( cookieRegion && cookieRegion !== "all" ) {
					$("body").attr('region-id', cookieRegion);
				}
			}( jQuery ) );
		</script>
		<?php

	}

  public static function get_current_region_id() {
    $cookie_name = 'protherics_region';
    return isset($_COOKIE[$cookie_name]) ? intval($_COOKIE[$cookie_name]) : null;
}

}

new Protherics_Region;

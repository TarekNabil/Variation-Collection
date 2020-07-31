<?php
/**
 * ariation Collection Functionality
 *
 * @category  Class
 * @package   WordPress
 * @author    Tarek Nabil
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      https://tareknabil.net/variation-collection/
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly.
}

/**
 * Class to manage vendor's custom fields.
 */
class Variation_Collection_Functionality {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);    

	}


	// Add custom field input @ Product Data > Variations > Single Variation
	 
	static public function variation_collection_add_input( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( array(
				'id' => 'variation_custom_select[' . $loop . ']',
				'class' => 'vrn_cln_label chzn-select',
				'label' => __( 'Variation Collection:', 'variation_collection' ),
				'placeholder' => 'Enter products ID seprate By Commas',
				'value' => get_post_meta( $variation->ID, 'variation_custom_select', true )
			)
		);
	}
	 
	// Save custom field on product variation save
	 
	 
	public function variation_collection_save_data( $variation_id, $i ) {
		$variation_custom_select = $_POST['variation_custom_select'][$i];
		if ( isset( $variation_custom_select ) ) update_post_meta( $variation_id, 'variation_custom_select', esc_attr( $variation_custom_select ) );
	}
	 
	//  Store custom field value into variation data
	
	public static function variation_collection_add_data( $variations ) {
		$variations['variation_custom_select'] =get_post_meta( $variations[ 'variation_id' ], 'variation_custom_select', true );
		return $variations;
	}
	
	public static function variation_collection_add_custom_variations(){
		global $product;
		if( !$product->is_type( 'variable' ) )return;
		$available_variations = $product->get_available_variations(); 
		foreach ($available_variations as $value) {
			$custom_select_ids = explode(',', $value["variation_custom_select"]);
			$myloop=array();
			foreach  ($custom_select_ids as $custom_select_id) {
				$myloop[]= $custom_select_id;
			}
			if( sizeof($myloop) > 0 ) self:: create_section($myloop, $value["variation_id"]);
		}
	}
	
	public static function create_section($myloop, $variation_id){

		echo '<div style="display:none;" id="custom-variation-for-'.$variation_id.'" class="custom_variations">';
		$args = array('related_products'=> array_filter( array_map( 'wc_get_product', $myloop )));
		wc_set_loop_prop( 'name', 'variation_collection_loop' );
		wc_get_template( 'single-product/related.php', $args );
		echo'</div>';
	}
	
	public static function enqueue_js_scripts(){
	
		if ( 'product' === get_post_type() ) {

			wp_enqueue_script( 'variation_collection_js', plugins_url( '../assets/js/variation_collection.js' , __FILE__ ), array( 'jquery' ) );
		}
	}

	public function addPluginAdminMenu() {

		add_menu_page(  'Variation Collection', 'Variation Coll..', 'administrator', 'Variation Collection', array( $this, 'displayPluginAdminDashboard' ), 'dashicons-format-gallery', 26 );
		add_action('admin_init', array( $this, 'registerAndBuildFields' ));

	}
	public function displayPluginAdminDashboard() {
    
    	require_once 'variation-collection-admin-settings-display.php';
    
    }

	public function registerAndBuildFields() {
	
		register_setting( 'variation-collection-settings-group', 'variation_collection_section_title' );

	}

}

return new Variation_Collection_Functionality();

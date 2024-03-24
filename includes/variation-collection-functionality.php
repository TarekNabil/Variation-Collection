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


	// Add select input @ Product Data > Variations > Single Variation

		static public function variation_collection_add_select_input($loop, $variation_data, $variation) {

			$variatoin_ids = get_post_meta( $variation->ID, 'variation_custom_select', true );
			$product_ids =  $variatoin_ids;
	    if( empty($product_ids) )
	        $product_ids = array();
	    ?>
	    <div class="options_group">

	            <p class="form-field">
	                <label for="variation_custom_select"><?php _e( 'Variation Collection:', 'woocommerce' ); ?></label>
									<?php echo wc_help_tip( __( 'Add Products that matches this variation collection.', 'woocommerce' ) ); ?>
	                <select
									class="wc-product-search"
									multiple="multiple"
									style="width: 100%;"
									id="variation_custom_select[<?php echo $loop;?>]"
									name="variation_custom_select[<?php echo $loop;?>][]"
									data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
									data-action="woocommerce_json_search_products_and_variations"
									>
	                    <?php

	                        foreach ( $product_ids as $product_id ) {
	                            $product = wc_get_product( $product_id );
	                            if ( is_object( $product ) ) {
	                                echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
	                            }
	                        }
	                    ?>
	                </select>
	            </p>

	    </div>
	    <?php

	}

	// Save custom field on product variation save

	public static function variation_collection_save_data( $variation_id, $i ) {
		$variation_custom_select = $_POST['variation_custom_select'][$i];

		if ( isset( $variation_custom_select ) ) update_post_meta( $variation_id, 'variation_custom_select',  $variation_custom_select  );
	}

	//  Store custom field value into variation data

	public static function variation_collection_add_data( $variations ) {
		$variations['variation_custom_select'] =get_post_meta( $variations[ 'variation_id' ], 'variation_custom_select', true );
		return $variations;
	}

	/**
	 * extract product variations and get each variation collection ids
	 *
	 */
	public static function variation_collection_add_custom_variations() {
		global $product;

		if ( ! $product->is_type( 'variable' ) ) {
			return;
		}

		foreach ( $product->get_available_variations() as $variation ) {
			$variation_ids = $variation['variation_custom_select'];

			if ( empty( $variation_ids ) ) {
				continue;
			}

			self::create_section( $variation_ids, $variation['variation_id'] );
		}
	}

	public static function create_section($products, $variation_id) {
		$heading = apply_filters( 'variation_collection_products_heading', __( 'Collection', 'woocommerce' ) );

		echo '<section style="display:none;" class="custom_variations products custom-variation-for-' . esc_attr( $variation_id ) . '">';

		if ( $heading ) {
			echo '<h2>' . esc_html( $heading ) . '</h2>';
		}

		woocommerce_product_loop_start();

		foreach ( $products as $product_id ) {
			$post_object = get_post( $product_id );
			setup_postdata( $post_object );
			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();
		echo '</section>';
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
	/**
	 * Add the Variation collection column to the exporter and the exporter column menu.
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	public static function add_export_column( $columns ) {

		$columns['variation_collection'] = 'Variation Collection';
		return $columns;
	}
	/**
	 * Provide the data to be exported for one item in the column.
	 *
	 * @param mixed $value (default: '')
	 * @param WC_Product $product
	 * @return mixed $value - Should be in a format that can be output into a text file (string, numeric, etc).
	 */
	public static function add_export_data( $value, $product ) {

		if ($product->get_parent_id()) { 
			$variation_collection = $product->get_meta('variation_custom_select', true);
			$value = ($variation_collection) ? json_encode($variation_collection) : 'No Collection' ;
		}else{ 
			$value =  "Parent Product";
		}
		return $value;
	}
	/**
	 * Register the 'Variation Collection' column in the importer.
	 *
	 * @param array $options
	 * @return array $options
	 */
	public static function add_column_to_importer( $options ) {

		// column slug => column name
		$options['variation_collection'] = 'Variation Collection';

		return $options;
	}
	

	/**
	 * Add automatic mapping support for 'Custom Column'. 
	 * This will automatically select the correct mapping for columns named 'Custom Column' or 'custom column'.
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	public static function add_column_to_mapping_screen( $columns ) {
		
		$columns['Variation Collection'] = 'variation_collection';
		$columns['variation collection'] = 'variation_collection';

		return $columns;
	}

	/**
	 * Process the data read from the CSV file.
	 * This just saves the value in meta data, but you can do anything you want here with the data.
	 *
	 * @param WC_Product $object - Product being imported or updated.
	 * @param array $data - CSV data read for the product.
	 * @return WC_Product $object
	 */
	public static function process_import( $object, $data ) {
		
		if ( ! empty( $data['variation_collection'] ) ) {
			$object->update_meta_data( 'variation_custom_select', json_decode( $data['variation_collection']) );
		}
		
		return $object;
	}

}

return new Variation_Collection_Functionality();

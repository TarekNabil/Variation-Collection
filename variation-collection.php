<?php
/**
 * Plugin Name: Variation Collection
 * Version: 1.0.0
 * Plugin URI: https://tareknabil.net/variation-collection/
 * Description: WooCommerce Extension to add custom variation cololection for each product variation.
 * Author: Tarek Nabil
 * Author URI: https://tareknabil.net
 * Requires at least: 4.4.0
 * Tested up to: 5.4.2
 *
 * Text Domain: variation_collection
 * Domain Path: /languages
 *
 * @package WordPress
 * @author  Tarek Nabil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'variation_collection' ) ) {

	/**
	 * Main Class.
	 */
	class variation_collection {


		/**
		* Plugin version.
		*
		* @var string
		*/
		const VERSION = '1.0.0';


		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object single instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', array( $this, 'fallback_notice' ) );
			} else {
				$this->load_plugin_textdomain();
				$this->includes();
			}
			add_action('woocommerce_after_single_product_summary', array( 'Variation_Collection_Functionality', 'variation_collection_add_custom_variations'), 19 );
			add_action( 'woocommerce_variation_options_pricing', array( 'Variation_Collection_Functionality','variation_collection_add_select_input'), 10, 3 );
			add_action( 'woocommerce_save_product_variation', array( 'Variation_Collection_Functionality','variation_collection_save_data'), 10, 2 );
			add_filter( 'woocommerce_available_variation', array( 'Variation_Collection_Functionality','variation_collection_add_data') );
			add_action( 'wp_enqueue_scripts', array( 'Variation_Collection_Functionality','enqueue_js_scripts') );
			add_filter('woocommerce_product_related_products_heading',function(){
				if('variation_collection_loop'===wc_get_loop_prop('name')){
					if(get_option('variation_collection_section_title')){
						return get_option('variation_collection_section_title');
					}else{
						return  __( 'Shop The Collection', 'variation_collection' ) ;
					}
				}else{
					return  __( 'Related products', 'woocommerce' ) ;
				}

			},1);
			//Add 2 Filters to Variation collection column  to the exporter and the exporter column menu.
			add_filter( 'woocommerce_product_export_column_names', array('Variation_Collection_Functionality','add_export_column') );
			add_filter( 'woocommerce_product_export_product_default_columns', array('Variation_Collection_Functionality','add_export_column' ));
			// Filter to add the data
			add_filter( 'woocommerce_product_export_product_column_variation_collection', array('Variation_Collection_Functionality','add_export_data'), 10, 2 );

		}

        /**
         * Method to call and run all the things that you need to fire when your plugin is activated.
         *
         */
        public static function activate() {
    		if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', array( 'variation_collection', 'fallback_notice' ) );
			}
        }


		/**
		 * Method to includes our dependencies.
		 *
		 * @var string
		 */
		public function includes() {

			include_once 'includes/variation-collection-functionality.php';
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @access public
		 * @return bool
		 */

		public function load_plugin_textdomain() {

			$locale = apply_filters( 'wepb_plugin_locale', get_locale(), 'variation_collection' );

			load_plugin_textdomain( 'variation_collection', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			return true;
		}

		/**
		 * Fallback notice.
		 *
		 * We need some plugins to work, and if any isn't active we'll show you!
		 */
		public function fallback_notice() {

			echo '<div class="error">';
			echo '<p>' . __( '<strong>Variation Collection Plugin</strong> is active now but not functional, Please install and activate <strong>WooCommerce</strong> plugin to make it effective.', 'variationtion_collection' ) . '</p>';
			echo '</div>';
		}
	}
}

/**
* Hook to run when your plugin is activated
*/
register_activation_hook( __FILE__, array( 'variation_collection', 'activate' ) );


/**
* Initialize the plugin.
*/
add_action( 'plugins_loaded', array( 'variation_collection', 'get_instance' ) );

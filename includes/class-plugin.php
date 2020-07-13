<?php

namespace WooCommerceMoreSales;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin class.
 *
 * @since 1.0.0
 */
final class Plugin {
	/**
	 * Plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Plugin
	 */
	public static $instance;

	/**
	 * The plugin name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var string
	 */
	public static $plugin_name;

	/**
	 * The plugin version number.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var string
	 */
	public static $plugin_version;

	/**
	 * The plugin directory.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var string
	 */
	public static $plugin_path;

	/**
	 * The plugin URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var string
	 */
	public static $plugin_url;

	/**
	 * The plugin assets URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var string
	 */
	public static $plugin_assets_url;

	/**
	 * Disable class cloning and throws an error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object. Therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; uh?', 'woocommerce-more-sales' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; uh?', 'woocommerce-more-sales' ), '1.0.0' );
	}

	/**
	 * Ensure only one instance of the plugin class is or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
		$this->_define_constants();
	}

	/**
	 * Autoload classes based on namesapce.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $class Name of the class.
	 */
	public function autoload( $class ) {

		// Return if WooCommerceMoreSales name space is not set.
		if ( false === strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$filename = str_replace( __NAMESPACE__ . '\\', '', $class );
		$filename = str_replace( '\\', DIRECTORY_SEPARATOR, $filename );
		$filename = str_replace( '_', '-', $filename );
		$filename = dirname( __FILE__ ) . '/class-' . strtolower( $filename ) . '.php';

		// Return if file is not found.
		if ( ! is_readable( $filename ) ) {
			return;
		}

		include_once $filename;
	}

	/**
	 * Define constants used by the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function _define_constants() {
		$plugin_data = get_file_data( WC_MORE_SALES_FILE__, array( 'Plugin Name', 'Version' ) );

		self::$plugin_name       = array_shift( $plugin_data );
		self::$plugin_version    = array_shift( $plugin_data );
		self::$plugin_path       = trailingslashit( plugin_dir_path( WC_MORE_SALES_FILE__ ) );
		self::$plugin_url        = trailingslashit( plugin_dir_url( WC_MORE_SALES_FILE__ ) );
		self::$plugin_assets_url = trailingslashit( self::$plugin_url . 'assets' );
	}

	/**
	 * Add required hooks.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function init_hooks() {
		// actions
		add_action( 'wp_head', array( $this, 'css' ) );

		// filters
		add_action( 'woocommerce_get_price_html', array( $this, 'sale_trigger' ), 10, 2 );
		/**
		 * Create the section beneath the products tab.
		 */
		add_filter( 'woocommerce_get_sections_products', array( __NAMESPACE__ . '\Settings', 'add_section' ) );
		/**
		 * Add settings to the section we created above.
		 */
		add_filter( 'woocommerce_get_settings_products', array( __NAMESPACE__ . '\Settings', 'add_settings' ), 10, 2 );
	}

	/**
	 * Output the sale trigger phrase based on The Rule of 100
	 * {@link https://jonahberger.com/fuzzy-math-what-makes-something-seem-like-a-good-deal/}.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param string     $html    The current html which will be displayed on the front-end for the product price.
	 * @param WC_Product $product The product.
	 *
	 * @return string The sale trigger phrase HTML.
	 */
	public function sale_trigger( $html, $product ) {
		if ( ! $product->is_on_sale() ) {
			return $html;
		}

		$raw_sale_trigger      = Settings::get( 'sale_trigger' );
		$replaced_sale_trigger = $this->_replace_sale_trigger( esc_html( $raw_sale_trigger ), $product );
		$break                 = '';

		if ( 'no' === Settings::get( 'inline_trigger' ) ) {
			$break = '<br>';
		}

		return $html . sprintf( ' %2$s<span class="wc_more_sales wcms_sale_trigger">%1$s</span>%2$s', $replaced_sale_trigger, $break );
	}

	/**
	 * Get the sale trigger phrase with the placeholders replaced.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @param string     $sale_trigger The raw sale trigger phrase as input by the user.
	 * @param WC_Product $product      The product.
	 *
	 * @return string
	 */
	private function _replace_sale_trigger( $sale_trigger, $product ) {
		$value_saved   = $this->_get_value_saved( $product );
		$replace_pairs = array( ':save' => $value_saved );

		if ( ! is_null( $product->date_on_sale_to ) ) {
			$replace_pairs[':date'] = $this->_get_date_on_sale_to( $product->date_on_sale_to );
		}

		return strtr( $sale_trigger, $replace_pairs );
	}

	/**
	 * Get the sale's expiry date as e.g. ```Jan 29, 2020``` if same as current year, ```Jan 29``` otherwise.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @param WC_DateTime $sale_to_date
	 *
	 * @return string
	 */
	private function _get_date_on_sale_to( $sale_to_date ) {
		$format = $this->_sale_to_year_is_current( $sale_to_date ) ? 'M j' : 'M j, Y';
		return $sale_to_date->date_i18n( $format );
	}

	/**
	 * Check if the year of the sale's expiry date is the current year.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @param WC_DateTime $sale_to_date
	 *
	 * @return bool
	 */
	private function _sale_to_year_is_current( $sale_to_date ) {
		$current_year = date( 'Y', current_time( 'timestamp' ) );
		$sale_to_year = date( 'Y', $sale_to_date->getOffsetTimestamp() );
		return $current_year === $sale_to_year;
	}

	/**
	 * Get the value saved by the customer as a percentage or absolute value.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	private function _get_value_saved( $product ) {
		if ( $product->sale_price <= 100 ) {
			// we use percentage
			$value_saved = round( ( ( $product->regular_price - $product->sale_price ) * 100 ) / $product->regular_price );
			$value_saved = sprintf(
				/* translators: %d is a percentage */
				__( '%d%%', 'woocommerce-more-sales' ),
				$value_saved
			);
		} else {
			$value_saved = wc_price( $product->regular_price - $product->sale_price );
		}

		return $value_saved;
	}

	/**
	 * Output the CSS for the frontend. We do it this way for now since we don't have much CSS.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function css() {
		$color = Settings::get( 'trigger_color' );
		?>
		<style type="text/css">
			.wc_more_sales.wcms_sale_trigger {
				<?php if ( ! empty( $color ) ) : ?>
					color: <?php echo $color; ?>;
				<?php endif; ?>
			}
		</style>
	<?php
	}
}

/**
 * Initialize the Plugin.
 *
 * @since 1.0.0
 */
function maybe_load_wc_more_sales() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$GLOBALS['wc_more_sales'] = Plugin::get_instance();
	}
}

maybe_load_wc_more_sales();
/**
 * Register hooks outside the constructor for easier unit testing.
 */
$GLOBALS['wc_more_sales']->init_hooks();

<?php
/**
 * WooCommerce More Sales
 *
 * @package     WooCommerceMoreSales
 * @author      Lanre Smith
 *
 * @wordpress-plugin
 * Plugin Name: WooCommerce More Sales
 * Plugin URI:  https://github.com/lanresmith
 * Description: Implement strategies to turn in more sales for a WooCommerce store.
 * Version:     1.0.0
 * Author:      Lanre Smith
 * Author URI:  https://github.com/lanresmith
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-more-sales
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_MORE_SALES_FILE__', __FILE__ );

require_once plugin_dir_path( WC_MORE_SALES_FILE__ ) . 'includes/class-plugin.php';

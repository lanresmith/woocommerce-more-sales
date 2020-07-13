<?php

namespace WooCommerceMoreSales;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class.
 *
 * @since 1.0.0
 */
class Settings {
	/**
	 * The prefix used by all option names.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string
	 */
	private static $_prefix = 'wcmoresales_';

	/**
	 * The defaults for all options.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array
	 */
	private static $_defaults = array(
		'sale_trigger'   => 'Save :save',
		'inline_trigger' => 'yes',
		'trigger_color'  => '',
	);

	/**
	 * Create the settings section beneath the product settings tab.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public static function add_section( $sections ) {
		$sections['wc_more_sales'] = 'WooCommerce More Sales';
		return $sections;
	}

	/**
	 * Add the settings fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $settings
	 * @param string $current_section
	 *
	 * @return array
	 */
	public static function add_settings( $settings, $current_section ) {
		/**
		 * Check that the current section is what we want
		 */
		if ( $current_section == 'wc_more_sales' ) {
			$settings_more_sales = array();

			$settings_more_sales[] = array(
				'name' => __( 'Sale Trigger Phrase', 'woocommerce-more-sales' ),
				'type' => 'title',
				'desc' => __( 'The following options are used to configure the display of the sale trigger phrase.', 'woocommerce-more-sales' ),
				'id'   => 'wcmoresales',
			);

			$settings_more_sales[] = array(
				'name' => __( 'Sale trigger phrase', 'woocommerce-more-sales' ),
				'id'   => self::$_prefix . 'sale_trigger',
				'type' => 'text',
				'desc' => __( 'This is the sale trigger phrase to be shown to the customer', 'woocommerce-more-sales' ),
			);

			$settings_more_sales[] = array(
				'title'    => __( 'Inline trigger', 'woocommerce-more-sales' ),
				'desc'     => __( 'Make sale trigger phrase inline', 'woocommerce-more-sales' ),
				'id'       => self::$_prefix . 'inline_trigger',
				'type'     => 'checkbox',
				'default'  => 'yes',
				'desc_tip' => __( 'This will make the trigger phrase next to the sale price.', 'woocommerce-more-sales' ),
			);

			$settings_more_sales[] = array(
				'name'  => __( 'Text color', 'woocommerce-more-sales' ),
				'id'    => self::$_prefix . 'trigger_color',
				'type'  => 'text',
				'class' => 'colorpick',
				'css'   => 'width:200px;',
				'desc'  => __( 'The text color of the trigger phrase. Leave empty to use the same color as the sale price.', 'woocommerce-more-sales' ),
			);

			$settings_more_sales[] = array(
				'type' => 'sectionend',
				'id'   => 'wcmoresales',
			);
			return $settings_more_sales;
		} else {
			return $settings;
		}
	}

	/**
	 * Get an option.
	 *
	 * @param string $name
	 *
	 * @return mixed Value set for the option or the default if option does not exist.
	 */
	public static function get( $name ) {
		$full_name = self::$_prefix . $name;
		if ( is_multisite() ) {
			$option = get_site_option( $full_name );
		} else {
			$option = get_option( $full_name );
		}

		if ( false === $option ) {
			// not set yet, so set default
			return self::$_defaults[ $name ];
		}

		if ( 'trigger_color' === $name ) {
			$option = self::_prepare_color( $option );
		}

		return $option;
	}

	/**
	 * Set an option.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param bool   $autoload
	 *
	 * @return bool True if value was successfully updated, false otherwise.
	 */
	public static function set( $name, $value, $autoload = true ) {
		$name = self::$_prefix . $name;
		if ( is_multisite() ) {
			return update_site_option( $name, $value );
		} else {
			return update_option( $name, $value, $autoload );
		}
	}

	/**
	 * Ensure that the hex color string is well formed.
	 *
	 * @param string $color
	 *
	 * @return string The well-formed hex color or empty string.
	 */
	private static function _prepare_color( $color ) {
		if ( preg_match( '/\A#[a-f0-9]{6}\Z/i', $color ) ) {
			return $color;
		} else if ( preg_match( '/\A[a-f0-9]{6}\Z/i', $color ) ) {			
			$color = '#' . $color;
			// update DB
			self::set( 'trigger_color', $color );
			return $color;
		}
		return '';
	}
}

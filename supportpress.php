<?php

/**
 * Plugin Name: SupportPress
 * Plugin URI:  http://supportpress.com/
 * Description: Reinventing how you support your customers.
 * Author:      Daniel Bachhuber, Alex Mills, Andrew Spittle, Automattic
 * Author URI:  http://automattic.com/
 * Version:     0.1
 *
 * Text Domain: supportpress
 * Domain Path: /languages/
 */

class SupportPress {

	/** Magic *****************************************************************/

	/**
	 * SupportPress uses many variables, most of which can be filtered to customize
	 * the way that it works. To prevent unauthorized access, these variables
	 * are stored in a private array that is magically updated using PHP 5.2+
	 * methods. This is to prevent third party plugins from tampering with
	 * essential information indirectly, which would cause issues later.
	 *
	 * @see SupportPress::setup_globals()
	 * @var array
	 */
	private $data;

	/** Not Magic *************************************************************/

	/**
	 * @var obj Add-ons append to this (Akismet, etc...)
	 */
	public $extend;

	/** Singleton *************************************************************/

	/**
	 * @var SupportPress The one true SupportPress
	 */
	private static $instance;

	/**
	 * Main SupportPress Instance
	 *
	 * SupportPress is fun
	 * Please load it only one time
	 * For this, we thank you
	 *
	 * Insures that only one instance of SupportPress exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since SupportPress 0.1
	 * @staticvar array $instance
	 * @uses SupportPress::setup_globals() Setup the globals needed
	 * @uses SupportPress::includes() Include the required files
	 * @uses SupportPress::setup_actions() Setup the hooks and actions
	 * @see SupportPress()
	 * @return The one true SupportPress
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new SupportPress;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent SupportPress from being loaded more than once.
	 *
	 * @since SupportPress 0.1
	 * @see SupportPress::instance()
	 * @see SupportPress();
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent SupportPress from being cloned
	 *
	 * @since SupportPress 0.1
	 */
	public function __clone() { wp_die( __( 'Cheatin’ uh?' ) ); }

	/**
	 * A dummy magic method to prevent SupportPress from being unserialized
	 *
	 * @since SupportPress 0.1
	 */
	public function __wakeup() { wp_die( __( 'Cheatin’ uh?' ) ); }

	/**
	 * Magic method for checking the existence of a certain custom field
	 *
	 * @since SupportPress 0.1
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting SupportPress varibles
	 *
	 * @since SupportPress 0.1
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting SupportPress varibles
	 *
	 * @since SupportPress 0.1
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/** Private Methods *******************************************************/

	/** Private Methods *******************************************************/

	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @since SupportPress 0.1
	 * @access private
	 * @uses plugin_dir_path() To generate SupportPress plugin path
	 * @uses plugin_dir_url() To generate SupportPress plugin url
	 * @uses apply_filters() Calls various filters
	 */
	private function setup_globals() {

		/** Version ***********************************************************/

		$this->version        = '0.1-alpha'; // SupportPress version

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file           = __FILE__;
		$this->basename       = apply_filters( 'supportpress_plugin_basenname', plugin_basename( $this->file ) );
		$this->plugin_dir     = apply_filters( 'supportpress_plugin_dir_path',  plugin_dir_path( $this->file ) );
		$this->plugin_url     = apply_filters( 'supportpress_plugin_dir_url',   plugin_dir_url ( $this->file ) );

		// Languages
		$this->lang_dir       = apply_filters( 'supportpress_lang_dir',         trailingslashit( $this->plugin_dir . 'languages' ) );

		/** Identifiers *******************************************************/

		$this->post_type      = apply_filters( 'supportpress_thread_post_type', 'sp_thread' ); // TODO: Prefix with full "supportpress_" ?
		$this->post_statuses  = apply_filters( 'supportpress_thread_post_statuses', array() );

		/** Misc **************************************************************/

		$this->extend         = new stdClass(); // Plugins add data here
		$this->errors         = new WP_Error(); // Feedback
	}

	/**
	 * Include required files
	 *
	 * @since SupportPress 0.1
	 * @access private
	 * @todo Be smarter about conditionally loading code
	 * @uses is_admin() If in WordPress admin, load additional file
	 */
	private function includes() {

		/** Core **************************************************************/



		/** Extensions ********************************************************/

		# TODO: Akismet plugin?

		/** Admin *************************************************************/

		// Quick admin check and load if needed
		if ( is_admin() ) {
			require_once( $this->plugin_dir . 'classes/class-supportpress-admin.php' );
		}
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since SupportPress 0.1
	 * @access private
	 * @uses register_activation_hook() To register the activation hook
	 * @uses register_deactivation_hook() To register the deactivation hook
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {
		add_action( 'init', array( $this, 'action_init_register_post_type' ) );

		do_action_ref_array( 'supportpress_after_setup_actions', array( &$this ) );
	}

	/**
	 * Register the custom post type
	 *
	 * @since SupportPress 0.1
	 * @uses register_post_type() To register the post types
	 * @uses apply_filters() Calls various filters to modify the arguments
	 *                        sent to register_post_type()
	 */
	public function action_init_register_post_type() {
		register_post_type( $this->post_type, array(
			'labels' => array(
				'name'               => __( 'Threads',                   'supportpress' ),
				'singular_name'      => __( 'Thread',                    'supportpress' ),
				'all_items'          => __( 'All Threads',               'supportpress' ),
				'add_new_item'       => __( 'Add New Thread',            'supportpress' ),
				'edit_item'          => __( 'Edit Thread',               'supportpress' ),
				'new_item'           => __( 'New Thread',                'supportpress' ),
				'view_item'          => __( 'View Thread',               'supportpress' ),
				'search_item'        => __( 'Search Threads',            'supportpress' ),
				'not_found'          => __( 'No threads found',          'supportpress' ),
				'not_found_in_trash' => __( 'No threads found in trash', 'supportpress' ),
				),
			'public'        => true,
			'menu_position' => 3,
			'supports'      => array(
				'title',
				'comments',
			),
		) );
	}
}

/**
 * The main function responsible for returning the one true SupportPress instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $supportpress = SupportPress(); ?>
 *
 * @return The one true SupportPress Instance
 */
function SupportPress() {
	return SupportPress::instance();
}

add_action( 'plugins_loaded', 'SupportPress' );
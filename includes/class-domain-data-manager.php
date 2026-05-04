<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 * @author     Manus AI <your-name@example.com>
 */
class Domain_Data_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Domain_Data_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'domain-data-manager';
		$this->version = DDM_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Domain_Data_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Domain_Data_Manager_i18n. Defines internationalization functionality.
	 * - Domain_Data_Manager_Admin. Defines all hooks for the admin area.
	 * - Domain_Data_Manager_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once DDM_PLUGIN_DIR . 'includes/class-domain-data-manager-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once DDM_PLUGIN_DIR . 'admin/class-domain-data-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once DDM_PLUGIN_DIR . 'public/class-domain-data-manager-public.php';

        /**
         * The class responsible for database interactions.
         */
        require_once DDM_PLUGIN_DIR . 'includes/class-domain-data-manager-db.php';

        /**
         * The class responsible for logging.
         */
        require_once DDM_PLUGIN_DIR . 'includes/class-domain-data-manager-logger.php';

		$this->loader = new Domain_Data_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Domain_Data_Manager_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
        // Internationalization can be added here later if needed.
        // For now, we'll skip loading the i18n class.
		// $plugin_i18n = new Domain_Data_Manager_i18n();
		// $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Domain_Data_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

        // AJAX hooks for admin actions
        $this->loader->add_action( 'wp_ajax_ddm_handle_csv_upload', $plugin_admin, 'handle_csv_upload' );
        $this->loader->add_action( 'wp_ajax_ddm_add_data', $plugin_admin, 'handle_add_data' );
        $this->loader->add_action( 'wp_ajax_ddm_update_data', $plugin_admin, 'handle_update_data' );
        $this->loader->add_action( 'wp_ajax_ddm_delete_data', $plugin_admin, 'handle_delete_data' );
        $this->loader->add_action( 'wp_ajax_ddm_get_data', $plugin_admin, 'handle_get_data' ); // For editing
        $this->loader->add_action( 'wp_ajax_ddm_export_data', $plugin_admin, 'handle_export_data' );

        // Hook for saving settings
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Domain_Data_Manager_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Shortcode registration
        $this->loader->add_shortcode( 'domain_data_table', $plugin_public, 'render_shortcode' );

        // AJAX hook for frontend data retrieval (used by search/sort)
        $this->loader->add_action( 'wp_ajax_ddm_get_public_data', $plugin_public, 'handle_get_public_data' );
        $this->loader->add_action( 'wp_ajax_nopriv_ddm_get_public_data', $plugin_public, 'handle_get_public_data' );

        // Additional AJAX hooks for enhanced functionality
        $this->loader->add_action( 'wp_ajax_ddm_get_domain_types', $plugin_public, 'handle_get_domain_types' );
        $this->loader->add_action( 'wp_ajax_nopriv_ddm_get_domain_types', $plugin_public, 'handle_get_domain_types' );
        $this->loader->add_action( 'wp_ajax_ddm_copy_domain', $plugin_public, 'handle_copy_domain' );
        $this->loader->add_action( 'wp_ajax_nopriv_ddm_copy_domain', $plugin_public, 'handle_copy_domain' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Domain_Data_Manager_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

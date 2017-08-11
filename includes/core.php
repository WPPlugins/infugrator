<?php
// 0f62adefea7e1661d541dba077ea42aa tr315
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Infugrator
 * @subpackage Infugrator/includes
 * @author     Cosmin Schiopu <sc.cosmin@gmail.com>
 */


class Infugrator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      IFG_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;


	/**
	 * @since    1.0.0
	 * @access   public
	 */
	public $settings;


	/**
	 * @since    1.0.0
	 * @access   public
	 */
	public $utility;



	/**
	 * Run the main processes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run() {

		spl_autoload_register( array($this, 'autoloader') );

		$this->loader   = new IFG_Loader;
		$this->settings = new IFG_Settings;
		$this->utility  = new IFG_Utility;

		$this->load_libraries();
		$this->run_modules();

		$this->loader->add_action('activated_plugin', $this, 'activation_redirect', 2);
		$this->loader->add_filter('wpmu_drop_tables', $this, 'drop_db_tables_list');
		$this->loader->run();

	}



	/**
	 * Auto load all classes from a specific path
	 *
	 * @since  1.0.0
	 * @param  string $file_name
	 */
	public function autoloader($filename){

		$dir = plugin_dir_path(dirname(__FILE__)) . 'autoloader/class-*.php';
		$subdir = plugin_dir_path(dirname(__FILE__)) . 'autoloader/**/class-*.php';

		$paths = glob('{'.$dir.','.$subdir.'}', GLOB_BRACE);

		if( is_array($paths) && count($paths) > 0 ){
			foreach( $paths as $file ) {
				if ( file_exists( $file ) ) {
					require_once $file;
				}
			}
		}
	}



	/**
	 * Load required libraries
	 *
	 * @since  1.0.0
	 */
	private function load_libraries(){

		//Mustache
		if(!class_exists('Mustache_Autoloader')){
			require_once plugin_dir_path(dirname(__FILE__)) . 'vendors/lib/Mustache/Autoloader.php';
		}
		Mustache_Autoloader::register();


		//Infusionsoft SDK
		if(!class_exists('Infusionsoft_AppPool')){
			require_once plugin_dir_path(dirname(__FILE__)) . 'vendors/lib/Infusionsoft/infusionsoft.php';
		}
		Infusionsoft_AppPool::addApp(new Infusionsoft_App($this->settings->get('application', 'name'), $this->settings->get('application', 'key'), 443));

	}



	/**
	 * Create the list with module paths
	 *
	 * @since    1.0.0
	 */
	public function add_modules(){

		$list = $this->utility->wp_option('get', 'ifg-modules');

		$dir    = plugin_dir_path(dirname(__FILE__)) . 'modules/class-*.php';
		$subdir = plugin_dir_path(dirname(__FILE__)) . 'modules/**/class-*.php';
		$paths  = glob('{'.$dir.','.$subdir.'}', GLOB_BRACE);

		$list = array('list' => $paths);

		$this->utility->wp_option('update', 'ifg-modules', $list);
	}



	/**
	 * Run all active modules
	 *
	 * @since    1.0.0
	 */
	public function run_modules(){

		$modules = $this->utility->wp_option('get', 'ifg-modules');
		$paths = isset($modules['active']) ? $modules['active'] : '';

		if(is_array($paths) && count($paths) > 0){
			foreach ($paths as $filename){
				require_once $filename;
			}
		}
	}



	/**
	 * Redirect after plugin activation
	 *
	 * @since  1.0.0
	 */
	public function activation_redirect( $plugin ) {

		$path = plugin_basename(dirname(dirname(__FILE__))).'/infugrator.php';

		$this->load_first();

	    if( $plugin == $path ) {
	        exit(wp_redirect( admin_url('admin.php?page=infugrator' )));
	    }
	}



	/**
	 * Ensure our plugin is loaded first but not above the dependencies.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function load_first(){

		$paths = array();
		$active = $this->utility->wp_option('get', 'active_plugins');
		$search = array('infugrator.php', 'contact-form-7', 'gravityforms');

		foreach($search as $item){
			foreach($active as $key => $value){
				if(strpos($value, $item) !== false){
					$paths[] = $value;
					array_splice( $active, $key, 1 );
				}
			}
		}

		if(count($paths) > 0){
			foreach($paths as $path){
				array_unshift( $active, $path );
			}
			$this->utility->wp_option('update', 'active_plugins', $active );
		}
	}



	/**
	 * Return the list of tables which will be deleted on a blog deletion
	 *
	 * @since  1.0.0
	 * @param  array $tables
	 * @return array
	 */
	public function drop_db_tables_list($tables) {

	    return apply_filters('ifg_drop_db_tables_list', $tables);
	}

}

$plugin = new Infugrator;
$plugin->run();
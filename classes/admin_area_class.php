<?php

//inlcude dependent class

require_once( 'admin_area_components.php' );

class Admin_Area_Class {


	public $pluginUrl;
	public $pluginAssets;
	public $urlSlug;
	public $verssionWordpress;
	public $pluginPath;
	public $activeTab;
	public $userID;
	public $display_settings_key = 'admin_area_display_settings';
	public $site_settings_key = 'admin_area_site_settings';
	public $extend_configuration_key = 'admin_area_extend_settings';

	public $display_settings = array();
	public $site_settings = array();
	public $extend_configuration = array();

	function __construct() {
		global $wp_version;

		$this->pluginUrl = plugins_url( '', dirname( __FILE__ ) );
		$this->pluginPath = dirname( dirname( __FILE__ ) );

		$this->verssionWordpress = substr( $wp_version, 0, 3 );
		$this->urlSlug = 'admin-area-options';


		$this->pluginAssets = $this->pluginUrl . '/assets';


		//setup theme

		$this->admin_area_setup();

	}

	public function admin_area_setup() {
		/*load text domain*/

		/*current user*/
		$this->userID = get_current_user_id();



		$this->initialize_all_options(); //initialize all options before use it
		$this->setup_user_options(); //setup user option
		$this->modify_site_settings(); //modify site settings




		//add plugin scripts & css
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_area_enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_area_enqueue_styles' ) );
		add_action( 'login_head', array( $this, 'admin_area_option_styles' ) );


		/*Customize login screen*/
		if ( isset( $this->display_settings['logourl'] ) ) {

			add_filter( 'login_headertitle', array( $this, 'get_option_login_url' ) );
			add_filter( 'login_headerurl', array( $this, 'get_option_login_url' ) );

		}

    //add options page
		add_action( 'admin_menu', array( $this, 'add_admin_area_menu' ) );




		//Initialize the plugins's options
		add_action( 'admin_init', array( $this, 'admin_area_intialize_visual_options' ) ); //initialize visual options
		add_action( 'admin_init', array( $this, 'admin_area_intialize_site_options' ) ); //initialize site options
		add_action( 'admin_init', array( $this, 'admin_area_intialize_extend_options' ) ); //initialize extend options

		//Notifications & screen options
		add_action( 'init', array( $this, 'extend_notify_settings' ), 2 ); //hide notyfication
		add_action( 'init', array( $this, 'extend_remove_help_nad_options' ) ); //hide help options

		//Admin area modifications
		add_action( 'wp_before_admin_bar_render', array( $this, 'modify_admin_bar_menu' ) ); //modify admin bar menu
		add_action( 'wp_dashboard_setup', array( $this, 'modify_admin_dashboard' ) ); //modify dashboard metaboxes
		add_action( 'wp_dashboard_setup', array( $this, 'additional_components' ) ); //add additional components


	}


	public function initialize_all_options() {

		//initialize display options
		if ( false == get_option( $this->display_settings_key ) ) {
			add_option( $this->display_settings_key, $this->admin_area_default_visual_options()  );
		}
		$this->display_settings = get_option( $this->display_settings_key );

		//initialize settings options
		if ( false == get_option( $this->site_settings_key ) ) {
			add_option( $this->site_settings_key,  $this->admin_area_default_site_options( ) );
		}

			$this->site_settings = get_option( $this->site_settings_key );

		//initialize extend options
		if ( false == get_option( $this->extend_configuration_key ) ) {
			add_option( $this->extend_configuration_key, $this->admin_area_default_extend_options() );
		}
			$this->extend_configuration = get_option( $this->extend_configuration_key );

	}

	public function get_option_login_url() {
		return $this->display_settings['logourl'];
	}


	public function admin_area_enqueue_scripts() {

		wp_enqueue_media(); //add uploader files

		if ( isset( $this->display_settings['style'] ) && $this->display_settings['style'] == 1 ) {
			if ( version_compare( $this->verssionWordpress, '3.5', '>=' ) ) {
				wp_enqueue_script( 'cms_admin_area_js', $this->pluginAssets . '/cms_admin_area.js', array( 'jquery' ), '1.0', false );
			}
		}
		//add common script
		wp_enqueue_script( 'cms_admin_area_common', $this->pluginAssets . '/common.js', array( 'jquery' ), '1.0', false );

	}

	public function admin_area_enqueue_styles() {

		if ( isset( $this->display_settings['style'] ) && $this->display_settings['style'] == 1 ) {
			if ( version_compare( $this->verssionWordpress, '3.5', '>=' ) ) {
				wp_enqueue_style( 'cms_admin_area_css', $this->pluginAssets . '/cms_admin_area.css', array(), '1.0', false );
			}
			else {
				wp_enqueue_style( 'cms_admin_area_css', $this->pluginAssets . '/cms_admin_area_legacy.css', array(), '1.0', false );
			}
		}

		//add common style
		wp_enqueue_style( 'cms_admin_area_maincss', $this->pluginAssets . '/main.css', array(), '1.0', false );

	}

	public function admin_area_option_styles() {

		if ( isset( $this->display_settings['logo'] ) && strlen( $this->display_settings['logo'] ) > 0 )
			?>
    <style>
			.login h1 a{
		background: url( <?php echo $this->display_settings['logo'] ?> ) no-repeat 0 0;
		background-size: cover;
	}
    </style>
			<?php
	}

	/*TABS FUNCTIONS*/

	public function routing_tabs() {
		if ( isset( $_GET['tab'] ) ) {
			$this->activeTab = $_GET['tab'];
		}
		else {
			$this->activeTab = $this->display_settings_key;
		}
	}

	/*add admin menu*/

	public function add_admin_area_menu() {
		add_options_page( __( 'Admin Area Options', 'cms-admin-area' ), __( 'Admin Area Options', 'cms-admin-area' ), 'manage_options', 'admin-area-options', array( $this, 'display_options_page' ) );

	}

	/*modify admin bar menu*/

	public function modify_admin_bar_menu() {
		global $wp_admin_bar;
		$key_filter = array( 'wp-logo', 'updates', 'view-site', 'new-link', 'new-user', 'new-post', 'comments' );

		foreach ( $this->display_settings as $key=> $value ) {

			if ( in_array( $key, $key_filter ) && $value == "1" ) {

				$wp_admin_bar->remove_menu( $key );
			}
		}

	}

	/*remove all dashboard metabox*/
	public function modify_admin_dashboard() {

		if ( isset( $this->display_settings['dashboard_metabox'] ) && $this->display_settings['dashboard_metabox'] == '1' ) {
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' ); // Right Now
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' ); // Recent Comments
			remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' ); // Incoming Links
			remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' ); // Plugins
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' ); // Quick Press
			remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' ); // Recent Drafts
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' ); // WordPress blog
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' ); // Other WordPress News
		}

	}

	/*hide welcome panel*/
	public function setup_user_options() {

		if ( isset( $this->display_settings['welcome_panel'] ) && $this->display_settings['welcome_panel'] == '1' ) {
			update_user_meta( $this->userID, 'show_welcome_panel', 0 );
		}

		//Change footer text
		if ( isset( $this->display_settings['footer_text'] ) && strlen( $this->display_settings['footer_text'] ) > 0 ) {
			//footer text
			add_filter( 'admin_footer_text', array( $this, '_modify_footer_text' ) );
		}
	}

	/*add components to admin area*/
	public function additional_components() {

		if ( ( isset( $this->display_settings['add_metabox_first'] ) && $this->display_settings['add_metabox_first'] == '1' ) ) {
			/*check user capability*/
			if ( current_user_can( 'manage_options' ) ) {
				add_meta_box( 'cms_sectionid_left', __( 'Appearance', 'cms-admin-area' ), array( 'Admin_Area_Components', 'dashboard_cms_widget_first' ), 'dashboard', 'side', 'high' );
			}
		}

		if ( ( isset( $this->display_settings['widget_statistic'] ) && $this->display_settings['widget_statistic'] == '1' ) ) {
			/*check user capability*/
			if ( current_user_can( 'publish_pages' ) ) {
				add_meta_box( 'cms_sectionid_right', __( 'Content summary', 'cms-admin-area' ), array( 'Admin_Area_Components', 'dashboard_cms_widget_statistic' ), 'dashboard', 'normal', 'high' );
			}
		}

	}

	/*admin area site settings*/
	public function modify_site_settings() {

		$key_action = array( 'wp_generator', 'rsd_link', 'wlwmanifest_link', 'feed_links_extra', 'feed_links', 'new-post', 'comments' );

		foreach ( $this->site_settings as $key=> $value ) {

			if ( in_array( $key, $key_action ) && $value == "1" ) {
				if ( $key == 'feed_links' )
					remove_action( 'wp_head', $key, 2 );
				elseif ( $key == 'feed_links_extra' )
					remove_action( 'wp_head', $key, 3 );
				else
					remove_action( 'wp_head', $key );
				if ( $key == 'wp_generator' )
					add_filter( 'the_generator', array( $this, 'filter_remove_version' ) );

			}
		}


	}


	public function _modify_footer_text() {
		if ( $this->display_settings['footer_text'] != '0' )
			echo $this->display_settings['footer_text'];
		else
			echo '';

	}

	public function extend_notify_settings() {
		if ( ( isset( $this->extend_configuration['core_notyfication'] ) && $this->extend_configuration['core_notyfication'] == '1' ) ) {
			add_filter( 'update_footer', '__return_false', 20 );
			add_filter( 'site_transient_update_core', create_function( '$a', "return null;" ) );
		}

		if ( ( isset( $this->extend_configuration['plugin_notyfication'] ) && $this->extend_configuration['plugin_notyfication'] == '1' ) ) {
			add_filter( 'site_transient_update_plugins', create_function( '$a', "return null;" ) );
		}
		if ( ( isset( $this->extend_configuration['theme_notyfication'] ) && $this->extend_configuration['theme_notyfication'] == '1' ) ) {
			add_filter( 'site_transient_update_themes', create_function( '$a', "return null;" ) );
		}
	}


	/*add filters depend on options*/
	public function extend_remove_help_nad_options() {
		if ( ( isset( $this->extend_configuration['screen_options'] ) && $this->extend_configuration['screen_options'] == '1' ) ) {
			add_filter( 'screen_options_show_screen', '__return_false' );
		}

		if ( ( isset( $this->extend_configuration['help_tab'] ) && $this->extend_configuration['help_tab'] == '1' ) ) {
			add_action( 'admin_head', array( $this, '_remove_help_tabs' ) );
		}

	}


	/*function remove help tabs*/
	public function _remove_help_tabs() {
		$screen = get_current_screen();
		$screen->remove_help_tabs();

	}

	/*remove verison from head file and RSS feeds*/
	public function  filter_remove_version() {

		return '';

	}


	/*DISPLAY OPTION PAGE*/

	public function display_options_page() {

		$this->routing_tabs();

		include( $this->pluginPath . '/views/option-page.php' );
	}

	/* -----------------------------------------
		   Initializes the CMS admin area options
		----------------------------------------- */


	/* visual  default options */

	function admin_area_default_visual_options() {

		$defaults = array(

			'style'             => '1',
			'wp-logo'           => '0',
			'comments'          => '0',
			'add_metabox_first' => '0',
			'widget_statistic'  => '0'

		);

		return $defaults ;

	}


	/*site default options */


	function admin_area_default_site_options() {

		$defaults = array(
			'wp_generator'     => '1',
			'rsd_link'         => '0',
			'wlwmanifest_link' => '0',
			'feed_links'       => '0',
			'feed_links_extra' => '0',

		);

		return  $defaults;

	}

	/*extend default options  - */

	function admin_area_default_extend_options() {

		$defaults = array(
			'core_notyfication'  => '0',
			'plugin_notyfication'=> '0',
			'theme_notyfication' => '0',
			'help_tab'           => '0',
			'screen_options'     => '0'
		);

		return 				 $defaults ;

	}



		/* -----------------------------------------
	   SECTIONS & FIELDS
	----------------------------------------- */

	/*INITIALIZE VISUAL OPTIONS*/

	public function admin_area_intialize_visual_options() {

		//all options initialized in initialize_all_options


		/* Login Screen section */
		add_settings_section(
			'admin_area_logo_settings_section',
			__( 'Login Screen', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->display_settings_key
		);

		/* Admin panel style   section*/
		add_settings_section(
			'admin_area_style_settings_section',
			__( 'Admin panel style', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->display_settings_key
		);

		/* Admin footer text*/
		add_settings_section(
			'admin_area_footer_text_section',
			__( 'Footer', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->display_settings_key
		);


		/* Admin bar settings  section*/
		add_settings_section(
			'admin_area_bar_settings_section',
			__( 'Admin bar settings', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->display_settings_key
		);

		/* Admin bar settings  section*/
		add_settings_section(
			'admin_area_dashboard_settings_section',
			__( 'Dashboard settings', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->display_settings_key
		);


		/*add logo field*/
		add_settings_field(
			'amin_area_logo', // ID used to identify the field throughout the plugin
			__( 'Logo Image', 'cms-admin-area' ),
			array( $this, 'admin_area_logo_display_input' ),
			$this->display_settings_key,
			'admin_area_logo_settings_section',
			array(
				__( 'Change the logo of the login screen', 'cms-admin-area' )
			)
		);

		/*add logo url*/
		add_settings_field(
			'display_logo_url',
			__( 'Login Screen URL', 'cms-admin-area' ),
			array( $this, 'admin_area_logourl_display_input' ),
			$this->display_settings_key,
			'admin_area_logo_settings_section',
			array(
				__( 'Customize the URL of the logo', 'cms-admin-area' )
			)
		);

		/*add admin panel style field*/
		add_settings_field(
			'display_option_style',
			__( 'Admin Horizontal Tabs', 'cms-admin-area' ),
			array( $this, 'admin_area_style_display_input' ),
			$this->display_settings_key,
			'admin_area_style_settings_section',
			array(
				''
			)
		);

		/*add admin panel style field*/
		add_settings_field(
			'display_footer_text',
			__( 'Admin footer text', 'cms-admin-area' ),
			array( $this, 'admin_area_footer_text_input' ),
			$this->display_settings_key,
			'admin_area_footer_text_section',
			array(
				__( 'choose "0" if you want to completly remove footer text', 'cms-admin-area' )
			)
		);

		/*add admin bar logo field*/
		add_settings_field(
			'display_bar_wp-logo',
			__( 'Remove admin bar wp-logo', 'cms-admin-area' ),
			array( $this, 'admin_area_wp_logo_display_input' ),
			$this->display_settings_key,
			'admin_area_bar_settings_section',
			array(
				''
			)
		);

		/*add admin bar comment field*/
		add_settings_field(
			'display_bar_wp_comment',
			__( 'Remove admin bar comments', 'cms-admin-area' ),
			array( $this, 'admin_area_wp_comment_display_input' ),
			$this->display_settings_key,
			'admin_area_bar_settings_section',
			array(
				''
			)
		);

		/*add admin bar updates field*/
		add_settings_field(
			'display_bar_updates',
			__( 'Remove admin bar updates', 'cms-admin-area' ),
			array( $this, 'admin_area_wp_updates_display_input' ),
			$this->display_settings_key,
			'admin_area_bar_settings_section',
			array(
				''
			)
		);
		/*add admin bar new user field*/
		add_settings_field(
			'display_bar_new-user',
			__( 'Remove admin bar new user', 'cms-admin-area' ),
			array( $this, 'admin_area_wp_new_user_display_input' ),
			$this->display_settings_key,
			'admin_area_bar_settings_section',
			array(
				''
			)
		);

		/*add dashboard metabox hide field*/
		add_settings_field(
			'display_dashboard_metaboxes',
			__( 'Remove dashboard metaboxes', 'cms-admin-area' ),
			array( $this, 'admin_area_dashboard_metabox_display_input' ),
			$this->display_settings_key,
			'admin_area_dashboard_settings_section',
			array(
				''
			)
		);
		/*add dashboard metabox hide field*/
		add_settings_field(
			'display_dashboard_welcome_panel',
			__( 'Remove welcome panel', 'cms-admin-area' ),
			array( $this, 'admin_area_welcome_panel_display_input' ),
			$this->display_settings_key,
			'admin_area_dashboard_settings_section',
			array(
				''
			)
		);

		/*add dashboard first widget links*/
		add_settings_field(
			'display_dashboard_add_metabox_first',
			__( 'Add short link widget', 'cms-admin-area' ),
			array( $this, 'admin_area_welcome_panel_add_metabox_first' ),
			$this->display_settings_key,
			'admin_area_dashboard_settings_section',
			array(
				''
			)
		);
		/*add content summary field*/
		add_settings_field(
			'display_dashboard_add_widget_statistic',
			__( 'Add  widget content summary', 'cms-admin-area' ),
			array( $this, 'admin_area_welcome_panel_widget_statistic' ),
			$this->display_settings_key,
			'admin_area_dashboard_settings_section',
			array(
				''
			)
		);


		register_setting(
			$this->display_settings_key,
			$this->display_settings_key
		);

	}

	public function admin_area_section_description() {


	}

	/*INITIALIZE VISUAL OPTIONS*/

	public function admin_area_intialize_site_options() {


		/* Login Screen section */
		add_settings_section(
			'admin_area_meta_section',
			__( 'Meta Fields', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->site_settings_key
		);

		/*add site generator field*/
		add_settings_field(
			'show_site_generator',
			__( 'Hide site generator', 'cms-admin-area' ),
			array( $this, 'admin_area_site_generator' ),
			$this->site_settings_key,
			'admin_area_meta_section',
			array(
				__( 'Remove wp_generator tag', 'cms-admin-area' )
			)
		);

		/*add RSD link field*/
		add_settings_field(
			'show_rsd_link',
			__( 'Hide RSD link', 'cms-admin-area' ),
			array( $this, 'admin_area_rsd_link' ),
			$this->site_settings_key,
			'admin_area_meta_section',
			array(
				''
			)
		);

		/*add wlwmanifest.xml field*/
		add_settings_field(
			'show_wlwmanifest_link',
			__( 'Hide wlwmanifest.xml file', 'cms-admin-area' ),
			array( $this, 'admin_area_wlwmanifest_link' ),
			$this->site_settings_key,
			'admin_area_meta_section',
			array(
				''
			)
		);

		/*add feed_links field*/
		add_settings_field(
			'show_feed_links',
			__( 'Hide feed links', 'cms-admin-area' ),
			array( $this, 'admin_area_feed_links' ),
			$this->site_settings_key,
			'admin_area_meta_section',
			array(
				''
			)
		);

		/*add feed_links extra field*/
		add_settings_field(
			'show_feed_links_extra',
			__( 'Hide feed links extra', 'cms-admin-area' ),
			array( $this, 'admin_area_feed_links_extra' ),
			$this->site_settings_key,
			'admin_area_meta_section',
			array(
				''
			)
		);

		register_setting(
			$this->site_settings_key,
			$this->site_settings_key
		);
	}

	public function admin_area_intialize_extend_options() {

		/* notify section */
		add_settings_section(
			'admin_area_extend_notify_section',
			__( 'Remove Notifications', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->extend_configuration_key
		);

		/* screen option section */
		add_settings_section(
			'admin_area_extend_screen_help_section',
			__( 'Remove screen options & help tab', 'cms-admin-area' ),
			array( $this, 'admin_area_section_description' ),
			$this->extend_configuration_key
		);


		/*add hide core notyfication field*/
		add_settings_field(
			'hide_core_notyfication',
			__( 'Hide  wordPress core update notice', 'cms-admin-area' ),
			array( $this, 'admin_area_core_notyfication' ),
			$this->extend_configuration_key,
			'admin_area_extend_notify_section',
			array(
				''
			)
		);

		/*add hide plugin notyfication */
		add_settings_field(
			'hide_plugin_notyfication',
			__( 'Hide plugin update notice', 'cms-admin-area' ),
			array( $this, 'admin_area_plugin_notyfication' ),
			$this->extend_configuration_key,
			'admin_area_extend_notify_section',
			array(
				''
			)
		);

		/*add hide theme notyfication */
		add_settings_field(
			'hide_theme_notyfication',
			__( 'Hide theme update notice', 'cms-admin-area' ),
			array( $this, 'admin_area_theme_notyfication' ),
			$this->extend_configuration_key,
			'admin_area_extend_notify_section',
			array(
				''
			)
		);

		/*add screen options field*/
		add_settings_field(
			'hide_screen_options',
			__( 'Hide screen options', 'cms-admin-area' ),
			array( $this, 'admin_area_screen_options' ),
			$this->extend_configuration_key,
			'admin_area_extend_screen_help_section',
			array(
				''
			)
		);

		/*add help links field*/
		add_settings_field(
			'hide_help_links',
			__( 'Hide help tab', 'cms-admin-area' ),
			array( $this, 'admin_area_help_tab' ),
			$this->extend_configuration_key,
			'admin_area_extend_screen_help_section',
			array(
				''
			)
		);

		register_setting(
			$this->extend_configuration_key,
			$this->extend_configuration_key
		);
	}


	/* -----------------------------------------
	   CALLBACK INPUT FIELDS
	----------------------------------------- */


	/*DISPLAY SETTINGS FIELDS*/

	//display logo input
	public function admin_area_logo_display_input( $args ) {
		
		$html = '<div><input type="text" id="amin_area_logo-field" name="' . $this->display_settings_key . '[logo]" value="' . Admin_Area_Helper::getOptionValue( $this->display_settings, 'logo' ) . '" />';

		
		$html .= '<a href="#" class="button admin-area-logo-upload">' . __( 'Upload Logo' ) . '</a></div>';
		$html .= '<div class="logo-outer-admin-area ' . Admin_Area_Helper::getLogoContainerClass( $this->display_settings, 'logo' ) . '"><img src="' . Admin_Area_Helper::getLogoImage( $this->display_settings, 'logo' ) . '" /><a href="#" class="button" rel="' . Admin_Area_Helper::getLogoImage( $this->display_settings, 'logo' ) . '">' . __( 'Delete image' ) . '</a></div>';
		echo $html;

	}


	public function admin_area_style_display_input( $args ) {
		
		$html = '<input type="checkbox" id="field-change-style" name="' . $this->display_settings_key . '[style]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'style' ), false ) . '/>';

		
		$html .= '<label for="field-change-style"> ' . $args[0] . '</label>';

		echo $html;

	}

	public function admin_area_logourl_display_input( $args ) {
		$html = '<input type="text" id="field-logo-url" name="' . $this->display_settings_key . '[logourl]" value="' . Admin_Area_Helper::getOptionValue( $this->display_settings, 'logourl' ) . '"' . '/>';

		
		$html .= '<label for="field-logo-url"> ' . $args[0] . '</label>';

		echo $html;
	}


	public function admin_area_footer_text_input( $args ) {
		$html = '<textarea id="field-footer_text" name="' . $this->display_settings_key . '[footer_text]" >' . Admin_Area_Helper::getOptionValue( $this->display_settings, 'footer_text' ) . '</textarea>';

		
		$html .= '<label for="field-footer_text"> ' . $args[0] . '</label>';

		echo $html;
	}


	public function admin_area_wp_logo_display_input( $args ) {
		
		$html = '<input type="checkbox" id="field-change-wp-logo" name="' . $this->display_settings_key . '[wp-logo]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'wp-logo' ), false ) . '/>';

		
		$html .= '<label for="field-change-wp-logo"> ' . $args[0] . '</label>';

		echo $html;

	}

	public function admin_area_wp_comment_display_input( $args ) {
		
		$html = '<input type="checkbox" id="field-change-comments" name="' . $this->display_settings_key . '[comments]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'comments' ), false ) . '/>';

		
		$html .= '<label for="field-change-comments"> ' . $args[0] . '</label>';

		echo $html;

	}

	/*add admin bar updates field*/
	public function admin_area_wp_updates_display_input( $args ) {
		
		$html = '<input type="checkbox" id="field-change-updates" name="' . $this->display_settings_key . '[updates]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'updates' ), false ) . '/>';

		
		$html .= '<label for="field-change-updates"> ' . $args[0] . '</label>';

		echo $html;

	}


	public function admin_area_wp_new_user_display_input( $args ) {
		
		$html = '<input type="checkbox" id="field-change-new-user" name="' . $this->display_settings_key . '[new-user]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'new-user' ), false ) . '/>';

		
		$html .= '<label for="field-change-new-user"> ' . $args[0] . '</label>';

		echo $html;

	}

	public function admin_area_dashboard_metabox_display_input( $args ) {
		
		$html = '<input type="checkbox" id="field-dashboard_metabox" name="' . $this->display_settings_key . '[dashboard_metabox]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'dashboard_metabox' ), false ) . '/>';

		
		$html .= '<label for="field-dashboard_metabox"> ' . $args[0] . '</label>';

		echo $html;

	}

	public function admin_area_welcome_panel_display_input( $args ) {

		$html = '<input type="checkbox" id="field-welcome-panel" name="' . $this->display_settings_key . '[welcome_panel]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'welcome_panel' ), false ) . '/>';

		
		$html .= '<label for="field-welcome-panel"> ' . $args[0] . '</label>';

		echo $html;

	}


	public function admin_area_welcome_panel_add_metabox_first( $args ) {

		$html = '<input type="checkbox" id="field-add_metabox_first" name="' . $this->display_settings_key . '[add_metabox_first]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'add_metabox_first' ), false ) . '/>';

		
		$html .= '<label for="field-add_metabox_first"> ' . $args[0] . '</label>';

		echo $html;

	}


	public function admin_area_welcome_panel_widget_statistic( $args ) {

		$html = '<input type="checkbox" id="field-widget_statistic" name="' . $this->display_settings_key . '[widget_statistic]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->display_settings, 'widget_statistic' ), false ) . '/>';

		
		$html .= '<label for="field-widget_statistic"> ' . $args[0] . '</label>';

		echo $html;

	}

	/*SITE SETTINGS FIELDS*/

	public function admin_area_site_generator( $args ) {
		$html = '<input type="checkbox" id="field-site_generator" name="' . $this->site_settings_key . '[wp_generator]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->site_settings, 'wp_generator' ), false ) . '/>';

		
		$html .= '<label for="field-site_generator"> ' . $args[0] . '</label>';

		echo $html;
	}

	public function admin_area_rsd_link( $args ) {
		$html = '<input type="checkbox" id="field-site_generator" name="' . $this->site_settings_key . '[rsd_link]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->site_settings, 'rsd_link' ), false ) . '/>';

		
		$html .= '<label for="field-rsd_link"> ' . $args[0] . '</label>';

		echo $html;
	}

	public function admin_area_wlwmanifest_link( $args ) {
		$html = '<input type="checkbox" id="field-wlwmanifest_link" name="' . $this->site_settings_key . '[wlwmanifest_link]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->site_settings, 'wlwmanifest_link' ), false ) . '/>';

		
		$html .= '<label for="field-wlwmanifest_link"> ' . $args[0] . '</label>';

		echo $html;
	}


	public function admin_area_feed_links( $args ) {
		$html = '<input type="checkbox" id="field-feed_links" name="' . $this->site_settings_key . '[feed_links]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->site_settings, 'feed_links' ), false ) . '/>';

		
		$html .= '<label for="field-feed_links"> ' . $args[0] . '</label>';

		echo $html;
	}

	public function admin_area_feed_links_extra( $args ) {
		$html = '<input type="checkbox" id="field-feed_links_extra" name="' . $this->site_settings_key . '[feed_links_extra]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->site_settings, 'feed_links_extra' ), false ) . '/>';

		
		$html .= '<label for="field-feed_links_extra"> ' . $args[0] . '</label>';

		echo $html;
	}

	/*EXTEND SETTINGS FIELDS*/

	public function admin_area_core_notyfication( $args ) {
		$html = '<input type="checkbox" id="field-core_notyfication" name="' . $this->extend_configuration_key . '[core_notyfication]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->extend_configuration, 'core_notyfication' ), false ) . '/>';

		
		$html .= '<label for="field-core_notyfication"> ' . $args[0] . '</label>';

		echo $html;
	}


	public function admin_area_plugin_notyfication( $args ) {
		$html = '<input type="checkbox" id="field-plugin_notyfication" name="' . $this->extend_configuration_key . '[plugin_notyfication]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->extend_configuration, 'plugin_notyfication' ), false ) . '/>';

		
		$html .= '<label for="field-plugin_notyfication"> ' . $args[0] . '</label>';

		echo $html;
	}

	public function admin_area_theme_notyfication( $args ) {
		$html = '<input type="checkbox" id="field-theme_notyfication" name="' . $this->extend_configuration_key . '[theme_notyfication]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->extend_configuration, 'theme_notyfication' ), false ) . '/>';

		
		$html .= '<label for="field-theme_notyfication"> ' . $args[0] . '</label>';

		echo $html;
	}


	public function admin_area_screen_options( $args ) {
		$html = '<input type="checkbox" id="field-screen_options" name="' . $this->extend_configuration_key . '[screen_options]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->extend_configuration, 'screen_options' ), false ) . '/>';

		
		$html .= '<label for="field-screen_options"> ' . $args[0] . '</label>';

		echo $html;
	}

	public function admin_area_help_tab( $args ) {
		$html = '<input type="checkbox" id="field-help_tab" name="' . $this->extend_configuration_key . '[help_tab]" value="1" ' . checked( 1, Admin_Area_Helper::getOptionValue( $this->extend_configuration, 'help_tab' ), false ) . '/>';

		
		$html .= '<label for="field-help_tab"> ' . $args[0] . '</label>';

		echo $html;
	}
}
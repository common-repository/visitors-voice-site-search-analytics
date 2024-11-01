<?php

	/*
	* 
	* Plugin Name: Visitors Voice Site Search Analytics 
	* Plugin URI: http://en.visitorsvoice.com/site-search-analytics-for-wordpress/
	* Description: The Visitors Voice plugin gives suggestions based on user behavior.
	* Author: Pontus Rosin <pontus.rosin@adeptic.se>, Adeptic Technologies AB.
	* Version: 1.1.0
	* Author URI: http://visitorsvoice.com
	* @license: GPLv2 or later
	*
	*/

	define( 'VISITORSVOICE_VERSION', '1.1.0' );
	define( 'VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST', 'vv_pagesuggestionlist');		# DATABASE TABLE THAT STORES ALL SUGGESTIONS
	define( 'VISITORSVOICE_TABLENAME_PRIORITIZEDPAGES', 'vv_prioritizedpages');			# DATABASE TABLE THAT STORES ALL SUGGESTIONS
	define( 'BASE_URL', get_bloginfo('url'));
	define( 'PLUGIN_URL', get_bloginfo('url').'/wp-content/plugins/visitors-voice-site-search-analytics/');
	
	function VisitorsVoice_on_activation()
	{
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		global $wpdb;
		if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
		
		$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
		$table_prioritizedpages = $wpdb->prefix . VISITORSVOICE_TABLENAME_PRIORITIZEDPAGES;
		
		// This tables stores API call RefinementsToUrl
		$sql = "CREATE TABLE $table_suggestionlist (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				post_id mediumint(9) DEFAULT '0' NOT NULL,
				status mediumint(2) DEFAULT '0' NOT NULL,
				searchterm text NOT NULL,
				refinement text NOT NULL,
				count mediumint(4) DEFAULT '0' NOT NULL,
				time date DEFAULT '0000-00-00' NOT NULL,
				url VARCHAR(255) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
		)DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		// This tables stores API call RefinementsToUrlList
		$sql = "CREATE TABLE $table_prioritizedpages (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				post_id mediumint(9) DEFAULT '0' NOT NULL,
				status mediumint(2) DEFAULT '0' NOT NULL,
				time date DEFAULT '0000-00-00' NOT NULL,
				url VARCHAR(255) DEFAULT '' NOT NULL,
				count mediumint(4) DEFAULT '0' NOT NULL,
				UNIQUE KEY id (id)
		)DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		add_option( 'visitorsvoice_version', VISITORSVOICE_VERSION );
		add_option( 'visitorsvoice_next_call_page_suggestions', '0000' );
		add_option( 'visitorsvoice_api_key', '0' );
		add_option( 'visitorsvoice_api_authorized', '0' );
		add_option( 'visitorsvoice_fixed_suggestions_expire', '0' );
		add_option( 'visitorsvoice_days_before_check_for_suggestion', '0' );
		add_option( 'visitorsvoice_max_nr_prioritized_pages', '0' );
		add_option( 'visitorsvoice_internal_search_url', '' );
		add_option( 'visitorsvoice_google_url', 'http://www.google.se/?#q=##KEYWORD##' );
		add_option( 'visitorsvoice_manual_keywords_field', '0');
		add_option( 'visitorsvoice_url_context_days_back', '0');
	}

	function VisitorsVoice_on_deactivation()
	{
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );

		delete_option( 'visitorsvoice_version' );
		delete_option( 'visitorsvoice_next_call_page_suggestions');
		delete_option( 'visitorsvoice_api_key' );
		delete_option( 'visitorsvoice_api_authorized' );
		delete_option( 'visitorsvoice_fixed_suggestions_expire' );
		delete_option( 'visitorsvoice_days_before_check_for_suggestion' );
		delete_option( 'visitorsvoice_max_nr_prioritized_pages', '0' );
		delete_option( 'visitorsvoice_internal_search_url' );
		delete_option( 'visitorsvoice_google_url' );
		delete_option( 'visitorsvoice_manual_keywords_field' );
		delete_option( 'visitorsvoice_url_context_days_back' );
		
		global $wpdb;
		if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
		
		$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
		$table_prioritizedpages = $wpdb->prefix . VISITORSVOICE_TABLENAME_PRIORITIZEDPAGES;
		
		$wpdb->query("DROP TABLE {$table_suggestionlist}");
		$wpdb->query("DROP TABLE {$table_prioritizedpages}");
	}

	function VisitorsVoice_on_uninstall()
	{
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		check_admin_referer( 'bulk-plugins' );

		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
			return;
	}

	register_activation_hook(   __FILE__, 'VisitorsVoice_on_activation' );
	register_deactivation_hook( __FILE__, 'VisitorsVoice_on_deactivation' );
	register_uninstall_hook(    __FILE__, 'VisitorsVoice_on_uninstall' );	

	require_once 'class-visitorsvoice-client.php';
	require_once 'class-visitorsvoice-error.php';
	require_once 'class-visitorsvoice-metabox.php';
	require_once 'class-visitorsvoice-page-suggestions-page.php';
	require_once 'class-visitorsvoice-plugin.php';

	function visitorsvoice_init() {
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( 'visitorsvoice', false, $plugin_dir.'/languages'  );
	}
	
	VisitorsVoicePlugin::get_instance();
	if ( is_admin() ) {
		add_action( 'load-post.php', 'VisitorsVoiceMetabox::get_instance' );
		add_action( 'plugins_loaded', 'visitorsvoice_init' );
	}

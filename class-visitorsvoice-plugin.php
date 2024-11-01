<?php

	/**
	* The Visitors Voice Wordpress Plugin
	*
	* This class handles all calls for the API, all database calls and render the admin menu
	*
	* @author  Pontus Rosin <pontus@Visitors Voice.com>
	* @since 1.0
	* @license: GPLv2 or later
	* @license URI: http://www.gnu.org/licenses/gpl-2.0.html
	*
	*/
	
	class VisitorsVoicePlugin {

		private static $instance = null;
	
		private $client = NULL;
		
		private $api_key = NULL;
		private $api_authorized = false;
				
		private $fixed_suggestions_expire = NULL;
		private $days_before_check_for_suggestion = NULL;
		private $max_nr_prioritized_page = NULL;
		
		private $url_context_SearchTermsToUrl = NULL;
		private $url_context_SearchTermsFromUrl = NULL;
		private $url_context_KeywordsToUrl = NULL;
		
		private $keywords_field = NULL;
		
		private $internal_search_url = '';
		private $google_url = '';
		
		private $url_context_days_back = NULL;
		
		private $manual_keywords_field = '';
		
		private $error = NULL;
		
		public static function get_instance() {
	 
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		private function __construct() {

			add_action('admin_init', array( $this, 'enqueue' ) ); 
			add_action( 'admin_menu', array( $this, 'visitorsvoice_menu' ) );
			add_action( 'admin_init', array( $this, 'initialize_admin_screen' ) );
			
			$this->api_key = get_option( 'visitorsvoice_api_key' );
			$this->api_authorized = get_option( 'visitorsvoice_api_authorized' );						
			$this->days_before_check_for_suggestion = get_option( 'visitorsvoice_days_before_check_for_suggestion' );
			$this->fixed_suggestions_expire = get_option( 'visitorsvoice_fixed_suggestions_expire' );
			$this->max_nr_prioritized_page = get_option( 'visitorsvoice_max_nr_prioritized_pages' );
			$this->internal_search_url = get_option( 'visitorsvoice_internal_search_url' );
			$this->google_url = get_option( 'visitorsvoice_google_url' );			
			$this->keywords_field = get_option('visitorsvoice_manual_keywords_field');
			$this->url_context_days_back = get_option( 'visitorsvoice_url_context_days_back' );
			
			$this->client = new VisitorsVoiceClient;
			$this->client->set_api_key( $this->api_key );
		
		}
		
		/**
		* Initialize the Visitors Voice plugin's admin screen
		*
		* Performs most of the mechanical work of the admin settings screen. Gets/Sets Option values based on user input, and binds
		* functions ot different actions that are triggered in the admin area.
		*/
		public function initialize_admin_screen() {
			if ( current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', array( $this, 'error_notice' ) );

				if( isset( $_POST['action'] ) ) {
					if( $_POST['action'] == 'visitorsvoice_set_api_key' ) {
						check_admin_referer( 'visitorsvoice-nonce' );
						$this->api_key = sanitize_text_field( $_POST['api_key'] );						
					} elseif( $_POST['action'] == 'visitorsvoice_configure' ) {
						check_admin_referer( 'visitorsvoice-nonce' );
						
						$fixed_suggestions_expire = sanitize_text_field( $_POST['fixed_suggestions_expire'] );
						if(is_numeric ( $fixed_suggestions_expire ) && $fixed_suggestions_expire > 0 && $fixed_suggestions_expire < 366){
							update_option( 'visitorsvoice_fixed_suggestions_expire', $fixed_suggestions_expire );
							$this->fixed_suggestions_expire = $fixed_suggestions_expire;
						}
						
						$days_before_check_for_suggestion = sanitize_text_field( $_POST['days_before_check_for_suggestion'] );
						if(is_numeric ( $this->days_before_check_for_suggestion ) && $days_before_check_for_suggestion > 0 && $days_before_check_for_suggestion < 366){
							update_option( 'visitorsvoice_days_before_check_for_suggestion', $days_before_check_for_suggestion );
							$this->days_before_check_for_suggestion = $days_before_check_for_suggestion;
						}

						$max_nr_prioritized_page = sanitize_text_field( $_POST['max_nr_prioritized_page'] );
						if(is_numeric ( $max_nr_prioritized_page ) && $max_nr_prioritized_page > 0 && $max_nr_prioritized_page < 100){
							update_option( 'visitorsvoice_max_nr_prioritized_pages', $max_nr_prioritized_page );
							$this->max_nr_prioritized_page = $max_nr_prioritized_page;
						}
						
						$url_context_days_back = sanitize_text_field( $_POST['url_context_days_back'] );
						if(is_numeric ( $url_context_days_back ) && $url_context_days_back > 0 && $url_context_days_back < 180){
							update_option( 'visitorsvoice_url_context_days_back', $url_context_days_back );
							$this->url_context_days_back = $url_context_days_back;
						}
						
						$internal_search_url = sanitize_text_field( $_POST['internal_search_url']);
						if (filter_var( $internal_search_url , FILTER_VALIDATE_URL) !== false){
							update_option( 'visitorsvoice_internal_search_url', $internal_search_url );
							$this->internal_search_url = $internal_search_url;
						}
					
						$google_url = sanitize_text_field( $_POST['google_url']);
						if (filter_var( $google_url , FILTER_VALIDATE_URL) !== false){
							update_option( 'visitorsvoice_google_url', $google_url );
							$this->google_url = $google_url;
						}
						
						if (isset($_POST['should_manage_keywords']) && $_POST['should_manage_keywords'] === 'yes')
						{
							update_option( 'visitorsvoice_should_manage_keywords', 'yes' );
						}
						else
						{
							update_option( 'visitorsvoice_should_manage_keywords', 'no');
						}
						
						$manual_keywords_field = sanitize_text_field( $_POST['manual_keywords_field'] );
						update_option('visitorsvoice_manual_keywords_field', $manual_keywords_field);
						
						
					} elseif( $_POST['action'] == 'visitorsvoice_settings' ) {
						check_admin_referer( 'visitorsvoice-nonce' );
						
						$internal_search_url = sanitize_text_field( $_POST['internal_search_url']);
						if (filter_var( $internal_search_url , FILTER_VALIDATE_URL) !== false){
							update_option( 'visitorsvoice_internal_search_url', $internal_search_url );
							$this->internal_search_url = $internal_search_url;
						}
					
						$google_url = sanitize_text_field( $_POST['google_url']);
						if (filter_var( $google_url , FILTER_VALIDATE_URL) !== false){
							update_option( 'visitorsvoice_google_url', $google_url );
							$this->google_url = $google_url;
						}
						
						$manual_keywords_field = sanitize_text_field( $_POST['manual_keywords_field'] );
						update_option('visitorsvoice_manual_keywords_field', $manual_keywords_field);
						
					} elseif( $_POST['action'] == 'visitorsvoice_clear_config' ) {
						check_admin_referer( 'visitorsvoice-nonce' );
						update_option( 'visitorsvoice_api_key', '0' );
						update_option( 'visitorsvoice_api_authorized', '0' );
						update_option( 'visitorsvoice_next_call_page_suggestions', '0000');
						update_option( 'visitorsvoice_fixed_suggestions_expire', '0' );
						update_option( 'visitorsvoice_days_before_check_for_suggestion', '0' );
						update_option( 'visitorsvoice_max_nr_prioritized_pages', '0' );
						update_option( 'visitorsvoice_internal_search_url', '' );
						update_option( 'visitorsvoice_google_url', '' );					
						update_option( 'visitorsvoice_manual_keywords_field', '');
						update_option( 'visitorsvoice_url_context_days_back', '0');
						
						global $wpdb;
						if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
						
						$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
						$table_prioritizedpages = $wpdb->prefix . VISITORSVOICE_TABLENAME_PRIORITIZEDPAGES;
						
						$wpdb->query("TRUNCATE TABLE {$table_suggestionlist}");
						$wpdb->query("TRUNCATE TABLE {$table_prioritizedpages}");
		
					} elseif( $_POST['action'] == 'visitorsvoice_update_oldpage' ) {
						check_admin_referer( 'visitorsvoice-nonce' );
						$this->update_suggestion_status_by_url(sanitize_text_field( $_POST['url'] ), 1);
					} 
				}

				$this->client = new VisitorsVoiceClient;
				$this->client->set_api_key( $this->api_key );
				$this->check_api_authorized();
				if( ! $this->api_authorized )
					return;
			}
		}

		/**
		* Display an error message in the dashboard if there was an error in the plugin
		*
		* @return null
		*/
		public function error_notice() {
			if( ! is_admin() )
				return;
		  if( isset( $this->error ) && ! empty( $this->error ) ) {
		  	echo '<div class="error"><p>' . $this->error . '</p></div>';
		  }
		}
		
		/**
		* Check whether or not the provided API key is valid or not
		* API key is being used for retrieving data from Visitors Voice API - api.visitorsvoice.com
		*
		* @return null
		*/
		private function check_api_authorized() {
			if( ! is_admin() )
				return;
			if( $this->api_authorized )
				return;
			if( $this->api_key && strlen( $this->api_key ) > 0 ) {
				try {
					$this->api_authorized = $this->client->api_authorized();
					update_option( 'visitorsvoice_api_key', $this->api_key  );
					update_option( 'visitorsvoice_api_authorized', $this->api_authorized );
				} catch( VisitorsVoiceError $e ) {
					$this->api_authorized = false;
				}
			} else {
				$this->api_authorized = false;
			}
		}
		
		/**
		* Check whether the plugin is fully configured or not
		*
		* @return bool
		*/
		public function check_configured() {
			
			if( is_admin() &&
				$this->api_authorized && 
				$this->fixed_suggestions_expire > 0 && 
				$this->days_before_check_for_suggestion > 0 && 
				$this->max_nr_prioritized_page > 0 &&
				$this->internal_search_url <> '' &&
				$this->google_url <> '' &&
				$this->keywords_field <> '' &&
				$this->url_context_days_back <> 0 ) return true;

			else return false;
		}
		
		
		/**
		* Get a new list of pages from API with all suggestions ordered by number of suggestions from Visitors Voice API and store it in database
		*
		* @return null
		*/			
		public function get_new_page_suggestions_list() {
			if( ! is_admin() )
				return;
				
			try {
				$resp = json_decode($this->client->get_new_page_suggestions_list($this->max_nr_prioritized_page), true);
				
				global $wpdb;
				if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
				
				$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
				$table_prioritizedpages = $wpdb->prefix . VISITORSVOICE_TABLENAME_PRIORITIZEDPAGES;
				
				$today = date("Y-m-d");
				$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));
				
				foreach($resp as $rec)
				{
					$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));
					$myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_suggestionlist where url=%s and time > %s and status = %s", $rec['Url'], $old, 1 ));
					
					if(count($myrows) == 0){					
						$postid = url_to_postid( $rec['Url'] );
						if($postid>0){
							$rows_affected = $wpdb->insert( $table_prioritizedpages, array( 'url' => $rec['Url'], 'count' => $rec['Count'], 'post_id' => $postid, 'time' =>  date('Y-m-d')) );									
						}
					}
				}
				return;
			} catch( VisitorsVoiceError $e ) {
				return;
			}
		}
		
		/**
		* Get a list of pages with all suggestions ordered by number of suggestions from current month
		*
		* @return array of objects
		*/			
		public function get_page_suggestions_list() {
			if( ! is_admin() )
				return;

			global $wpdb;
			if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
			
			$table_prioritizedpages = $wpdb->prefix . VISITORSVOICE_TABLENAME_PRIORITIZEDPAGES;
			
			$today = date('Y-m-d');
			$first_day = date("ymd", strtotime($today." -".$this->fixed_suggestions_expire." days"));
			$last_day = date("ymd", strtotime($today." +".$this->days_before_check_for_suggestion." days"));
			
			return $wpdb->get_results( $wpdb->prepare("SELECT id, post_id, url, count FROM $table_prioritizedpages where time between %s and %s and post_id <> 0 and status=0 order by count desc", $first_day, $last_day ));
				
		}
		
		/**
		* Update status of a prioritized page by id
		*
		* @return null
		*/			
		public function update_page_by_id($id, $chk) {
			if( ! is_admin() )
				return;
				
			global $wpdb;
			if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
			
			$table_prioritizedpages = $wpdb->prefix . VISITORSVOICE_TABLENAME_PRIORITIZEDPAGES;
			
			$today = date("Y-m-d");
			$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));

			$wpdb->query($wpdb->prepare("UPDATE $table_prioritizedpages SET status='$chk' WHERE post_id=%s and time between %s and %s", $id, $old, $today));
				
		}
		
		/**
		* Get a list of pages with all suggestions ordered by number of suggestions from Visitors Voice API and store it in database
		*
		* @return null
		*/			
		public function get_new_page_suggestions($permalink) {
			if( ! is_admin() )
				return;
			try {
				
				$resp = json_decode($this->client->get_new_page_suggestions($permalink), true);
				
				global $wpdb;
				if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
				
				$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
				
				$today = date("Y-m-d");
				$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));
				
				$pageid = url_to_postid( $permalink );
				
				foreach($resp as $record)
				{
					// check that the suggestion doesnt exist within today and fixed suggestion expire date
					$myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_suggestionlist WHERE url=%s AND searchterm=%s AND refinement=%s AND time > %s", $permalink, $record['SearchTerm'], $record['Refinement'], $old ));
					
					if(count($myrows) == 0) {	
						$rows_affected = $wpdb->insert( $table_suggestionlist, array( 'url' => $permalink, 'post_id' => $pageid, 'searchterm' => $record['SearchTerm'], 'refinement' => $record['Refinement'], 'count' => $record['Count'], 'time' =>  date('Y-m-d')) );									
					
					} else if($myrows[0]->count< $record['Count']){
						$wpdb->query($wpdb->prepare("UPDATE $table_suggestionlist SET count=%s WHERE url=%s AND searchterm=%s AND refinement=%s and time > %s", $record['Count'], $permalink, $record['SearchTerm'], $record['Refinement'], $old ));
					}
				}
				return;
			} catch( VisitorsVoiceError $e ) {
				return;
			}
		}
		
		/**
		* Get suggestions for a page if there are any
		*
		* @return array of objects
		*/
		public function get_suggestions_for_page($permalink) {
			if( ! is_admin() )
				return;
			
			// check if new call for suggestions needs to be made for this page
			$newcall = get_option( 'visitorsvoice_next_call_page_suggestions' );
			$days_before_check_for_suggestion = get_option( 'visitorsvoice_days_before_check_for_suggestion' );
			$lastcall = date('ymd',(strtotime ( '-'.$days_before_check_for_suggestion.' day' , strtotime ( $newcall) ) ));
			
			global $wpdb;
			if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
			
			$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
			
			$myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_suggestionlist WHERE url=%s AND time >= %s LIMIT 1", $permalink, $lastcall ));
			
			if(count($myrows) == 0)
				$this->get_new_page_suggestions($permalink);
			
			$today = date("Y-m-d");
			$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));
			
			return $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_suggestionlist where url=%s and time between %s and %s ORDER BY count desc", $permalink, $old, $today ));
		}

		/**
		* Update status for a pages suggestions
		*
		* @return null
		*/
		public function update_suggestion_status_by_id($id, $chk){

			$this->update_page_by_id($id, $chk);
			
			global $wpdb;
			if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
			
			$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;

			$today = date("Y-m-d");
			$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));

			$permalink = get_permalink( $id );
			$wpdb->query($wpdb->prepare("UPDATE $table_suggestionlist SET status='$chk' WHERE url=%s and time between %s and %s", $permalink, $old, $today));
			
		}
		
		/**
		* Update status for a pages suggestions
		*
		* @return null		
		*/
		public function update_suggestion_status_by_url($permalink, $chk){

			$id = url_to_postid( $permalink );
			$this->update_page_by_id($id, $chk);
			
			global $wpdb;
			if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
			
			$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
			
			$today = date("Y-m-d");
			$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));

			$wpdb->query($wpdb->prepare("UPDATE $table_suggestionlist SET status='$chk' WHERE url=%s and time between %s and %s", $permalink, $old, $today));
			
		}
		
		/**
		* Update status for a pages suggestions
		*
		* @return null		
		*/
		public function update_suggestion_status_by_searchterm($id, $searchterm, $chk){

			global $wpdb;
			if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
			
			$table_suggestionlist = $wpdb->prefix . VISITORSVOICE_TABLENAME_PAGESUGGESTIONLIST;
			
			$today = date("Y-m-d");
			$old = date("Y-m-d", strtotime($today." -".$this->fixed_suggestions_expire." days"));

			$wpdb->query($wpdb->prepare("UPDATE $table_suggestionlist SET status='$chk' WHERE post_id= %s and searchterm=%s and time between %s and %s", $id, $searchterm, $old, $today));
			
			// Check if page in prioritized pages should be updated to
			$rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_suggestionlist where post_id=%s and time between %s and %s and status=0", $id, $old, $today ));
			if(count($rows)==0)
				$this->update_page_by_id($id, $chk);
		}
		
		/**
		* Get URL Context for a URL (SearchTermsFromUrl, SearchTermsToUrl & KeywordsToUrl) from Visitors Voice API and store it in database
		*
		* @return bool
		*/			
		public function get_url_context($url) {
			if( ! is_admin() )
				return false;
			if(!empty($url)){
				try {
					$url_context = json_decode($this->client->get_url_context($url, $this->url_context_days_back), true);
					if(count($url_context) == 0)
						return false;
					$this->url_context_SearchTermsToUrl = $url_context['SearchTermsToUrl'];
					$this->url_context_SearchTermsFromUrl = $url_context['SearchTermsFromUrl'];
					$this->url_context_KeywordsToUrl = $url_context['KeywordsToUrl'];
										
					return true;
				} catch( VisitorsVoiceError $e ) {
					return false;
				}
			} else return false;
		}
		
		/**
		* Get searchterms_to_url
		*
		* @return array
		*/			
		public function  get_searchterms_to_url(){
			if( ! is_admin() )
				return;
			return $this->url_context_SearchTermsToUrl;
		}
		
		/**
		* Get searchterms_from_url
		*
		* @return array
		*/			
		public function get_searchterms_from_url() {
			if( ! is_admin() )
				return;
			return $this->url_context_SearchTermsFromUrl;
		}
		
		/**
		* Get keywords_to_url
		*
		* @return array
		*/			
		public function get_keywords_to_url() {
			if( ! is_admin() )
				return;
			return $this->url_context_KeywordsToUrl;
		}
		
		/**
		* Get api_key
		*
		* @return string
		*/			
		public function get_api_key() {
			if( ! is_admin() )
				return;
			return $this->client->get_api_key();
		}
		
		/**
		* Get keywords_field;
		*
		* @return string
		*/
		public function get_keywords_field()
		{
			return $this->keywords_field;
		}

		/**
		* Get value of keywords_field;
		*
		* @return string
		*/		
		public function get_keywords_field_value($post_id)
		{
			if ($this->get_keywords_field())
			{
				$text = trim(get_post_meta($post_id, $this->get_keywords_field(), true));
				return strlen($text) ? explode(',', $text) : array();
			}
		}
		/**
		* Update value of keywords_field;
		*
		* @return null
		*/
		public function set_keywords_field_value($post_id, $keywords = array())
		{
			if ($this->get_keywords_field())
			{
				foreach ($keywords as $key => $value) {
					if (!trim($value))
						unset($keywords[$key]);
				}
				update_post_meta($post_id, $this->get_keywords_field(), implode(',', $keywords));
			}
		}
		
		/**
		* Includes the Visitors Voice plugin's admin page
		*
		* @return null
		*/
		public function Visitorsvoice_admin_page() {
			include( 'visitorsvoice-admin-page.php' );
		}
		
		/**
		* Includes the Visitors Voice plugin's page suggestions list page
		*
		* @return null
		*/
		public function Visitorsvoice_page_suggestions_list() {
			
			if( ! is_admin() )
				return;
			if( $this->api_authorized )	{
				if($this->fixed_suggestions_expire  > 0 && $this->days_before_check_for_suggestion > 0) {
					$visitorsvoicepagesuggestionspage = VisitorsVoice_Page_Suggestions_Page::get_instance();
					$visitorsvoicepagesuggestionspage->display_page_suggestion_list();
				} else {
					$this->display_non_authorized();
				}
			} else {
				$this->display_non_authorized();
			}
		}
		
		/**
		* Includes the Visitors Voice plugin's page suggestions list page
		*
		* @return null
		*/
		public function Visitorsvoice_search_terms_list() {
			if( ! is_admin() )
				return;
			if($this->check_configured()) {
				echo '<iframe src="http://service.visitorsvoice.com/?token=VIEW-'.$this->client->get_api_key().'" width="95%" height="950" frameBorder="0"></iframe>';
			} else {
				$this->display_non_authorized();
			}
		}
		
		/**
		* Includes the Visitors Voice plugin's total search quality report
		*
		* @return null
		*/
		public function Visitorsvoice_total_search_quality() {
			if( ! is_admin() )
				return;
			if($this->check_configured()) {
				echo '<iframe src="http://service.visitorsvoice.com/OverViewGraph/?token=VIEW-'.$this->client->get_api_key().'" width="95%" height="950" frameBorder="0"></iframe>';
			} else {
				$this->display_non_authorized();
			}
		}
		
		/**
		* Displays a page for administrators that has not yet set API key, User key or configured the plugin yet
		* Also calls for a latest page suggestion list whenever there is a new month
		* This method is called by the admin_menu action
		*
		* @return null
		*/
		public function display_non_authorized() {

			echo '<div class="wrap">';
			echo '<div id="icon-visitorsvoice-page" ><br></div>';
			echo '<h2 class="visitorsvoice-header">'. __("Visitors Voice Plugin", "visitorsvoice").'</h2>';
			echo '</br>';
			echo __("The Visitors Voice plugin needs to be configured, before you can continue. Configure Visitors Voice plugin here <a href='admin.php?page=visitorsvoice'>here</a>.", "visitorsvoice");
			echo '</br></br>';
			echo __('If you don\'t have a Visitors Voice account, use our <a href="http://en.visitorsvoice.com/signup/" target="_new"> free trial</a>', 'visitorsvoice');
			echo '</br></br>';
			echo __('Read more about <a href="http://en.visitorsvoice.com/visitors-voice-for-wordpress/" target="_new">Visitors Voice for WordPress</a>.', 'visitorsvoice');
			echo '</div>';
		}
		
		/**
		* Creates a menu in the Wordpress admin for the Visitors Voice plugin
		* Also calls for a latest page suggestion list whenever there is a new month
		* This method is called by the admin_menu action
		*
		* @return null
		*/
		public function Visitorsvoice_menu() {
		
			// check if a new API call should be made for a list of pages with suggestions
			if(get_option( 'visitorsvoice_next_call_page_suggestions' ) < date('ymd')){
				if($this->check_configured()) {
					$this->get_new_page_suggestions_list();
					$today = date("ymd");
					$new = date("ymd", strtotime($today." +".$this->days_before_check_for_suggestion." days"));
					update_option( 'visitorsvoice_next_call_page_suggestions', $new );
				}
			} 
			
			$rows = $this->get_page_suggestions_list();
			
			$pages = array();
			$page = add_menu_page(
				'Visitors Voice',
				'Visitors Voice'.'<span class="update-plugins count-'.count($rows).'"><span class="plugin-count">'.count($rows).'</span></span>', 
				'manage_options',
				"visitorsvoice_page_suggestions_list",
				array( $this, 'visitorsvoice_page_suggestions_list' ), 
				plugins_url( 'assets/visitorsvoice_logo_menu.png', __FILE__ ) );
			
			$pages[] = $page;
			
			$page = add_submenu_page(
				'visitorsvoice_page_suggestions_list',
				'Visitors Voice search term list', 					
				__("Searchterm list","visitorsvoice"), 
				'manage_options',
				"visitorsvoice_search_terms_list",
				array( $this, 'visitorsvoice_search_terms_list' ));

			$pages[] = $page;
			
			$page = add_submenu_page(
				'visitorsvoice_page_suggestions_list',
				'Visitors Voice total search quality', 					
				__("Total search quality","visitorsvoice"), 
				'manage_options',
				"visitorsvoice_total_search_quality",
				array( $this, 'visitorsvoice_total_search_quality' ));

			$pages[] = $page;
			
			$page = add_submenu_page(
				'visitorsvoice_page_suggestions_list',
				'Visitors Voice settings', 					
				__("Settings","visitorsvoice"), 
				'manage_options',
				"visitorsvoice",
				array( $this, 'visitorsvoice_admin_page' ));

			$pages[] = $page;
			
			do_action( 'visitorsvoice_admin_menu', $pages );
			
		}
		
		public function enqueue() {
			wp_enqueue_style(  'visitorsvoice-suggestionbox', plugins_url( 'assets/suggestionbox.css',   __FILE__ )             );
			wp_enqueue_style(  'visitorsvoice-jquery-datatables-custom', plugins_url( 'assets/custom_styles.css',   __FILE__ )             );
			wp_enqueue_script( 'visitorsvoice-jquery-datatables', 	plugins_url( 'assets/DataTables/media/js/jquery.dataTables.min.js', __FILE__), array('jquery'));
			wp_enqueue_style(  'visitorsvoice-metabox-table', 		plugins_url( 'assets/metabox_table.css', __FILE__ ));
			wp_enqueue_script( 'visitorsvoice-init-pagesuggestionlist-tables', 	plugins_url( 'assets/init_pagesuggestionlist_table.js', __FILE__));
		}
	}
	

<?php
	/**
	* Page suggestion list class for the Visitors Voice plugin
	* 
	* @author  Pontus Rosin <pontus@visitorsvoice.com>
	* @since 1.0
	* @license: GPLv2 or later
	* @license URI: http://www.gnu.org/licenses/gpl-2.0.html
	*/

	class VisitorsVoice_Page_Suggestions_Page {
	
		private static $instance = null;
		
		public static function get_instance() {
	 
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	
		public function __construct() {}
		
		public function display_page_suggestion_list() {
		
			if(!class_exists('VisitorsVoicePlugin'))
				require_once( ABSPATH . 'wp-content/plugins/visitors-voice/class-visitorsvoice-plugin.php' );
			
			$visitorsvoiceplugin = VisitorsVoicePlugin::get_instance();
			$rows = $visitorsvoiceplugin->get_page_suggestions_list();
			$url_context_days_back = get_option( 'visitorsvoice_url_context_days_back' );
			$fixed_suggestions_expire = get_option( 'visitorsvoice_fixed_suggestions_expire' );
			$days_before_check_for_suggestion = get_option( 'visitorsvoice_days_before_check_for_suggestion' );
	
			
			$pages = array();
			$oldpages = array();

			echo '<div class="wrap">';
			echo '<h1>'.__("Visitors Voice - Site Search Statitics that matters", "visitorsvoice").'</h1>';
			echo '<h2>'.__("Are you an editor?", "visitorsvoice").'</h2>';
			_e("When you edit a page Visitors Voice will display which search terms the users searched for before they entered the page.", "visitorsvoice");
			
			echo "<br><br>";
			
			_e("Visitors Voice will also show which search terms the users used leaving your page. Together this might give you a better understanding of what is asked from your page.", "visitorsvoice");
			
			echo "<br><br>";
			
			_e("Number of days back Visitors Voice get statistics for: ", "visitorsvoice");
			
			echo " ".$url_context_days_back."<br><br>";
			
			echo '<h2>'.__("Are you the Site Search owner?", "visitorsvoice").'</h2>';

			_e("Visitors Voice suggests keywords for pages based on if there are users refining their searches before they click on the page in the search engine result list.", "visitorsvoice");
			
			echo "<br><br>";

			_e("Number of days before Visitors Voice checks for new suggestions: ", "visitorsvoice");
			echo " ".$days_before_check_for_suggestion."<br><br>";
			
			echo "<strong>";
			_e("The list below shows pages with keyword suggestions:", "visitorsvoice");
			echo "</strong>";
			
			echo "<br><br>";
						
			if(empty($rows))
			{
				_e("Congratulations, all suggestions have been fixed.", "visitorsvoice");
				
				return;
			}
			$count = 0;
			foreach ( $rows as $row ) 
			{
				if($row->post_id > 0){
					$pages[$count] = get_post($row->post_id);
					$count++;
				} else {
					$oldpages[] = $row;
				}
			}
?>
			
			<table class="display" id="page_suggestion_list_table">
				<thead>
					<tr>
						<th><?php _e("Title", "visitorsvoice"); ?></th>
						<th><?php _e("Author", "visitorsvoice"); ?></th>
						<th><?php _e("Pagestatus", "visitorsvoice"); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
					$i=1;
					foreach($pages as $page){
					
						$editlink  = BASE_URL.'/wp-admin/post.php?action=edit&post='.(int)$page->ID;
						$user_info = get_userdata(stripslashes($page->post_author));

						if($i % 2 === 0) echo '<tr class="even gradeX">';
						else echo '<tr class="odd gradeX">';
						
						echo '<td><a href="'.$editlink.'">'.$page->post_title.'</a></td>';						
						echo '<td>'.$user_info->user_login.'</td>';
						echo '<td>'.$page->post_status.'</td>';
						echo '</tr>';
						
						$i++;
					}			
?>
				</tbody>
			</table>
			</br>
			</br>
			</div>
<?php
			
		}
	}
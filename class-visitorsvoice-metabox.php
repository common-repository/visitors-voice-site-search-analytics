<?php
	/**
	* Metabox class for the Visitors Voice plugin
	*
	* The metabox shown on the edit mode for a page with suggestions and related search terms
	* Inspiration: https://github.com/PeteMall/Metabox-Tabs/blob/master/jf-metabox-tabs.php
	* Inspiration: https://datatables.net/release-datatables/examples/basic_init/filter_only.html
	* 
	* @author  Pontus Rosin <pontus@visitorsvoice.com>
	* @since 1.0
	* @license: GPLv2 or later
	* @license URI: http://www.gnu.org/licenses/gpl-2.0.html
	*/

	class VisitorsVoiceMetabox {
	
		const LANG = 'visitorsvoice_metabox';
		private static $instance = null;
		private $internal_search_url = '';
		private $google_url = '';
		
		public static function get_instance() {
	 
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	
		public function __construct() {
		
			add_action( 'save_post', 								array( $this,'save_visitorsvoice_metabox'));
			add_action( 'add_meta_boxes',                  			array( $this, 'add_meta_box' ) );
			add_action( 'admin_print_styles-post-new.php', 			array( $this, 'enqueue'      ) );
			add_action( 'admin_print_styles-post.php',     			array( $this, 'enqueue'      ) );
			
			$this->internal_search_url = get_option( 'visitorsvoice_internal_search_url' );
			$this->google_url = get_option( 'visitorsvoice_google_url' );
		}
		
		function save_visitorsvoice_metabox($post_id) 
		{
			// Bail if we're doing an auto save  
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id; 
		 
			// make sure data came from our meta box
			if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'visitorsvoice_meta_box_nonce' ) ) return $post_id;
		 
			// check user permissions
			if ($_POST['post_type'] == 'page') 
			{
				if (!current_user_can('edit_page', $post_id)) 
					return $post_id;
			}
			
			// save status of the checkbox into database
			$chk = isset( $_POST['visitorsvoice_meta_box_check'] ) && $_POST['visitorsvoice_meta_box_check'] ? '1' : '0'; 
			$visitorsvoiceplugin = VisitorsVoicePlugin::get_instance();
			$visitorsvoiceplugin->update_suggestion_status_by_id($post_id, $chk);
						
			// merge keywords from Visitors Voice plugin with meta data handler plugin
			if ($visitorsvoiceplugin->get_keywords_field())
			{
				$keywords = $visitorsvoiceplugin->get_keywords_field_value($post_id);
				if (isset($_POST['vv_add_keywords'])) {
					foreach ($_POST['vv_add_keywords'] as $keyword) {
						$keyword = trim(sanitize_text_field($keyword));
						if (!$keyword) continue;
						if (!in_array($keyword, $keywords))
						{
							$keywords[] = $keyword;
							$visitorsvoiceplugin->update_suggestion_status_by_searchterm($post_id, $keyword, 1);
						}
					}
				}

				$visitorsvoiceplugin->set_keywords_field_value($post_id, $keywords);
			}
			return $post_id;
		}
		
		public function add_meta_box() {
			
			add_meta_box(
				 'visitorsvoice_metabox_id'					# HTML 'id' attribute of the edit screen section
				,__( 'Visitors Voice - Site Search Statistics', self::LANG )			# Title of the metabox the edit screen section, visible to user
				,array( &$this, 'render_meta_box_content' )	# Call back function that prints out the HTML for the edit screen section. 
				,'page'										# The type of Write screen on which to show the edit screen section ('post', 'page', 'dashboard', 'link', 'attachment' or 'custom_post_type')
				,'advanced'									# The part of the page where the edit screen section should be shown ('normal', 'advanced', or 'side')
				,'high'										# The priority within the context where the boxes should show ('high', 'core', 'default' or 'low')
			);
		}
		
		public function render_meta_box_content() {
			
			global $wpdb;
			if (!isset($wpdb)) $wpdb = $GLOBALS['wpdb'];
			
			wp_nonce_field( 'visitorsvoice_meta_box_nonce', 'meta_box_nonce' ); 
			
			$visitorsvoiceplugin = VisitorsVoicePlugin::get_instance();
			
			if(!$visitorsvoiceplugin->check_configured()){
				$visitorsvoiceplugin->display_non_authorized();
				return;
			}
			
			$pid = $_REQUEST['post'];
			$permalink = get_permalink( $pid );
			$rows_suggested_keywords = $visitorsvoiceplugin->get_suggestions_for_page($permalink);
			
			$existing_keywords = $visitorsvoiceplugin->get_keywords_field_value($pid);
			
			// Check if search words already are added to the current keywords meta field
			$rows = '';
			if ($visitorsvoiceplugin->get_keywords_field())
			{
				foreach ( $rows_suggested_keywords as $row )
				{
					if(!in_array($row->searchterm, $existing_keywords))
						$rows[] = $row;
					else 
						$visitorsvoiceplugin->update_suggestion_status_by_searchterm($pid, $row->searchterm, 1);
				}
			}
			
			if(!empty($rows)){
				if($rows[0]->status==0){
?>				
					<fieldset id="visitorsvoice_metabox_suggestion" class="itemlist" style="display: block;">
					<legend><?php _e("Keyword suggestions", "visitorsvoice"); ?></legend>
					<?php _e("Users has searched for the initial search terms below, then refined their searches and clicked on this page in the search engine hit list. Consider to use the search terms in your content or add them to the keywords of this page in order to promote them in the search engine hit list:", "visitorsvoice")."<br>"; ?>
					</br></br>
					<table class="display" id="searchterms_refinements_table">
						<thead>
							<tr>
								<th><?php _e("Initial search terms", "visitorsvoice"); ?></th>
								<th><?php _e("Refinements", "visitorsvoice"); ?></th>
								<th><?php _e("Users", "visitorsvoice"); ?></th>
								<?php if ($visitorsvoiceplugin->get_keywords_field()): ?>
									<th><?php _e("Add initial search term to keywords", "visitorsvoice"); ?></th>
								<?php endif ?>
							</tr>
						</thead>
						<tbody>
<?php					
							$i=1;
							
							
							foreach ( $rows as $row )
							{
								if($i % 2 === 0) {
									echo '<tr class="even gradeX"><td>';
									echo '<a href="'.str_replace("##SEARCHTERM##", $row->searchterm, $this->internal_search_url).'" target="_blank">'.$row->searchterm;
									#echo '<img src="'.PLUGIN_URL.'/assets/open_in_new_window.png" class="open-in-new-ico open-hitlist tooltip" title="'.__('Open the page in new window','visitorsvoice').'">';
									echo '</a>';
									echo '</td><td>';
									echo '<a href="'.str_replace("##SEARCHTERM##", $row->refinement, $this->internal_search_url).'" target="_blank">'.$row->refinement;
									#echo '<img src="'.PLUGIN_URL.'/assets/open_in_new_window.png" class="open-in-new-ico open-hitlist tooltip" title="'.__('Open the page in new window','visitorsvoice').'">';
									echo '</a>';
									echo '</td><td>'.$row->count.'</td>';
									if ($visitorsvoiceplugin->get_keywords_field()):
										echo '<td><input type="checkbox" name="vv_add_keywords[]" value="'.$row->searchterm.'"></td>';
									endif;
									echo '</tr>';
								} else { 
									echo '<tr class="odd gradeX"><td>';
									echo '<a href="'.str_replace("##SEARCHTERM##", $row->searchterm, $this->internal_search_url).'" target="_blank">'.$row->searchterm;
									#echo '<img src="'.PLUGIN_URL.'/assets/open_in_new_window.png" class="open-in-new-ico open-hitlist tooltip" title="'.__('Open the page in new window','visitorsvoice').'">';
									echo '</a>';
									echo '</td><td>';
									echo '<a href="'.str_replace("##SEARCHTERM##", $row->refinement, $this->internal_search_url).'" target="_blank">'.$row->refinement;
									#echo '<img src="'.PLUGIN_URL.'/assets/open_in_new_window.png" class="open-in-new-ico open-hitlist tooltip" title="'.__('Open the page in new window','visitorsvoice').'">';
									echo '</a>';
									echo '</td><td>'.$row->count.'</td>';
									if ($visitorsvoiceplugin->get_keywords_field()):
										echo '<td><input type="checkbox" name="vv_add_keywords[]" value="'.$row->searchterm.'"></td>';
									endif;
									echo '</tr>';
								}
								$i++;
							}
?>
						</tbody>
					</table>
					</br></br>
					<input type="hidden" id="visitorsvoice_meta_box_suggestion_id" name="visitorsvoice_meta_box_suggestion_id" value="'.$pid.'"/>
					<?php if($row->status==0)
						echo '<input type="checkbox" id="visitorsvoice_meta_box_check" name="visitorsvoice_meta_box_check" />';
					else 
						echo '<input type="checkbox" id="visitorsvoice_meta_box_check" name="visitorsvoice_meta_box_check" checked />';
					?>
					<label for="visitorsvoice_meta_box_text">
					<?php 	
							_e('Mark all these keyword suggestions as fixed', 'visitorsvoice').'<br>'; 
					?>			
					</fieldset>
					</br>
<?php			}
			}
			
			$visitorsvoiceplugin->get_url_context($permalink );

?>			
			<div class="metabox-tabs-div">
				<h4><?php _e("Search terms related to the page", "visitorsvoice"); ?></h4>
				<ul class="metabox-tabs" id="metabox-tabs">
					<li class="active tab1"><a class="active" href="javascript:void(null);"><?php _e("Outgoing", "visitorsvoice"); ?></a></li>
					<li class="tab2"><a href="javascript:void(null);"><?php _e("Incoming", "visitorsvoice"); ?></a></li>
					<li class="tab3"><a href="javascript:void(null);"><?php _e("External", "visitorsvoice"); ?></a></li>
				</ul>
				<div class="tab1">
					<h4 class="heading"><?php _e("Outgoing", "visitorsvoice"); ?></h4>
					<table class="display" id="outgoing_searchterms_table">
						<thead>
							<tr>
								<th><?php _e("Search terms", "visitorsvoice"); ?></th>
								<th><?php _e("Users", "visitorsvoice"); ?></th>
								<th><?php _e("Users that found", "visitorsvoice"); ?></th>
								<th><?php _e("Analyze search terms", "visitorsvoice"); ?></th>
							</tr>
						</thead>
						<tbody>
<?php		
						$searchterms_from_url = $visitorsvoiceplugin->get_searchterms_from_url();
						$i1=1;
						if(count($searchterms_from_url)>0)
						{
							foreach( $searchterms_from_url as $record ) {
								if($i1 % 2 === 0) {
									echo '<tr class="even gradeX">';
									echo '<td><a href="'.str_replace('##SEARCHTERM##', $record["SearchTerm"], $this->internal_search_url).'" target="_blank">'.$record["SearchTerm"].'</a></td>';
									echo '<td>'.$record["User"].'</td>';
									echo '<td>'.$record["UnHappyUser"].'</td>';
									echo '<td><a href="http://service.visitorsvoice.com/SearchTermAnalysis?searchterm='.$record["SearchTerm"].'&token=view-'.$visitorsvoiceplugin->get_api_key().'" target="_blank">'.__("Analyze", "visitorsvoice").'</a></td>';
									echo '</tr>';
								} else {
									echo '<tr class="odd gradeX">';
									echo '<td><a href="'.str_replace('##SEARCHTERM##', $record["SearchTerm"], $this->internal_search_url).'" target="_blank">'.$record["SearchTerm"].'</a></td>';
									echo '<td>'.$record["User"].'</td>';
									echo '<td>'.$record["UnHappyUser"].'</td>';
									echo '<td><a href="http://service.visitorsvoice.com/SearchTermAnalysis?searchterm='.$record["SearchTerm"].'&token=view-'.$visitorsvoiceplugin->get_api_key().'" target="_blank">'.__("Analyze", "visitorsvoice").'</a></td>';
									echo '</tr>';
								}
								$i1++;
							}
						}
?>
						</tbody>
					</table>
				</div>
				<div class="tab2">
					<h4 class="heading"><?php _e("Incoming", "visitorsvoice"); ?></h4>
					<table class="display" id="incoming_searchterms_table">
						<thead>
							<tr>
								<th><?php _e("Search terms", "visitorsvoice"); ?></th>
								<th><?php _e("Count", "visitorsvoice"); ?></th>
								<th><?php _e("Users", "visitorsvoice"); ?></th>
								<th><?php _e("Analyze search terms", "visitorsvoice"); ?></th>
							</tr>
						</thead>
						<tbody>
<?php
						$searchterms_to_url = $visitorsvoiceplugin->get_searchterms_to_url();
						$i=1;
						if(count($searchterms_to_url)>0)
						{
							foreach( $searchterms_to_url as $record ) {
								if($i % 2 === 0) {
									echo '<tr class="even gradeX">';
									echo '<td><a href="'.str_replace('##SEARCHTERM##', $record["SearchTerm"], $this->internal_search_url).'" target="_blank">'.$record["SearchTerm"];
									echo '</a></td>';
									echo '<td>'.$record["Count"].'</td>';
									echo '<td>'.$record["User"].'</td>';
									echo '<td><a href="http://service.visitorsvoice.com/SearchTermAnalysis?searchterm='.$record["SearchTerm"].'&token=view-'.$visitorsvoiceplugin->get_api_key().'" target="_blank">'.__("Analyze", "visitorsvoice");
									echo '</a></td>';
									echo '</tr>';
								} else {
									echo '<tr class="odd gradeX">';
									echo '<td><a href="'.str_replace('##SEARCHTERM##', $record["SearchTerm"], $this->internal_search_url).'" target="_blank">'.$record["SearchTerm"];
									echo '</a></td>';
									echo '<td>'.$record["Count"].'</td>';
									echo '<td>'.$record["User"].'</td>';
									echo '<td><a href="http://service.visitorsvoice.com/SearchTermAnalysis?searchterm='.$record["SearchTerm"].'&token=view-'.$visitorsvoiceplugin->get_api_key().'" target="_blank">'.__("Analyze", "visitorsvoice");
									echo '</a></td>';
									echo '</tr>';
								}
								$i++;
							}
						}
?>
						</tbody>
					</table>
				</div>
				<div class="tab3">
					<h4 class="heading"><?php _e("External", "visitorsvoice"); ?></h4>
					<table class="display" id="incoming_keywords_table">
						<thead>
							<tr>
								<th><?php _e("Keywords", "visitorsvoice"); ?></th>
								<th><?php _e("Count", "visitorsvoice"); ?></th>
								<th><?php _e("Users", "visitorsvoice"); ?></th>
							</tr>
						</thead>
						<tbody>
<?php		
						$keywords_to_url = $visitorsvoiceplugin->get_keywords_to_url();
						$i2=1;
						if(count($keywords_to_url)>0)
						{
							foreach( $keywords_to_url as $record ) {
								if($i2 % 2 === 0) {
									echo '<tr class="even gradeX">';
									echo '<td><a href="'.str_replace('##KEYWORD##', $record["Keyword"], $this->google_url).'" target="_blank">'.$record["Keyword"];
									#echo '<img src="'.PLUGIN_URL.'/assets/open_in_new_window.png" class="open-in-new-ico open-hitlist tooltip" title="'.__('Open the page in new window','visitorsvoice').'">';
									echo '</a></td>';
									echo '<td>'.$record["Count"].'</td>';
									echo '<td>'.$record["User"].'</td>';
									echo '</tr>';
								} else {
									echo '<tr class="odd gradeX">';
									echo '<td><a href="'.str_replace('##KEYWORD##', $record["Keyword"], $this->google_url).'" target="_blank">'.$record["Keyword"];
									#echo '<img src="'.PLUGIN_URL.'/assets/open_in_new_window.png" class="open-in-new-ico open-hitlist tooltip" title="'.__('Open the page in new window','visitorsvoice').'">';
									echo '</a></td>';
									echo '<td>'.$record["Count"].'</td>';
									echo '<td>'.$record["User"].'</td>';
									echo '</tr>';
								}
								$i2++;
							}
						}
?>
						</tbody>
					</table>
				</div>
			</div>
			
			
			
			<?php
		}
		
		public function enqueue() {
			
			$color = get_user_meta( get_current_user_id(), 'admin_color', true );

			wp_enqueue_style(  'visitorsvoice-jquery-datatables-custom', plugins_url( 'assets/custom_styles.css',   __FILE__ )             );	
			wp_enqueue_style(  'visitorsvoice-suggestionbox', plugins_url( 'assets/suggestionbox.css',   __FILE__ )             );
			wp_enqueue_style(  'visitorsvoice-metabox-tabs', plugins_url( 'assets/metabox-tabs.css',   __FILE__ )                    );
			wp_enqueue_style(  "visitorsvoice-$color",       plugins_url( "assets/metabox-$color.css", __FILE__ )                    );
			wp_enqueue_script( 'visitorsvoice-metabox-tabs', plugins_url( 'assets/metabox-tabs.js',    __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'visitorsvoice-jquery-datatables', 	plugins_url( 'assets/DataTables/media/js/jquery.dataTables.min.js', __FILE__), array('jquery'));
			wp_enqueue_style(  'visitorsvoice-metabox-table', 		plugins_url( 'assets/metabox_table.css', __FILE__ ));
			wp_enqueue_script( 'visitorsvoice-init-metabox-tables', 	plugins_url( 'assets/init_metabox_table.js', __FILE__));
			wp_enqueue_script( 'visitorsvoice-metabox-ajax', 	plugins_url( 'assets/test.js', __FILE__));
		}
	}
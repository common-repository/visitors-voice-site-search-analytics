<?php

	$api_key = get_option( 'visitorsvoice_api_key' );
	$fixed_suggestions_expire = get_option( 'visitorsvoice_fixed_suggestions_expire' );
	$days_before_check_for_suggestion = get_option( 'visitorsvoice_days_before_check_for_suggestion' );
	$max_nr_prioritized_page = get_option( 'visitorsvoice_max_nr_prioritized_pages' );
	$internal_search_url = get_option( 'visitorsvoice_internal_search_url' );
	$google_url = get_option( 'visitorsvoice_google_url' );
	$manual_keywords_field = get_option( 'visitorsvoice_manual_keywords_field' );
	$url_context_days_back = get_option( 'visitorsvoice_url_context_days_back' );
?>
	<div class="wrap">
		<div id="icon-visitorsvoice-page" ><br></div>
			<h2><?php _e("Visitors Voice Plugin", "visitorsvoice"); ?></h2>
		</br>
		<?php _e('Visitors Voice will show your visitors search behavior for each url and give suggestions how you can improve your content and metadata.', 'visitorsvoice');?>
		</br></br>
		<?php _e("To administer your Visitors Voice account, you need to login to <a href='http://service.visitorsvoice.com/' target='_new'>Visitors Voice Dashboard</a>", "visitorsvoice"); ?></a>.
		</br></br>
		<form name="visitorsvoice_configure" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
			<?php wp_nonce_field('visitorsvoice-nonce'); ?>
			<input type="hidden" name="action" value="visitorsvoice_configure">
			<table class="widefat" style="width: 650px;">
				<thead>
					<tr>
						<th class="row-title" colspan="2"><?php _e("Configure the plugin - step 2(2)", "visitorsvoice"); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php _e("API Key", "visitorsvoice"); ?>:</td>
						<td><?php print( $api_key ); ?></td>
					</tr>
					<tr>
						<td>
							<?php _e("Visitors Voice analyzes your visitors search and click behavior in order to find suggestions for you to improve the content and metadata of your pages. This might help more visitors find them; both in your internal search engine as well as the external search engines like Google.", "visitorsvoice");?>
							</br>
							</br>
							<?php _e("How often should Visitors Voice get top priority pages from the API?", "visitorsvoice");?></br>
							<input type="text" style="width:50px;" name="days_before_check_for_suggestion" class="regular-text" value="<?php if($days_before_check_for_suggestion>0) echo $days_before_check_for_suggestion; ?>"/>
							<?php _e("(days)", "visitorsvoice");?>
							</br>
							</br>
							<?php _e("How many top priority pages should Visitors Voice get from the API at the most?", "visitorsvoice");?></br>
							<input type="text" style="width:50px;" name="max_nr_prioritized_page" class="regular-text" value="<?php if($max_nr_prioritized_page>0) echo $max_nr_prioritized_page; ?>"/>
							<?php _e("(pages)", "visitorsvoice");?></br>
							</br>
							</br>
							<?php _e("How many days must pass before a suggestion you have fixed for a page could appear again?", "visitorsvoice");?></br>
							<input type="text" style="width:50px;" name="fixed_suggestions_expire" class="regular-text" value="<?php if($fixed_suggestions_expire>0) echo $fixed_suggestions_expire; ?>"/>
							<?php _e("(days)", "visitorsvoice");?>
							</br>
							</br>
							<?php _e("How many days back to display search terms for a page?", "visitorsvoice");?></br>
							<input type="text" style="width:50px;" name="url_context_days_back" class="regular-text" value="<?php if($url_context_days_back>0) echo $fixed_suggestions_expire; ?>"/>
							<?php _e("(days)", "visitorsvoice");?>
							</br>
							</br>
							<?php _e("Your internal site search url. Replace the search term in the url with ##SEARCHTERM##:", "visitorsvoice");?></br>
							<input type="text" style="width:550px;" name="internal_search_url" class="regular-text" value="<?php if($internal_search_url<>'') echo $internal_search_url; ?>"/>
							</br>
							<?php _e("<i>http://www.yourdomain.com?q=##SEARCHTERM##</i>", "visitorsvoice");?></br>
							</br>
							</br>
							<?php _e("Customize your Google search url. Replace the keyword in the url with ##KEYWORD##:", "visitorsvoice");?></br>
							<input type="text" style="width:550px;" name="google_url" class="regular-text" value="<?php if($google_url<>'') echo $google_url; ?>"/>
							</br>
							<?php _e("<i>http://www.google.se/?#q=##KEYWORD##</i>", "visitorsvoice");?></br>
							</br>
							</br>
							<?php _e('In order to make this plugin work, you need a separate plugin for handlng keywords metadata.', "visitorsvoice") ?>
							</br>
							<?php _e('Specify post meta field name for the plugin that manages keywords metadata:', "visitorsvoice") ?>
							<input type="text" name="manual_keywords_field" style="width: 550px;" class="regular-text" value="<?php echo $manual_keywords_field ?>">
							</br>
							</br>
							<input type="submit" name="Submit" value="<?php _e("Save", "visitorsvoice"); ?>" class="button-primary" />
						</td>
					</tr>
						<td>
					</tr>
				</tbody>
			</table>
		</form>
		<br/>
		<hr/>
		<p>
			<?php _e("If you're having trouble with the Visitors Voice plugin you may clear your configuration by clicking the button below. This will remove all data stored for the plugin.", "visitorsvoice"); ?><br/><br/> 
		</p>
		<form name="visitorsvoice_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
			<?php wp_nonce_field('visitorsvoice-nonce'); ?>
			<input type="hidden" name="action" value="visitorsvoice_clear_config">
			<input type="submit" name="Submit" value="<?php _e("Clear Visitors Voice Configuration", "visitorsvoice"); ?>"  class="button-primary" />
		</form>
	</div>
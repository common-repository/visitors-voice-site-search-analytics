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
		<?php _e("To administer your Visitors Voice account, visit the <a href='http://service.visitorsvoice.com/' target='_new'>Visitors Voice Dashboard</a>", "visitorsvoice"); ?></a>.
		</br></br>
		<form name="visitorsvoice_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
			<?php wp_nonce_field('visitorsvoice-nonce'); ?>
			<input type="hidden" name="action" value="visitorsvoice_settings">
			<table class="widefat" style="width: 650px;">
				<thead>
					<tr>
						<th class="row-title" colspan="2"><?php _e("Visitors Voice Plugin Settings", "visitorsvoice"); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php _e("API Key", "visitorsvoice"); ?>:</td>
						<td><?php print( $api_key ); ?></td>
					</tr>
					<tr>
						<td><?php _e("How often Visitors Voice get top priority pages from the API:", "visitorsvoice");?></td>
						<td><?php echo $days_before_check_for_suggestion; ?> <?php _e("(days)", "visitorsvoice");?></td>
					</tr>
					<tr>
						<td><?php _e("Maximum number of top priority pages Visitors Voice could get at a time:", "visitorsvoice");?></td>
						<td><?php echo $max_nr_prioritized_page ; ?> <?php _e("(pages)", "visitorsvoice");?></td>
					</tr>
					<tr>
						<td><?php _e("Number of days before a fixed suggestion could appear again:", "visitorsvoice");?></td>
						<td><?php echo $fixed_suggestions_expire; ?> <?php _e("(days)", "visitorsvoice");?></td>
					</tr>
					<tr>
						<td><?php _e("Number of days back to display search terms for a page:", "visitorsvoice");?></td>
						<td><?php echo $url_context_days_back; ?> <?php _e("(days)", "visitorsvoice");?></td>
					</tr>
					<tr>
						<td colspan="2">
							<?php _e("Your internal site search url:", "visitorsvoice");?>
							</br>
							<input type="text"  style="width:600px;" name="internal_search_url" class="regular-text" value="<?php if($internal_search_url<>'') echo $internal_search_url; ?>"/>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php _e("Your customized Google search url:", "visitorsvoice");?>
							</br>
							<input type="text"  style="width:600px;" name="google_url" class="regular-text" value="<?php if($google_url<>'') echo $google_url; ?>"/>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php _e('In order to make this plugin work, you need a separate plugin for handlng keywords metadata.', "visitorsvoice") ?>
							</br>
							<?php _e('Specify post meta field name for the plugin that manages keywords metadata:', "visitorsvoice") ?>
							<input type="text" name="manual_keywords_field" style="width: 600px;" class="regular-text" value="<?php echo $manual_keywords_field ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="submit" name="Submit" value="<?php _e("Save", "visitorsvoice"); ?>" class="button-primary" />
						</td>
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
<?php
/**
 * @package Pick_Giveaway_Winner
 * @version 1.3
 */
/*
Plugin Name: Pick Giveaway Winner for WPForms
Plugin URI: https://www.makeworthymedia.com/plugins/
Description: Randomly select a winner or winners from the entrants of a WPForms form. To choose a winner go to WPForms -> Pick Giveaway Winner.
Author: Websavers Inc.
Version: 1.0
Author URI: https://websavers.ca/
License: GPL2
*/

/*  Copyright 2010 Jennette Fulda  (email : contact@makeworthymedia.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_menu', 'pgw_menu');

function pgw_menu() {
	add_submenu_page('admin.php?page=wpforms-overview', 'Pick Giveaway Winner Options', 'Pick Giveaway Winner', 'manage_options', 'pick-giveaway-winner', 'pgw_options');
}

function pgw_options() {
	global $wpdb;
	
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	// If someone submitted the form, select the giveaway winners
	if( is_numeric($_POST['pgw-entry-id']) && is_numeric($_POST['pgw-num-winners']) ) {

		// Get the winning form entries from the selected form
		
		$form = wpforms()->form->get( absint( $_POST['pgw-entry-id'] ) );
		if ( empty( $form ) ) {
			return;
		}
		
		$form_data = !empty( $form->post_content ) ? wpforms_decode( $form->post_content ) : '';
		
		//$winners = $wpdb->get_results($wpdb->prepare("SELECT fields FROM $wpdb->wpforms_entries WHERE form_id = %d ORDER BY RAND() LIMIT %d", $_POST['pgw-entry-id'], $_POST['pgw-num-winners']));
		$entries   = wpforms()->entry->get_entries( array( 'form_id' => absint( $_POST['pgw-entry-id'] ), 'number' => $_POST['pgw-num-winners'], 'order' => 'ASC', 'orderby' => 'rand' ) );
			
		$winners_text="";
		$count = 1;
		$winners = array();
		foreach ($entries as $entry) {
			$fields = wpforms_decode( $entry->fields );
			foreach( $fields as $field ) {
				$winners[$count] = array( $field['name'] => wp_strip_all_tags($field['value']) );
			}
			$count++;
		}
		foreach ( $winners as $count => $winner ){
			$winners_text .= "<p>$count) {$winner['Name']}: <a href='mailto:{$winner['Email']}'>{$winner['Email']}</a></p>\n";
		}	

		// If the number of winners is greater than the number of entries, send alert to screen
		if ($count <= $_POST['pgw-num-winners']) {
			$winners_text .= "<p><strong>There were no more entries for this form!</strong></p>";
		}	

		// Get title of form
		//$winning_post = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $_POST['pgw-entry-id']));
		$form_title = $form_data['title']; //Not sure about this.

	// Put an settings updated message on the screen
?>
	<div class="updated"><p><strong>Your <?php echo($_POST['pgw-num-winners']); ?> winners on "<?php echo($form_title); ?>" are:</strong></p>
		<?php echo $winners_text; ?></p></div>
<?php

	} // End of winner selection


	// Check posted variables to be sure they are numbers. No hacking!
	if (is_numeric($_POST['pgw-entry-id'])) {
		$saved_entry_id = $_POST['pgw-entry-id'];
	}
	
	if (is_numeric($_POST['pgw-num-winners'])) {
		$saved_num_winners = $_POST['pgw-num-winners'];
	}

?>
  <div class="wrap">
  	<h2>Pick Giveaway Winner</h2>
  	<p>This plugin allows you to randomly select a winner or winners from the entries of a WPForm.</p>
  	
  	<form name="pgw_form" id="pgw_form" action="" method="post">
		<p><label>Select Form:</label>
			<select name="pgw-entry-id">
				<?php pgw_get_forms_dropdown($saved_entry_id); ?>
			</select></p>
			
		<p><label>How many winners?</label>
			<select name="pgw-num-winners">
				<?php pgw_get_number_winners_dropdown($saved_num_winners); ?>
			</select>
		</p>
		<p><input type="submit" value="Pick winners!"></p>
  	</form>
  </div>
<?php 
}

/* Prints dropdown list of all forms */
function pgw_get_forms_dropdown($entry_id) {
	
	$args = array(
		'post_status' => 'publish',
		'post_type' => 'post',
		'orderby' => 'date',
		'order' => 'DESC',
		'posts_per_page' => 100,
	);
	
	$forms = wpforms()->form->get('', $args);
	
	$entry_options = "";
	
	foreach ($forms as $form){
		
		$selected = '';
		if ($entry_id == get_the_ID() ) {
			$selected .= " selected='selected'";
		}
		
		$entry_options .= sprintf("<option value='%s'%s>%s</option>\n", $form['id'], $selected, $form['name']) );
		
	}
	
	echo $entry_options;
}

/* Prints dropdown list of number of winners*/
function pgw_get_number_winners_dropdown($num_winners) {
	$num_winner_options ="";
	for($i = 1; $i <= 50; $i++) {
		$selected = '';
		if ($num_winners==$i) {
			$selected .= " selected";
		}

		$num_winner_options .= sprintf("<option%s>%s</option>\n", $selected, $i);
	}
	
	echo $num_winner_options;
}
?>
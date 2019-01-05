<?php
/**
 * @package Pick_Giveaway_Winner_For_WPForms
 * @version 1.0
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

add_action('admin_menu', 'pgw_init', 1000);

function pgw_init(){
	add_filter('wpforms_tools_views', 'pgw_menu', 10, 1);
	add_action('wpforms_tools_display_tab_giveaway', 'pgw_options');
}

function pgw_menu($tabs){
	$key = esc_html__( 'Giveaway Winner', 'wpforms' );
	$tabs[$key] = array( 'giveaway' );
	return $tabs;
}

/*
add_action('admin_menu', 'pgw_menu');
function pgw_menu() {
	add_submenu_page('admin.php?page=wpforms-overview', 'Pick Giveaway Winner Options', 'Pick Giveaway Winner', 'manage_options', 'pick-giveaway-winner', 'pgw_options');
}
*/
function pgw_options() {
	//global $wpdb;
	
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	// If someone submitted the form, select the giveaway winners
	if( !empty($_POST['pgw-entry-id']) && is_numeric($_POST['pgw-entry-id']) &&
			!empty($_POST['pgw-num-winners']) && is_numeric($_POST['pgw-num-winners']) ) {

		// Get the winning form entries from the selected form
		$form = wpforms()->form->get( absint( $_POST['pgw-entry-id'] ) );
		if ( empty( $form ) ) {
			return;
		}
		
		$form_data = !empty( $form->post_content ) ? wpforms_decode( $form->post_content ) : '';
		
		var $entry_args = array( 
			'form_id' => absint( $_POST['pgw-entry-id'] ), 
			'number' => $_POST['pgw-num-winners'], 
			'order' => 'ASC', 
			'orderby' => 'rand' 
		);
		
		if (!empty($_POST['pgw-giveaway-number']) && is_numeric($_POST['pgw-giveaway-number'])) {
			$entry_args['filter'] = $_POST['pgw-giveaway-number'];
		}
		
		$entries = wpforms()->entry->get_entries( $entry_args );
			
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
		$form_title = $form_data['title']; //Not sure about this.

	// Put an settings updated message on the screen
?>
	<div class="updated"><p><strong>Your <?php echo($_POST['pgw-num-winners']); ?> winners on "<?php echo($form_title); ?>" are:</strong></p>
		<?php echo $winners_text; ?></p></div>
<?php

	} // End of winner selection


	// Check posted variables to be sure they are numbers. No hacking!
	if (!empty($_POST['pgw-entry-id']) && is_numeric($_POST['pgw-entry-id'])) {
		$saved_entry_id = $_POST['pgw-entry-id'];
	}
	else $saved_entry_id = null;
	
	if (!empty($_POST['pgw-num-winners']) && is_numeric($_POST['pgw-num-winners'])) {
		$saved_num_winners = $_POST['pgw-num-winners'];
	}
	else $saved_num_winners = null;

?>
  <div class="wpforms-setting-row tools">
  	<h3>Pick Giveaway Winner</h3>
  	<p>This plugin allows you to randomly select a winner or winners from the entries of a WPForm.</p>
  	
  	<form name="pgw_form" id="pgw_form" action="" method="post">
			<p>
				<?php echo pgw_get_forms_dropdown($saved_entry_id); ?>
			</p>
				
			<p><label>How many winners?</label>
				<select name="pgw-num-winners">
					<?php echo pgw_get_number_winners_dropdown($saved_num_winners); ?>
				</select>
			</p>
			<p><input type="submit" value="Pick winners!"></p>
  	</form>
  </div>
<?php 
}

/* Prints dropdown list of all forms */
function pgw_get_forms_dropdown($sel_entry_id) {

	$forms = wpforms()->form->get();

	$entry_options = '<label>Select Form:</label>
		<select name="pgw-entry-id" id="pgw-entry-id">';
		
	$fields = array();
	foreach ( $forms as $form ){
						
			$selected = '';
			if ($sel_entry_id == $form->ID ) {
				$selected .= " selected";
			}
			
			$entry_options .= sprintf("<option value='%s'%s>%s</option>\n", $form->ID, $selected, $form->post_title );
			$form_data = !empty( $form->post_content ) ? wpforms_decode( $form->post_content ) : '';
			$fields[$form->ID] = $form_data['fields'];
	}
	
	$entry_options .= '</select>';
	
	/** TODO:
	 * Eventually this should use some of the code below to dynamically pull all form 
	 * fields from the selected form and allow filtering by any given field. Writing
	 * this part of the code was out of scope and unnecessary for our project, so 
	 * instead the only field we need to filter by is hard coded below. 
	 * I don't love doing this, but it's necessary to keep project in scope.
	 * -Jordan
	 */
	
	$entry_options .= "<label>Select a giveway:</label><select name='pgw-giveaway-number'>
	<option value='1'>Giveaway #1</option>
	<option value='2'>Giveaway #2</option>
	</select>";
	
	/** Partly written dynamic field filtering code commented out below **/
	
	/*
	$entry_options .= '<a href="javascript:pgw_show_filters();"><small><em>filter by form field?</em></small></a>';
	$entry_options .= '<div id="pgw-filters"></div>';
	$entry_options .= '<script>$form_fields = ' . json_encode($fields) . ';
	function pgw_show_filters(){
		var $form_id = jQuery("#pgw-entry-id").val();
		jQuery.each( $form_fields[$form_id], function( key, field ) {
  		if (field->type == "checkbox"){
				jQuery("#pgw-filters").append("<select name=\"filter[\'" +  + "\']\"> <option></option> </select>");
			}
		});
	}
	</script>';
	*/
	
	//$entry_options .= print_r($forms, true); ///DEBUG
	
	return $entry_options;
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
	
	return $num_winner_options;
}
?>
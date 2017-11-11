<?php
/**
 * @package Pick_Giveaway_Winner
 * @version 1.1
 */
/*
Plugin Name: Pick Giveaway Winner
Plugin URI: https://www.makeworthymedia.com/plugins/
Description: Randomly select a winner or winners from the comments of a giveaway post. To choose a winner, go to Tools -> Pick Giveaway Winner or <a href="tools.php?page=pick-giveaway-winner">click here</a>.
Author: Makeworthy Media
Version: 1.1
Author URI: https://www.makeworthymedia.com/
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
	add_management_page('Pick Giveaway Winner Options', 'Pick Giveaway Winner', 'manage_options', 'pick-giveaway-winner', 'pgw_options');
}

function pgw_options() {
	global $wpdb;
	
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	// If someone submitted the form, select the giveaway winners
	if( is_numeric($_POST['pgw-entry-id']) && is_numeric($_POST['pgw-num-winners']) && is_numeric($_POST['pgw-dupes']) ) {

		// Get the winning comments on the selected post
		
		if ($_POST['pgw-dupes']==3) {
			// Multiple entries allowed
			$winners = $wpdb->get_results($wpdb->prepare("SELECT comment_author, comment_author_email FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = 1 ORDER BY RAND() LIMIT %d", $_POST['pgw-entry-id'], $_POST['pgw-num-winners']));
		}
		
		if ($_POST['pgw-dupes']==2) {
			// Removes multiple emails, but keeps 1 entry for the email in question
			$winners = $wpdb->get_results($wpdb->prepare("SELECT comment_author, comment_author_email FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = 1 GROUP BY comment_author_email ORDER BY RAND() LIMIT %d", $_POST['pgw-entry-id'], $_POST['pgw-num-winners'] ));
		}

		if ($_POST['pgw-dupes']==1) {
			// Removes any duplicant entrants from the winners list
			$winners = $wpdb->get_results($wpdb->prepare("SELECT comment_author, comment_author_email FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = 1 GROUP BY comment_author_email ORDER BY RAND()", $_POST['pgw-entry-id'])); // gets ALL comments on the entry
			
			// Get list of all duplicate emails
			$losers = $wpdb->get_results($wpdb->prepare("SELECT comment_author_email, COUNT(*) c FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = 1 GROUP BY comment_author_email HAVING COUNT(*)>1", $_POST['pgw-entry-id']));

			// Save hash of ineligible winners
			$disqualified = array();
			foreach ($losers as $loser) {
				$disqualified[$loser->comment_author_email] = 1;
			}
		}
			
		$winners_text="";
		$count = 1;
		foreach ($winners as $winner) {
			// If we're eliminating duplicates, check to see if this email is a dupe.
			if ( ($_POST['pgw-dupes']==1) && ($disqualified[$winner->comment_author_email]==1) ) {
				continue;		
			}
			$winners_text .= "<p>$count) $winner->comment_author: <a href='mailto:$winner->comment_author_email'>$winner->comment_author_email</a></p>\n";
			$count++;
			// If we're eliminating duplicates, stop loop once we've reached the limit of winners
			if ( ($_POST['pgw-dupes']==1) && ($count > $_POST['pgw-num-winners']) ) {
				break;
			}
		}		

		// If the number of winners is greater than the number of comments, send alert to screen
		if ($count <= $_POST['pgw-num-winners']) {
			$winners_text .= "<p><strong>There were no more comments on this entry!</strong></p>";
		}

		// Lists emails of duplicate entrants
		if ($_POST['pgw-dupes']==1) {
			//if (count($disqualified) > 0) {
				$addresses = array();
				$winners_text .= "<p><strong>" . count($disqualified) ." email addresses were eliminated because of multiple entries:</strong> ";
				foreach ($losers as $loser) {
					//$winners_text .= "$loser->comment_author_email, ";
					array_push($addresses, $loser->comment_author_email);
				}
				$winners_text .= join(", ", $addresses);
				$winners_text .= "</p>";
			//}
		}			

		// Get title of post you selected winners from
		$winning_post = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $_POST['pgw-entry-id']));

	// Put an settings updated message on the screen
?>
	<div class="updated"><p><strong>Your <?php echo($_POST['pgw-num-winners']); ?> winners on "<?php echo($winning_post[0]->post_title); ?>" are:</strong></p>
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
  	<p>This plugin allows you to randomly select a winner or winners from the comments of a giveaway post.</p>
  	
  	<form name="pgw_form" id="pgw_form" action="" method="post">
		<p><label>Select entry:</label>
			<select name="pgw-entry-id">
				<?php pgw_get_entries_dropdown($saved_entry_id); ?>
			</select></p>
			
		<p><label>How many winners?</label>
			<select name="pgw-num-winners">
				<?php pgw_get_number_winners_dropdown($saved_num_winners); ?>
			</select>
		</p>
		<p><label>How should we handle multiple entries (by email address)?:</label></p>
		<p><input type="radio" name="pgw-dupes" id="pgw-dupes-1" value="1"<?php if ($_POST['pgw-dupes']==1 || !isset($_POST['pgw-dupes'])) : ?> checked<?php endif; ?>> <label for="pgw-dupes-1">Disqualify multiple entrant from drawing completely</label>
			<br /><input type="radio" name="pgw-dupes" id="pgw-dupes-2" value="2"<?php if ($_POST['pgw-dupes']==2) : ?> checked<?php endif; ?>> <label for="pgw-dupes-2">Discard multiple entries, but allow the entrant a single entry</label>
			<br /><input type="radio" name="pgw-dupes" id="pgw-dupes-3" value="3"<?php if ($_POST['pgw-dupes']==3) : ?> checked<?php endif; ?>> <label for="pgw-dupes-3">Allow multiple entries</label>
		</p>
		<p><input type="submit" value="Pick winners!"></p>
  	</form>
  </div>
<?php 
}

/* Prints dropdown list of all published posts */
function pgw_get_entries_dropdown($entry_id) {
	global $wpdb;
	$published = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' and post_type = 'post' ORDER BY post_date DESC"));

	$entry_options = "";
	foreach ($published as $publish) {
		$entry_options .= "<option value='$publish->ID'";
		if ($entry_id==$publish->ID) {
			$entry_options .= " selected";
		}
		$entry_options .= ">$publish->post_title</option>\n";
	}

	echo $entry_options;
}

/* Prints dropdown list of number of winners*/
function pgw_get_number_winners_dropdown($num_winners) {
	$num_winner_options ="";
	for($i = 1; $i <= 50; $i++) {
		$num_winner_options .= "<option";
		if ($num_winners==$i) {
			$num_winner_options .= " selected";
		}
		$num_winner_options .= ">$i</option>\n";
	}
	
	echo $num_winner_options;
}
?>
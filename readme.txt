=== Pick Giveaway Winner ===
Contributors: jennettefulda
Donate link: http://makemyblogpretty.com/plugins
Tags: giveaway,give away,winner,winners,contest,draw,prize,prizes,random,random number
Requires at least: 3.0
Tested up to: 4.3
Stable tag: 1.1

Randomly select a winner or winners from the comments of a giveaway post.

== Description ==

This plugin randomly selects a winner or winners from the comments of a giveaway post. You can also disqualify people who've entered more than once (determined by email), or reduce their entries to only one. The plugin uses MySQL's random function RAND() to randomly select the winners directly from the database.

== Installation ==

1. Upload the folder 'pick-giveaway-winner' to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Tools -> Pick Giveaway Winner.
4. Select the giveaway post from the drop-down menu.
5. Select how many winners you want.
6. Choose how to handle duplicate entries (determined by email).
7. Press the "Pick Winner!" button to randomly draw your winners.

== Frequently Asked Questions ==

= How are the winners randomly selected? =

The plugin uses MySQL's RAND() function to pick the winners directly from the database.

= Why does it take so long to choose a winner? =

The more comments your post has, the longer it will take to parse the data and determine a winner. Also, if you choose to "Discard multiple entries, but allow the entrant a single entry" the plugin has to do an additional database query to get your results, which takes more time. Be patient and your results will be returned as soon as possible.

= This isn't random! I ran the plugin twice in a row and got the same winner. =

It may seem counterintuitive, but if you re-run the plugin on the same post and occasionally get the same winner, this proves that the plugin actually is random. For example, if you roll a dice several times you will eventually get the same number twice in a row. The same thing happens with the plugin. However, if you run the plugin several times in a row and keep getting the same winner, that could indicate a problem.

= This is awesome! Can I send you money? =

Sure! Go to my plugin page at http://makemyblogpretty.com/plugins to donate.

= Would you like to thank anyone? =

Yes, I would! Thanks to Roni Noone at http://www.roninoone.com/ for beta testing this plugin.

== Screenshots ==

1. This is how the winner selection screen looks right after you've chosen a winner or winners.

== Changelog ==

= 1.1 =
* Removed wpdb::prepare() errors you might see when running the plugin in Wordpress 3.5 and up.
* Added label tags to radio button options so you can select an option by clicking on the text next to it and not just directly on the radio button.
* Updated the FAQ to answer a frequent question about the randomness of the plugin.

= 1.0 =
* Original version of the plugin.

== Upgrade Notice ==

= 1.1 =
Removed wpdb::prepare() errors you might see when running the plugin in Wordpress 3.5 and up. Updated FAQ.

= 1.0 =
Original version of the plugin.
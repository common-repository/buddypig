=== BuddyPig ===
Contributors: evansolomon, beaulebens
Tags: buddypress, game
Requires at least: 3.4
Tested up to: 3.4.1
Stable tag: 1.1.2

A quiz game built on BuddyPress that asks you questions about your fellow members. Built for an Automattic hack day.

== Description ==

BuddyPig is a game meant to help BuddyPress group members get to know each other better.  It was built during a hack day at Automattic.  Since we're a distributed company, it's not uncommon to go months without talking to certain colleagues.  We came up with BuddyPig as a fun way to work around that.

PIG, in case you're curious, is an acronym for Personal Information Game.

To use it you'll have to write a small plugin to create questions and choose the BuddyPress group you'd like to use.  PIG is very hook-able, so there's lots of other stuff you can customize if you're so inclined.  We'll include an example plugin to setup PIG below.

PIG requires BuddyPress and its Activity Streams feature to be running.  It also requires **PHP 5.3**, which is slightly higher than the default requirement for WordPress.

== Installation ==

1. Upload the `buddypig` directory to your plugins directory, most likely `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Write and activate your custom PIG setup plugin (example below)

== Frequently Asked Questions ==

= Where is the PIG menu? =

PIG doesn't have an admin menu.  Its only UI is the PIG button in the admin bar.

= I don't use the admin bar, can I use PIG? =

No.  PIG's only UI is the admin bar button that opens the game.

= I installed PIG, why isn't it doing anything? =

PIG requires an accompanying plugin to setup your questions.  We'll show you an example below.

== Example setup ==

You have to tell PIG what profile items to use and how to ask the questions.  We gave you a function called `pig_register_question()` to do just that.  By default, `pig_register_question()` takes 2 arguments, the BuddyPress profile field name and a format to use to ask the question.  Here's an example:

`pig_register_question( 'Address', 'Where does %s live?' );`

You'll notice the `%s` in the question format, which is where we drop in the user's name that the question is about.  If you've written PHP before, you may recognize this syntax from the `sprintf()` function, which is what is used behind the scenes.

You'll also have to tell PIG what BuddyPress group to use, which can be done with `pig_set_group()` by passing the group's ID.  Here's an example:

`pig_set_group( 1 );`

`pig_register_question()` can take two additional arguments.  The first one tells PIG whether to make the name possessive when it asks the question.  For example, "Where was Beau born?" would not be possessive, but "Where is Beau's house?" would be.  If you want PIG to make the name possessive, you can add `true` as the third argument, like this:

`pig_register_question( 'Address', 'Where is %s house?', true );`

The last optional argument is a callback.  PIG can format answers for you before displaying them.  One use case of this is deduplicating multiple phrases that mean the same thing, like "United States" and "USA".  If you use this callback, you must also make sure the function exists for PIG to call.  Here's an example:

`
pig_register_question( 'Address', 'Where is %s house?', 'a8c_dedupe_countries' );
function a8c_pig_dedupe_countries( $country ) {
	$usa_versions = array( 'US', 'USA', 'United States', 'U.S.A', 'U.S.', 'America', 'United States of America' );
	$uk_versions = array( 'UK', 'U.K.', 'United Kingdom' );
	foreach ( $usa_versions as $usa_version ) {
		if ( strtolower( $country ) == strtolower( $usa_version ) )
			return 'United States';
	}

	foreach ( $uk_versions as $uk_version ) {
		if ( strtolower( $country ) == strtolower( $uk_version ) )
			return 'United Kingdom';
	}

	return $country;
}
`

You can put these setup calls into your own plugin and activate it just like any other.  If you insist, you could also put them into your theme's `functions.php` file.  To make sure they're loaded after PIG is setup, you can hook them to the `pig_loaded` action.

== Screenshots ==

1. PIG automatically generates questions and potential answers based on BuddyPress profiles.
2. Correct answers receive happy bacon.
3. Incorrect answers receive sad carrots.

== Changelog ==

= 1.1 =
* PIG flies
* PIG is safey thanks to AJAX nonces

= 1.0 =
* PIG unleashed
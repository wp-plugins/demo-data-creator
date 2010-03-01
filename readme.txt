=== Wordpress MU Demo Data Creator ===
Contributors: mrwiblog
Donate link: http://www.stillbreathing.co.uk/donate/
Tags: wordpress mu, buddypress, demo, data, example, dummy, users, blogs, sample
Requires at least: 2.7
Tested up to: 2.9.1
Stable tag: 0.7

Demo Data Creator is a Wordpress MU and BuddyPress plugin that allows a Wordpress developer to create demo users, blogs, posts, comments and blogroll links for a Wordpress MU site. For BuddyPress you can also create user friendships, user statuses, user wire posts, groups, group members and group wire posts.

== Description ==

Warning: some parts of this plugin are not compatible with BuddyPress 1.2. I am working on a fix.

If you develop Wordpress MU websites it's useful to have some demo data in your system while it's being built. This allows you to check that lists of things are displaying as they should, and that themes are working when they get data in them.

Historically it's been a pain to add that data in. Either you need to take a backup of another site and use that data, or you need to tediously create multiple users and blogs yourself. No more, not now my Demo Data Creator is in town!

This Wordpress MU and BuddyPress plugin gives you a new admin screen where you can enter some parameters, click a button and (after a short wait) random demo data will be created. The parameter options include:

    * Number of users to create
    * Number of blogs per user
	* Whether users must have a blog not
	* Number of categories in each blog
    * Number of posts in each blog
	* Number of paragraphs in each blog post
	* Number of pages in each blog
	* Number of top-level pages
	* Number of levels to nest pages
    * Number of comments per post for each blog
    * Number of links in blogroll for each blog
	
For BuddyPress you also have:

	* Number of groups
	* Number of members per group
	* Number of wire posts for each group
	* Number of friends per user
	* Number of statuses for each user
	* Number of wire posts for each user

Post content and comment text is automatically generated from Lorem ipsum text, for post content it's even HTML-formatted.

== Installation ==

The plugin should be placed in your /wp-content/mu-plugins/ directory (*not* /wp-content/plugins/) and requires no activation. SO the path to the file should be /wp-content/mu-plugins/demodata.php. Acces the form from the "Site Admin" menu in the Dashboard.

== Frequently Asked Questions ==

= Why did you write this plugin? =

To scratch my own itch when developing [BeatsBase.com](http://beatsbase.com "Free mix hosting for DJs"). Hopefully this plugin helps other developers too.

= What about BuddyPress support =

The DemoData plugin now supports BuddyPress.

== Screenshots ==

1. The demo data admin page

== Changelog ==

0.7 Added support link and donate button

0.6 Fixed bug which stopped demo blog tables being created

0.5 Fixed bugs with user wire posts, added support for group wire posts, cleaned up code

0.4 Prepared code for proper translation support, fixed bug with BuddyPress XProfile data, added support for BuddyPress status messages

0.3 Cleaned up code

0.2 Added categories, pages for normal Wordpress MU sites. Added BuddyPress groups, group members and user friends BuddyPress-enabled sites.

0.1 Initial version added to Wordpress plugin repository
=== Wordpress MU Demo Data Creator ===
Contributors: mrwiblog
Donate link: http://www.stillbreathing.co.uk/donate/
Tags: wordpress mu, demo, data, example, dummy, users, blogs, sample
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 0.1

Demo Data Creator is a Wordpress MU plugin that allows a Wordpress developer to create demo users, blogs, posts, comments and blogroll links.

== Description ==

If you're a web geek like me and into developing Wordpress MU websites (like wibsite.com), it's useful to have a bit of demo data in your system while it’s being built. This allows you to check that lists of things are displaying as they should, and that themes are working when they get data in them.

Historically it's been a pain to add that data in. Either you need to take a backup of another site and use that data, or you need to tediously create multiple users and blogs yourself. No more, not now my Demo Data Creator is in town!

This Wordpress MU plugin gives you a new admin screen where you can enter some parameters, click a button and (after a short wait) random demo data will be created. The parameter options include:

    * The number of users
    * Whether you want just user, or users and blogs
    * Maximum number of blogs per user
    * Maximum number of posts per blog
    * Maximum number of blog post paragraphs
    * Maximum number of comments per post
    * Maximum number of links in blogroll

Post content and comment text is automatically generated from Lorem ipsum text, for post content it’s even HTML-formatted. You can also set a template for what you want the blog URLs to be, for example demoblog-1.mysite.com.

== Installation ==

The plugin should be placed in your /wp-content/mu-plugins/ directory (*not* /wp-content/plugins/) and requires no activation.

== Frequently Asked Questions ==

= Why did you write this plugin? =

To scratch my own itch when developing [BeatsBase.com](http://beatsbase.com "Free mix hosting for DJs"). Hopefully this plugin helps other developers too.

= Are you planning to support BuddyPress =

Yes, but I'm not sure when I'll have time to develop that section of the plugin.
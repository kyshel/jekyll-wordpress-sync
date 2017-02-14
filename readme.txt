=== Jekyll Wordpress Sync ===
Contributors: kyshel
Donate link: https://paypal.me/kyshel2016
Tags: jekyll, import
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A wordpress plugin to import posts from a jekyll site.

== Description ==

Jekyll-Wordpress-Sync is inspired by an idea: One Content Source, Multi-site publish.
Here, jekyll site is content source, wordpress is publish site.
All the post data was transferred by GitHub API.

== Installation ==

1. Download the repo,Unzip it to wordpress\wp-content\plugins\jekyll-wordpress-sync
2. Open Dashboard -> Plugins , activate Jekyll Wordpress Sync
3. Open Dashboard -> Jekyll-WP-Sync -> Setting , set the Repository and Github Token
4. Open Dashboard -> Jekyll-WP-Sync -> Sync, click Analyze
5. If analyze result is satisfied, click Sync Now to finish sync


== Frequently Asked Questions ==

= What should I do if my jekyll was not host on github? =

JWS is developed based on Github API, so just push your jekyll(remove _site directory) to github to finish sync, after sync complete, you can reserve the repo or delete it from github.



== Screenshots ==

1. Setting Page
2. Posts list that ready to sync 

== Changelog ==

= 0.1 =
First Version.


== Upgrade Notice ==

= 0.1 =
First realease.



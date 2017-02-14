# Jekyll Wordpress Sync

A wordpress plugin to import posts from a jekyll site.

## Introduction
JWS is inspired by an idea: One Content Source, Multi-site publish.    
Here, jekyll site is content source, wordpress is publish site.    
All the post data was transferred by GitHub API.


## Usage
1. Download [the repo](https://github.com/kyshel/jekyll-wordpress-sync/archive/master.zip), unzip it to `wordpress\wp-content\plugins\jekyll-wordpress-sync`
2. Open Dashboard -> Plugins , activate Jekyll Wordpress Sync
3. Open Dashboard -> Jekyll-WP-Sync -> Setting , set the **Repository** and [**Github Token**](https://github.com/blog/1509-personal-api-tokens)
4. Open Dashboard -> Jekyll-WP-Sync -> Sync, click **Analyze** 
5. If analyze result is satisfied, click **Sync Now** to finish sync

## Info
- [A simple video guide](https://www.youtube.com/watch?v=nUq85s_qrVk&feature=youtu.be)
- Repository name must match format: `[OWNER]/[REPOSITORY]` Example: `kyshel/kyshel.github.io`
- Sync time depend on **wordpress server request&get time** to github.com
- [Shortage](https://github.com/kyshel/jekyll-wordpress-sync/issues/1)
- [Forward](https://github.com/kyshel/jekyll-wordpress-sync/issues/2)


## Credits
Made with ❤ by [kyshel](https://github.com/kyshel)    
License: GPLv2 

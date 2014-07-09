News SEO for WordPress SEO
==========================

News SEO module for the WordPress SEO plugin.

Installation
============

1. Go to Plugins -> Add New.
2. Click "Upload" right underneath "Install Plugins".
3. Upload the zip file that this readme was contained in.
4. Activate the plugin.
5. Go to SEO -> Extensions -> Licenses, enter your license key and Save.
6. Your license key will be validated.
7. You can now use News SEO. See also https://yoast.com/wordpress/plugins/news-seo/news-seo-configuration-guide/

Changelog
=========

### 2.1: July 9th, 2014
* Several performance optimizations for sitemap generation.
* Added button that links to news sitemap on admin page.
* Added an option to include only the featured image in the XML News sitemap.
* Introduced filter `wpseo_locale` for locale/language of the XML News sitemap.
* Introduced filter `wpseo_news_sitemap_url` to allow changing the XML News sitemap URL.

### 2.0.6: June 10th, 2014
* Removed the wptexturize filter from the_title and the_content in the Editors' Pick feed because corrupts our output.
* Added guid elements to item elements in the Editors' Pick feed.
* Added an atom:link element as recommended by the RSS Advisory Board to identifying a feed's URL within the feed.
* Added the WPSEO News fields to the WPSEO meta fields class to fix a bug where the post meta genre field isn't saved.

### 2.0.5: June 5th, 2014
* Fixed a publication_date timezone bug.

### 2.0.4: May 15th, 2014
* Bugfixes
  * Add CDATA tags to RSS feed text output.
  * Now using the same title for the Editors' Pick title as the channel title.

### 2.0.3: April 23rd, 2014
* Enhancement
  * Sitemaps now use creation dates instead of modified dates.

### 2.0.2: April 22nd, 2014
* Bugfixes
  * Fixed a bug with version_compare.

* Enhancement
  * Adds sanitize callback to register_settings.

### 2.0.1: April 22nd, 2014
* Bugfix
  * Changed EDD product name to 'News SEO'.

### 2.0: April 22nd, 2014
* Initial release

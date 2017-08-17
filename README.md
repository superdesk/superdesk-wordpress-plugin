# What is the WordPress Superdesk Publisher plugin?
The Superdesk WordPress Publisher plugin enables content to be sent from an instance of Sourcefabric's Superdesk newsroom management system (www.superdesk.org) and the WordPress instance it's installed on. Superdesk itself is open source (https://github.com/superdesk/superdesk).

# What does the WordPress Superdesk Publisher plugin do?
When both Superdesk and the WordPress Superdesk Publisher plugin are correctly configured, a Superdesk user publishes an item and it is automatically published in WordPress. Users can still log in to WordPress to administer the site.

# How does it work?
Superdesk supports the IPTC's industry standard ninjs (http://dev.iptc.org/ninjs), which standardizes the representation of news in JSON - a lightweight, easy-to-parse, data interchange format. The Superdesk WordPress Publisher plugin will also enable multiple instances of WordPress to receive content from a single instance of Superdesk; output channels are defined in Superdesk, so you could have multiple, or only one, or none at all (which wouldn't be that useful but still possible (wink)). This plugin should also work with other information sources that use ninjs.

We've created a table to map fields between Superdesk's ninjs implementation and WordPress: https://wiki.sourcefabric.org/display/NR/Ninjs+to+WP+data+structure+mapping

On the Superdesk side, there are publishing providers which are the ones in charge of actually delivering the content in the configured format and delivery mechanism; this is done on a publishing channel basis in Superdesk.

Stories are checked in Superdesk as published, then the publishing providers take over to distribute the content.

The Superdesk WordPress Publisher plugin currently uses Superdesk's HTTP PUSH to receive content in ninjs. When the plugin receives new content via HTTP PUSH, it automatically loads this content into the WordPress database via the REST API.

# Settings menu
A settings menu in WordPress lets you customize the way WordPress handles content from Superdesk. For example, you can map Superdesk's categories to WordPress's tags, or use Superdesk subjects as categories. Authors, copyright information, post status, and default categories are just some of the WordPress fields that can be controlled from the Settings menu.

# What's next?
We do not have a native WordPress publishing provider in Superdesk, and there is no plan to write one in the current roadmap. We prefer to use ninjs as an standard interchange format.

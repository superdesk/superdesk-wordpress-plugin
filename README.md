# What is the Superdesk plugin for WordPress?
The Superdesk plugin for WordPress enables content to be sent from an instance of Sourcefabric's Superdesk newsroom management system (www.superdesk.org) to the WordPress instance it's installed on. Superdesk itself is open source (https://github.com/superdesk/superdesk).

# What does the Superdesk plugin for WordPress do?
The Superdesk plugin for WordPress receives articles in the IPTC ninjs format from Superdesk and stores them in WordPress.

# How does it work?
Superdesk supports the IPTC's industry standard ninjs (https://www.iptc.org/std/ninjs/userguide/), which standardizes the representation of news in JSON - a lightweight, easy-to-parse data interchange format. The Superdesk plugin for WordPress will also enable multiple instances of WordPress to receive content from a single instance of Superdesk; output channels are defined in Superdesk, so you could have multiple or only one. This plugin should also work with other content sources that use ninjs.

On the Superdesk side, there are publishing providers that are in charge of actually delivering the content in the configured format and delivery mechanism; this is done on a publishing channel basis in Superdesk.

Stories are checked in Superdesk as published (pushed), then the publishing providers take over to distribute the content.

The Superdesk plugin for WordPress currently uses Superdesk's HTTP PUSH to receive content in ninjs. When the plugin receives new content via HTTP PUSH, it automatically loads this content into the WordPress database via the REST API.

# Settings
A settings menu in WordPress lets you customize the way WordPress handles content from Superdesk. For example, you can map Superdesk's categories to WordPress's tags or use Superdesk subjects as categories. Authors, copyright information, post status, and default categories are just some of the WordPress fields that can be controlled from the Settings menu.

# Is there a native WordPress publishing provider?
We do not have a native WordPress publishing provider in Superdesk, and there is no plan to write one in the current roadmap. We prefer to use ninjs as a standard interchange format.

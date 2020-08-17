=== WooCommerce Bookings Instructor Helper ===
Contributors: jessepearson
Tags: woocommerce bookings, resource, instructor
Requires at least: 5.0.0
Tested up to: 5.5.0
Stable tag: 1.0.0

License: GPLv2 or later


== Description ==

WooCommerce Bookings Instructor Helper adds additional functionality to the WooCommerce Bookings extension, and requires WooCommerce Bookings to function.

There are instances, typically with instructors of some sort, where they would like to have two or more products joined by a single resource that have an availability greater than 1, and would like to have it to when one product gets booked, all the rest get their availability blocked off. This does that. 

Imagine an instructor has 3 classes, each is a bookable product. Those products each have a shared single resource, the instructor. The instructor's availability is 10, since each class can have 10 people in it. They would like to have each of the products available all the time, and not just certain times. This allows for more booking flexibility. They would like to have it so if someone books a slot in class 1, then classes 2 and 3 are not available for that time or day. That's what this plugin accomplished by setting availability rules on the related products, making those days or slots not available. 


= Features =

* Automatically adds availability rules to bookable products when a booking is made on a related product. 
* Also automatically updates or removes those rules if the booking is edited or removed. 

== Installation ==

1. Download the .zip file.
1. Go to Plugins > Add New and choose Upload at the top.
1. Upload the .zip file and activate. 


= Usage =

After installing & activating the plugin:

1. Go to the resource under Bookings > Resources you'd like to apply this to.
1. Enable the Availability automation.
1. That's it.

In order for this to work:

* There must be only resource on the products,
* There must be more than one product related to the resource,
* The resource should have an availability higher than 1. 


== Changelog ==
= 1.0.0 =
* Initial release.
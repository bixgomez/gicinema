=== iPanorama 360 - Advanced Virtual Tour Builder ===
Contributors: Avirtum
Tags: virtual tour, panorama, panorama viewer, virtual tour, 360 panorama
Requires at least: 4.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.9.1
License: GPLv3

Let's create virtual tours for your site that empowers your visitors and clients!!! Build a live tour in just a few steps.

== Description ==

iPanorama 360 is the WordPress plugin out there that lets you create excellent virtual tours for clients from directly inside the WordPress admin in seconds. The plugin supports markers for providing information about any part of the scene or for navigation to other rooms/areas. With a well-builded tooltip system, you can enrich a scene with text, images, video, and other online media resources. Use this plugin to create interactive & virtual tours, maps, presentations.

Just upload 360 panoramic images, and this plugin will help you to transform them into a good realistic virtual tour.

>iPanorama 360 supports the classic shortcode, plus it's fully compatible with Gutenberg Block Editor and has iFrame embed feature that help to share your tour on a third-party web resources.

== Why use iPanorama 360? ==
This plugin has all features that are needed for your deployed a good virtual tour without any problem.

https://youtu.be/4EKn6LWUwjM


**Powerful Builder Interface To Create Virtual Tours Easily**

iPanorama 360 has a modern web interface, created to help you build an interactive virtual tour quick and easy.

>Just imagine, you can create your first virtual tour in seconds!!!

The plugin supports the preview mode, it will help you to see all your changes before the publish. Isn't that a good feature?)


**Create A Realistic Tour From Scene to Another**

You can create several scenes from 360 degree images and connect them using markers. The user can click on a marker to go previous or  next scene. The walking to another scene can be styled by using a special transition effects (fade, zoom, twist and etc). Plus you can set tooltips & popovers for markers on each scene to show additional information to a viewer.


***Shortcode Support***

Inside the classic editor you can simply embed a virtual tour using this **[ipano id="123"]** shortcode. Just set your virtual tour ID and your tour is ready to publish.

***Gutenberg Block Support***

Inside the Gutenberg block editor you can find the iPanorama 360 block, see the common section. This block is used to embed your virtual tour quckly on a page or post. Select a virtual tour ID from the list, set width & height if it's needed and the tour is ready to go.

***IFrame Embed Feature Support***

You can share your tour on other web sites using the html iframe. To do this you should go to the tour builder, select the "shortcode" section and copy/paste the embed iframe code.

**Control Permissions for Authors and Editors**

The plugin gives you a way to edit the access for wp roles to create virtual tours. Only selected roles can modify virtual tours. Each selected role can have one state from this list: 'private', 'group', 'all'.

- **private** - you can create & edit only your items
- **group** - you can create & edit the group items
- **all** - you can create & edit all items (from any group, including admin items)


==Who Should Use iPanorama 360?==

- Stores
- Schools
- Museums
- Showrooms
- Travel Agency
- Art Galleries
- Real Estate Agency

and much more...


= List of features =

* Easy To Use Builder
* Gutenberg block support
* Create Multiple Scenes (5 types)
* Flat Scene
* Sphere & Cube Scene
* Little Planet Scene
* Google Street View Scene
* Create Markers & Tooltips & Popovers
* Two Popover Styles (inbox, lightbox)
* Marker Style Creator
* Customization with Custom CSS
* Tooltips Show & Hide Animations (50+)
* Popover Show & Hide Animations (50+)
* Connect Scenes via Special Markers
* Duplicate Tour on One Click
* Scene Transition Animation
* Live Preview Virtual Tour Before Publish
* Gyroscope & Keyboard Navigation
* Auto Rotation (play, stop, speed)
* Audio Background (play, stop, volume)
* Normal & Stereo View
* Thumbnails Preview
* Embed iframe Feature
* Responsive Default Design
* Export & Import Config
* Two Themes (light & dark)
* Permission System For WP User Roles
* Great Customization
* Powerful API

https://youtu.be/DA8Dh8vP7cY


== Installation ==

1. Upload the entire **ipanorama** folder to the **/wp-content/plugins/** directory.
2. Activate the plugin through the **Plugins** menu in WordPress admin.
3. Create a new virtual tour, Use **[ipano id="123"]** shortcode to publish your virtual tour into any page or post.


== Screenshots ==

1. List of virtual tours
2. Scene creation
4. Setup the scene autorotation
5. Setup the audio player
6. Shortcode & Embed


== Frequently Asked Questions ==

= Why should I use iPanorama 360? =
You can easily create a virtual tour for your web site using this plugin. Just provide a 360 degree panoramic photo and the plugin will do all job for you. To attract users more you can add markers with tooltips which include additional information, add more scenes to navigate too.

= Installation =
You can download the plugin from wordpress.org. Once you have downloaded it, you can go to your dashboard, select plugins item and upload the file. Then activate the plugin. Once activated, on the left side under your dashboard, you will find the option iPanorama 360 at the bottom.

= I'd like access to more features and support. How can I get them? =
You can get access to more features and support by visiting the CodeCanyon website and [purchasing the pro version](https://1.envato.market/getipanorama360). Purchasing the plugin gets you to access to the full version of the iPanorama 360 Virtual Tour Builder WordPress plugin, automatic updates and support.

= What is the difference between Lite and PRO =
The lite version has only one limitation. You can create and use only one item. All other features are the same as PRO has.


== Changelog ==

= 1.9.1 =
* Fix: resolved 403 errors occurring when strict HTTP standards enforcement is enabled

= 1.9.0 =
* Mod: lazy load mode – panorama is loaded only when visible in the web view screen
* New: toggle the white logo

= 1.8.8 =
* Fix: issue with marker position depending on device pixel ratio (devicePixelRatio)

= 1.8.7 =
* Fix: support iframe for popup and tooltip data

= 1.8.6 =
* Fix: scene transition effects, black screen
* Fix: tooltip & popover data sanitization

= 1.8.5 =
* Mod: upgraded the threejs lib to r127
* Mod: redesigned the UI

= 1.8.4 =
* Fix: unauthorized access to preview of deactivated panoramas for sub+ users

= 1.8.3 =
* Fix: constant FILTER_SANITIZE_STRIPPED is deprecated
* Fix: unauthorized access to config.json of deactivated panoramas

= 1.8.2 =
* Fix: unauthorized access to view deactivated panoramas

= 1.8.1 =
* Fix: feedback form
* Fix: SQL injection via shortcode

= 1.8.0 =
* Fix: SQL injection vulnerability (list-table-items.php)

= 1.7.3 =
* Fix: constant FILTER_SANITIZE_STRING is deprecated

= 1.7.2 =
* Fix: incorrect rest_rout url for the plain permalink type

= 1.7.1 =
* Fix: MySQL current_timestamp update

= 1.7.0 =
* Mod: cancel the use of json files to store the item config

= 1.6.32 =
* Fix: $wpdb->prepare calls

= 1.6.31 =
* Fix: incorrect render order

= 1.6.30 =
* Fix: prevent cross-site scripting (XSS) from shortcode
* Mod: polish the code

= 1.6.29 =
* Fix: empty list in wp 6.1
* New: feedback form

= 1.6.28 =
* Fix: scene preload images

= 1.6.27 =
* Fix: warped images

= 1.6.26 =
* Fix: unexpected output during activation

= 1.6.25 =
* Mod: texture loading improvements

= 1.6.24 =
* Fix: hidden markers in RTL mode

= 1.6.23 =
* Fix: pinch zoom for the flat scene type

= 1.6.22 =
* Fix: prevent XSS attacks

= 1.6.21 =
* New: set the compass North from the preview window

= 1.6.20 =
* New: set the start point coord from the preview window
* Fix: popover close on click outside
* Fix: audio & video continues play after a popover close

= 1.6.19 =
* New: Gutenberg block support

= 1.6.18 =
* Fix: permissions list

= 1.6.17 =
* Fix: permissions for the role can be: 'private', 'group', 'all'

= 1.6.16 =
* New: show the current scene pos & zoom
* New: permissions for the role can be private or general

= 1.6.15 =
* New: shortcode attribute "customdata"
* Fix: zoom for the flat type scene

= 1.6.14 =
* Fix: marker autofocus

= 1.6.13 =
* Fix: can't change the markers position
* Fix: can't open an item, the infinity loading

= 1.6.12 =
* Fix: item date
* Mod: audio player with a new interface
* New: global styles & the 'onLoad' callback

= 1.6.11 =
* Fix: removing disabled elements from the output
* Fix: converting the url protocol from HTTP to HTTPS if needed
* New: search item box, trash support
* New: shortcode attribute 'slug'

= 1.6.10 =
* Fix: widgets thumbnail images lazyload

= 1.6.9 =
* Fix: website under HTTPS (secured with SSL certificate), but scene images are under HTTP (unsecured)
* Mod: preload image generation

= 1.6.8 =
* New: compass support
* New: tooltip show trigger "enter"

= 1.6.7 =
* Fix: 'civic' widget home button
* Mod: tooltip show/hide actions after the hover event
* New: load a scene from the URL param 'sceneid'
* New: support Emoji

= 1.6.6 =
* Fix: markers from the previous scenes are accessible even though they are invisible
* Fix: can't navigate to a google street scene type
* Fix: the builder improperly adds a marker to the center of the scene

= 1.6.5 =
* Fix: marker "go to a scene" option not shown
* Fix: virtual tour doesn't work on some Mac, empty scene

= 1.6.4 =
* Fix: save & load an item from a config file

= 1.6.3 =
* New: preload images to get faster pano loading experience
* New: shortcode attribute 'width', 'height'

= 1.6.2 =
* Fix: camera save option doesn't work (cause black screen)
* Fix: look at a shape
* Mod: add the core plugin versioning to the loader

= 1.6.1 =
* Fix: loader can't load old items

= 1.6.0 =
* New: scene transition effects (fade, zoom, swirl and etc)

= 1.5.25 =
* Fix: compatibly with old items (resave item)

= 1.5.24 =
* Fix: marker positions for the flat scene type
* Fix: widget styles

= 1.5.23 =
* Fix: disabled the marker autoFocus & go to a new scene
* Mod: sets the camera lookAt position via yaw & pitch values
* New: sets the marker position via yaw & pitch values
* New: DB field 'editor'

= 1.5.22 =
* Fix: super admin can't see menu items
* Fix: on IE is no vertical scroll bar (lightbox & inbox)

= 1.5.21 =
* New: item slug (URL valid name)
* Mod: file system operations

= 1.5.20 =
* New: options for preview & iframe embed page
* New: image for a marker
* New: auto focus on a marker after the click event
* Mod: light & dark themes, the 'civic' widget
* Fix: FontAwesome i2svg breaks the admin frontend

= 1.5.19 =
* New: edit roles with access to the plugin

= 1.5.18 =
* Fix: loader is called only once on a page

= 1.5.17 =
* Mod: user can view & edit only their items

= 1.5.16 =
* Fix: audio options (stop previous)
* Fix: popovers in the fullscreen mode
* Mod: items pagination view

= 1.5.15 =
* Fix: pinch zoom for scene types: 'sphere', 'cube', 'flat', 'gsv'

= 1.5.14 =
* Fix: undefined variable timestamp

= 1.5.13 =
* Fix: base css styles
* New: widget - 'List'

= 1.5.12 =
* Fix: marker creation for the flat scene type
* Mod: fullscreen mode for widgets: 'Civic', 'Modern'
* Mod: preview & iframe embed feature out of the box

= 1.5.11 =
* Fix: marker mouse pointer still appears after scene is changed
* Fix: markers scrolling for the flat scene type
* Fix: scenes navigation for the civic widget

= 1.5.10 =
* New: edit permissions for roles: administrator, editor, author, contributor
* Fix: warnings with framebuffer is incomplete

= 1.5.9 =
* New: shapes (planes with texture)
* New: container background styles can be inline or not
* New: shortcode attribute 'sceneid'

= 1.5.8 =
* New: popover feature (inbox or lightbox)

= 1.5.7 =
* New: theme - 'bubbles'
* Fix: update widgets

= 1.5.6 =
* Fix: bug with the browser cache, don't see updates after changes

= 1.5.5 =
* New: widget - 'Civic'
* New: embed & preview is independent of WordPress themes and third-party plugins

= 1.5.4 =
* Fix: lost details in the fullscreen mode

= 1.5.3 =
* Fix: cubemap scene, front & bottom images issue

= 1.5.2 =
* Fix: preview & embed URL generation

= 1.5.1 =
* Fix: save & load config to and from a file
* New: interactive preview image builder

= 1.5.0 =
* New: absolutely new version, incompatible with the old one

= 1.4.0 =
* Fix: transfer userData & title parameters to frontend

= 1.3.9 =
* Fix: black screen issue in Chrome with zooming
* Fix: fullscreen toggle

= 1.3.8 =
* Fix: compatible with Smart Product Viewer
* Fix: exit full-screen mode in IE

= 1.3.7 =
* New: save or not the camera look at vector if you move between scenes
* New: top and bottom pitch limits for each scene
* New: left and right yaw limits for each scene
* Fix: scene images background load
* Fix: fullscreen on iOS

= 1.3.6 =
* Fix: bug with a short touch on a link hotspot
* Fix: bug with a sceneId parameter in frontend

= 1.3.5 =
* Fix: bug with multiple instances on one page

= 1.3.4 =
* Fix: works better with touch events
* Fix: sometimes lost webgl context in FF
* Fix: bug with fullscreen on IE11

= 1.3.3 =
* New: global settings
* New: zoom by pinch gestures
* New: the cube scene type can have one single texture or six separated
* Fix: bug with fullscreen on iOS (leave only standard fullscreen API)

= 1.3.2 =
* New: top and bottom pitch limits
* New: specifying a custom onload callback javascript code
* New: enable/disable the prevention for default behavior on the mouseWheel event
* Fix: bug with loading a saved config
* Fix: bug with appearing hotspot images in the upper left corner

= 1.3.1 =
* Fix: bug with distortion

= 1.3.0 =
* Fix: bug with char encoding, problems with item update
* New: image URL can be local relative to the upload folder or full
* New: easy hotspot customization

= 1.2.1 =
* New: add loading progress bar for the builder
* Fix: support multiple angular.js versions on the same page
* Fix: warning about array_map() after the item has updated

= 1.2.0 =
* New: new dark theme
* New: thumbnails (vertical, horizontal)
* New: thumbnails toggle control
* New: next & Prev scene controls
* New: show a popover when the scene's loaded
* New: save & load a config from file
* New: default width and height settings

= 1.1.0 =
* New: updated the jQuery plugin

= 1.0.0 =
* Initial release
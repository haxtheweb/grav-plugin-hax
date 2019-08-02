# Grav HAX Plugin

HAX is a [Grav](http://github.com/getgrav/grav) plugin that can be used to get the HAX editor integrated into your Grav site with ease.

## Installation

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm).  From the root of your Grav install type:

    bin/gpm install hax

### Manual Installation

If for some reason you can't use GPM you can manually install this plugin. Download the zip version of this repository and unzip it under `GRAV-INSTALL-ROOT/user/plugins`. Then, rename the folder to `hax`.

You should now have all the plugin files under:

    GRAV-INSTALL-ROOT/user/plugins/hax

Also note that if you do this method you will need to install the [webcomponents](https://github.com/elmsln/grav-plugin-webcomponents) plugin as well.

## Usage

This should give you the dependencies you need to get going.
1. Enable the HAX plugin and any dependencies it requires.
2. The default is to serve JS assets up from a CDN as per the webcomponents plugin.
   Should you need to change this keep reading into building your own assets.

Important note: HAX generates HTML, and while Grav can handle HTML, it's Markdown parser can get angry about HTML tags + no endlines. As HAX tries to generate markup automatically, it can get mad and it will appear that content disappears, however this is the Markdown parser conflicting with HTML. Disable markdown processing on posts that you use HAX with, and it should be fine.

HAX is **enabled** by default.  You can change this behavior by setting `enabled: false` in the plugin's configuration.  Simply copy the `user/plugins/hax/hax.yaml` into `user/config/plugins/hax.yaml` and make your modifications. There is also a variable to adjust what elements to auto-load (or attempt to) during spin up
which you can also modify via the UI.

```yaml
enabled: true # enabled by default
autoload_element_list: 'video-player wikipedia-query pdf-element lrn-table media-image' # a sample list of elements to expose.
```
## Settings

The settings page has ways of hooking up youtube, vimeo and more via the "App
store" concept built into HAX. You can also make small tweaks to your needs on
this page.

## End user

Go to the node's hax tab, then hit the pencil in the top right. When your done
editing hit the power button again and it should confirm that it has saved back
to the server. Congratulations on destoying the modules you need to build an
awesome site!

### Developer functions
By default, the auto-loaded elements will append to the page on node view mode
full. To override this, set hax_autoload_element_node_view to false in
settings.php

# Front end Developers
You may build HAX from source if needed. HAX defaults to use CDNs which will effectively point to
this directory or some mutation of it -- https://github.com/elmsln/HAXcms/tree/master/build

If you want to build everything from source, your welcome to use yarn / npm to do so though our
build routine effectively will end in the same net result.  If you want to do custom build routines
such as rollup or webpack and not use our prebuilt copies / split build approaches, then your welcome
to check the box related to not loading front end assets in the settings page in order to tailor
the build to your specific needs.

## Getting dependencies
You need polymer cli (not polymer but the CLI library) in order to interface with web components in your site. Get polymer cli installed prior to usage of this (and (yarn)[https://yarnpkg.com/lang/en/docs/install/#mac-stable] / an npm client of some kind)
```bash
$ yarn global add polymer-cli
```
Perform this on your computer locally, this doesn't have to be installed on your server.

## Usage
- Find `CopyThisStuff` directory in `/your/site/grav/user/plugins/webcomponents`.
- create a `/your/site/grav/user/data/webcomponents` directory
- copy the files from `CopyThisStuff` into `/your/site/grav/user/data/webcomponents`

Then run the following (from the directory you copied it over to) in order to get dependencies:
```bash
$ yarn install
```
Now run `polymer build` and you'll have files in `build/` which contain everything you'll need to get wired up to web components in your site. Modifying build.js or package.json can be used in order to get new elements and have them be implemented.

### Shouldn't I put web components in my theme?
We don't think so. While it may seem counter intuitive, the theme layer should be effectively implementing what the site is saying is available. If you think of standard HTML tags are being part of this (p, div, a, etc) then it makes a bit more sense. You don't want functional HTML components to ONLY be supplied if your theme is there, you want your theme to implement and leverage the components.

## New to web components?
We built our own tooling to take the guess work out of creating, publishing and testing web components for HAX and other projects. We highly recommend you use this tooling though it's not required:
- https://open-wc.org - great, simple tooling and open community resource
- https://github.com/elmsln/wcfactory - Build your own web component library
- https://github.com/elmsln/lrnwebcomponents - Our invoking of this tooling to see what a filled out repo looks like

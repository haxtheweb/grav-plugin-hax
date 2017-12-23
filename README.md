# Grav HAX Plugin

HAX is a [Grav](http://github.com/getgrav/grav) plugin that can be used to get the HAX editor integrated into your Grav site with ease. Simply run from inside the hax plugin directory

# Installation

## GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm).  From the root of your Grav install type:

    bin/gpm install hax

## Manual Installation

If for some reason you can't use GPM you can manually install this plugin. Download the zip version of this repository and unzip it under `GRAV-INSTALL-ROOT/user/plugins`. Then, rename the folder to `hax`.

You should now have all the plugin files under:

	GRAV-INSTALL-ROOT/user/plugins/hax

# Usage

Run `bower install` inside the `GRAV-INSTALL-ROOT/user/plugins/hax`


## Configuration

HAX is **enabled** by default.  You can change this behavior by setting `enasbled: false` in the plugin's configuration.  Simply copy the `user/plugins/hax/hax.yaml` into `user/config/plugins/hax.yaml` and make your modifications. There is also a variable to adjust positioning of the hax context menus based on theme interference as well as what elements to auto-load (or attempt to) during spin up.

```
enabled: true                       # enabled by default
autoload_element_list: 'video-player wikipedia-query pdf-element lrn-table media-image'                        # a sample list of elements to expose. these will be removed as HAX matures and be optional / pointed to as far as what's compatible
offset_left: 0                  # theme compatibility if it's goofed up

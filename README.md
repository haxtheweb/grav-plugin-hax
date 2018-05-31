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

## Simple Usage

Create this folder if it doesn't exist yet: `GRAV-INSTALL-ROOT/user/data/webcomponents`. Copy `GRAV-INSTALL-ROOT/user/plugins/hax/bower.json` to `GRAV-INSTALL-ROOT/user/data/webcomponents/bower.json`. Then go to `GRAV-INSTALL-ROOT/user/webcomponents` and run `bower install LRNWebComponents/wysiwyg-hax`. Answer any questions about dependencies that it has (we try to auto select these as best we can).

### Configuration

Important note: HAX generates HTML, and while Grav can handle HTML, it's Markdown parser can get angry about HTML tags + no endlines. As HAX tries to generate markup automatically, it can get mad and it will appear that content disappears, however this is the Markdown parser conflicting with HTML. Disable markdown processing on posts that you use HAX with, and it should be fine.

HAX is **enabled** by default.  You can change this behavior by setting `enabled: false` in the plugin's configuration.  Simply copy the `user/plugins/hax/hax.yaml` into `user/config/plugins/hax.yaml` and make your modifications. There is also a variable to adjust positioning of the hax context menus based on theme interference as well as what elements to auto-load (or attempt to) during spin up.


```yaml
enabled: true                       # enabled by default
autoload_element_list: 'video-player wikipedia-query pdf-element lrn-table media-image'                        # a sample list of elements to expose. these will be removed as HAX matures and be optional / pointed to as far as what's compatible
offset_left: 0                  # theme compatibility if it's goofed up

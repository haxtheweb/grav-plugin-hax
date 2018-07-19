<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use \Grav\Common\Grav;
use \Grav\Common\Utils;
use \Grav\Common\Page\Page;
use \Grav\Common\Session;
use RocketTheme\Toolbox\Event\Event;
use RocketTheme\Toolbox\File\File;
use Symfony\Component\Yaml\Yaml;
use Grav\Plugin\AtoolsPlugin;
use Grav\Plugin\WebcomponentsPlugin;

define('HAX_DEFAULT_OFFSET', 0);
define('HAX_DEFAULT_ELEMENTS', 'video-player wikipedia-query pdf-element lrn-table media-image');

class HAXPlugin extends Plugin {
  public static function getSubscribedEvents() {
    return ['onPluginsInitialized' => ['onPluginsInitialized', 0], 'onTwigSiteVariables' => ['onTwigSiteVariables', 0]];
  }
  public function onPluginsInitialized() {
      // Verify installation files.
      if (!($this->verifyWebcomponentsInstallation())){
        return;
      }

    // Only use HAX if in normal admin mode, not expert mode.
    if($this->isAdmin() && ($this->grav['uri']->param("mode") != "expert")) {
      $this->grav['locator']->addPath('blueprints', '', __DIR__ . DS . 'blueprints');
      $this->enable(['onTwigTemplatePaths' => ['onTwigTemplatePaths', 999]]);
    }
  }
  public function onTwigTemplatePaths() {
    $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
  }
  public function onTwigSiteVariables() {
    if($this->isAdmin() && strpos($this->grav['uri']->route(), $this->config['plugins']['admin']['route'] . '/pages/') !== false) {
      // this is the admin area
      $offsetLeft = $this->config->get('plugins.hax.offset_left');
      // discover and autoload our components
      $assets = $this->grav['assets'];
      // Webcomponents plugin doesn't include the polyfill for admin editing pages. Adding it here.
      $assets->addJS('user/data/webcomponents/bower_components/webcomponentsjs/' . WebcomponentsPlugin::polyfillLibrary(), array('priority' => 1000, 'group' => 'head'));
      $file = $this->getBaseURL() . 'bower_components/wysiwyg-hax/wysiwyg-hax.html';
      $imports = $this->createHTMLImport($file) . "\n";
      // build the inline import w/ bable helpers
      $inline = '!function(e){var r=e.babelHelpers={};r.typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},r.classCallCheck=function(e,r){if(!(e instanceof r))throw new TypeError("Cannot call a class as a function")},r.createClass=function(){function e(e,r){for(var t=0;t<r.length;t++){var n=r[t];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(r,t,n){return t&&e(r.prototype,t),n&&e(r,n),r}}(),r.defineEnumerableProperties=function(e,r){for(var t in r){var n=r[t];n.configurable=n.enumerable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,t,n)}return e},r.defaults=function(e,r){for(var t=Object.getOwnPropertyNames(r),n=0;n<t.length;n++){var o=t[n],i=Object.getOwnPropertyDescriptor(r,o);i&&i.configurable&&void 0===e[o]&&Object.defineProperty(e,o,i)}return e},r.defineProperty=function(e,r,t){return r in e?Object.defineProperty(e,r,{value:t,enumerable:!0,configurable:!0,writable:!0}):e[r]=t,e},r.extends=Object.assign||function(e){for(var r=1;r<arguments.length;r++){var t=arguments[r];for(var n in t)Object.prototype.hasOwnProperty.call(t,n)&&(e[n]=t[n])}return e},r.get=function e(r,t,n){null===r&&(r=Function.prototype);var o=Object.getOwnPropertyDescriptor(r,t);if(void 0===o){var i=Object.getPrototypeOf(r);return null===i?void 0:e(i,t,n)}if("value"in o)return o.value;var a=o.get;if(void 0!==a)return a.call(n)},r.inherits=function(e,r){if("function"!=typeof r&&null!==r)throw new TypeError("Super expression must either be null or a function, not "+typeof r);e.prototype=Object.create(r&&r.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),r&&(Object.setPrototypeOf?Object.setPrototypeOf(e,r):e.__proto__=r)},r.instanceof=function(e,r){return null!=r&&"undefined"!=typeof Symbol&&r[Symbol.hasInstance]?r[Symbol.hasInstance](e):e instanceof r},r.newArrowCheck=function(e,r){if(e!==r)throw new TypeError("Cannot instantiate an arrow function")},r.objectDestructuringEmpty=function(e){if(null==e)throw new TypeError("Cannot destructure undefined")},r.objectWithoutProperties=function(e,r){var t={};for(var n in e)r.indexOf(n)>=0||Object.prototype.hasOwnProperty.call(e,n)&&(t[n]=e[n]);return t},r.possibleConstructorReturn=function(e,r){if(!e)throw new ReferenceError("this hasn\'t been initialised - super() hasn\'t been called");return!r||"object"!=typeof r&&"function"!=typeof r?e:r},r.set=function e(r,t,n,o){var i=Object.getOwnPropertyDescriptor(r,t);if(void 0===i){var a=Object.getPrototypeOf(r);null!==a&&e(a,t,n,o)}else if("value"in i&&i.writable)i.value=n;else{var u=i.set;void 0!==u&&u.call(o,n)}return n},r.slicedToArray=function(){function e(e,r){var t=[],n=!0,o=!1,i=void 0;try{for(var a,u=e[Symbol.iterator]();!(n=(a=u.next()).done)&&(t.push(a.value),!r||t.length!==r);n=!0);}catch(e){o=!0,i=e}finally{try{!n&&u.return&&u.return()}finally{if(o)throw i}}return t}return function(r,t){if(Array.isArray(r))return r;if(Symbol.iterator in Object(r))return e(r,t);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),r.taggedTemplateLiteral=function(e,r){return Object.freeze(Object.defineProperties(e,{raw:{value:Object.freeze(r)}}))},r.temporalRef=function(e,r,t){if(e===t)throw new ReferenceError(r+" is not defined - temporal dead zone");return e},r.temporalUndefined={},r.toArray=function(e){return Array.isArray(e)?e:Array.from(e)},r.toConsumableArray=function(e){if(Array.isArray(e)){for(var r=0,t=Array(e.length);r<e.length;r++)t[r]=e[r];return t}return Array.from(e)}}("undefined"==typeof global?self:global)
  </script>' . $imports . "<script>";
      $assets->addCSS('plugin://hax/hax.css', array('priority' => 103, 'group' => 'head'));
       // add it into the document
      $assets->addInlineJs($inline, array('priority' => 103, 'group' => 'head'));
      $location = '';
      $paths2 = $this->grav['uri'];
      $paths = $this->grav['uri']->paths();
      // Remove the "admin" and "pages" from paths
      foreach ($paths as $key => $path) {
        if ($key > 1) {
          $location .= "/" . $path ;
        }
      }
      $pagebody = '';
      // ensure we have this otherwise it's a new page
      if (method_exists($this->grav['pages']->find($location),'modular')) {
        // if modular, make sure this doesn't run through a template or it'll brick
        if ($this->grav['pages']->find($location)->modular()) {
          $this->grav['pages']->find($location)->modularTwig(false);
          $pagebody = $this->grav['pages']->find($location)->content();
          $this->grav['pages']->find($location)->modularTwig(true);
        }
        else {
          $pagebody = $this->grav['pages']->find($location)->content();
        }
      }
      // strip off wrapper cruft if it exists
      if (strpos($pagebody, '<div class="modular-row form ">')) {
        $pagebody = str_replace ('<div class="modular-row form ">', '', $pagebody);
        $pagebody .= substr($pagebody, 0, strrpos($pagebody, '</div>'));
      }
      // work on the app store based on internal settings
	  $apikeys = array();
	  $baseApps = $this->baseSupportedApps();
	  foreach ($baseApps as $key => $app) {
	    if ($this->config->get('plugins.hax.' . $key . '_key') != '') {
	      $apikeys[$key] = $this->config->get('plugins.hax.' . $key . '_key');
	    }
    }
    // generate array of elements to autoload
    $elementstring = $this->config->get('plugins.hax.autoload_element_list');
    $elements = explode(' ', $elementstring);
	  $appStoreConnection = array(
	  	'status' => 200,
	  	'apps' => $this->loadBaseAppStore($apikeys),
	  	'autoloader' => $elements,
	  	'blox' => $this->loadBaseBlox(),
	  	'stax' => $this->loadBaseStax(),
	  );
      $this->grav['twig']->twig_vars['appStoreConnection'] = json_encode($appStoreConnection);
      $this->grav['twig']->twig_vars['haxSlotArea'] = '<template>' . $pagebody . '</template>';
      $this->grav['twig']->twig_vars['bodyOffsetLeft'] = $offsetLeft;
    }
  }
  /**
   * Return the base url for forming paths on the front end.
   * @return string  The base path to the user / webcomponents directory
   */
  public function getBaseURL() {
    return $this->grav['base_url'] . '/user/data/webcomponents/';
  }
  

  /**
   * Return the file system directory for forming paths on the front end.
   * @return string  The base path to the user / webcomponents directory
   */
  public function webcomponentsDir() {
    return getcwd() . '/user/data/webcomponents/';
  }
  /**
   * Simple HTML Import render.
   */
  public function createHTMLImport($path, $rel = 'import') {
    return '<link rel="' . $rel . '" href="' . $path . '">';
  }

  /**
   * Render a webcomponent to the screen.
   * @param  [type] $vars [description]
   * @return [type]       [description]
   */
  public function renderWebcomponent($vars) {
    return '<' . $vars['tag'] . ' ' . $this->webcomponentAttributes($vars['properties']) . '>' . "\n" . $vars['innerHTML'] . "\n" . '</' . $vars['tag'] . '>' . "\n";
  }
  /**
   * Convert array into attributes for placement in an HTML tag.
   * @param  array  $attributes array of attribute name => value pairs
   * @return string             HTML name="value" output
   */
  protected function webcomponentAttributes(array $attributes = array()) {
    foreach ($attributes as $attribute => &$data) {
      $data = implode(' ', (array) $data);
      $data = $attribute . '="' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '"';
    }
    return $attributes ? ' ' . implode(' ', $attributes) : '';
  }
  /**
   * returns an array of app store definitions based
   * on passing in the apikeys for the ones we have
   * baked in support for.
   * @param  array $apikeys  array of API keys per service
   * @return array           HAX appstore specification
   */
  public function loadBaseAppStore($apikeys = array()) {
    $json = array();
    // youtube
    if (isset($apikeys['youtube'])) {
      $jsonstring = '{
        "details": {
          "title": "Youtube",
          "icon": "av:play-arrow",
          "color": "red",
          "author": "Google, Youtube LLC",
          "description": "The most popular online video sharing and remix site.",
          "status": "available",
          "tags": ["video", "crowdsourced"]
        },
        "connection": {
            "protocol": "https",
            "url": "www.googleapis.com/youtube/v3",
            "data": {
              "key": "' . $apikeys['youtube'] . '"
            },
            "operations": {
              "browse": {
                "method": "GET",
                "endPoint": "search",
                "pagination": {
                  "style": "page",
                  "props": {
                    "previous": "prevPageToken",
                    "next": "nextPageToken",
                    "total_items": "pageInfo.totalResults"
                  }
                },
                "search": {
                  "q": {
                    "title": "Search",
                    "type": "string"
                  }
                },
                "data": {
                  "part": "snippet",
                  "type": "video",
                  "maxResults": "20"
                },

                  "url": "https://www.youtube.com/watch?v=",

                "resultMap": {
                  "defaultGizmoType": "video",
                  "items": "items",
                  "preview": {
                    "title": "snippet.title",
                    "details": "snippet.description",
                    "image": "snippet.thumbnails.default.url",
                    "id": "id.videoId"
                  },
                  "gizmo": {
                    "title": "snippet.title",
                    "description": "snippet.description",
                    "id": "id.videoId",
                    "_url_source": "https://www.youtube.com/watch?v=<%= id %>",
                    "caption": "snippet.description",
                    "citation": "snippet.channelTitle"
                  }
                }
              }
            }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // memegenerator
    if (isset($apikeys['memegenerator'])) {
      $jsonstring = '{
        "details": {
          "title": "Meme generator",
          "icon": "android",
          "color": "blue",
          "author": "Meme generator",
          "description": "A search engine of popular memes.",
          "status": "available",
          "tags": ["picture", "crowdsourced", "image", "meme"]
        },
        "connection": {
          "protocol": "http",
          "url": "version1.api.memegenerator.net",
          "data": {
            "apiKey": "' . $apikeys['memegenerator'] . '"
          },
          "operations": {
            "browse": {
              "method": "GET",
              "endPoint": "Generators_Search",
              "pagination": {
                "style": "page",
                "props": {
                  "previous": "prevPageToken",
                  "next": "nextPageToken",
                  "total_items": "pageInfo.totalResults"
                }
              },
              "search": {
                "q": {
                  "title": "Search",
                  "type": "string"
                }
              },
              "data": {
                "pageIndex":"0",
                "pageSize":"20"
              },
              "resultMap": {
                "defaultGizmoType": "image",
                "items": "result",
                "preview": {
                  "title": "displayName",
                  "details": "",
                  "image": "imageUrl",
                  "id": "imageID"
                },
                "gizmo": {
                  "title": "displayName",
                  "id": "imageID",
                  "source": "imageUrl"
                }
              }
            }
          }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // vimeo
    if (isset($apikeys['vimeo'])) {
      $jsonstring = '{
        "details": {
          "title": "Vimeo",
          "icon": "av:play-circle-filled",
          "color": "blue",
          "author": "Vimeo Inc.",
          "description": "A high quality video sharing community.",
          "status": "available",
          "tags": ["video", "crowdsourced"]
        },
        "connection": {
          "protocol": "https",
          "url": "api.vimeo.com",
          "data": {
            "access_token": "' . $apikeys['vimeo'] . '"
          },
          "operations": {
            "browse": {
              "method": "GET",
              "endPoint": "videos",
              "pagination": {
                "style": "link",
                "props": {
                  "first": "paging.first",
                  "next": "paging.next",
                  "previous": "paging.previous",
                  "last": "paging.last"
                }
              },
              "search": {
                "query": {
                  "title": "Search",
                  "type": "string"
                }
              },
              "data": {
                "direction": "asc",
                "sort": "alphabetical",
                "filter": "CC",
                "per_page": "20"
              },
              "resultMap": {
                "defaultGizmoType": "video",
                "items": "data",
                "preview": {
                  "title": "name",
                  "details": "description",
                  "image": "pictures.sizes.1.link",
                  "id": "id"
                },
                "gizmo": {
                  "_url_source": "https://vimeo.com<%= id %>",
                  "id": "uri",
                  "title": "title",
                  "caption": "description",
                  "description": "description",
                  "citation": "user.name"
                }
              }
            }
          }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // giphy
    if (isset($apikeys['giphy'])) {
      $jsonstring = '{
        "details": {
          "title": "Giphy",
          "icon": "gif",
          "color": "green",
          "author": "Giphy",
          "description": "Crowd sourced memes via animated gifs.",
          "status": "available",
          "tags": ["gif", "crowdsourced", "meme"]
        },
        "connection": {
          "protocol": "https",
          "url": "api.giphy.com",
          "data": {
            "api_key": "' . $apikeys['giphy'] . '"
          },
          "operations": {
            "browse": {
              "method": "GET",
              "endPoint": "v1/gifs/search",
              "pagination": {
                "style": "offset",
                "props": {
                  "offset": "pagination.offset",
                  "total": "pagination.total_count",
                  "count": "pagination.count"
                }
              },
              "search": {
                "q": {
                  "title": "Search",
                  "type": "string"
                },
                "rating": {
                  "title": "Rating",
                  "type": "string",
                  "component": {
                    "name": "dropdown-select",
                    "slot": "<paper-item value=\'Y\'>Y</paper-item><paper-item value=\'G\'>G</paper-item><paper-item value=\'PG\'>PG</paper-item><paper-item value=\'PG-13\'>PG-13</paper-item><paper-item value=\'R\'>R</paper-item>"
                  }
                },
                "lang": {
                  "title": "Language",
                  "type": "string",
                  "component": {
                    "name": "dropdown-select",
                    "slot": "<paper-item value=\'en\'>en</paper-item><paper-item value=\'es\'>es</paper-item><paper-item value=\'pt\'>pt</paper-item><paper-item value=\'id\'>id</paper-item><paper-item value=\'fr\'>fr</paper-item><paper-item value=\'ar\'>ar</paper-item><paper-item value=\'tr\'>tr</paper-item><paper-item value=\'th\'>th</paper-item><paper-item value=\'vi\'>vi</paper-item><paper-item value=\'de\'>de</paper-item><paper-item value=\'it\'>it</paper-item><paper-item value=\'ja\'>ja</paper-item><paper-item value=\'zh-CN\'>zh-CN</paper-item><paper-item value=\'zh-TW\'>zh-TW</paper-item><paper-item value=\'ru\'>ru</paper-item><paper-item value=\'ko\'>ko</paper-item><paper-item value=\'pl\'>pl</paper-item><paper-item value=\'nl\'>nl</paper-item><paper-item value=\'ro\'>ro</paper-item><paper-item value=\'hu\'>hu</paper-item><paper-item value=\'sv\'>sv</paper-item><paper-item value=\'cs\'>cs</paper-item><paper-item value=\'hi\'>hi</paper-item><paper-item value=\'bn\'>bn</paper-item><paper-item value=\'da\'>da</paper-item><paper-item value=\'fa\'>fa</paper-item><paper-item value=\'tl\'>tl</paper-item><paper-item value=\'fi\'>fi</paper-item><paper-item value=\'iw\'>iw</paper-item><paper-item value=\'ms\'>ms</paper-item><paper-item value=\'no\'>no</paper-item><paper-item value=\'uk\'>uk</paper-item>"
                  }
                }
              },
              "data": {
                "limit": "20",
                "lang": "en"
              },
              "resultMap": {
                "defaultGizmoType": "image",
                "items": "data",
                "preview": {
                  "title": "title",
                  "details": "description",
                  "image": "images.preview_gif.url",
                  "id": "id"
                },
                "gizmo": {
                  "source": "images.original.url",
                  "source2": "images.480w_still.url",
                  "id": "id",
                  "title": "title",
                  "alt": "title",
                  "caption": "user.display_name",
                  "citation": "user.display_name"
                }
              }
            }
          }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // unsplash
    if (isset($apikeys['unsplash'])) {
      $jsonstring = '{
        "details": {
          "title": "Unsplash",
          "icon": "image:collections",
          "color": "grey",
          "author": "Unsplash",
          "description": "Crowd sourced, open photos",
          "status": "available",
          "tags": ["images", "crowdsourced", "cc"]
        },
        "connection": {
          "protocol": "https",
          "url": "api.unsplash.com",
          "data": {
            "client_id": "' . $apikeys['unsplash'] . '"
          },
          "operations": {
            "browse": {
              "method": "GET",
              "endPoint": "search/photos",
              "pagination": {
                "style": "link",
                "props": {
                  "first": "paging.first",
                  "next": "paging.next",
                  "previous": "paging.previous",
                  "last": "paging.last"
                }
              },
              "search": {
                "query": {
                  "title": "Search",
                  "type": "string"
                }
              },
              "data": {
              },
              "resultMap": {
                "defaultGizmoType": "image",
                "items": "results",
                "preview": {
                  "title": "tags.0.title",
                  "details": "description",
                  "image": "urls.thumb",
                  "id": "id"
                },
                "gizmo": {
                  "id": "id",
                  "source": "urls.regular",
                  "alt": "description",
                  "caption": "description",
                  "citation": "user.name"
                }
              }
            }
          }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // flickr
    if (isset($apikeys['flickr'])) {
      $jsonstring = '{
        "details": {
          "title": "Flickr",
          "icon": "image:collections",
          "color": "pink",
          "author": "Yahoo",
          "description": "The original photo sharing platform on the web.",
          "status": "available",
          "rating": "0",
          "tags": ["images", "creative commons", "crowdsourced"]
        },
        "connection": {
          "protocol": "https",
          "url": "api.flickr.com",
          "data": {
            "api_key": "' . $apikeys['flickr'] . '"
          },
          "operations": {
            "browse": {
              "method": "GET",
              "endPoint": "services/rest",
              "pagination": {
                "style": "page",
                "props": {
                  "per_page": "photos.perpage",
                  "total_pages": "photos.pages",
                  "page": "photos.page"
                }
              },
              "search": {
                "text": {
                  "title": "Search",
                  "type": "string"
                },
                "safe_search": {
                  "title": "Safe results",
                  "type": "string",
                  "value": "1",
                  "component": {
                    "name": "dropdown-select",
                    "valueProperty": "value",
                    "slot": "<paper-item value=\'1\'>Safe</paper-item><paper-item value=\'2\'>Moderate</paper-item><paper-item value=\'3\'>Restricted</paper-item>"
                  }
                },
                "license": {
                  "title": "License type",
                  "type": "string",
                  "value": "",
                  "component": {
                    "name": "dropdown-select",
                    "valueProperty": "value",
                    "slot": "<paper-item value=\'\'>Any</paper-item><paper-item value=\'0\'>All Rights Reserved</paper-item><paper-item value=\'4\'>Attribution License</paper-item><paper-item value=\'6\'>Attribution-NoDerivs License</paper-item><paper-item value=\'3\'>Attribution-NonCommercial-NoDerivs License</paper-item><paper-item value=\'2\'>Attribution-NonCommercial License</paper-item><paper-item value=\'1\'>Attribution-NonCommercial-ShareAlike License</paper-item><paper-item value=\'5\'>Attribution-ShareAlike License</paper-item><paper-item value=\'7\'>No known copyright restrictions</paper-item><paper-item value=\'8\'>United States Government Work</paper-item><paper-item value=\'9\'>Public Domain Dedication (CC0)</paper-item><paper-item value=\'10\'>Public Domain Mark</paper-item>"
                  }
                }
              },
              "data": {
                "method": "flickr.photos.search",
                "safe_search": "1",
                "format": "json",
                "per_page": "20",
                "nojsoncallback": "1",
                "extras": "license,description,url_l,url_s"
              },
              "resultMap": {
                "defaultGizmoType": "image",
                "items": "photos.photo",
                "preview": {
                  "title": "title",
                  "details": "description._content",
                  "image": "url_s",
                  "id": "id"
                },
                "gizmo": {
                  "title": "title",
                  "source": "url_l",
                  "alt": "description._content"
                }
              }
            }
          }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // pixabay
    if (isset($apikeys['pixabay'])) {
      $jsonstring = '{
        "details": {
          "title": "Pixabay images",
          "icon": "places:all-inclusive",
          "color": "orange",
          "author": "Pixabay",
          "description": "Pixabay open image community",
          "status": "available",
          "tags": ["images", "crowdsourced"]
        },
        "connection": {
          "protocol": "https",
          "url": "pixabay.com",
          "data": {
            "key": "' . $apikeys['pixabay'] . '"
          },
          "operations": {
            "browse": {
              "method": "GET",
              "endPoint": "api",
              "pagination": {
                "style": "page",
                "props": {
                  "total_items": "totalHits",
                  "page": "page"
                }
              },
              "search": {
                "q": {
                  "title": "Search",
                  "type": "string"
                }
              },
              "data": {
                "image_type": "photo"
              },
              "resultMap": {
                "defaultGizmoType": "image",
                "items": "hits",
                "preview": {
                  "title": "tags",
                  "details": "user",
                  "image": "previewURL",
                  "id": "id"
                },
                "gizmo": {
                  "source": "webformatURL",
                  "id": "uri",
                  "title": "tags",
                  "caption": "tags",
                  "citation": "user.name"
                }
              }
            }
          }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // Google Poly
    if (isset($apikeys['googlepoly'])) {
      $jsonstring = '{
        "details": {
          "title": "Google Poly",
          "icon": "icons:3d-rotation",
          "color": "red",
          "author": "Google",
          "description": "Google 3D object sharing service",
          "status": "available",
          "tags": ["3D", "creative commons", "crowdsourced"]
        },
        "connection": {
          "protocol": "https",
          "url": "poly.googleapis.com/v1",
          "data": {
            "key": "' . $apikeys['googlepoly'] . '"
          },
          "operations": {
            "browse": {
              "method": "GET",
              "endPoint": "assets",
              "pagination": {
                "style": "page",
                "props": {
                  "previous": "prevPageToken",
                  "next": "nextPageToken",
                  "total_items": "pageInfo.totalResults"
                }
              },
              "search": {
                "keywords": {
                  "title": "Search",
                  "type": "string"
                },
                "category": {
                  "title": "Category",
                  "type": "string",
                  "value": "",
                  "component": {
                    "name": "dropdown-select",
                    "valueProperty": "value",
                    "slot": "<paper-item value=\'\'>Any</paper-item><paper-item value=\'animals\'>Animals</paper-item><paper-item value=\'architecture\'>Architecture</paper-item><paper-item value=\'art\'>Art</paper-item><paper-item value=\'food\'>Food</paper-item><paper-item value=\'nature\'>Nature</paper-item><paper-item value=\'objects\'>Objects</paper-item><paper-item value=\'people\'>People</paper-item><paper-item value=\'scenes\'>Scenes</paper-item><paper-item value=\'technology\'>Technology</paper-item><paper-item value=\'transport\'>Transport</paper-item>"
                  }
                }
              },
              "data": {
                "pageSize": "20"
              },
              "resultMap": {
                "defaultGizmoType": "video",
                "items": "assets",
                "preview": {
                  "title": "displayName",
                  "details": "description",
                  "image": "thumbnail.url",
                  "id": "name"
                },
                "gizmo": {
                  "title": "displayName",
                  "description": "description",
                  "id": {
                    "property": "name",
                    "op": "split",
                    "delimiter": "/",
                    "position": "1"
                  },
                  "image": "thumbnail.url",
                  "_url_source": "https://poly.google.com/view/<%= id %>/embed",
                  "caption": "description",
                  "citation": "authorName",
                  "license": "license"
                }
              }
            }
          }
        }
      }';
      $tmp = json_decode($jsonstring);
      array_push($json, $tmp);
    }
    // nasa
    $jsonstring = '{
      "details": {
        "title": "NASA",
        "icon": "places:all-inclusive",
        "color": "blue",
        "author": "US Government",
        "description": "The cozmos through one simple API.",
        "status": "available",
        "tags": ["images", "government", "space"]
      },
      "connection": {
        "protocol": "https",
        "url": "images-api.nasa.gov",
        "operations": {
          "browse": {
            "method": "GET",
            "endPoint": "search",
            "pagination": {
              "style": "page",
              "props": {
                "page": "page"
              }
            },
            "search": {
              "q": {
                "title": "Search",
                "type": "string"
              }
            },
            "data": {
              "media_type": "image"
            },
            "resultMap": {
              "defaultGizmoType": "image",
              "items": "collection.items",
              "preview": {
                "title": "data.0.title",
                "details": "data.0.description",
                "image": "links.0.href",
                "id": "links.0.href"
              },
              "gizmo": {
                "id": "links.0.href",
                "source": "links.0.href",
                "title": "data.0.title",
                "caption": "data.0.description",
                "description": "data.0.description",
                "citation": "data.0.photographer",
                "type": "data.0.media_type"
              }
            }
          }
        }
      }
    }';
    $tmp = json_decode($jsonstring);
    array_push($json, $tmp);
    // sketchfab
    $jsonstring = '{
      "details": {
        "title": "Sketchfab",
        "icon": "icons:3d-rotation",
        "color": "purple",
        "author": "Sketchfab",
        "description": "3D sharing community.",
        "status": "available",
        "rating": "0",
        "tags": ["3D", "creative commons", "crowdsourced"]
      },
      "connection": {
        "protocol": "https",
        "url": "api.sketchfab.com",
        "data": {
          "type": "models"
        },
        "operations": {
          "browse": {
            "method": "GET",
            "endPoint": "v3/search",
            "pagination": {
              "style": "page",
              "props": {
                "per_page": "photos.perpage",
                "total_pages": "photos.pages",
                "page": "photos.page"
              }
            },
            "search": {
              "q": {
                "title": "Search",
                "type": "string"
              },
              "license": {
                "title": "License type",
                "type": "string",
                "value": "",
                "component": {
                  "name": "dropdown-select",
                  "valueProperty": "value",
                  "slot": "<paper-item value=\'\'>Any</paper-item><paper-item value=\'by\'>Attribution</paper-item><paper-item value=\'by-sa\'>Attribution ShareAlike</paper-item><paper-item value=\'by-nd\'>Attribution NoDerivatives</paper-item><paper-item value=\'by-nc\'>Attribution-NonCommercial</paper-item><paper-item value=\'by-nc-sa\'>Attribution NonCommercial ShareAlike</paper-item><paper-item value=\'by-nc-nd\'>Attribution NonCommercial NoDerivatives</paper-item><paper-item value=\'cc0\'>Public Domain Dedication (CC0)</paper-item>"
                }
              }
            },
            "resultMap": {
              "defaultGizmoType": "video",
              "items": "results",
              "preview": {
                "title": "name",
                "details": "description._content",
                "image": "thumbnails.images.2.url",
                "id": "uid"
              },
              "gizmo": {
                "title": "name",
                "source": "embedUrl",
                "alt": "description"
              }
            }
          }
        }
      }
    }';
    $tmp = json_decode($jsonstring);
    array_push($json, $tmp);
    // dailymotion
    $jsonstring = '{
      "details": {
        "title": "Dailymotion",
        "icon": "av:play-circle-filled",
        "color": "blue",
        "author": "Dailymotion",
        "description": "A crowdsourced video platform that is ad supported.",
        "status": "available",
        "tags": ["video", "crowdsourced"]
      },
      "connection": {
        "protocol": "https",
        "url": "api.dailymotion.com",
        "operations": {
          "browse": {
            "method": "GET",
            "endPoint": "videos",
            "pagination": {
              "style": "page",
              "props": {
                "total_items": "total",
                "total_pages": "total_pages",
                "page": "page"
              }
            },
            "search": {
              "search": {
                "title": "Search",
                "type": "string"
              }
            },
            "data": {
              "fields":"description,embed_url,thumbnail_240_url,title,id",
              "no_live":"1",
              "ssl_assets":"true",
              "sort":"relevance",
              "limit":"20"
            },
            "resultMap": {
              "defaultGizmoType": "video",
              "items": "list",
              "preview": {
                "title": "title",
                "details": "description",
                "image": "thumbnail_240_url",
                "id": "id"
              },
              "gizmo": {
                "title": "title",
                "description": "description",
                "source": "embed_url",
                "alt": "description",
                "caption": "description"
              }
            }
          }
        }
      }
    }';
    $tmp = json_decode($jsonstring);
    array_push($json, $tmp);
    // wikipedia
    $jsonstring = '{
      "details": {
        "title": "Wikipedia",
        "icon": "account-balance",
        "color": "grey",
        "author": "Wikimedia",
        "description": "Encyclopedia of the world.",
        "status": "available",
        "tags": ["content", "encyclopedia", "wiki"]
      },
      "connection": {
        "protocol": "https",
        "url": "en.wikipedia.org",
        "data": {
          "action": "query",
          "list": "search",
          "format": "json",
          "origin": "*"
        },
        "operations": {
          "browse": {
            "method": "GET",
            "endPoint": "w\/api.php",
            "pagination": {
              "style": "offset",
              "props": {
                "offset": "sroffset"
              }
            },
            "search": {
              "srsearch": {
                "title": "Search",
                "type": "string"
              }
            },
            "data": {},
            "resultMap": {
              "image": "https://en.wikipedia.org/static/images/project-logos/enwiki.png",
              "defaultGizmoType": "content",
              "items": "query.search",
              "preview": {
                "title": "title",
                "details": "snippet",
                "id": "title"
              },
              "gizmo": {
                "_url_source": "https://en.wikipedia.org/wiki/<%= id %>",
                "id": "title",
                "title": "title",
                "caption": "snippet",
                "description": "snippet"
              }
            }
          }
        }
      }
    }';
    $tmp = json_decode($jsonstring);
    array_push($json, $tmp);
    // cc-mixter
    $jsonstring = '{
      "details": {
        "title": "CC Mixter",
        "icon": "av:library-music",
        "color": "purple",
        "author": "CC Mixter",
        "description": "User submitted audio files and music.",
        "status": "available",
        "tags": ["audio", "crowdsourced"]
      },
      "connection": {
        "protocol": "http",
        "url": "ccmixter.org",
        "data": {
          "format":"json",
          "sort":"name",
          "limit":"20"
        },
        "operations": {
          "browse": {
            "method": "GET",
            "endPoint": "api/query",
            "pagination": {
              "style": "link",
              "props": {
                "first": "paging.first",
                "next": "paging.next",
                "previous": "paging.previous",
                "last": "paging.last"
              }
            },
            "search": {
              "tags": {
                "title": "Search",
                "type": "string"
              }
            },
            "data": {
              "direction": "asc",
              "sort": "alphabetical",
              "filter": "CC",
              "per_page": "20"
            },
            "resultMap": {
              "defaultGizmoType": "audio",
              "items": "",
              "preview": {
                "title": "upload_name",
                "details": "upload_description_plain",
                "image": "license_logo_url",
                "id": "upload_id"
              },
              "gizmo": {
                "source": "files.0.download_url",
                "id": "upload_id",
                "title": "upload_name",
                "caption": "upload_description_plain",
                "description": "upload_description_plain",
                "citation": "license_name"
              }
            }
          }
        }
      }
    }';
    $tmp = json_decode($jsonstring);
    array_push($json, $tmp);
    // codepen
    $jsonstring = '{
      "details": {
        "title": "Codepen.io",
        "icon": "code",
        "color": "green",
        "author": "Code pen",
        "description": "HTML / CSS / JS sharing community",
        "status": "available",
        "rating": "0",
        "tags": ["code", "development", "html", "js", "crowdsourced"]
      },
      "connection": {
        "protocol": "https",
        "url": "cpv2api.com",
        "operations": {
          "browse": {
            "method": "GET",
            "endPoint": "search/pens",
            "pagination": {
              "style": "page",
              "props": {
                "per_page": "photos.perpage",
                "total_pages": "photos.pages",
                "page": "photos.page"
              }
            },
            "search": {
              "q": {
                "title": "Search",
                "type": "string"
              }
            },
            "resultMap": {
              "defaultGizmoType": "video",
              "items": "data",
              "preview": {
                "title": "title",
                "details": "details",
                "image": "images.small",
                "id": "id"
              },
              "gizmo": {
                "_url_source": "https://codepen.io/fchazal/embed/<%= id %>/?theme-id=0&default-tab=html,result&embed-version=2",
                "id": "id",
                "image": "images.large",
                "title": "title",
                "caption": "details",
                "description": "details"
              }
            }
          }
        }
      }
    }';
    $tmp = json_decode($jsonstring);
    array_push($json, $tmp);

    return $json;
  }
    /**
   * Returns some example / default BLOX definitions, which are
   * the layouts as defined by HAX to go in a grid-plate element.
   * This is the specification required by the HAX appstore in
   * order to correctly present the listing of layouts in their 
   * associated layout drawer.
   * @return array           HAX blox specification
   */
  public function loadBaseBlox() {
    $jsonstring = '[
    {
      "details": {
        "title": "50% columns",
        "author": "ELMS:LN",
        "icon": "hax:6/6",
        "status": "available",
        "layout": "6/6"
      },
      "blox": [
        {
          "tag": "h2",
          "properties": {
            "slot": "col-1"
          },
          "content": "Heading"
        },
        {
          "tag": "p",
          "properties": {
            "slot": "col-1"
          },
          "content": "A paragraph of text would go here to describe the work."
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-2",
            "type": "image"
          },
          "content": ""
        }
      ]
    },
    {
      "details": {
        "title": "66 / 33 columns",
        "author": "ELMS:LN",
        "icon": "hax:8/4",
        "status": "available",
        "layout": "8/4"
      },
      "blox": [{
          "tag": "place-holder",
          "properties": {
            "slot": "col-1",
            "type": "text"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-2",
            "type": "image"
          },
          "content": ""
        }
      ]
    },
    {
      "details": {
        "title": "33% columns",
        "author": "ELMS:LN",
        "icon": "hax:4/4/4",
        "status": "available",
        "layout": "4/4/4"
      },
      "blox": [
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-1",
            "type": "image"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-2",
            "type": "image"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-3",
            "type": "image"
          },
          "content": ""
        }
      ]
    },
    {
      "details": {
        "title": "33 / 66 columns",
        "author": "ELMS:LN",
        "icon": "hax:4/8",
        "status": "available",
        "layout": "4/8"
      },
      "blox": [
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-2",
            "type": "text"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-1",
            "type": "image"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-1",
            "type": "image"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-1",
            "type": "image"
          },
          "content": ""
        }
      ]
    },
    {
      "details": {
        "title": "25% columns",
        "author": "ELMS:LN",
        "icon": "hax:3/3/3/3",
        "status": "available",
        "layout": "3/3/3/3"
      },
      "blox": [
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-1",
            "type": "image"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-2",
            "type": "image"
          },
          "content": ""
        },
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-3",
            "type": "image"
          },
          "content": ""
        }, 
        {
          "tag": "place-holder",
          "properties": {
            "slot": "col-4",
            "type": "image"
          },
          "content": ""
        }
      ]
    }
    ]';
    return json_decode($jsonstring);
  }

  /**
   * Returns some example STAX definitions, which are
   * predefined sets of items which can be broken apart
   * after the fact. This is like a template in traditional WYSIWYGs.
   * @return array           HAX stax specification
   */
  public function loadBaseStax() {
    $jsonstring = '[{
      "details": {
        "title": "Example Lesson",
        "author": "ELMS:LN",
        "description": "An example of what HAX can do",
        "status": "available",
        "rating": "0",
        "tags": ["example"]
      },
      "stax": [
        {
          "tag": "h2",
          "properties": {},
          "content": "Introduction to ELMS: Learning Network"
        },
        {
          "tag": "p",
          "properties": {},
          "content": "What is ELMS: Learning Network? How is it fundamentally different from other learning technologies? Why is it your reading this when it\'s example text that you\'ve just added to see how to make a really solid introduction to a new unit of instruction? Let\'s keep reading to find out!"
        },
        {
          "tag": "video-player",
          "properties": {
            "style": "width: 75%; margin: 0px auto; display: block;",
            "source": "https://www.youtube.com/watch?v=pKLPQ4ufo64",
            "src": "https://www.youtube-nocookie.com/embed/pKLPQ4ufo64?showinfo=0&controls=1&rel=0",
            "iframed": true,
            "sandboxed": false,
            "width": "560",
            "height": "315",
            "responsive": true,
            "caption": "What is ELMS:LN? Why is it fundamentally different from any other educational technology that\'s ever existed? What is sustainable innovation? Why is this so ...",
            "secondaryColor": "#fff9c4",
            "textColor": "#000000",
            "secondaryColorClass": "yellow lighten-4",
            "textColorClass": "black-text",
            "ytNocookie": true,
            "ytSuggested": false,
            "ytControls": true,
            "ytTitle": false,
            "vimeoTitle": false,
            "vimeoByline": false,
            "vimeoPortrait": false,
            "videoColor": "FF031D"
          },
          "content": ""
        }
      ]
    }]';
    return json_decode($jsonstring);
  }
  /**
   * Return an array of the base app keys we support. This
   * can reduce the time to integrate with other solutions.
   * @return array  service names keyed by their key name
   */
  public function baseSupportedApps() {
    return array(
      'youtube' => array(
        'name' => 'YouTube',
        'docs' => 'https://developers.google.com/youtube/v3/getting-started',
      ),
      'googlepoly' => array(
        'name' => 'Google Poly',
        'docs' => 'https://developers.google.com/poly/develop/api',
      ),
      'memegenerator' => array(
        'name' => 'Meme generator',
        'docs' => 'https://memegenerator.net/Api',
      ),
      'vimeo' => array(
        'name' => 'Vimeo',
        'docs' => 'https://developer.vimeo.com/',
      ),
      'giphy' => array(
        'name' => 'Giphy',
        'docs' => 'https://developers.giphy.com/docs/',
      ),
      'unsplash' => array(
        'name' => 'Unsplash',
        'docs' => 'https://unsplash.com/developers',
      ),
      'flickr' => array(
        'name' => 'Flickr',
        'docs' => 'https://www.flickr.com/services/developer/api/',
      ),
      'pixabay' => array(
        'name' => 'Pixabay',
        'docs' => 'https://pixabay.com/api/docs/',
      ),
    );
  }

    /**
     * Check if webcomponents are installed.
     */
    private function verifyWebcomponentsInstallation() {

      $grav = new Grav();
      $required_files = array(
          $this->webcomponentsDir() . 'bower_components/webcomponentsjs/' . WebcomponentsPlugin::polyfillLibrary(),
          $this->webcomponentsDir() . 'bower_components/wysiwyg-hax/wysiwyg-hax.html',
      );

      // Check if webcomponents plugin is installed and enabled.
      if (!isset($grav::instance()['config']['plugins']['webcomponents']) || $grav::instance()['config']['plugins']['webcomponents']['enabled'] == false){
        $message = "Webcomponents plugin is not installed/ enabled.";
        AtoolsPlugin::disablePlugin($message, 'error', 'hax');
        return false;
      }

      foreach ($required_files as $file) {
          if (!file_exists($file)) {
              $message = 'Missing Hax dependency file at ' . $file;
              AtoolsPlugin::disablePlugin($message, 'error', 'hax');
              return false;
          }
      }
      return true;
    }
}

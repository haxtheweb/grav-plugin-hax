<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use \Grav\Common\Grav;
use \Grav\Common\Utils;
use \Grav\Common\Page\Page;
use \Grav\Common\Session;
use RocketTheme\Toolbox\Event\Event;

define('HAX_DEFAULT_OFFSET', 0);
define('HAX_DEFAULT_ELEMENTS', 'video-player wikipedia-query pdf-element lrn-table media-image');

class HAXPlugin extends Plugin {
  public static function getSubscribedEvents() {
    return ['onPluginsInitialized' => ['onPluginsInitialized', 0], 'onTwigSiteVariables' => ['onTwigSiteVariables', 0]];
  }
  public function onPluginsInitialized() {
    if($this->isAdmin()) {
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
      $elementstring = $this->config->get('plugins.hax.autoload_element_list');
      // discover and autoload our components
      $assets = $this->grav['assets'];
      $file = $this->getBaseURL() . 'bower_components/gravcms-hax/gravcms-hax.html';
      $imports = $this->createHTMLImport($file) . "\n";
      // build the inline import
      $inline = "
  </script>" . $imports . "<script>";
      $assets->addCSS('plugin://hax/hax.css', array('priority' => 103, 'group' => 'head'));
       // add it into the document
      $assets->addInlineJs($inline, array('priority' => 103, 'group' => 'head'));
      // blow up based on space
      $elements = explode(' ', $elementstring);
      $haxSlotArea = '';
      foreach ($elements as $element) {
        // sanity check
        if (!empty($element)) {
          $haxSlotArea .= '<' . $element . ' slot="autoloader">' . '</' . $element . '>';
        }
      }
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
      $haxSlotArea .= $pagebody;
      $this->grav['twig']->twig_vars['haxSlotArea'] = $haxSlotArea;
      $this->grav['twig']->twig_vars['bodyOffsetLeft'] = $offsetLeft;
    }
  }
  /**
   * Return the base url for forming paths on the front end.
   * @return string  The base path to the user / webcomponents directory
   */
  public function getBaseURL() {
    return $this->grav['base_url'] . '/user/webcomponents/';
  }

  /**
   * Return the file system directory for forming paths on the front end.
   * @return string  The base path to the user / webcomponents directory
   */
  public function webcomponentsDir() {
    return getcwd() . '/user/webcomponents/';
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
}

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
      // directory they live in physically
      $file = $this->getBaseURL() . 'elements/_build/build.html';
      $imports = $this->createHTMLImport($file) . "\n";
      $file = $this->getBaseURL() . 'elements/cms-hax/cms-hax.html';
      $imports .= $this->createHTMLImport($file) . "\n";
      $file = $this->getBaseURL() . 'elements/marked-element/marked-element.html';
      $imports .= $this->createHTMLImport($file) . "\n";
      // build the inline import
      $inline = "
  </script>" . $imports . "<script>";
      // add it into the document
      $assets->addInlineJs($inline, array('priority' => 103, 'group' => 'head'));
      // @todo wrap body text in cms-hax tag w/ variable in there
      // blow up based on space
      $elements = explode(' ', $elementstring);
      $autoload = '';
      foreach ($elements as $element) {
        // sanity check
        if (!empty($element)) {
          $autoload .= '<' . $element . ' slot="autoloader">' . '</' . $element . '>';
        }
      }
      $this->grav['twig']->field['autoload'] = $autoload;
      $this->grav['twig']->field['offsetLeft'] = $offsetLeft;
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

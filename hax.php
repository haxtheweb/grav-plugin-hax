<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use \Grav\Common\Grav;
use \Grav\Common\Utils;
use \Grav\Common\Page\Page;
use \Grav\Common\Session;

define('HAX_DEFAULT_OFFSET', 0);
define('HAX_DEFAULT_ELEMENTS', 'video-player wikipedia-query pdf-element lrn-table media-image');

class HaxPlugin extends Plugin
{
  public $activeApp;
  /**
   * @return array
   */
  public static function getSubscribedEvents()
  {
    return [
      'onThemeInitialized' => ['onThemeInitialized', 0],
      'onTwigTemplatePaths' => ['onTwigTemplatePaths', 100],
      'onPluginsInitialized' => ['onPluginsInitialized', 100000]
    ];
  }

  /**
   * Initialize the plugin
   */
  public function onPluginsInitialized()
  {
    // Don't proceed if we are in the admin plugin
    if ($this->isAdmin()) {
      return;
    }

    $uri = $this->grav['uri'];
    // do this for all routes, assuming we have a user account
    foreach ($routes as $machine_name => $app) {
      // if our route matches one we have, load up
      if ("/blog/$machine_name" == $uri->path()) {
        $this->activeApp = (array)$app;
        $this->enable([
            'onPageInitialized' => ['onPageInitialized', 0]
        ]);
      }
      // check for data endpoints if they exist
      if (isset($app['endpoints'])) {
        foreach ($app['endpoints'] as $path => $endpoint) {
          if ("/hax-save/$machine_name/$path" == $uri->path()) {
            $this->activeApp = (array)$app;
            $this->enable([
                'onPageInitialized' => ['onPageInitializedData', 0]
            ]);
          }
        }
      }
    }
  }
  /**
   * Add current directory to twig lookup paths.
   */
  public function onTwigTemplatePaths()
  {
      $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
  }


  /**
   * Autoload a hax.
   */
  public function onPageInitialized()
  {
    // set a dummy page
    $page = new Page;
    $page->init(new \SplFileInfo(__DIR__ . '/pages/hax.md');
    unset($this->grav['page']);
    $this->grav['page'] = $page;
  }

  /**
   * Autoload a hax path.
   */
  public function onPageInitializedData()
  {
    // return data
    $return = array();
    // validate CSRF token and ensure we have something
    if (is_array($this->activeApp) || Utils::getNonce('hax') == $_GET['token']) {
      $app = $this->activeApp;
      $machine_name = $app['machine_name'];
      $args = explode('?', str_replace($this->grav['base_url'], '', $_SERVER['REQUEST_URI']));
      $args = explode('/', $args[0]);
      // this ensures that apps/machine-name get shifted off
      array_shift($args);
      array_shift($args);
      array_shift($args);
      // match the route that was specified in $app['endpoints']
      $endpointpath = NULL;
      if (isset($app['endpoints'])) {
        foreach ($app['endpoints'] as $path => $endpoint) {
          // we're going to compare the args array and the endpoint.
          // to do this we are going to convert the path to an array.
          $path_ary = explode('/', $path);
          // see if args and path are the same length
          if (count($path_ary) == count($args)) {
            // see if there are any differences between the two
            $ary_diff = array_diff($path_ary, $args);
            // if no differences then we found the path and we should exit
            // immediately
            if (empty($ary_diff)) {
              $endpointpath = $path;
              break;
            }
            // if there is a difference in the path but the only differences
            // are wildcards then it's a match
            else {
              $mismatch = false;
              foreach ($ary_diff as $diff) {
                if ($diff != '%') {
                  $mismatch = true;
                }
              }
              // if we went through the diffs and there were no
              // matches other than % then it's a match
              if (!$mismatch) {
                $endpointpath = $path;
              }
            }
          }
        }
      }
      // attempt autoload here in the event this was invoked via a load all
      if (isset($app['autoload']) && $app['autoload'] === TRUE) {
        include_once $app['filepath'] . $machine_name . '.php';
      }
      // make sure the machine name and the data callback both exist
      if (!empty($machine_name) && !empty($app) && isset($app['endpoints']) && function_exists($app['endpoints'][$endpointpath]->callback)) {
        $params = filter_var_array($_GET, FILTER_SANITIZE_STRING);
        // include additional url arguments to downstream
        // check for extended args on this call
        $return = call_user_func($app['endpoints'][$endpointpath]->callback, $machine_name, WEBCOMPONENTS_APP_PATH . '/' . $machine_name, $params, $args);
      }
      else {
        $return = array(
          'status' => '404',
          'detail' => 'Not a valid callback',
        );
      }
    }
    else {
      $return = array(
        'status' => '403',
        'detail' => 'Invalid CSRF token',
      );
    }
    // nothing set so make it 200 even though it already is
    if (empty($return['status'])) {
      $return['status'] = '200';
    }
    // ensure there's some form of detail even if empty
    if (empty($return['detail'])) {
      $return['detail'] = '';
    }
    // ensure there's some form of detail even if empty
    if (empty($return['environment'])) {
      $return['environment'] = array();
    }
    // set output headers as JSON
    header('Content-Type: application/json');
    header('Status: ' . $return['status']);
    // return JSON!
    echo json_encode($return);
    exit();
  }

  /**
   * Initialize configuration
   */
  public function onThemeInitialized()
  {
    if ($this->isAdmin()) {
      return;
    }

    // if not enasbled see if the theme expects to load the hax plugin
    if (!$this->config->get('plugins.hax.enabled')) {
      $theme = $this->grav['theme'];
      // @todo check if user has a session
      $load_events = true;
    }
    else {
      $load_events = true;
    }

    if ($load_events) {
      $this->enable([
        'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
      ]);
    }
  }

  /**
   * If enabled on this page, load the JS + CSS and set the selectors.
   */
  public function onTwigSiteVariables() {
    $offsetLeft = $this->config->get('plugins.hax.offset_left');
    $elementstring = $this->config->get('plugins.hax.autoload_element_list');
    // discover and autoload our components
    $assets = $this->grav['assets'];
    // directory they live in physically
    $file = $this->getBaseURL() . 'cms-hax/cms-hax.html';
    $imports = $this->createHTMLImport($file) . "\n";
    // build the inline import
    $inline = "
</script>" . $imports . "<script>";
    // add it into the document
    $assets->addInlineJs($inline, array('priority' => 103, 'group' => 'head'));
    // @todo wrap body text in cms-hax tag w/ variable in there
    // blow up based on space
    $elements = explode(' ', $elementstring);
    $components = '';
    foreach ($elements as $element) {
      // sanity check
      if (!empty($element)) {
        $components .= '<' . $element . ' slot="autoloader">' . '</' . $element . '>';
      }
    }
    // write content to screen, wrapped in tag to do all the work
    $content = '
    <cms-hax end-point="' . $this->grav['base_url'] . 'hax-save/' . $pageID . '/' . Utils::getNonce('hax') . '" body-offset-left="' . $offsetLeft . '">'
    . $components .
      $pageBody
    .'</cms-hax>';
    // @todo return this in a meaningful way
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

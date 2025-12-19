<?php

namespace Drupal\micon\Commands;

use Drupal\micon\MiconIconManager;
use Drush\Commands\DrushCommands;
use Drush\Drush;

/**
 * Drush integration for the Micon module.
 */
class MiconCommands extends DrushCommands {

  /**
   * The Micon icon manager.
   *
   * @var \Drupal\micon\MiconIconManager
   */
  protected $miconIconManager;

  /**
   * Constructs a new MiconCommands object.
   *
   * @param \Drupal\micon\MiconIconManager $micon_icon_manager
   *   The active configuration storage.
   */
  public function __construct(
    MiconIconManager $micon_icon_manager
  ) {
    parent::__construct();
    $this->miconIconManager = $micon_icon_manager;
  }

  /**
   * Generates SCSS mixins and variables for currently enabled Micon icon sets.
   *
   * @param string $path
   *   The destination where the _micon.scss mixin file should be created.
   *   Do not include a trailing slash.
   *
   * @command micon
   * @usage drush micon themes/my_theme/src/scss/base
   *   Creates the SCSS mixin file and places it
   *   within SITE_ROOT/themes/my_theme/src/scss/base
   */
  public function micon($path) {

    // If no $name provided, abort.
    if (!$path) {
      $this->output()->writeln(dt('Location path missing. See help using drush micon --help.'));
      return;
    }

    $path = Drush::bootstrapManager()->getRoot() . '/' . $path;
    if (!file_exists($path)) {
      $this->output()->writeln(dt('Location directory not found. See help using drush micon --help.'));
      return;
    }

    $fullpath = $path . '/_micon.scss';

    $content = [];
    $content[] = '/**';
    $content[] = '* Micon icon mixins and variables.';
    $content[] = '*';
    $content[] = '* DO NOT MAKE MANUAL CHANGES TO THIS FILE';
    $content[] = '* Generate via `drush micon ' . $path . '`.';
    $content[] = '*/' . "\n";
    $content[] = '@mixin micon($package: fa, $icon: rebel, $position: before) {';
    $content[] = '  @if $position == both {';
    $content[] = '    $position: \'before, &:after\';';
    $content[] = '  }' . "\n";
    $content[] = '  &:#{$position} {';
    $content[] = '    font-family: \'#{$package}\' !important;';
    $content[] = '    display: inline-block;';
    $content[] = '    speak: none;';
    $content[] = '    font-style: normal;';
    $content[] = '    font-weight: normal;';
    $content[] = '    font-variant: normal;';
    $content[] = '    text-transform: none;';
    $content[] = '    line-height: 1;';
    $content[] = '    vertical-align: middle;';
    $content[] = '    -webkit-font-smoothing: antialiased; // sass-lint:disable-line no-vendor-prefixes';
    $content[] = '    -moz-osx-font-smoothing: grayscale; // sass-lint:disable-line no-vendor-prefixes';
    $content[] = '    content: "#{map-get($micons, #{$package}-#{$icon})}"; // sass-lint:disable-line quotes';
    $content[] = '    @content;';
    $content[] = '  }';
    $content[] = '}' . "\n";

    $content[] = '$micons: (';
    foreach ($this->miconIconManager->getIcons() as $icons) {
      foreach ($icons as $icon) {
        if ($icon->multipleNames()) {
          foreach ($icon->getNames() as $name) {
            $content[] = '  ' . $icon->getPrefix() . $name . ': \'' . $icon->getHex() . '\',';
          }
        }
        else {
          $content[] = '  ' . $icon->getSelector() . ': \'' . $icon->getHex() . '\',';
        }
      }
    }
    $content[] = ');';

    $content[] = "\n";

    file_put_contents($fullpath, implode("\n", $content));

    // Notify user.
    $message = 'Successfully created the Micon SCSS file in: !path';

    $message = dt($message . '.', [
      '!path' => $path,
    ]);
    $this->output()->writeln($message);

  }

}

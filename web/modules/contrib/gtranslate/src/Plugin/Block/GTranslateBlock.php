<?php

namespace Drupal\gtranslate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'GTranslate' block.
 *
 * @Block(
 *   id = "gtranslate_block",
 *   admin_label = @Translation("GTranslate"),
 *   category = @Translation("Accessibility"),
 * )
 */
class GTranslateBlock extends BlockBase {

    protected $languages = array('en'=>'English','ar'=>'Arabic','bg'=>'Bulgarian','zh-CN'=>'Chinese (Simplified)','zh-TW'=>'Chinese (Traditional)','hr'=>'Croatian','cs'=>'Czech','da'=>'Danish','nl'=>'Dutch','fi'=>'Finnish','fr'=>'French','de'=>'German','el'=>'Greek','hi'=>'Hindi','it'=>'Italian','ja'=>'Japanese','ko'=>'Korean','no'=>'Norwegian','pl'=>'Polish','pt'=>'Portuguese','ro'=>'Romanian','ru'=>'Russian','es'=>'Spanish','sv'=>'Swedish','ca'=>'Catalan','tl'=>'Filipino','iw'=>'Hebrew','id'=>'Indonesian','lv'=>'Latvian','lt'=>'Lithuanian','sr'=>'Serbian','sk'=>'Slovak','sl'=>'Slovenian','uk'=>'Ukrainian','vi'=>'Vietnamese','sq'=>'Albanian','et'=>'Estonian','gl'=>'Galician','hu'=>'Hungarian','mt'=>'Maltese','th'=>'Thai','tr'=>'Turkish','fa'=>'Persian','af'=>'Afrikaans','ms'=>'Malay','sw'=>'Swahili','ga'=>'Irish','cy'=>'Welsh','be'=>'Belarusian','is'=>'Icelandic','mk'=>'Macedonian','yi'=>'Yiddish','hy'=>'Armenian','az'=>'Azerbaijani','eu'=>'Basque','ka'=>'Georgian','ht'=>'Haitian Creole','ur'=>'Urdu','bn' => 'Bengali','bs' => 'Bosnian','ceb' => 'Cebuano','eo' => 'Esperanto','gu' => 'Gujarati','ha' => 'Hausa','hmn' => 'Hmong','ig' => 'Igbo','jw' => 'Javanese','kn' => 'Kannada','km' => 'Khmer','lo' => 'Lao','la' => 'Latin','mi' => 'Maori','mr' => 'Marathi','mn' => 'Mongolian','ne' => 'Nepali','pa' => 'Punjabi','so' => 'Somali','ta' => 'Tamil','te' => 'Telugu','yo' => 'Yoruba','zu' => 'Zulu','my' => 'Myanmar (Burmese)','ny' => 'Chichewa','kk' => 'Kazakh','mg' => 'Malagasy','ml' => 'Malayalam','si' => 'Sinhala','st' => 'Sesotho','su' => 'Sudanese','tg' => 'Tajik','uz' => 'Uzbek','am' => 'Amharic','co' => 'Corsican','haw' => 'Hawaiian','ku' => 'Kurdish (Kurmanji)','ky' => 'Kyrgyz','lb' => 'Luxembourgish','ps' => 'Pashto','sm' => 'Samoan','gd' => 'Scottish Gaelic','sn' => 'Shona','sd' => 'Sindhi','fy' => 'Frisian','xh' => 'Xhosa');

    private function render_widget($widget_look, $enable_cdn, $gt_settings) {
        // todo: remove needless settings based on widget_look from gt_settings

        switch($widget_look) {
            case 'float': return $this->render_widget_float($enable_cdn, $gt_settings); break;
            case 'dropdown_with_flags': return $this->render_widget_dwf($enable_cdn, $gt_settings); break;
            case 'flags_dropdown': return $this->render_widget_fd($enable_cdn, $gt_settings); break;
            case 'flags': return $this->render_widget_flags($enable_cdn, $gt_settings); break;
            case 'dropdown': return $this->render_widget_dropdown($enable_cdn, $gt_settings); break;
            case 'flags_name': return $this->render_widget_fn($enable_cdn, $gt_settings); break;
            case 'flags_code': return $this->render_widget_fc($enable_cdn, $gt_settings); break;
            case 'lang_names': return $this->render_widget_ln($enable_cdn, $gt_settings); break;
            case 'lang_codes': return $this->render_widget_lc($enable_cdn, $gt_settings); break;
            case 'globe': return $this->render_widget_globe($enable_cdn, $gt_settings); break;
            case 'popup': return $this->render_widget_popup($enable_cdn, $gt_settings); break;

            default: return 'unknown widget look'; break;
        }
    }

    private function get_script_code($src, $orig_url, $orig_domain) {
        //jQuery does not preserve script attributes, so instead of the line below we have to do tricks
        //$script_code = '<script src="'.$src.'" data-gt-orig-url="'.$orig_url.'" data-gt-orig-domain="'.$orig_domain.'" defer></script>';

        $script_code = '<script>';
        $script_code .= "(function(){var js = document.createElement('script');";
        $script_code .= "js.setAttribute('src', '$src');";
        $script_code .= "js.setAttribute('data-gt-orig-url', '$orig_url');";
        $script_code .= "js.setAttribute('data-gt-orig-domain', '$orig_domain');";
        $script_code .= "document.body.appendChild(js);})();";
        $script_code .= '</script>';

        return $script_code;
    }

    private function render_widget_float($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/float.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/float.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_dwf($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/dwf.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/dwf.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_fd($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/fd.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/fd.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_flags($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/flags.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/flags.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_dropdown($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/dropdown.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/dropdown.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_fn($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/fn.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/fn.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_fc($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/fc.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/fc.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_ln($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/ln.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/ln.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_lc($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/lc.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/lc.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_globe($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/globe.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/svg/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/globe.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    private function render_widget_popup($enable_cdn, $gt_settings) {
        $widget_code = '';
        if($gt_settings['wrapper_selector'] == '.gtranslate_wrapper')
            $widget_code .= '<div class="gtranslate_wrapper"></div>';

        if(!empty($gt_settings['custom_domains']))
            $gt_settings['custom_domains'] = json_decode($gt_settings['custom_domains']);

        $orig_url = Html::escape(\Drupal::request()->getBasePath() . \Drupal::request()->getPathInfo());
        $orig_domain = Html::escape(\Drupal::request()->getHost());

        if($enable_cdn) {
            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . ';</script>';
            $widget_code .= $this->get_script_code('https://cdn.gtranslate.net/widgets/latest/popup.js', $orig_url, $orig_domain);
        } else {
            $base_path = base_path() . \Drupal::service('extension.list.module')->getPath('gtranslate');
            $gt_settings['flags_location'] = $base_path . '/flags/';

            $widget_code .= '<script>window.gtranslateSettings = ' . json_encode($gt_settings) . '</script>';
            $widget_code .= $this->get_script_code($base_path . '/js/popup.js', $orig_url, $orig_domain);
        }

        return $widget_code;
    }

    /**
     * {@inheritdoc}
     */
    public function build() {
        $settings = \Drupal::config('gtranslate.settings');

        $widget_look = $settings->get('widget_look');

        $float_position = $settings->get('float_position');
        if($float_position == 'inline')
            $switcher_horizontal_position = $switcher_vertical_position = 'inline';
        else
            list($switcher_vertical_position, $switcher_horizontal_position) = explode('-', $float_position);

        $position = $settings->get('position');
        if($position == 'inline')
            $horizontal_position = $vertical_position = 'inline';
        else
            list($vertical_position, $horizontal_position) = explode('-', $position);

        $float_switcher_open_direction = $settings->get('float_switcher_open_direction');
        $switcher_open_direction = $settings->get('switcher_open_direction');
        $default_language = $settings->get('source_lang');
        $native_language_names = $settings->get('native_language_names');
        $detect_browser_language = $settings->get('detect_browser_language');
        $add_new_line = $settings->get('add_new_line');
        $select_language_label = $settings->get('select_language_label');
        $flag_size = intval($settings->get('flag_size'));
        $flag_style = $settings->get('flag_style');
        $globe_size = intval($settings->get('globe_size'));

        $alt_flags = array();
        /* example raw_alt_flags: Array(
            [usa] => usa
            [mexico] => mexico
            [canada] => 0
            [argentina] => 0
            [colombia] => 0
            [brazil] => 0
            [quebec] => 0
        ) */
        $raw_alt_flags = $settings->get('alt_flags');
        foreach($raw_alt_flags as $country => $val) {
            if(($country == 'usa' or $country == 'canada') and $val == $country)
                $alt_flags['en'] = $val;
            elseif($country == 'brazil' and $val == $country)
                $alt_flags['pt'] = $val;
            elseif(($country == 'mexico' or $country == 'argentina' or $country == 'colombia') and $val == $country)
                $alt_flags['es'] = $val;
            elseif($country == 'quebec' and $val == $country)
                $alt_flags['fr'] = $val;
        }

        $wrapper_selector = $settings->get('wrapper_selector');
        $url_structure = $settings->get('url_structure');
        $custom_domains = $settings->get('custom_domains');
        $custom_domains_config = $settings->get('custom_domains_config');
        $languages = array_values(array_filter(array_values($settings->get('languages')), function($l) {return !empty($l);}));
        $custom_css = $settings->get('custom_css');
        $enable_cdn = $settings->get('enable_cdn');

        $gt_settings = array(
            'switcher_horizontal_position' => $switcher_horizontal_position,
            'switcher_vertical_position' => $switcher_vertical_position,
            'horizontal_position' => $horizontal_position,
            'vertical_position' => $vertical_position,
            'float_switcher_open_direction' => $float_switcher_open_direction,
            'switcher_open_direction' => $switcher_open_direction,
            'default_language' => $default_language,
            'native_language_names' => $native_language_names,
            'detect_browser_language' => $detect_browser_language,
            'add_new_line' => $add_new_line,
            'select_language_label' => $select_language_label,
            'flag_size' => $flag_size,
            'flag_style' => $flag_style,
            'globe_size' => $globe_size,
            'alt_flags' => $alt_flags,
            'wrapper_selector' => $wrapper_selector,
            'url_structure' => $url_structure,
            'custom_domains' => $custom_domains ? $custom_domains_config : null,
            'languages' => $languages,
            'custom_css' => $custom_css
        );

        $color_scheme = $settings->get('color_scheme');
        if($widget_look == 'dropdown_with_flags' and $color_scheme == 'dark') {
            $gt_settings['switcher_text_color'] = '#f7f7f7';
            $gt_settings['switcher_arrow_color'] = '#f2f2f2';
            $gt_settings['switcher_border_color'] = '#161616';
            $gt_settings['switcher_background_color'] = '#303030';
            $gt_settings['switcher_background_shadow_color'] = '#474747';
            $gt_settings['switcher_background_hover_color'] = '#3a3a3a';
            $gt_settings['dropdown_text_color'] = '#eaeaea';
            $gt_settings['dropdown_hover_color'] = '#748393';
            $gt_settings['dropdown_background_color'] = '#474747';
        }

        $block_content = $this->render_widget($widget_look, $enable_cdn, $gt_settings);

        $return = array(
          '#theme' => 'gtranslate',
          '#gtranslate_html' => $block_content,
          '#cache' => array(
              'max-age' => Cache::PERMANENT,
              'tags' => array('config:gtranslate.settings'),
              'contexts' => array(
                  'url',
                  'url.query_args'
              )
          ),
        );

        return $return;
    }
}
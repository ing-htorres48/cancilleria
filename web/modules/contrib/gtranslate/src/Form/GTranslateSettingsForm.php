<?php
/**
 * @file
 * Contains \Drupal\gtranslate\Form\GTranslateSettingsForm.
 */

namespace Drupal\gtranslate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Controller location for Live Weather Settings Form.
 */
class GTranslateSettingsForm extends ConfigFormBase {

  protected $languages = array('en'=>'English','ar'=>'Arabic','bg'=>'Bulgarian','zh-CN'=>'Chinese (Simplified)','zh-TW'=>'Chinese (Traditional)','hr'=>'Croatian','cs'=>'Czech','da'=>'Danish','nl'=>'Dutch','fi'=>'Finnish','fr'=>'French','de'=>'German','el'=>'Greek','hi'=>'Hindi','it'=>'Italian','ja'=>'Japanese','ko'=>'Korean','no'=>'Norwegian','pl'=>'Polish','pt'=>'Portuguese','ro'=>'Romanian','ru'=>'Russian','es'=>'Spanish','sv'=>'Swedish','ca'=>'Catalan','tl'=>'Filipino','iw'=>'Hebrew','id'=>'Indonesian','lv'=>'Latvian','lt'=>'Lithuanian','sr'=>'Serbian','sk'=>'Slovak','sl'=>'Slovenian','uk'=>'Ukrainian','vi'=>'Vietnamese','sq'=>'Albanian','et'=>'Estonian','gl'=>'Galician','hu'=>'Hungarian','mt'=>'Maltese','th'=>'Thai','tr'=>'Turkish','fa'=>'Persian','af'=>'Afrikaans','ms'=>'Malay','sw'=>'Swahili','ga'=>'Irish','cy'=>'Welsh','be'=>'Belarusian','is'=>'Icelandic','mk'=>'Macedonian','yi'=>'Yiddish','hy'=>'Armenian','az'=>'Azerbaijani','eu'=>'Basque','ka'=>'Georgian','ht'=>'Haitian Creole','ur'=>'Urdu','bn' => 'Bengali','bs' => 'Bosnian','ceb' => 'Cebuano','eo' => 'Esperanto','gu' => 'Gujarati','ha' => 'Hausa','hmn' => 'Hmong','ig' => 'Igbo','jw' => 'Javanese','kn' => 'Kannada','km' => 'Khmer','lo' => 'Lao','la' => 'Latin','mi' => 'Maori','mr' => 'Marathi','mn' => 'Mongolian','ne' => 'Nepali','pa' => 'Punjabi','so' => 'Somali','ta' => 'Tamil','te' => 'Telugu','yo' => 'Yoruba','zu' => 'Zulu','my' => 'Myanmar (Burmese)','ny' => 'Chichewa','kk' => 'Kazakh','mg' => 'Malagasy','ml' => 'Malayalam','si' => 'Sinhala','st' => 'Sesotho','su' => 'Sudanese','tg' => 'Tajik','uz' => 'Uzbek','am' => 'Amharic','co' => 'Corsican','haw' => 'Hawaiian','ku' => 'Kurdish (Kurmanji)','ky' => 'Kyrgyz','lb' => 'Luxembourgish','ps' => 'Pashto','sm' => 'Samoan','gd' => 'Scottish Gaelic','sn' => 'Shona','sd' => 'Sindhi','fy' => 'Frisian','xh' => 'Xhosa');

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormId() {
    return 'gtranslate_admin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['gtranslate.settings'];
  }

  public function syncCustomDomains(array &$form, FormStateInterface $form_state) {
    $ch = curl_init('https://tdns.gtranslate.net/tdn-bin/load-custom-domains');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-gt-domain: ' . \Drupal::request()->getHost(), 'user-agent: Drupal ' .  \Drupal::VERSION));

    $result = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($result);

    $custom_domains = '';
    if($result !== null and !isset($result->err))
        $custom_domains = json_encode($result);

    $form['general']['custom_domains_config']['#value'] = $custom_domains;
    return $form['general']['custom_domains_config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gtranslate.settings');

    $form['general'] = array(
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#open' => true,
    );

    $form['general']['widget_look'] = array(
        '#type' => 'select',
        '#title' => $this->t('Widget look'),
        '#default_value' => $config->get('widget_look'),
        '#options' => array('float' => 'Float', 'dropdown_with_flags' => 'Nice dropdown with flags', 'flags_dropdown' => 'Flags and dropdown', 'flags' => 'Flags', 'dropdown'=> 'Dropdown', 'flags_name' => 'Flags with language name', 'flags_code' => 'Flags with language code', 'lang_names' => 'Language names', 'lang_codes' => 'Language codes', 'globe' => 'Globe', 'popup' => 'Popup'),
        '#required' => TRUE
    );

    $form['general']['source_lang'] = array(
        '#type' => 'select',
        '#title' => $this->t('Translate from'),
        '#default_value' => $config->get('source_lang'),
        '#options' => $this->languages,
        '#required' => TRUE
    );

    $form['general']['url_structure'] = array(
        '#type' => 'radios',
        '#title' => $this->t('URL structure'),
        '#default_value' => $config->get('url_structure'),
        '#options' => array('none' => 'None (free)', 'sub_directory' => 'Sub-directory (paid) - example.com/fr', 'sub_domain' => 'Sub-domain (paid) - fr.example.com'),
        '#required' => TRUE
    );
    $form['general']['url_structure']['none']['#description'] = $this->t('Translations will be done on the fly on the same page using JavaScript. Search engines will not index translations.');
    $form['general']['url_structure']['sub_directory']['#description'] = $this->t('Translated pages will have a separate sub-directory URL which can be indexed by search engines.');
    $form['general']['url_structure']['sub_domain']['#description'] = $this->t('Translated pages will have a separate sub-domain URL which can be indexed by search engines.');

    $form['general']['custom_domains'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Custom domains - example.fr'),
        '#default_value' => $config->get('custom_domains'),
        '#description' => $this->t('Custom domains with language hosting option configured in <a href="https://my.gtranslate.io/settings#advanced" target="_blank">GTranslate dashboard</a>'),
        '#states' => array(
            'visible' => array(
                ':input[name="url_structure"]' => array('value' => 'sub_domain')
            )
        ),
        '#ajax' => array(
            'callback' => '::syncCustomDomains',
            'wrapper' => 'edit-custom_domains_config',
            'event' => 'change',
            'progress' => array('type' => 'throbber', 'message' => $this->t('Synchronizing configuration...'))
        )
    );

    $form['general']['custom_domains_config'] = array(
        '#type' => 'hidden',
        '#default_value' => $config->get('custom_domains_config'),
        '#prefix' => '<div id="edit-custom_domains_config">',
        '#suffix' => '</div>'
    );

    $form['general']['native_language_names'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Native language names'),
        '#default_value' => $config->get('native_language_names'),
        '#description' => $this->t("Show language names in their native alphabet")
    );

    $form['general']['detect_browser_language'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Detect browser language'),
        '#default_value' => $config->get('detect_browser_language'),
        '#description' => $this->t("Auto switch to browser language on the first visit")
    );

    $form['general']['enable_cdn'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Enable CDN'),
        '#default_value' => $config->get('enable_cdn'),
        '#description' => $this->t("Static files like images and javascript will be loaded from GTranslate CDN")
    );

    $form['general']['add_new_line'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Add new line'),
        '#default_value' => $config->get('add_new_line'),
        '#description' => $this->t("Adds line break between flags and dropdown"),
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array('value' => 'flags_dropdown')
            )
        )
    );

    $form['general']['select_language_label'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Select language label'),
        '#default_value' => $config->get('select_language_label'),
        '#description' => $this->t("Default text inside the dropdown when no language is selected"),
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array(
                    array('value' => 'dropdown'),
                    array('value' => 'flags_dropdown'),
                ),
            )
        )
    );

    $form['general']['globe_size'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Globe size'),
        '#default_value' => $config->get('globe_size'),
        '#options' => array(20 => '20px', 40 => '40px', 60 => '60px'),
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array('value' => 'globe')
            )
        ),
        '#required' => TRUE
    );

    $form['general']['color_scheme'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Color scheme'),
        '#default_value' => $config->get('color_scheme'),
        '#options' => array('light' => 'Light', 'dark' => 'Dark'),
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array('value' => 'dropdown_with_flags')
            )
        ),
        '#required' => TRUE
    );

    $form['general']['flag_size'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Flag size'),
        '#default_value' => $config->get('flag_size'),
        '#options' => array(16 => '16px', 24 => '24px', 32 => '32px', '48' => '48px'),
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array(
                    array('value' => 'flags'),
                    array('value' => 'dropdown_with_flags'),
                    array('value' => 'flags_dropdown'),
                    array('value' => 'flags'),
                    array('value' => 'flags_name'),
                    array('value' => 'flags_code'),
                    array('value' => 'popup'),
                ),
            )
        ),
        '#required' => TRUE
    );

    $form['general']['flag_style'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Flag style'),
        '#default_value' => $config->get('flag_style'),
        '#options' => array('2d' => '2D (.svg)', '3d' => '3D (.png)'),
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array(
                    array('value' => 'float'),
                    array('value' => 'flags'),
                    array('value' => 'dropdown_with_flags'),
                    array('value' => 'flags_dropdown'),
                    array('value' => 'flags'),
                    array('value' => 'flags_name'),
                    array('value' => 'flags_code'),
                    array('value' => 'popup'),
                ),
            )
        ),
        '#required' => TRUE
    );

    $form['alt_flags'] = array(
        '#type' => 'details',
        '#title' => $this->t('Alternative flags'),
        '#open' => false,
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array(
                    array('value' => 'float'),
                    array('value' => 'dropdown_with_flags'),
                    array('value' => 'flags_dropdown'),
                    array('value' => 'flags'),
                    array('value' => 'flags_name'),
                    array('value' => 'flags_code'),
                    array('value' => 'popup'),
                    array('value' => 'globe'),
                ),
            )
        )
    );

    $form['alt_flags']['alt_flags'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Alternative flags'),
        '#default_value' => $config->get('alt_flags'),
        '#options' => array('usa' => 'USA flag (English)', 'canada' => 'Canada flag (English)', 'brazil' => 'Brazil flag (Portuguese)', 'mexico' => 'Mexico flag (Spanish)', 'argentina' => 'Argentina flag (Spanish)', 'colombia' => 'Colombia flag (Spanish)', 'quebec' => 'Quebec flag (French)'),
    );

    $form['alt_flags']['alt_flags']['usa']['#states'] = array(
        'disabled' => array(
            ':input[name="alt_flags[canada]"]' => array('checked' => true),
        )
    );
    $form['alt_flags']['alt_flags']['canada']['#states'] = array(
        'disabled' => array(
            ':input[name="alt_flags[usa]"]' => array('checked' => true),
        )
    );
    $form['alt_flags']['alt_flags']['mexico']['#states'] = array(
        'disabled' => array(
            array(':input[name="alt_flags[argentina]"]' => array('checked' => true)),
            array(':input[name="alt_flags[colombia]"]' => array('checked' => true)),
        )
    );
    $form['alt_flags']['alt_flags']['argentina']['#states'] = array(
        'disabled' => array(
            array(':input[name="alt_flags[mexico]"]' => array('checked' => true)),
            array(':input[name="alt_flags[colombia]"]' => array('checked' => true)),
        )
    );
    $form['alt_flags']['alt_flags']['colombia']['#states'] = array(
        'disabled' => array(
            array(':input[name="alt_flags[argentina]"]' => array('checked' => true)),
            array(':input[name="alt_flags[mexico]"]' => array('checked' => true)),
        )
    );

    $form['general']['float_position'] = array(
        '#type' => 'select',
        '#title' => $this->t('Position'),
        '#default_value' => $config->get('float_position'),
        '#options' => array('bottom-left' => 'Bottom left', 'bottom-right' => 'Bottom right', 'top-left' => 'Top left', 'top-right' => 'Top right', 'inline' => 'Inline'),
        '#required' => true,
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array(
                    array('value' => 'float'),
                    array('value' => 'dropdown_with_flags'),
                )
            )
        )
    );

    $form['general']['float_switcher_open_direction'] = array(
        '#type' => 'select',
        '#title' => $this->t('Open direction'),
        '#default_value' => $config->get('float_switcher_open_direction'),
        '#options' => array('left' => 'Left', 'right' => 'Right', 'top' => 'Top', 'bottom' => 'Bottom'),
        '#required' => true,
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array('value' => 'float')
            )
        )
    );

    $form['general']['switcher_open_direction'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Open direction'),
        '#default_value' => $config->get('switcher_open_direction'),
        '#options' => array('top' => 'Top', 'bottom' => 'Bottom'),
        '#required' => true,
        '#states' => array(
            'visible' => array(
                ':input[name="widget_look"]' => array('value' => 'dropdown_with_flags')
            )
        )
    );

    $form['general']['position'] = array(
        '#type' => 'select',
        '#title' => $this->t('Position'),
        '#default_value' => $config->get('position'),
        '#options' => array('inline' => 'Inline', 'bottom-left' => 'Bottom left', 'bottom-right' => 'Bottom right', 'top-left' => 'Top left', 'top-right' => 'Top right'),
        '#required' => true,
        '#states' => array(
            'invisible' => array(
                ':input[name="widget_look"]' => array(
                    array('value' => 'float'),
                    array('value' => 'dropdown_with_flags'),
                )
            )
        )
    );

    $form['general']['wrapper_selector'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Wrapper selector'),
        '#default_value' => $config->get('wrapper_selector'),
        '#description' => $this->t("CSS selector of the wrapper element where the language switcher will be rendered, default: .gtranslate_wrapper"),
        '#required' => true
    );

    $form['language'] = array(
      '#type' => 'details',
      '#title' => $this->t('Languages'),
    );

    $form['language']['languages'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Languages'),
        '#options' => $this->languages,
        '#default_value' => $config->get('languages')
    );
    // todo: hide source_lang option, add all option
    // todo: flag languages option for flags and dropdown
    // todo: option to reorder languages

    $form['customization'] = array(
      '#type' => 'details',
      '#title' => $this->t('Custom CSS'),
    );

    $form['customization']['custom_css'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Custom CSS'),
        '#default_value' => $config->get('custom_css')
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
     $form_value = $form_state->getValues();

    $this->config('gtranslate.settings')
      ->set('widget_look', $form_value['widget_look'])
      ->set('source_lang', $form_value['source_lang'])
      ->set('url_structure', $form_value['url_structure'])
      ->set('flag_size', $form_value['flag_size'])
      ->set('flag_style', $form_value['flag_style'])
      ->set('globe_size', $form_value['globe_size'])
      ->set('color_scheme', $form_value['color_scheme'])
      ->set('alt_flags', $form_value['alt_flags'])
      ->set('wrapper_selector', $form_value['wrapper_selector'])
      ->set('float_position', $form_value['float_position'])
      ->set('float_switcher_open_direction', $form_value['float_switcher_open_direction'])
      ->set('position', $form_value['position'])
      ->set('switcher_open_direction', $form_value['switcher_open_direction'])
      ->set('native_language_names', $form_value['native_language_names'])
      ->set('detect_browser_language', $form_value['detect_browser_language'])
      ->set('enable_cdn', $form_value['enable_cdn'])
      ->set('add_new_line', $form_value['add_new_line'])
      ->set('select_language_label', $form_value['select_language_label'])
      ->set('custom_domains', $form_value['custom_domains'])
      ->set('custom_domains_config', $form_value['custom_domains_config'])
      ->set('custom_css', $form_value['custom_css']);

    $form_value['languages'][$form_value['source_lang']] = $form_value['source_lang'];
    $this->config('gtranslate.settings')->set('languages', $form_value['languages']);

    $form_value['flag_languages'][$form_value['source_lang']] = $form_value['source_lang'];
    $this->config('gtranslate.settings')->set('flag_languages', $form_value['flag_languages']);

    $this->config('gtranslate.settings')->save();

    parent::submitForm($form, $form_state);
  }
}
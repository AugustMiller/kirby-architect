<?php class Architect {
  # Architect Plugin Version
  private static $version = "0.3.0";

  # Blueprint Cache
  private static $blueprints = [];

  # Fetch all data for a Blueprint as an associative array
  public static function blueprint ($template) {
    if ( isset(static::$blueprints[$template]) ) {
      return static::$blueprints[$template];
    } else {
      return static::$blueprints[$template] = yaml::decode(kirby()->get('blueprint', $template));
    }
  }

  # Get data for a specific field in a template
  public static function field_info ($template, $field) {
    $fields = static::blueprint($template)['fields'];
    return array_key_exists($field, $fields) ? $fields[$field] : false;
  }

  # Get all `options` for a select/radio/checkboxes field
  public static function field_options ($template, $field) {
    $info = static::field_info($template, $field);
    return isset($info['options']) ? $info['options'] : [];
  }

  # Generate a `select` menu from a field's options
  public static function field_options_menu ($template, $field, $required = null, $language = null) {
    $menu = new Brick('select', [
      'id' => $field,
      'name' => $field
    ]);

    $options = static::field_info($template, $field);

    if ( $required === true || (is_null($required) && isset($options['required']) && $options['required']) ) {
      # Don't pad with an empty option
      $menu->attr('required', true);
    } else {
      $menu->append(new Brick('option', '', [
        'value' => ''
      ]));
    }

    # Append actual selectable ones:
    foreach ( static::field_options($template, $field) as $value => $labels ) {
      $option = new Brick('option', static::field_option_label($template, $field, $value, $language), [
        'value' => $value
      ]);

      # Add the `option` element, unless it's value is present in the global blacklist.
      if ( !in_array($value, c::get('architect.blacklist', [])) ) {
        if ( r::get($field) == $value ) $option->attr('selected', true);
        $menu->append($option);
      }
    }
    return $menu;
  }

  # Get a localized `label` for a field
  public static function field_label ($template, $key, $language = null) {
    if ( is_null($language) ) $language = site()->language()->locale();
    if ( $field = static::field_info($template, $key) ) {
      $label = $field['label'];
      if ( is_array($label) ) {
        return isset($label[$language]) ? $label[$language] : $label[site()->defaultLanguage()->locale()];
      } else {
        return $label;
      }
    }
  }

  # Get a localized label for an field's value
  public static function field_option_label ($template, $field, $value, $language = null) {
    if ( is_null($language) ) $language = site()->language()->locale();
    
    # Coerce $value into string— we need to use this to dereference an array in a moment!
    if ( !is_string($value) ) $value = (string)$value;

    $options = static::field_options($template, $field);

    if ( is_array($options[$value]) ) {
      return isset($options[$value][$language]) ? $options[$value][$language] : $options[$value][site()->defaultLanguage()->locale()];
    } else {
      return $options[$value];
    }
  }
}

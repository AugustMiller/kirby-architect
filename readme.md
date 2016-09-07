# Kirby Architect: A Blueprint Reader

> Easily reference Blueprint data from anywhere in your Kirby application.

For projects where the same field and option labels are shared between the Panel and front-end, this plugin dramatically reduces the amount of duplicated text in your codebase. Generally speaking, it simplifies access of Blueprint data so your templates can stay clean.

This plugin is especially well-suited for generating forms used to filter pages.

Only Kirby 2.3 and up are supported, as the Architect plugin makes use of the [Registry](https://getkirby.com/docs/developer-guide/plugins/registry).

## Installation

You can install the Architect plugin as a submodule, if you're familiar with Git and the command line:

```sh
cd /path/to/your/project
git submodule add https://github.com/AugustMiller/kirby-architect.git site/plugins/architect
```

It's important that the folder be named `architect`, because Kirby looks for the plugin in a PHP file with the same name as its folder.

You can also directly [download](https://github.com/AugustMiller/kirby-architect/archive/master.zip) an archive of the current project state, rename the folder to `architect`, and add it to the `plugins` folder of your site.


## Blueprints

The Blueprint below contains all the information we'll work with in the following examples. Assume the Blueprint is named `variety.yml`.

```yml
title: Variety
notes: >
  As you'll see, you can store any kind of information in your Blueprints, and access it anywhere!
fields:
  title:
    label:
      en: Scientific Identifier
      es_419: Identificador científica
    type: text

  density:
    label:
      en: Planting Density
      es_419: Densidad de la siembra
    type: select
    options:
      unknown:
        en: Unknown
        es_419: Desconocido
      bourbon-like:
        en: Bourbon-like (3000-4000 a/ha)
        es_419: Similar al Borbón (3000/4000 por Ha)
      caturra-like:
        en: Caturra-like (5000-6000 a/ha)
        es_419: Similar al Caturra (5000/6000 por Ha)
      f1-hybrid-like:
        en: F1 hybrid-like (4000-5000 a/ha)
        es_419: Similar a los Híbridos F1 (4000-5000 por Ha)
    default: unknown
    help: >
      Value should be calibrated against Caturra.
```

Historically, it's been difficult to output the full, translated option text (i.e. `Bourbon-like (3000-4000 a/ha)`, `Similar al Borbón (3000/4000 por Ha)`) in the front-end. Often, it requires managing translations in your [language files](https://getkirby.com/docs/languages/variables), which can quickly generate duplicate code.

## Examples

### Get an entire Blueprint by template

Returns a parsed Blueprint. The class will look for `yml` and `yaml` extensions.

```php
Architect::blueprint('variety');
```

### Get a field's label

Returns the label for a field, in the site's current language. Accepts a language code as the third argument to override.

```php
Architect::field_label('variety', 'title'); # -> "Scientific Identifier"
Architect::field_label('variety', 'title', 'es_419'); # -> "Identificador científica"
```

### Get a field value's human-facing label

Get the localized label for a value:

```php
Architect::field_option_label('variety', 'density', 'bourbon'); # -> "Bourbon-like (3000-4000 a/ha)"
```

Better yet, you can dynamically fetch the selected option's localized label from your page's data:

```php
Architect::field_option_label('variety', 'density', $page->density());
```

### Get all options for a field

Return an associative array of options and labels.

```php
Architect::field_options('variety', 'density');
```

### Output a `select` menu for a given set of options

Returns a `Brick` instance populated with `option` elements corresponding to the field's `options` and their localized labels.

```php
Architect::field_options_menu('variety', 'density');
```

```html
<!-- Viewed in the default language, `en` -->
<select id="density" name="density">
  <option></option>
  <option value="unknown">Unknown</option>
  <option value="bourbon-like">Bourbon-like (3000-4000 a/ha)</option>
  <option value="caturra-like">Caturra-like (5000-6000 a/ha)</option>
  <option value="f1-hybrid-like">F1 hybrid-like (4000-5000 a/ha)</option>
</select>
```

The blank `option` element is inserted when a field is not required in the Blueprint, or the third argument is `false`: `Architect::field_options_menu('variety', 'density', false);`. `true` as the third argument makes the menu required. Use `null` as the third argument to use the default behavior (or whatever your override preference is), when passing the fourth language-override argument.

You can black-list values from being output in menus. This is useful if you only want to filter by _some_ values that are present in the blueprint. It defaults to an empty array.

```php
c::set('architect.blacklist', ['your', 'custom', 'field', 'values']);
```

### Get arbitrary Blueprint data

You can get any data attached to a Blueprint, manually:

```php
Architect::blueprint('variety')['title'] # -> "Variety"
```

Blueprints are a great place to store any additional data you may want to associate with a particular template:

```php
Architect::blueprint('variety')['notes'] # -> "As you'll see, you can store any kind of information in your Blueprints, and access it anywhere!"
```

The same goes for data inside each field:

```php
Architect::field_info('variety', 'density')['help'] # -> Value should be calibrated against Caturra.
```

Localization is not automatic when retrieving data manually.

## Other Notes

The plugin implements a static `$blueprints` property that acts as a cache for parsed Blueprints. Repeated reading and parsing of Blueprints has a major impact on performance.

A welcome supplement or alternative to this plugin would be a field method to fetch a field definition's data from a `Field` object, directly.

You can emulate this functionality piecemeal by adding a new file in your `plugins` folder:

```php
kirby()->set('field::method', 'label', function ($field) {
  return Architect::field_label($field->page->intendedTemplate(), $field->name);
});

kirby()->set('field::method', 'formattedValue', function ($field) {
  return Architect::field_option_label($field->page->intendedTemplate(), $field->name, $field->value);
});
```

It's also possible to add a method to fetch entire Blueprints from thier `Page` object:

```php
kirby()->set('page::method', 'architect', function($page) {
  return Architect::blueprint($page->intendedTemplate());
});
```

These methods are not required to use the Architect plugin— they just provide a shortcut to common methods. You can alias any of the plugin’s features this way.

_Psst!_ If you're not used to the [Kirby Registry](https://getkirby.com/docs/developer-guide/plugins/registry) yet, you can back-port these solutions by [manually modifying](https://getkirby.com/docs/developer-guide/objects/) the Kirby objects.

:deciduous_tree:

---
name: opencart-i18n
description: OpenCart language-file and multilingual-data rules for admin/catalog modules, including Ukrainian/Russian projects and language_id-based entity translations.
---

# OpenCart Languages and Translations

Use this skill whenever a task adds or changes UI text, multilingual fields, SEO metadata, translated entities, language tabs, or language-aware URLs.

## Two translation systems must not be confused

OpenCart modules usually have two separate multilingual concerns:

1. **Interface translations** — labels, buttons, errors, headings, help text.
2. **Entity/content translations** — names, descriptions, meta data, SEO content stored in the database per `language_id`.

A correct module often needs both.

## Interface language files

Typical OpenCart 2.3-style locations:

```text
admin/language/<language>/extension/module/<module>.php
admin/language/<language>/extension/module/<module>/<screen>.php
catalog/language/<language>/extension/module/<module>.php
```

Typical definitions:

```php
$_['heading_title'] = '...';
$_['text_success'] = '...';
$_['entry_status'] = '...';
$_['help_example'] = '...';
$_['error_permission'] = '...';
```

Load the same route that matches the file:

```php
$this->load->language('extension/module/<module>');
```

or:

```php
$this->load->language('extension/module/<module>/<screen>');
```

Use `$this->language->get('key')` or the project's established pattern to populate `$data`.

## Never guess the language folder code

Legacy and customized OpenCart installations may use different folder names for the same human language.

For Ukrainian, repositories may contain names such as:

```text
ua
uk
ua-uk
uk-ua
ukrainian
```

For Russian, variants may include:

```text
ru
ru-ru
russian
```

The OCFilter 4.8.2 reference ships several aliases for broad distribution compatibility. That does **not** mean every custom module should duplicate them all.

For a project-specific module:

1. inspect `admin/language/` and `catalog/language/`;
2. find the language folders actually used by neighboring first-party/custom modules;
3. inspect configuration and language records when available;
4. create/update only the required folders unless distribution requirements say otherwise.

## Admin and catalog translations are separate

Do not assume an admin language file is available in catalog or vice versa.

If text appears in both contexts, define it in both appropriate files or centralize through a project-supported mechanism. Do not reach across application directories at runtime.

## Missing-key debugging

If the UI shows an empty string or untranslated key, trace in this order:

```text
active language
  ↓
actual language folder
  ↓
correct route/path
  ↓
key exists in file
  ↓
controller loads that route
  ↓
$data key passed to template
  ↓
template uses matching key
```

Also check OCMOD-injected menu/UI code because it may load a different language route than the target page.

## Multilingual database entities

Use description tables for user-editable multilingual content.

A common pattern is:

```text
<module>_item
<module>_item_description
```

with a description primary/composite key based on:

```text
item_id + language_id
```

Potential translated fields include:

- name;
- heading/title;
- description;
- meta title;
- meta description;
- meta keyword when the project still uses it;
- button/label text when configured by merchants;
- language-aware SEO keyword/slug if the project supports it.

Do not store all languages in one serialized text field when the project already follows OpenCart description-table conventions.

## Admin form pattern

For translatable entity forms:

1. load `model_localisation_language`;
2. get active languages;
3. render one tab/section per language;
4. name fields by `language_id`;
5. validate each required language entry deliberately;
6. save descriptions by `language_id`.

Conceptual request shape:

```text
item_description[1][name]
item_description[1][description]
item_description[2][name]
item_description[2][description]
```

Do not key persistent entity data by folder code if OpenCart already uses numeric `language_id`.

## Validation rules

Define which languages are mandatory.

Do not accidentally require every optional language field merely because multiple language tabs exist.

When validating translated values, keep errors indexed by `language_id` so the admin UI can display the error next to the correct tab/field.

The OCFilter reference follows this pattern for names, headings, meta fields, filter descriptions, and page descriptions.

## Adding a new shop language

If a module owns multilingual entity tables, consider how existing entities behave when a new OpenCart language is added.

The OCFilter reference registers a language-add event and copies descriptions from the configured default language into the new `language_id` using `INSERT IGNORE`.

This is a useful pattern when product requirements want immediate fallback content, but it is not always correct.

Choose deliberately:

- clone default-language content;
- create empty rows for translation;
- rely on runtime fallback;
- require manual translation.

If using an event, register it idempotently and remove it on uninstall.

## Fallback behavior

Fallback must be explicit.

A safe order for display can be:

1. requested/current language row;
2. configured default language row if product rules allow fallback;
3. neutral empty/placeholder behavior.

Do not silently save fallback text into a different language unless that is the intended business rule.

## Multistore + multilingual

When SEO URLs or content vary by both store and language, treat `(store_id, language_id)` as separate dimensions.

Do not assume one keyword can be reused across every language/store if the platform's SEO implementation requires uniqueness.

Search the target project for:

- `seo_url` vs `url_alias` implementation;
- language-aware SEO modifications;
- `store_id` and `language_id` columns;
- existing collision checks.

## Template language selectors/flags

OpenCart versions differ in language icon conventions.

The OCFilter reference checks both newer `language/<code>/<code>.png` locations and older `view/image/flags/...` conventions.

For a project-specific module, use the active project's existing language-tab UI instead of reproducing version compatibility code unnecessarily.

## Translation source-of-truth rule

Before adding a text string:

- search for an existing equivalent key in the same module;
- reuse consistent terminology;
- keep admin/catalog wording aligned where appropriate;
- do not hard-code user-visible strings directly in controllers, models, JavaScript, or templates unless the project intentionally does so.

For JavaScript UI text, pass translated strings from PHP/data attributes/config or use the project's JS localization mechanism.

## UA/RU module rule

When the target project is Ukrainian/Russian multilingual:

- verify both active folder codes;
- keep both files structurally synchronized;
- add every new key to both languages in the same change;
- verify admin and catalog independently;
- verify DB-translated entity save/load for both `language_id` values;
- verify language-aware SEO behavior if URLs are affected.

Do not leave English as an accidental fallback unless the project explicitly keeps English enabled.

## Completion checklist

Before finishing a language-related change:

- correct language folders confirmed;
- language route loaded;
- no new user-facing hard-coded strings;
- all required language files contain the same key set;
- entity translations save/load by `language_id`;
- validation maps to the correct language;
- new-language behavior is defined;
- multistore/SEO dimensions checked where relevant;
- template/JS receives translated values correctly.
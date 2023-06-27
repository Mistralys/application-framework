# Localization

The localization is handled by the [Application Localization] package.

## The principle

All texts are written in native english in the code, using one of the available
translation methods. The localization package then allows translating these using
a separate UI.

## Translation methods

- `t()` Translate a string and return the localized text.
- `pt()` Same as `t()`, but echo the text (for use in templates).
- `pts()` Same as `pt()`, but adds a space at the end for HTML.

All these methods accept an english string as input, and an arbitrary amount of
parameters which are inserted into the text using the `sprintf` function.

Example with a placeholder:

```php
$text = t('The record was saved at %1$s.', sb()->time());
```

Example in an HTML tag:

```html
<p><?php pt('Paragraph text here') ?></p>
<p>
    <?php
        pts('First sentence');
        pts('Second sentence');
    ?>
</p>
```

## Clientside translation

All javascript files can use the `t()` function. These are treated exactly like the
server side texts, including inserting values with placeholders. A sprintf shim
guarantees that the exact same syntax can be used.

Translations are loaded automatically clientside by the localization system. In
essence, nothing more needs to be done than to enclose english texts in `t()`.

## String concatenation

To concatenate strings, translated strings, icons and the like, the `UI_StringBuilder` class can
be used, which supports method chaining. Many of the framework UI methods accept string builder
instances natively. The shorthand method `sb()` can be used to create a new instance.

For example, displaying an icon with a text:

```php
sb()
    ->icon(UI::icon()->information())
    ->t('This is a translated text.');
```

## Translation UI

### Application texts

Developers can access the translation UI under _Manage > Translation_. This finds all instances
of the translation methods, and allows adding the translations for all locales defined
for the application.

> NOTE: All translatable texts will be shown, including Composer libraries like the framework
or [Application Utils][]. Avoid translating those, since they will be overwritten when those
libraries are updated.

### Framework texts

A translation UI is available for the translatable text within the framework itself.
Run a `composer install` in a local clone of the framework, then point your browser to the
`localization` subfolder.

## Releases and SATIS repository

### Create a development release

A development release is simply linking the composer.lock to a specific commit in the GitHub
package of the application framework.

1) Push all changes in the framework
2) Update the SATIS repository
3) Run `composer update` in the application
4) Commit the `composer.lock` file
5) Run `composer install` in the local working copies

> NOTE: This assumes that the framework version is set to `dev-main` in the application's
`composer.json` file for development purposes.

### Create a production release

A production release is tied to a version tag in GitHub.

01) Run PHPStan checks for coding issues
02) Run the unit tests
03) Add changes to the `changelog.txt` file
04) Enter the matching version number in the `VERSION` file
05) Push all changes in the framework
06) Create a release on GitHub for the version number
07) Update the SATIS repository
08) Change the application to the version number of the release
09) Run `composer update` in the application
10) Commit the composer files
11) Run `composer install` in the local working copies

### Updating the SATIS repository

The SATIS repository is a private composer packages repository like packagist.org.
It is hosted by Mistralys and is available here:

[Mistralys composer SATIS][]

The access credentials can be found in the application's `auth.json` file.

The updater script to refresh the data for all available packages is here:

[Mistralys SATIS updater][]

## The WHATSNEW.xml and VERSION files

### WHATSNEW.xml

#### Formatting

It is possible to use Markdown for formatting the texts.

Variables may also be used in texts, to insert application specific data:

- `$appURL` - URL to the application's htdocs folder.

  > NOTE: The available variables are defined in
  `Application_Whatsnew_Version_Language_Category_Item::renderVariables()`.

#### Inserting images

Images specific to the WHATSNEW.xml file can be stored in the theme's `whatsnew`
subfolder, and can be added with the command:

```
{image:filename.jpg}
```

### VERSION

This contains the application's current version string. It must be generated
automatically from the WHATSNEW.xml file on the production server. In local
development copies, it is done automatically by the [Framework Manager][].


[Framework Manager]: https://github.com/Mistralys/appframework-manager
[Application Utils]: https://github.com/Mistralys/application-utils
[Application Localization]: https://github.com/Mistralys/application-localization
[Mistralys composer SATIS]: https://composer.mistralys-eva.systems
[Mistalys SATIS updater]: https://composer.mistralys-eva.systems/updater.php

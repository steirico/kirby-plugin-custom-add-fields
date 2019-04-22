# Kirby custom Add Fields Plugin
Custom fields for Kirby's add dialog. This plugin allows to define the fields shown on Kirby's page add dialog in the corresponding
page's blueprint.

## Installation
Use one of the alternatives below.

### Download

Download and copy this repository to `/site/plugins/kirby-plugin-custom-add-fields`.

### Git submodule

```
git submodule add https://github.com/steirico/kirby-plugin-custom-add-fields.git site/plugins/kirby-plugin-custom-add-fields
```

### Composer

```
composer require steirico/kirby-plugin-custom-add-fields
```

## Usage

### Defining custom Add Fields
This plugin adds the extra property `addFields` to page blueprints.
To define custom add fields do as you would do for [defining regular fields](https://getkirby.com/docs/reference/panel/sections/fields)
but put the definition in the property `addFields`.

>   `/blueprints/pages/remote.yml`:
>   ```yaml
>   title: Blueprint with custom Add Fields
>
>   fields:
>       # field definitions
>       title:
>           label: Title
>           type: text
>       content:
>           label: Content
>           type: textarea
>
>   # custom add fields definition
>   addFields:
>       title:
>           label: Title
>           type: text
>           required: true
>           icon: title
>
>       remoteUrl:
>           label: URL to external Content
>           type: text
>           required: true
>           icon: url
>   ```

### Using custom Add Fields

Values of custom add fields that correspond to fields of the page blueprint are
taken into account for the new page straightforwardly. In the example above the value of `title` in the add page dialog will be set as page's `title`.

### `slug` Handling

In order to have kirby adding pages correctly the property `slug` has to be set.
There are three ways to define a page's `slug`:
1. Add a custom add field named `slug` in order to define the `slug` manually.
1. If a field named `slug` is missing the plugin will set the `slug` based on
   the current timestamp.
1. Set/overwrite the `slug` in a pages hook script (see below).

### Using custom Add Fields in Hook Scripts

The values of the custom add fields can be used on the server side for modifying the
page to be added.

To do so one can register a [`page.create:after` hook](https://getkirby.com/docs/reference/plugins/extensions/hooks) and modify the `page` object.

The plugin also registers a generic hook which automatically detects and calls the
[page model's](https://getkirby.com/docs/guide/templates/page-models) static
method named `hookPageCreate($page)`. Define a page model and the method as follow:

> `/site/models/remote.php`:
> ```php
> <?php
> class RemotePage extends Page {
>     public static function hookPageCreate($page){
>         // get value of add field remoteUrl
>         $remoteUrl = $page->remoteUrl()->value();
>
>         // fetch remote content
>         $content = myMagicRemoteContentGetter($remoteUrl);
>
>         // update page field content
>         $page->update(array(
>             'content' => $content
>         ));
>
>         // set slug according to add field title
>         $page->changeSlug(Str::slug($page->title()->value()));
>     }
> }
>```

## Know issues

There are some known issues related to this plugin:
- Use of [query language](https://getkirby.com/docs/guide/blueprints/query-language)
  in fields such as [select field](https://getkirby.com/docs/reference/panel/fields/select)
  fails.
- [Conditional fields](https://getkirby.com/docs/guide/blueprints/fields#conditional-fields)
  are untested.
- Kirby offers no possibility to redirect to the newly created page if the `slug`
  has been [modified in a hook](https://forum.getkirby.com/t/how-to-redirect-after-slug-changed-in-page-update-after-hook/13173/3).
  Therefore, after adding a page the panel remains on the actual page.


## License

MIT

## Credits

- [Rico Steiner](https://github.com/steirico)

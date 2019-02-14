# Kirby Custom Add Fields Plugin
Custom fields for Kirby's add dialog. This plugin allows to define the fields shown on Kirby's page add dialog in a page's blueprint.

## Installation
Use one of the alternatives below.

### 1. Clone or download

1. [Clone](https://github.com/steirico/kirby-plugin-custom-add-fields.git) or [download](https://github.com/steirico/kirby-plugin-custom-add-fields/archive/master.zip)  this repository.
2. Unzip the archive, rename the folder to `kirby-plugin-custom-add-fields` and move ist to `site/plugins/`.

**Make sure that the plugin folder structure looks like this:**

```
site/plugins/kirby-plugin-custom-add-fields/
```

### 2. Git Submodule

If you know your way around Git, you can download this plugin as a submodule:

```
$ cd path/to/kirby
$ git submodule add https://github.com/steirico/kirby-plugin-custom-add-fields site/plugins/kirby-plugin-custom-add-fields
```
## Usage

#### Define Fields
This plugins add the extra property `addFields` to blueprints.
To define custom add fields do as you would do for [defining regular fields](https://getkirby.com/docs/reference/panel/sections/fields)
but put the definition in the property `addFields`.

```yaml
title: My Blueprint with Custom Add Fields

# Blueprint definitions

addFields:
    title:
        label: Title
        type: text
        required: true
        icon: title
    slug:
        label: Define a Slug
        type: text
        required: true,
        counter: false
        icon: url
    content:
        label: Content
        type: textarea
```

### Hook Scripts
**TODO**
- `page.create:after`
- `PAGE_MODEL_CLASS::hookPageCreate($page)`

### uid/slug handling
**TODO**
- available -> taken into account
- not avialable -> random value taken (timestamp)
- set it in hoock script
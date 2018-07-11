# Kirby Custom Add Fields Plugin
Custom fields for Kirby's add dialog. This plugin allows to define the fields shown on Kirby's page add dialog in a page's blueprint.

The base concept for this plugin is taken from this [Kirby forum thread](https://forum.getkirby.com/t/add-field-to-page-creation-modal/9359)
and the code is based on [@lukaskleinschmidt's demo code](
https://github.com/lukaskleinschmidt/kirby-custom-add-form).

## Installation
Use one of the alternatives below.

### 1. Kirby CLI

If you are using the [Kirby CLI](https://github.com/getkirby/cli) you can install this plugin by running the following commands in your shell:

```
$ cd path/to/kirby
$ kirby plugin:install steirico/kirby-plugin-custom-add-fields
```

### 2. Clone or download

1. [Clone](https://github.com/steirico/kirby-plugin-custom-add-fields.git) or [download](https://github.com/steirico/kirby-plugin-custom-add-fields/archive/master.zip)  this repository.
2. Unzip the archive if needed and rename the folder to `kirby-plugin-custom-add-fields`.

**Make sure that the plugin folder structure looks like this:**

```
site/plugins/kirby-plugin-custom-add-fields/
```

### 3. Git Submodule

If you know your way around Git, you can download this plugin as a submodule:

```
$ cd path/to/kirby
$ git submodule add https://github.com/steirico/kirby-plugin-custom-add-fields site/plugins/kirby-plugin-custom-add-fields
```
## Usage

### uid handling
- available -> taken into account
- not avialable -> random value taken from uniqid()
- set it in hoock script
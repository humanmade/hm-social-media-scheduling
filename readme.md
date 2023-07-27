Human Made Social Media Scheduling
==========================

A plugin to schedule the posting of a social media post to Twitter and/or Facebook when publishing a post.

## Installation Instructions

It is recommended to install as a plugin or mu-plugin using composer.

```
composer require humanmade/hm-social-media-scheduling
```

HM Social Media Scheduling depends on [CMB2 plugin](https://wordpress.org/plugins/cmb2/) so make sure to install and active that plugin as well.

## Releasing a new version

1. Update the version numbers in `plugin.php`.
2. Add the changelog to the readme for the new version.
3. Commit your changes to `main` and push.
4. Manually create a new release and tag on GitHub.

## Changelog

### v1.2.0

* Fix issue with override robots.txt when blocking Twitter Bot.

### v1.1.0

* Added support for PHP 8.

### v1.0.0

* Release version 1.0.0 as pre-release testing was successful.

### v0.1.0-alpha

* Initial pre-release version.

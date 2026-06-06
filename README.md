# Drupal 11 Base Project

Reusable Drupal 11 starter — Bootstrap public theme, AdminLTE admin theme, config-entity admin sidebar.

## Structure (fixed — do not move)

```
modules/cassiopeia/
modules/cassiopeia_admin/
modules/contrib/
themes/cassiopeia_theme/
themes/cassiopeia_admin_theme/
themes/bootstrap/
```

## Quick start

```bash
composer install
cp sites/default/example.settings.local.php sites/default/settings.local.php
php scripts/patch-settings-include.php
drush site:install standard -y
drush en cassiopeia cassiopeia_admin admin_theme admin_toolbar -y
drush theme:enable cassiopeia_theme cassiopeia_admin_theme -y
drush config:set system.theme default cassiopeia_theme -y
drush config:set system.theme admin cassiopeia_admin_theme -y
drush php:script scripts/cassiopeia-post-enable.php
drush cr
```

Full plan: **`docs/BASE_PROJECT.md`**

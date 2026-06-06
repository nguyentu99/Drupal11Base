# Modules to enable (Cassiopeia stack)

Use **Extend** (`/admin/modules`) or Drush. Enabling **`cassiopeia_admin`** pulls in most dependencies via `cassiopeia_admin.info.yml`.

## Required (custom + core)

| Module | Purpose |
|--------|---------|
| **cassiopeia** | Twig helpers, images, render template |
| **cassiopeia_admin** | Admin sidebar menu, block/item CRUD |
| **Block** (`block`) | Theme regions |
| **User** (`user`) | Permissions, accounts |
| **Views** (`views`) | Admin listings (dependency) |
| **File** (`file`) | Images / uploads |
| **Node** (`node`) | Optional Twig `cassiopeia_node_load` |

## Required contrib

| Module | Purpose |
|--------|---------|
| **Admin Theme** (`admin_theme`) | Admin theme on custom paths (`/administrator`, `/manager`, …) |
| **Admin Toolbar** + **Admin Toolbar Tools** | Drupal admin toolbar |

## Recommended contrib (in repo)

| Module | Purpose |
|--------|---------|
| **Token** | Pathauto, Metatag |
| **Pathauto** | URL aliases |
| **Metatag** | SEO meta tags |
| **SMTP** | Mail via PHPMailer |
| **IMCE** | File browser |
| **Color Field** | Color fields |
| **Views Entity Form Field** | Views form integration |

Metatag submodules (Open Graph, Twitter, etc.) are optional — enable only if needed.

## Themes (`/admin/appearance`)

| Theme | Role |
|-------|------|
| **Bootstrap** | Base theme (dependency) |
| **cassiopeia_theme** | Default / public site |
| **cassiopeia_admin_theme** | Administration theme |

## Drush (one command)

```bash
composer require drush/drush:^13
php vendor/bin/drush pm:enable cassiopeia cassiopeia_admin admin_theme admin_toolbar admin_toolbar_tools token pathauto metatag color_field imce smtp views_entity_form_field -y
php vendor/bin/drush theme:enable cassiopeia_theme cassiopeia_admin_theme -y
php vendor/bin/drush config:set system.theme default cassiopeia_theme -y
php vendor/bin/drush config:set system.theme admin cassiopeia_admin_theme -y
php vendor/bin/drush role:perm:add administrator "cassiopeia admin block manager,cassiopeia admin content manager" -y
php vendor/bin/drush updb -y
php vendor/bin/drush cr
php vendor/bin/drush php:script scripts/install-administrator-entities.php
```

## Status report fixes

### Entity definitions (administrator_block / administrator_block_item)

After enabling config entities, run:

```bash
php vendor/bin/drush php:script scripts/install-administrator-entities.php
php vendor/bin/drush updb -y
php vendor/bin/drush cr
```

Or only `drush updb` if update `11005` is pending.

### Trusted host patterns

1. Copy `sites/default/example.settings.local.php` → `sites/default/settings.local.php` (already created if you ran setup).
2. At the **end** of `sites/default/settings.php`, uncomment:

```php
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
```

Add your production hostname to `$settings['trusted_host_patterns']` before go-live.

## Permissions

Grant to appropriate roles at `/admin/people/permissions`:

- **Cassiopeia admin block manager** — sidebar CRUD UI
- **Cassiopeia admin content manager** — floating “Quản trị” link → `/admin`
- **Cassiopeia manager theme access (legacy)** — floating link → `/manager` (optional)

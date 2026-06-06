# Project Audit Report — Drupal11Base

**Scope:** Full project review with emphasis on custom code (`cassiopeia`, `cassiopeia_admin`, `cassiopeia_theme`, `cassiopeia_admin_theme`), Composer stack, and operational patterns.  
**Severity legend:** 🔴 Critical · 🟠 High · 🟡 Medium · 🔵 Low  
**Remediation reference:** Performance sprints 1–5 (see `docs/PERFORMANCE_AUDIT_AND_MODERNIZATION.md`, `docs/TECHNICAL_DOCUMENTATION.md` §14–15).

---

## Remediation status (post sprints 1–5)

The following audit findings were **addressed in code** (May 2025). Items marked **Open** still need attention.

| Area | Fixed | Still open |
|------|-------|------------|
| Performance (menu cache, assets) | P-01–P-03, P-06 subset, P-08, P-10 theme stubs | Production aggregation (documented), Blackfire (manual) |
| Correctness (routes, delete, render) | S-04, S-05, M-01, M-02, D-01, D-02, D-13, M-05 FormState | — |
| Security (forms, deps, test route) | S-01–S-17 (incl. S-13 path validation, S-16 `.env.example`) | Contrib hardening (imce, metatag surface) |
| Architecture | Repository, config entities, 6 kernel tests, migration 11003–11005 | Optional Menu API sync |

---

## Executive summary

The custom Cassiopeia stack was a **Drupal 7–style admin menu system** partially migrated to Drupal 11. **Core functional and performance blockers from the original audit are largely resolved** (menu cache, routes, param converters, item delete, render helper, debug `dump()`). Remaining work centers on **operational hardening** (CSS/JS aggregation, monitoring).

| Category | Critical (open) | High (open) | Medium | Low |
|----------|-----------------|-------------|--------|-----|
| Security | 0 | 0 | 4 | 3 |
| Performance | 0 | 0 | 2 | 4 |
| Maintainability | 0 | 2 | 6 | 5 |
| Drupal best practices | 0 | 5 | 8 | 6 |

*(Counts reflect **remaining** issues after sprint fixes; see tables below for per-item status.)*

---

## 1. Security issues

### 🔴 Critical

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| S-01 | **Stored XSS via menu `icon` and `name` fields** | Forms, list `#markup`, DB fields | **Fixed** — `AdministratorFieldHelper`, sanitize on save, escaped `#markup`, safe icon preview JS |
| S-02 | **Outdated PHPMailer (v6.1.7, 2020)** | `composer.json` | **Fixed** — `^6.9` / v6.12.0 in `composer.lock` |

### 🟠 High

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| S-03 | **`{{ dump() }}` on admin pages** | `page.html.twig` | **Fixed** — removed |
| S-04 | **Broken item delete deletes parent block** | `BlockItemDeleteForm` | **Fixed** — `administrator_block_item_delete()` |
| S-05 | **Route parameter / param converter mismatch** | `cassiopeia_admin.routing.yml` | **Fixed** — paths, converters, parameter names |
| S-06 | **No ownership check on block/item IDs** | Param converter, repository, forms | **Fixed** — `bid` validated on routes, save, delete, position update |
| S-07 | **Entity load in Twig without access checks** | `CassiopeiaTwigExtension::cassiopeia_*_load()` | **Fixed** — module guard + `access('view')` on loaded entities |
| S-08 | **Public `/test` route** | `cassiopeia.routing.yml` | **Fixed** — requires `administer site configuration` |

### 🟡 Medium

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| S-09 | **Icon preview JS concatenates user input into DOM** | `js/icon-preview.js` | **Fixed** — class names sanitized client-side |
| S-10 | **`#markup` with unescaped DB values** | Block list forms | **Fixed** — `AdministratorFieldHelper::escapeText()` / `iconMarkup()` |
| S-11 | **Legacy permission referenced but undefined** | `cassiopeia_admin.permissions.yml`, `hook_page_bottom` | **Fixed** — permission defined; content manager machine name aligned with yml |
| S-12 | **Silent exception swallowing** | `AdministratorMenuRepository` | **Fixed** — errors logged to `cassiopeia_admin` channel |
| S-13 | **`CassiopeiaRenderTemplate` marks arbitrary Twig output safe** | `Markup::create($rendered)` | **Fixed** — path validated via `ExtensionPathResolver` + `realpath` before load |
| S-14 | **Contrib surface area** | `metatag` (+many submodules), `imce`, `smtp` | Larger attack surface; `imce` requires strict roles and private file scheme |

### 🔵 Low

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| S-15 | **No `hook_requirements` / security advisories workflow documented** | Project root | **Improved** — run `composer audit` after deploys (see TECHNICAL_DOCUMENTATION §13.4) |
| S-16 | **Sites settings gitignored** (correct) but no `.env.example` at root | `.gitignore` | **Fixed** — `.env.example` at repo root |
| S-17 | **t4t_admin.admin.inc** contains D7 `db_query` patterns | Dead file in repo | **Fixed** — removed `t4t_admin.admin.inc`, `t4t_admin.theme.inc` |

### Recommendations (security)

1. ~~Remove `dump()`~~ — done.
2. ~~Finish S-01~~ — done (`AdministratorFieldHelper`, escaped list markup, icon preview JS).
3. ~~Fix item delete~~ — done.
4. ~~Param converters and routes~~ — done.
5. ~~Upgrade PHPMailer~~ — done (v6.12.0).
6. ~~Add access checks in Twig helpers~~ — done.
7. ~~Restrict `/test` in production~~ — done.

---

## 2. Performance issues

### 🟠 High

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| P-01 | **Admin sidebar `max-age: 0`** | `lazyBuilder()` | **Fixed** — tag `cassiopeia_admin_menu:list`, max-age 3600, invalidation on CRUD |
| P-02 | **`dump()` in page template** | `page.html.twig` | **Fixed** |
| P-03 | **Heavy JS/CSS stack on all admin pages** | AdminLTE libraries | **Improved** — split libraries, defer JS, critical CSS, icons subset; aggregation still manual |

### 🟡 Medium

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| P-04 | **Full JOIN query for menu** | Repository (legacy SQL) | **Fixed** — config entities; no SQL JOIN for menu data |
| P-05 | **Entity::load() in Twig** | `cassiopeia_user_load`, etc. in `page.html.twig` / header | Extra queries per page; risk of duplicate loads if called multiple times |
| P-06 | **~2100 files under `libraries/`** | `bootstrap-icons` repo | **Improved** — full icon CSS disabled on Cassiopeia themes; admin uses subset CSS |
| P-07 | **Duplicate Bootstrap library definitions** | Both Cassiopeia themes redefine `bootstrap` library | Maintenance drift; potential double-load if libraries merged incorrectly |
| P-08 | **No cache metadata on custom tables** | Custom DB tables | **Fixed** — cache tags + invalidation pipeline |
| P-09 | **`.htaccess` ExpiresDefault 1 year** | Root `.htaccess` | Fine for static assets; ensure aggregated CSS/JS get cache-busting query strings (Drupal default) |

### 🔵 Low

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| P-10 | **Empty preprocess hooks** | `cassiopeia_theme.theme`, `cassiopeia_admin_theme.theme` | **Fixed** — stubs removed; only hooks with logic remain |
| P-11 | **Lazy builder placeholder still invokes callback per request** | Expected Drupal behavior | **Mitigated** — render cache hit skips rebuild; placeholder for BigPipe |
| P-12 | **Image style `style_200x200` in templates** | Admin header/sidebar | Image derivative generation on first hit; ensure style exists to avoid failures |

### Recommendations (performance)

1. ~~Menu cache tags + max-age~~ — done.
2. ~~Remove `dump()`~~ — done.
3. Enable CSS/JS aggregation in production (site config).
4. ~~AdminLTE split + defer; icons subset~~ — done.
5. ~~Preprocess avatars via image builder~~ — done; avoid Twig `user_load` where possible.

---

## 3. Maintainability issues

### 🔴 Critical

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| M-01 | **Missing `_cassiopeia_render_template_()`** | `TestController` | **Fixed** |
| M-02 | **Wrong namespace on `CassiopeiaRenderTemplate`** | Service registration | **Fixed** |

### 🟠 High

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| M-03 | **No automated tests** | Custom modules | **Partially fixed** — 6 kernel tests in `cassiopeia_admin/tests/src/Kernel/` (incl. `ConfigStorageTest`) |
| M-04 | **Procedural DB API in `.module`** | `administrator_block_*` | **Fixed** — `AdministratorMenuRepository` + wrappers |
| M-05 | **Incorrect `FormStateInterface` usage** | Administrator forms | **Fixed** — `AdministratorFormTrait` uses `administrator_block` / `administrator_block_item` keys |
| M-06 | **~400 lines commented D7 code** | `cassiopeia_admin.module` | **Fixed** — removed |
| M-07 | **Legacy files not removed** | `t4t_admin.admin.inc`, `t4t_admin.theme.inc` | **Fixed** — removed |
| M-08 | **No `hook_update_N()`** | `cassiopeia_admin` | **Fixed** — `hook_update_11001` (`url_meta`, index on `bid`) |
| M-09 | **Inconsistent redirect targets** | `setRedirect('admin/cassiopeia/...')` vs route names | Updates after add may fail silently |

### 🟡 Medium

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| M-10 | **TODO stubs in every form/controller** | `TestForm`, admin forms | **Fixed** — TestForm cleaned; admin forms complete |
| M-11 | **Custom modules outside `modules/custom/`** | `modules/cassiopeia*` | Diverges from Composer template; onboarding friction |
| M-12 | **No `composer.json` entries for custom packages** | Root composer | Custom code not versioned as Composer packages |
| M-13 | **Duplicate route controllers** | `CassiopeiaAdminAdministratorBlocksController` empty extend | Dead indirection |
| M-14 | **`cassiopeia` `test` table unused** | `hook_schema` | **Fixed** — empty schema; `cassiopeia_update_11001` drops table |
| M-15 | **`admin_theme_path` state unused** | `cassiopeia_admin_install()` | **Fixed** — `cassiopeia_admin_sync_admin_theme_paths()` + `update_11002` |
| M-16 | **Mixed Vietnamese/English UI strings** | Forms, messages | i18n inconsistency |
| M-17 | **No README for Cassiopeia stack** | `docs/` | **Improved** — TECHNICAL_DOCUMENTATION, AUDIT_REPORT, PERFORMANCE_AUDIT |

### 🔵 Low

| ID | Issue | Location | Impact |
|----|--------|----------|--------|
| M-18 | **Sparse theme templates** | Empty header/footer on public theme | Placeholder structure only |
| M-19 | **Version/datestamp in `.info.yml` only** | Packaging metadata | No CHANGELOG |

---

## 4. Drupal best practice violations

### 🔴 Critical

| ID | Violation | Current state | Drupal standard |
|----|-----------|---------------|-----------------|
| D-01 | **Missing param converters** | Routes + services | **Fixed** |
| D-02 | **Broken service class namespace** | `CassiopeiaRenderTemplate` | **Fixed** |
| D-13 | **No cache tags on custom data writes** | Block/item save | **Fixed** — repository + entity/module hooks + event subscriber |
| D-22 | **No Plugin API / menu alter** | Monolithic builder | **Fixed** — `hook_cassiopeia_admin_menu_blocks_alter` + legacy `hook_cassiopeia_admin_menu_alter` |

### 🟠 High

| ID | Violation | Current state | Drupal standard |
|----|-----------|---------------|-----------------|
| D-03 | **`\Drupal::` static calls everywhere** | Forms, services, Twig extension | Inject dependencies via constructor |
| D-04 | **Procedural persistence layer** | DB in `.module` | **Improved** — repository service; procedural wrappers remain for BC |
| D-05 | **Raw SQL for config-like data** | Menu blocks | **Fixed** — `administrator_block` / `administrator_block_item` config entities |
| D-06 | **HTML in menu link `#title` with `html => TRUE`** | `CassiopeiaAdminAdministrator` | **Fixed** — `#type` => `link` render arrays; escaped labels |
| D-07 | **`#markup` for user-provided strings** | Admin list forms | **Fixed** — `AdministratorFieldHelper` |
| D-08 | **`setRedirect()` with path not route** | Block forms | **Fixed** — route names |
| D-09 | **Twig extension loads optional modules unconditionally** | `CassiopeiaTwigExtension` | **Fixed** — `moduleExists()` guards + entity `access('view')` |
| D-10 | **No `cassiopeia_admin` dependency on `cassiopeia`** | `cassiopeia_admin.info.yml` | **Fixed** — `cassiopeia:cassiopeia` declared |

### 🟡 Medium

| ID | Violation | Current state | Drupal standard |
|----|-----------|---------------|-----------------|
| D-11 | **`hook_theme()` with legacy `file` key** | `cassiopeia_admin.theme.inc` | Prefer class-based `#[Hook]` or template in `templates/` with auto-discovery |
| D-12 | **Forms not using `ConfigFormBase` / entity forms** | All `FormBase` | Pattern mismatch for CRUD on structured data |
| D-13 | **No cache tags on custom data writes** | `administrator_block_save()` | **Fixed** — see critical section above |
| D-14 | **Permission machine names with spaces** | `cassiopeia admin block manager` | Valid but discouraged; prefer `administer cassiopeia blocks` |
| D-15 | **`RouteSubscriber` registered but empty** | `cassiopeia` | **Fixed** — service and class removed |
| D-16 | **Theme depends on module** | `dependencies: cassiopeia:cassiopeia` in theme | Modules should not be required by themes for business logic; use optional integration in preprocess |
| D-17 | **Controller returns `#markup` from render helper** | `TestController` | **Improved** — service works; prefer `#theme` embed for production |
| D-18 | **Global CSS classes in `hook_page_bottom`** | Inline styles in `#markup` | **Fixed** — `floating_admin_link` library + render array |
| D-19 | **Using `\stdClass` for records** | Block/item objects | Value object or typed DTO class |
| D-20 | **Array syntax `array()`** | Throughout custom code | `[]` per Drupal coding standards |

### 🔵 Low

| ID | Violation | Current state | Drupal standard |
|----|-----------|---------------|-----------------|
| D-21 | **No `declare(strict_types=1)`** | All custom PHP | Recommended for new PHP files |
| D-22 | **No Plugin API for extensibility** | Monolithic menu builder | **Fixed** — alter hooks documented in `cassiopeia_admin.api.php` |
| D-23 | **Optional config in theme not exported** | `config/optional/*.yml` | Good pattern; ensure install profile or docs mention enabling theme |

---

## 5. Contrib and platform notes

| Area | Observation |
|------|-------------|
| **Drupal core** | Standard 11.x layout; `.htaccess` protections present |
| **admin_theme** | Paths synced from state when `admin_theme.settings:paths` is empty (`cassiopeia_admin_sync_admin_theme_paths`) |
| **admin_toolbar** | Normal; no custom conflicts found |
| **bootstrap (theme)** | Large; pulls `bootstrap-icons` library — affects all Bootstrap subthemes |
| **metatag** | Many submodules increase maintenance; disable unused submodules |
| **imce** | Review upload permissions and allowed extensions in production |
| **smtp + PHPMailer** | Upgrade PHPMailer; store SMTP credentials in environment, not code |
| **Sites directory** | No committed `settings.php` (correct per `.gitignore`) |

---

## 6. Prioritized remediation roadmap

### Phase 1 — Immediate (production safety) — complete

1. ~~Remove `dump()`~~ ✅
2. ~~Escape/sanitize menu `name` and `icon` at form/input layer~~ ✅
3. ~~Fix block item delete~~ ✅
4. ~~Routing + param converters~~ ✅
5. ~~Upgrade PHPMailer~~ ✅ (v6.12.0)

### Phase 2 — Stability — complete

1. ~~Fix `CassiopeiaRenderTemplate` / `_cassiopeia_render_template_()`~~ ✅
2. ~~Replace `FormState` `#block` hacks~~ ✅ — `AdministratorFormTrait` + `administrator_block` keys
3. ~~Cache tags + menu max-age~~ ✅
4. ~~Remove dead D7 files / commented code~~ ✅
5. ~~Kernel tests for menu~~ ✅ (6 tests)

### Phase 3 — Architecture — largely complete

1. ~~Migrate administrator blocks to config entities~~ ✅ (`update_11003` / `11004` / `11005`)
2. ~~Repository service~~ ✅
3. ~~Split AdminLTE / icons subset~~ ✅
4. Document `admin_theme` path configuration — see TECHNICAL_DOCUMENTATION §13.2
5. Runbook in `docs/` — this file + TECHNICAL_DOCUMENTATION + PERFORMANCE_AUDIT + MODULES_TO_ENABLE

---

## 7. Issue index by file

| `modules/cassiopeia/src/Service/CassiopeiaRenderTemplate.php` | M-02 ✅, D-02 ✅, S-13 ✅ |
| `modules/cassiopeia/src/Controller/TestController.php` | M-01 ✅, S-08 ✅ |
| `modules/cassiopeia/cassiopeia.install` | M-14 ✅ |
| `modules/cassiopeia_admin/src/Repository/AdministratorMenuRepository.php` | M-04 ✅, P-04 ✅, P-08 ✅, D-13 ✅ |
| `modules/cassiopeia_admin/src/Service/CassiopeiaAdminAdministrator.php` | S-01 ✅, P-01 ✅, D-06 ✅ |
| `modules/cassiopeia_admin/cassiopeia_admin.module` | D-18 ✅, M-06 ✅ |
| `modules/cassiopeia_admin/templates/cassiopeia-admin-administrator-menu.html.twig` | S-01 ✅ |
| `composer.json` / `composer.lock` | S-02 ✅ |
| `.env.example` | S-16 ✅ |

---

*Audit based on static analysis of repository contents. Updated after performance sprints 1–5. Runtime configuration (enabled modules, PHP version, WAF) may surface additional findings.*

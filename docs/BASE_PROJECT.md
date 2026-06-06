# Base Project — Drupal 11 Reusable Starter

**Technical Lead assessment.** Giữ nguyên cấu trúc folder hiện tại (`modules/cassiopeia*`, `themes/cassiopeia_*`). Per-project code thêm vào `modules/{client}_*` khi fork.

---

## 1. Cấu trúc source tối ưu (giữ folder hiện tại)

```
Drupal11Base/
├── composer.json
├── README.md
├── .env.example
├── modules/
│   ├── cassiopeia/              ← REUSABLE (Composer package)
│   ├── cassiopeia_admin/        ← REUSABLE (Composer package)
│   ├── contrib/                 ← Phase 2: composer require only
│   └── {client}_*/              ← PER-PROJECT (business logic)
├── themes/
│   ├── cassiopeia_theme/        ← REUSABLE shell (rebrand per project)
│   ├── cassiopeia_admin_theme/  ← REUSABLE admin shell
│   └── bootstrap/               ← Contrib base theme
├── libraries/                   bootstrap, OverlayScrollbars, icons subset
├── scripts/
├── docs/
├── config/sync/                 Phase 3: export config
└── sites/default/example.settings.local.php
```

**Quy tắc tách logic**

| Generic (base repo) | Business (per-project module) |
|---------------------|-------------------------------|
| Twig helpers, image builder, Bootstrap library | Content types, migrations |
| Config-entity menu blocks/items + CRUD | `hook_cassiopeia_admin_menu_blocks_alter` |
| Theme layout shells | Client branding, custom blocks |

---

## 2. Module & theme — giữ cấu trúc folder hiện tại

### Reusable — giữ trong base

| Path | Machine name | Vai trò | → Composer package |
|------|--------------|---------|-------------------|
| `modules/cassiopeia/` | `cassiopeia` | Twig extension, `CassiopeiaImageBuilder`, `CassiopeiaRenderTemplate`, library `cassiopeia/bootstrap` | `your-org/cassiopeia` |
| `modules/cassiopeia_admin/` | `cassiopeia_admin` | Config entities `administrator_block` / `administrator_block_item`, sidebar menu, CRUD, cache | `your-org/cassiopeia-admin` |
| `themes/cassiopeia_theme/` | `cassiopeia_theme` | Public Bootstrap subtheme | `your-org/cassiopeia-theme` (optional) |
| `themes/cassiopeia_admin_theme/` | `cassiopeia_admin_theme` | Admin AdminLTE subtheme | optional |

### Contrib — giữ `modules/contrib/` (Phase 2: Composer)

`admin_theme`, `admin_toolbar`, `admin_toolbar_tools`, `token`, `pathauto`, `metatag`, `color_field`, `imce`, `smtp`, `views_entity_form_field`

### Base theme contrib

| Path | Ghi chú |
|------|---------|
| `themes/bootstrap/` | Bootstrap Barrio — Phase 2: `composer require drupal/bootstrap` → `themes/contrib/bootstrap/` |

### Per-project — không ship trong base

Tạo `modules/acme_content/`, `modules/acme_api/` khi fork cho client.

---

## 3. Danh sách file cần XÓA

### Đã xóa (refactor base)

| File | Lý do |
|------|--------|
| `modules/cassiopeia/src/Controller/TestController.php` | Demo |
| `modules/cassiopeia/src/Form/TestForm.php` | Demo |
| `modules/cassiopeia/cassiopeia.routing.yml` | Route `/test` |
| `modules/cassiopeia/templates/test.html.twig` | Demo |
| `modules/cassiopeia/templates/test_form.html.twig` | Demo |
| Permission `c3s quantri access theme` | Legacy dự án cũ |
| Route `cassiopeia_admin.administrator` (trùng `/blocks`) | Cấu hình dư |
| `color.preview` trong theme libraries | File không tồn tại |
| ~200 dòng comment AdminLTE demo trong admin `page.html.twig` | Comment thừa |

### Phase 2 — đề xuất xóa khỏi git

| Target | Lý do |
|--------|--------|
| `modules/contrib/*` (committed) | Cài lại qua Composer |
| `libraries/bootstrap-icons/` (full pack) | Chỉ dùng subset CSS |
| Metatag submodules không dùng | Giảm surface |

---

## 4. Danh sách file cần SỬA

### Đã sửa

| File | Thay đổi |
|------|----------|
| `modules/cassiopeia/cassiopeia.module` | Bỏ demo hooks, wrapper deprecated |
| `modules/cassiopeia/templates/render-fixture.html.twig` | Fixture kernel test only |
| `modules/cassiopeia_admin/cassiopeia_admin.permissions.yml` | English; bỏ legacy permission |
| `modules/cassiopeia_admin/cassiopeia_admin.module` | Floating link generic |
| `modules/cassiopeia_admin/cassiopeia_admin.install` | Paths `admin/*`, `user/*` |
| `modules/cassiopeia_admin/cassiopeia_admin.routing.yml` | Một route canonical |
| `themes/cassiopeia_theme/*` | Bỏ `test data`; thêm `css/app.css` |
| `themes/cassiopeia_admin_theme/templates/page.html.twig` | Trim ~40 dòng |
| `themes/cassiopeia_admin_theme/cassiopeia_admin_theme.libraries.yml` | Fix duplicate keys |
| `scripts/cassiopeia-post-enable.php` | Generic paths |
| `sites/default/example.settings.local.php` | Placeholder trung tính |

### Phase 2–3 — chưa sửa

| File | Action |
|------|--------|
| `composer.json` | `composer require drupal/*` |
| `docs/TECHNICAL_DOCUMENTATION.md` | Sync routes, permissions, bỏ SQL diagram |
| `docs/MODULES_TO_ENABLE.md` | Bỏ Quản trị, `/manager` |
| `cassiopeia_admin.info.yml` | Tách hard deps vs `suggest` |
| `src/**/*.php` | DI thay `\Drupal::` |
| Permissions machine names | `administer cassiopeia blocks` (+ update hook) |

---

## 5. Refactor đề xuất

1. **Composer-first contrib** — không commit `modules/contrib/`.
2. **Business qua alter hooks** — client module implement `hook_cassiopeia_admin_menu_blocks_alter`, không sửa `cassiopeia_admin`.
3. **Naming (Phase 2)** — permission không dấu cách; label English + `.po` cho locale.
4. **Install profile** — `profiles/d11base/` + `config/sync/` cho deploy một lệnh.
5. **Composer metapackage** — `your-org/d11base-metapackage` require toàn bộ stack.

---

## 6. Reusable module vs Composer package

| Thành phần | Reusable module | Composer package |
|------------|-----------------|------------------|
| `modules/cassiopeia` | ✅ | `your-org/cassiopeia` |
| `modules/cassiopeia_admin` | ✅ | `your-org/cassiopeia-admin` |
| `themes/cassiopeia_theme` | ✅ (fork theme) | optional |
| `themes/cassiopeia_admin_theme` | ✅ | optional |
| `modules/{client}_*` | ❌ per-project | `{client}/drupal-custom-module` |
| Contrib trong `modules/contrib/` | ❌ | `drupal/admin_toolbar`, … |

---

## 7. Technical debt

| ID | Mục | Mức |
|----|-----|-----|
| TD-01 | Contrib committed, chưa Composer | Cao |
| TD-02 | Thiếu `ctools` cho pathauto | Cao |
| TD-03 | `\Drupal::` static calls | Trung bình |
| TD-04 | Permission machine name có khoảng trắng | Thấp |
| TD-05 | `FormBase` thay entity forms | Chấp nhận |
| TD-06 | `\stdClass` trong repository | Thấp |
| TD-07 | Chưa có install profile | Trung bình |
| TD-08 | Full bootstrap-icons trong repo | Trung bình |

---

## 8. Source code sau refactor

### `cassiopeia.module`

```php
<?php

/**
 * @file
 * Primary module hooks for Cassiopeia base utilities.
 */
```

### `cassiopeia.libraries.yml`

```yaml
bootstrap:
  version: VERSION
  js:
    /libraries/bootstrap/dist/js/bootstrap.bundle.min.js:
      weight: -20
      attributes:
        defer: true
  css:
    component:
      /libraries/bootstrap/dist/css/bootstrap.min.css: { weight: -50 }
  dependencies:
    - core/jquery
```

### `cassiopeia_admin_page_bottom()`

```php
function cassiopeia_admin_page_bottom(array &$page_bottom) {
  $user = \Drupal::currentUser();
  $admin_context = \Drupal::service('router.admin_context');
  if ($admin_context->isAdminRoute() || !$user->hasPermission('cassiopeia admin content manager')) {
    return;
  }
  $page_bottom['cassiopeia_admin_menu'] = [
    '#type' => 'container',
    '#attributes' => ['class' => ['cassiopeia-admin-floating-link']],
    'link' => Link::fromTextAndUrl(t('Administration'), Url::fromUri('internal:/admin'))->toRenderable(),
    '#attached' => ['library' => ['cassiopeia_admin/floating_admin_link']],
  ];
}
```

### Admin route (canonical)

```yaml
cassiopeia_admin.administrator_block:
  path: '/admin/cassiopeia/administrator/blocks'
  defaults:
    _controller: '\Drupal\cassiopeia_admin\Controller\CassiopeiaAdminAdministratorController::content'
    _title: 'Administrator menu blocks'
  requirements:
    _permission: 'cassiopeia admin block manager'
```

### Admin `page.html.twig`

Xem `themes/cassiopeia_admin_theme/templates/page.html.twig` (~40 dòng, không demo).

---

## 9. Checklist fork dự án mới

1. Clone base — **không đổi** path `modules/cassiopeia*`.
2. Thêm `modules/{client}_*` cho business logic.
3. Rebrand `themes/cassiopeia_theme` (logo, CSS).
4. Copy `example.settings.local.php` → `settings.local.php`.
5. Enable contrib cần thiết; tắt metatag submodules thừa.
6. Export `config/sync/` hoặc tạo install profile.

---

*Refactor base áp dụng trên codebase. Cấu trúc folder giữ nguyên theo yêu cầu.*

# OC Redirect 301

OpenCart 3.x module for managing 301 redirects via the admin panel.

Redirects are stored in a database table, cached for 24 hours, and applied before any route processing — so even deleted or renamed pages redirect instantly without touching `.htaccess`.

---

## Features

- Add, edit, delete redirects from the admin panel
- Redirects fire at startup (patched into `catalog/controller/startup/router.php` via OCMOD), before any route logic
- 24-hour cache — no DB query on every request
- Cache is invalidated automatically on module uninstall
- Pagination and column sorting in the redirect list
- Admin languages: English, Russian

## Requirements

- OpenCart 3.x
- PHP 7.x+
- MySQL 5.6+

## Installation

1. Download or clone the repository
2. Pack the `upload/` folder and `install.xml` into a `.ocmod.zip` archive
3. In OpenCart admin go to **Extensions → Installer**, upload the archive
4. Go to **Extensions → Modifications**, click **Refresh**
5. Go to **Extensions → Modules**, find **301 Redirects**, click **Install**

## Usage

After installation, go to **Extensions → Modules → 301 Redirects**.

| Field | Description |
|-------|-------------|
| From URL | Relative path to match, e.g. `/old-page` |
| To URL | Destination — relative (`/new-page`) or absolute (`https://example.com/page`) |
| Status | Enable or disable this redirect |
| Sort Order | Execution order (lower = first) |

The module checks the incoming request URI against all enabled `From URL` entries and issues an HTTP 301 to the matching `To URL`.

Redirects are cached for 24 hours. If you added or changed a redirect and it doesn't work immediately — go to **Dashboard** and click the blue **Refresh** button (top right) to clear the store cache.

## File structure

```
install.xml                                          ← OCMOD patch (hooks into router.php)
upload/
  admin/
    controller/extension/module/redirect301.php      ← admin CRUD controller
    model/extension/module/redirect301.php           ← DB operations
    view/template/extension/module/
      redirect301_list.twig                          ← redirect list page
      redirect301_form.twig                          ← add / edit form
    language/
      en-gb/extension/module/redirect301.php
      ru-ru/extension/module/redirect301.php
  catalog/
    controller/extension/module/redirect301.php      ← intercepts requests, issues 301
    model/extension/module/redirect301.php           ← loads redirects (with cache)
```

## Disclaimer

This module is provided as-is. I wrote it in about an hour with the help of [Claude Code](https://claude.ai/code) and shared it in case it's useful to someone. I'm not a professional developer, so I can't answer questions or provide support.

Use at your own risk.

## License

MIT

---
name: project-kavekalamd
description: "Core facts about the KávéKaland project — stack, deployment, access control"
metadata: 
  node_type: memory
  type: project
  originSessionId: fc5e81ec-9489-4723-8eed-d0a3197996b7
  modified: 2026-08-03T14:49:15.013Z
---

KávéKaland (CoffeeAdventure) is a static PHP/HTML Hungarian café website for a small home-based café selling handmade pastries and drinks.

**Stack:** PHP procedural, PHPMailer via Gmail SMTP (STARTTLS port 587), PHP sessions, no framework or test suite. PHP binary at `/c/JavaPrograms/php/php`.

**Deployment:** Push to `main` → GitHub Actions FTP deploy to InfinityFree htdocs/. Live URL: `https://coffeeadventure.rf.gd/index.php?key=<key>`. `smtp_config.php` is git-ignored and must be uploaded manually to hosting.

**Access control:** `?key=` query param, valid keys hardcoded: `['bf4Gde67f','ndskFCgfj23','mjlzbtk3Den','df56hdfFV3','nzkj57nf2h']`. Key must propagate to all pages except `unauthorized.php`.

**Verification:** `php -l` with the binary above. No automated test suite — functional testing is done manually by the user.

**Why:** Small family café project, simplicity and direct deployment are priorities.

**How to apply:** Always lint with `php -l` before committing. Never commit `smtp_config.php`. When touching access-controlled pages, verify `$allowed` array and key propagation are intact.

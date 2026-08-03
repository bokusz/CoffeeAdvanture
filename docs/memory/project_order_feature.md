---
name: project-order-feature
description: Current state of the sendOrder / successfulOrder feature as of 2026-08-03
metadata: 
  node_type: memory
  type: project
  originSessionId: fc5e81ec-9489-4723-8eed-d0a3197996b7
  modified: 2026-08-03T14:49:37.319Z
---

The order form feature is fully built and live. Key files: `sendOrder.php`, `order_menu.php`, `successfulOrder.php`.

**order_menu.php** contains shared helpers: `ca_menu()`, `ca_streets()`, `ca_order_id()`, `ca_collect_quantities()`, `ca_total_items()`, `ca_build_body()` (plain-text email/session body), `ca_success_message()`, `ca_build_html_body($body, $subject, $key)` (HTML email body mirroring successfulOrder.php style).

**sendOrder.php** — structured form with blocks: Sütemények (brownie/cookie/poffeteg 0–5), Italok (limonade 0–3, jegeskave/tejeskave 0–2), Szállítási adatok (Mikorra text field `yyyy.MM.dd HH:mm`, Utca dropdown, Házszám 1–100), opcionális Megrendelő adatai (email+phone), opcionális Megjegyzés. Sends HTML email (isHTML(true)) with plain-text AltBody. BCC to 4 addresses, CC to customer email only if valid.

**Mikorra validation** (in order): (1) format `Y.m.d H:i`, (2) ≥ now+30min, (3) date 2026-08-04–2026-08-07, (4) time 13:00–18:00. Invalid date/time shows inline `field-warning` under the field plus always-visible `field-hint`. Timezone: `date_default_timezone_set('Europe/Budapest')`.

**Email layout:** subject line as centered title header → thank-you block (`#154061` bg) → order details block (`#154061` bg, `#FFEDA8` heading) → centered "KávéKaland" button (pale-blue gradient) linking to `index.php?key=<key>`.

**successfulOrder.php** — reads `$_SESSION['ca_order']` (set by sendOrder), unsets after read, shows same thank-you + order details layout.

**index.php order section** — `.order-box` with a `.second-btn` CTA linking to `sendOrder.php?key=<key>`.

**Mobile:** `@media (max-width:520px)` stacks `.order-row` label+input vertically, input goes full-width.

**Why:** Built incrementally across multiple sessions; current state reflects all refinements through 2026-08-03.

**How to apply:** When modifying the order flow, read `order_menu.php` first — shared logic lives there. HTML email styling uses inline styles (email client compatibility). The `$key` value must flow through to `ca_build_html_body()` for the footer button.

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

KávéKaland (CoffeeAdventure) is a static PHP/HTML website for a small home-based café selling handmade pastries and drinks. It is written in Hungarian.

## Deployment

Pushes to `main` automatically deploy via FTP to InfinityFree hosting (htdocs/) using GitHub Actions. Secrets `FTP_SERVER`, `FTP_USERNAME`, and `FTP_PASSWORD` must be set in the repo.

Live URL example: `https://coffeeadventure.rf.gd/index.php?key=aaBB555`

## Access Control

[index.php](index.php) requires a `?key=` query parameter. Valid keys are hardcoded at the top of the file (`aaBB555`, `CCdd22`). Any request without a valid key redirects to [unauthorized.php](unauthorized.php).

## Architecture

- [index.php](index.php) — single-page site with all sections: hero, products, team, order info, contact, reviews
- [script.js](script.js) — product card click → modal popup using `data-product` attributes; also handles page-load animation via `document.body.classList.add("loaded")`
- [style.css](style.css) — all styling
- [unauthorized.php](unauthorized.php) — standalone denial page with inline styles
- [images/](images/) — all product and team photos

## Modal System

Product cards in HTML use `data-product="lemonade|brownie|cookie|poffeteg|coffee"`. Clicking a card looks up the key in the `products` object in [script.js](script.js) and populates a modal (`#productModal`, `#modalImage`, `#modalTitle`, `#modalDescription`). The modal element and `.close` button must exist in the HTML for the JS to work. Note: some image paths in the `products` JS object differ from actual filenames in [images/](images/) (e.g. `cookie.jpg` vs `cookiesprob.png`).

---
name: feedback-workflow
description: How the user expects work to be done and delivered
metadata: 
  node_type: memory
  type: feedback
  originSessionId: fc5e81ec-9489-4723-8eed-d0a3197996b7
  modified: 2026-08-03T14:48:52.761Z
---

Implement → verify with `php -l` → commit → push to `main` without asking for confirmation first. The user has consistently approved this pattern across every round of changes.

**Why:** Pushes to `main` auto-deploy via GitHub Actions FTP to InfinityFree hosting. The user treats each completed task as ready to ship immediately.

**How to apply:** After implementing and linting, commit and push in the same turn. Only pause for confirmation if there is genuine ambiguity in the request — not for routine deploy steps.

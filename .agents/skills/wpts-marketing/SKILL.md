---
name: wpts-marketing
description: Plan and produce WP Trip Summary marketing materials for users and developers, including product videos, generated README content, WordPress.org banners and icons, screenshots, and travel-photo campaigns. Use for WPTS positioning, launch kits, or maintaining these materials; ordinary plugin implementation uses the coding skill.
---

# WP Trip Summary marketing

Make the practical value of a real trip visible, then give each audience a useful next step. This skill owns product positioning, evidence and consistency across materials. Specialized media skills own rendering and image production.

## Start with the requested deliverable

Work from the plugin root. Read its `AGENTS.md`, inspect Git status and preserve existing changes. Read the relevant sources before editing; a strategy request produces strategy, not an automatic release campaign.

Use the reference matching the task; do not load all production workflows for a narrow edit:

| Task | Reference |
| --- | --- |
| Positioning, audiences, priorities, distribution or campaign planning | [Strategy](references/strategy.md), including the dated baseline |
| GitHub or WordPress.org copy | [README workflow](references/readme.md) |
| Banners, icons, product screenshots and channel graphics | [Visual assets](references/visual-assets.md) |
| Selecting the author's travel photographs and a demonstration trip | [Photo brief](references/photo-brief.md) |
| Product film, `/brag` variation or developer demonstration | [Video workflow](references/video.md) |

For a new campaign, record audience, target release/commit, locale, channel, one main promise, supporting product evidence, CTA destination and deliverables. Infer what the request and existing project already establish; ask only about missing inputs that affect the result.

## Canonical language and localized alternatives

English is canonical for this skill, its references, campaign briefs and default marketing materials. Keep canonical reference filenames unchanged and write new shared guidance in English. Romanian versions are optional localized alternatives, particularly for the author's personal audience; an explicit request for a Romanian deliverable does not change the canonical language of the shared guidance.

The [Romanian strategy](references/strategy.ro.md) and [Romanian photo brief](references/photo-brief.ro.md) retain the original local-language guidance. Use the `.ro.md` suffix for Romanian reference alternatives, label them as translations and link back to their English originals. Read them when that locale is useful; they do not replace the canonical references in the task table above.

Make substantive guidance changes in English first, then synchronize any affected translations or mark them as awaiting synchronization. English takes precedence if versions diverge. Keep localized campaign exports identifiable by locale and preserve existing historical Romanian materials as source assets.

## Ground the message

The shared idea is **the travel story with the route and practical details in the same WordPress post**. Users need a convincing result and an understandable first-use flow. Developers need a concrete integration, its source and its limits. Favor one promise per asset; use separate follow-up materials when the audiences need different detail.

Check the relevant implementation, documentation and target release. For a multi-asset campaign, keep a small evidence table in its working brief:

| Claim | Source / version | Evidence state | Allowed wording |
| --- | --- | --- | --- |
| The feature being shown | File, capture or tested example | released / checkout-only / roadmap / unverified | Wording appropriate to that state |

A local implementation is not proof that the distributed package includes it. Distinguish source inspection from running a demo. Never reuse roadmap items, old screenshot captions, reconstructed UI or historical validation as proof of current shipped behavior. If a material targets the next release, label it accordingly.

In particular:

- Self-hosted track files do not imply offline maps or no external services. Describe the selected tile provider accurately.
- Optional JSON-LD is not a promise of search ranking or rich results.
- Existing hooks and a REST field are not a blanket promise of a stable, complete read/write API.
- Check plugin metadata, runtime checks and `composer.lock` together before making compatibility claims. Report discrepancies without changing the supported baseline as a marketing edit.
- Preserve product identifiers, hook names and existing branding. Use **WP Trip Summary** as the public product name; retain exact repository names and slugs in URLs.

## Produce within the existing project

README outputs come from `readme/`; directory images live in `assets/`; `brag-output/` contains an existing experiment. Follow the relevant reference instead of creating a competing build path. Put a new campaign's brief, asset register and exports together in a clearly named working directory; keep original private photos and large renders out of Git unless the user requests otherwise. Existing `.gitignore` rules do not cover every new location.

Use `wpts-coding-conventions` if the task actually changes code or executable examples. Use `wpts-document-hooks` for a requested hook inventory/documentation task; merely linking existing hook documentation does not require an audit. Read `hyperframes` before video work and `brag:brag` when `/brag` is requested. Use `imagegen` for requested raster generation or editing; preserve code/vector assets in their native format. Discover those skills in the current environment rather than hardcoding their installation paths. If required media tooling is unavailable, complete the brief/copy and report the production limitation without claiming an export exists.

Local preparation does not authorize posting, messaging, committing to SVN or publishing a release. Honor publication authorization already given; when publication needs approval, present the completed artifacts and exact destinations first.

## Finish with evidence

Check the selected workflow's actual outputs: rendered README, exact image dimensions, legibility, consistent route data, source attribution, working destinations and, for video, the final media. Report files produced, checks run and unresolved publication blockers. Keep partial work explicitly marked as such. For strategy-only work, validate the skill and references; media renders and WordPress tests are not required.

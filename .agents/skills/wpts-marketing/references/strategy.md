# WP Trip Summary marketing strategy

Canonical English version. The [Romanian alternative](strategy.ro.md) is available for local use; English takes precedence if the versions differ. New shared documents and marketing materials start in English, with optional localized versions for the intended audience.

Initial proposal, 2026-10-09. This is a production planning document: messaging, priorities and suggested formats can be adjusted after initial use. It does not describe an already published campaign.

## Positioning

**The travel story, route and practical details in the same WordPress post.**

WP Trip Summary helps authors turn a travel account into a practical resource: readers can see the route, distance, climb and relevant trip information. Start with a real journey and support it with a clear product demonstration.

Proposed primary message for the international audience: **“Your trip. Your story. Your WordPress site.”** Supporting line: **“Add route maps, elevation profiles and practical trip details to your travel posts.”** The [Romanian alternative](strategy.ro.md) retains the message used in the existing video experiment. These are editorial proposals, not audience-tested results.

The initial priority is cycling and hiking bloggers who already use WordPress and have GPS files. Train journeys offer a useful visual and thematic distinction, best shown through a dedicated example. Clubs and guides are a secondary audience for route publishing; materials should not imply booking or field-navigation capabilities.

## Two audience paths

| Audience | Their question | Appropriate evidence | Next step |
| --- | --- | --- | --- |
| Blog author | “How can I make my post more useful without rebuilding everything?” | A real post with summary, map and profile, followed by the data-entry workflow | View the example → install → complete a first trip |
| WordPress developer / integrator | “Can I adapt this to my site, and do I understand its limits?” | A small customization example, the exact hook and visible result, with relevant documentation | View the example → run the integration → explore hooks / contribute |

The WordPress.org page should emphasize usage. GitHub should explain the product quickly and provide a visible entry for developers. The main film shows the reader's result and the author's workflow; the technical demonstration has its own material and CTA. One short film does not need to explain both paths.

## Verified starting point

The following observations come from the initial inventory on 2026-10-09; they are not a substitute for checking the selected release before production.

| Material / source | What exists | Production implication |
| --- | --- | --- |
| [README sources](../../../../readme/Makefile) and [manifest](../../../../readme/manifest.json) | Shared fragments, GitHub and WordPress.org targets, named screenshots | Edit the sources and use the existing generator |
| `README.md` and `README.txt` | `php bin/tools/build-readme.php --check` reported both as outdated | Review sources and preview before final regeneration |
| README fragments | Warnings in `hero`, `screenshots`, `whats-new`, `requirements`; “See it live” CTA with `href="#"` | Supply a real demo destination or remove the CTA; resolve incomplete claims |
| Version | Local header `0.3.3`; the generator found no changelog for this stable tag | Establish the promoted version and use verified changes; do not invent a changelog |
| Compatibility | PHP 8.0.0 declared; locked Monolog requires at least 8.1 | Resolve the discrepancy through the release workflow before public compatibility claims |
| [Video experiment](../../../../brag-output/brag-plan.md) | 22 s, Romanian, 1920×1080; animated UI reconstruction and an existing map image | Reuse the direction and project, but capture the current product for a new demonstration |
| [Video credits](../../../../brag-output/credits.md) | Fictional summary values labeled as examples; music, fonts and map credited | The example does not establish statistics for the pictured route; also verify usage rights before distribution |
| `brag-output/` | Some briefs reference `assets/ro_RO/viewer-map-alt-profile.png`, absent during inventory; an image exists in the composition | Resolve the exact provenance before reuse; do not assume all historical paths are valid |
| `assets/` | JPG banners, PNG icons, `en_US` screenshots; the inspected banner uses a stylized bicycle photograph | Keep the logo, start from the video's navy/mint identity and add product context to the banner |
| [Examples](../../../../examples) | Enabling the plugin for a custom post type and changing lookup labels | Concrete starting points for developer content; demonstrate them on the chosen version |

The public WordPress.org page could not be read through the web tool during the initial inventory. The distributed version and live presentation remain to be checked when preparing the campaign. The local inventory does not establish either.

## What we can demonstrate and what needs qualification

| Message idea | Verification source | Communication limit |
| --- | --- | --- |
| Specific information for cycling, hiking and train journeys | `readme/sections/features.md`, the chosen version's editor and viewer | Do not present new trip types as available features |
| GPX, KML and GeoJSON import; map and elevation profile | Parsers in `lib/route/track/documentParser/`, a real demo capture | Do not imply every format/file contains elevation or that imports take a guaranteed time |
| GPS files stored on the site's server | Upload/storage workflow and map configuration | Maps use tile sources; do not promise offline operation |
| Customization through hooks | `examples/e01-enable-custom-post-types/plugin.php`, `examples/e02-customize-lookup-type-labels/plugin.php`, `hook-docs/` | Use actual names and signatures; do not describe a complete public API as finished |
| REST field `wpts_trip_summary` | `lib/pluginModules/RestApiEnhancementsPluginModule.php` | The inspected source registers reading without an update callback; listing data is disabled by default and controlled by a filter |
| Future features | `readme/sections/roadmap.md` and the chosen release | Separate roadmap items from demonstrated features; do not promise delivery dates |

## First set of materials

Build one demonstration trip with matching photographs, GPS file and data. Derive the following materials from it:

| Priority | Material | Content and purpose | Completion criteria |
| --- | --- | --- | --- |
| P0 | Canonical example | A complete real-trip article with desktop/mobile captures | Identified version; consistent map, profile and values; verified public destination when publishing |
| P0 | GitHub + WordPress.org README | Benefit, visual result, first steps and limits; developer entry on GitHub | Sources and outputs synchronized; no false demo links or unsupported promises |
| P0 | WordPress.org kit | Two banners, two icons, revised leading screenshots and captions | Conforming files and verified correspondence with the SVN export |
| P1 | Main film | Proposed: 35–45 s, English, 16:9; trip → editing → result → CTA | Readable UI, understandable muted, verified export and credits |
| P1 | Short `/brag` variant | 15–25 s; the 22-second experiment can be a starting point | One promise and one CTA; optional Romanian version for the author's personal audience |
| P1 | Developer package | A hook example with a visible effect, a small integration diagram, a 45–60 s demonstration or short article | Example runs on the selected target; code can be copied from a linked page |
| P1 | Case study and sharing images | One trip's story, what readers see and what authors enter; a map + photo card | Original copy and images from the same case; destination is the demo or installation page |
| P2 | Channel variants | Vertical/square versions, video thumbnail, release card, railway example | Produced for a chosen channel with copy and framing adapted to it |

A new website is not required for the first round. The plugin page, GitHub and a demonstration article provide the initial destinations. A dedicated page becomes useful if explaining the product or measuring the path toward installation calls for one.

## Visual direction

Keep the existing logo and use the video project's navy, mint and fonts as a starting point, after checking legibility and licensing. Personal photographs add identity and context. Real captures demonstrate functionality. Diagrams and code examples support integration.

A banner can combine a restrained panoramic photograph, the plugin name and a short line about routes in WordPress. The icon should remain recognizable at small sizes. Product captures need enough space for readers to understand the map and interface; decoration should not obscure them.

Use the same trip, name and statistics across materials that claim to demonstrate it. Photographs from other trips may provide atmosphere but do not prove anything about the displayed route. The [photo brief](photo-brief.md) explains what to look for; the [visual specifications](visual-assets.md) separate WordPress requirements from suggested sizes for other channels.

## Distribution and measurement

Start with destinations we manage where people can see the product: the GitHub README, WordPress.org page and author's blog. Then propose sharing the case study with relevant blogging, cycling/hiking and WordPress communities where project presentations are permitted. Developer material links directly to the technical example. Preparing copy does not include posting or contacting people.

| Objective | Available signal | Interpretation |
| --- | --- | --- |
| Users understand the product | 3–5 people can explain after the demo what it does and whom it helps | Qualitative feedback, not statistical evidence of conversion |
| Interest in using it increases | Clicks to the demo / WordPress.org from channels where measurement exists | Compare similar periods and sources; a click is not an installation |
| First use succeeds | A few users complete a post with a route; record where they get stuck | Measure through voluntary sessions or feedback without adding plugin telemetry |
| Integration is understandable | A developer can run the example and explain the hook used | Track concrete questions and difficulties |
| Materials help maintenance | Types of support questions before/after the update | Volume and context matter; do not automatically attribute changes to marketing |

At launch, record the available values and their sources; check again roughly 2 and 6 weeks after publication. Public download and active-install statistics are indicative, not exact campaign attribution. GitHub stars can show interest but do not replace product usage. Do not schedule reports automatically or promise numerical targets without a baseline.

## Recommended order of work

1. Choose a version and representative trip; collect 8–12 candidate photographs and GPS data if available.
2. Prepare the demonstration article and verify its data; produce a consistent set of captures.
3. Review README sources and the WordPress.org kit; check destinations and previews.
4. Produce the main film and short version; use the same trip for the case study.
5. Demonstrate a hook customization and prepare the developer entry.
6. Publish to authorized destinations, measure the available signals and adjust the next set of materials.

Each release that changes the UI, demonstrated features or compatibility triggers a review of affected materials. Record the version, sources and capture date for each export so the next update can build on the existing work.

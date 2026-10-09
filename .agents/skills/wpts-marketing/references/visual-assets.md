# Visual assets

## Formal WordPress.org exports

Baseline verified 2026-10-09 against [How Your Plugin Assets Work](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/). Recheck at export time.

| Asset | Exact filename / dimensions | Limit |
| --- | --- | --- |
| Standard banner | `banner-772x250.jpg` or `.png`, 772×250 px | 4 MB |
| Retina banner | `banner-1544x500.jpg` or `.png`, 1544×500 px | 4 MB; accompanies the standard banner |
| Standard icon | `icon-128x128.png`, 128×128 px | 1 MB |
| Retina icon | `icon-256x256.png`, 256×256 px | 1 MB |
| Optional vector icon | `icon.svg` | Include a PNG fallback |
| Product screenshots | `screenshot-1.png` / `.jpg`, consecutively numbered | 10 MB each; the handbook specifies no fixed pixel dimensions |

Use lowercase filenames. Gallery numbering corresponds to readme captions. The published location is SVN's top-level `assets/`, beside `trunk` and `tags`. Localized filenames are supported; consult the handbook when producing them. Updates may take time to appear through the image cache.

## Repository mapping

The repository already uses JPG banners and PNG icons in `assets/`, with descriptive screenshot names in `assets/en_US/`. `bin/export-wp-plugin-dir-svn.sh` explicitly maps these into directory filenames. Preserve that mapping, or change and review it together with a requested asset update. Do not create `.wordpress-org/` as a competing source folder.

Keep export-ready assets in the established paths only after reviewing the replacements. Keep working masters, channel variants and intermediate renders in the campaign directory. Preserve existing `logo.png`, `logo-large.png` and icon designs unless a redesign was requested; inspect which consumers use them before replacing shared files.

## Composition and capture

- Banner: a short product promise, product name and restrained travel imagery. Test at the standard size; a tiny full-screen UI collage will not communicate the benefit. Keep essential content clear of edge crops or directory overlays, verified in the actual preview.
- Icon: use the existing recognizable mark. Test at small display sizes and on light/dark surrounding surfaces. Do not put a feature list or photo collage inside it.
- Screenshot sequence: lead with the published result, then map/profile, then the authoring flow. Add logs/settings only when they explain useful behavior. If changing the current sequence, reconcile the recipe and export script together.
- Capture the chosen release in a development/demo installation. Read the capture tool's help/source before running `bin/tools/screen-capture/run.py`; inspect local changes and output destinations. Do not print authentication configuration into task output.
- Use one named trip and matching data for a demonstrated flow. Capture desktop and a narrow viewport where responsive behavior matters. Record version, locale, viewport, date and route identifier with the source image.
- Keep map attribution visible and UI text legible. Remove private dashboard details from approved derivatives. Describe a reconstruction as such; do not use it as a current product screenshot.

## Additional exports: recommendations, not directory requirements

| Use | Suggested starting canvas | Content |
| --- | --- | --- |
| Social/link preview | 1200×630 px | Trip photo + readable product result + short headline |
| Square announcement | 1080×1080 px | One benefit and one product crop |
| Vertical short / cover | 1080×1920 px | Recompose for the channel; allow for its UI overlays |
| Video thumbnail | 1280×720 px | Recognizable product evidence and a few readable words |
| README hero | Responsive image, e.g. 1600 px wide master | Real article context, summary and map |
| Developer explainer | SVG or Mermaid when appropriate | Verified flow from WordPress hook to visible outcome |

Choose channels before making every variant; confirm their current requirements. Extend existing vector/code graphics with native tools. For raster generation/editing, follow the `imagegen` skill. AI visuals can provide decoration or concept exploration; product UI, real routes and claims need grounded sources.

## Asset register and quality check

For each used source/export, record: ID, path, role, author/source, permitted use or license, required credit, locale, release/commit where relevant, related trip, crop/focal point, dimensions and export destination. A short table in the campaign brief is enough. Retain originals and trace derivatives back to them; author-owned travel originals do not automatically become repository-licensed assets.

Check actual decoded dimensions, format and byte size, not just filenames. Inspect every export at its intended display size for cropping, contrast, spelling, matching statistics and retained attribution. Check the final screenshot numbering against captions and export mapping. Report source masters and exported files separately. Do not count an unrendered design or a correctly named file as a validated deliverable.

# README production

## Sources and outputs

Read `CONTRIBUTING.md` under “Updating the readme files” before changing the recipes. The existing pipeline is:

`readme/manifest.json` + `readme/Makefile` + `readme/sections/*.md` + `CHANGELOG.md` → `README.md` and `README.txt`.

Edit fragments and recipes, not generated outputs. `readme/docs/` holds longer linked documentation; `readme/archive/` is historical. `readme/staging/` contains prior previews, not authoritative sources. `README.txt` also supplies the runtime About changelog and is copied as lowercase `readme.txt` by packaging/export tooling.

## Editorial structure

For GitHub, make the first screen explain the result and show real product evidence. Give readers a primary install/demo destination and a visible developer entry. Continue with short getting-started instructions, supported use cases, customization examples, limitations and links to deeper documentation. Keep parser details out of the opening pitch.

For WordPress.org, emphasize what the plugin does, the author workflow and the result for readers. Keep practical FAQ answers about file formats, tile providers, placement, downloads and compatibility. Use benefit-oriented screenshot captions: explain what the reader learns from the screen, not just its menu name.

The proposed developer entry should link to `examples/`, `hook-docs/` and `CONTRIBUTING.md`. Prefer one working customization over a long list of extensibility claims. Distinguish existing hook/REST capabilities from roadmap API work. Do not expand a README task into implementation or a hook inventory refresh.

## WordPress.org rules

Recheck the [official readme documentation](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/) when publishing. Baseline checked 2026-10-09: short description at most 150 characters; 1–5 relevant tags; supported video URLs on their own line. The stable tag determines which readme is displayed. Required PHP/WordPress versions come from the main plugin header. Keep the readme compact; the handbook warns about files over 10 KB. Never update `Tested up to` without matching test evidence.

## Build and review

From the plugin root, with PHP 8.2 or newer:

```text
php bin/tools/build-readme.php --check
php bin/tools/build-readme.php --output-dir=readme/staging
```

Inspect existing staging files before overwriting them; use a distinct task directory if they contain work to preserve. For a preview-only request, stop after reviewing the staged outputs. For an authorized README update, resolve the source issues and generate the final outputs:

```text
php bin/tools/build-readme.php
php bin/tools/build-readme.php --check
git diff --check
```

Review both rendered targets, links and anchors, screenshot captions and order, short-description length, and warnings. A clean generation exit does not mean warnings are resolved. The initial strategy records pre-existing outdated outputs and unresolved source notes; report those separately from anything introduced by this task.

`screenshot[file]` order in `readme/Makefile` must match the explicit screenshot copies in `bin/export-wp-plugin-dir-svn.sh`. Reordering only the captions mislabels the public gallery. Inspect both sides and keep them synchronized when changing screenshots; do not run the release/export script just to validate copy. It clears its local export trunk and performs other release work.

Do not repair release metadata, a missing changelog or compatibility conflicts by guessing. Use verified release information or report the remaining blocker. Placeholder links must be replaced with verified destinations or omitted before publication. For text-only changes, content/diff checks are appropriate; changing the generator itself requires its documented standalone regression tests.

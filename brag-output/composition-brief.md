# Composition handoff

Canonical language: English. Product: WP Trip Summary. Campaign: **Every trip has a story. Give it a route.** See [plan.md](plan.md) for the creative plan and scene timings.

The 24-second film uses an actual travel video, an actual travel photograph, and supplied screenshots of the editor and reader view. The editor pointer is an authored animation over a screenshot, not a screen recording. The sample track is not asserted to match the photographed or filmed trip. Natural media has no color treatment; the layout crops it to the frame. Original files remain untouched.

| Claim | Evidence | State / permitted wording |
| --- | --- | --- |
| Upload GPX, KML and GeoJSON tracks | `readme/sections/features.md`; local editor screenshot | Present in inspected 0.3.3 checkout. “Add your GPS track.” No speed claim. |
| Reader map and elevation profile | `assets/en_US/wpts-sample-post-viewer-map-alt-profile.png` | Supplied product capture. “Route map” and “Elevation profile.” |
| Bike, hiking and train trip information | `readme/sections/features.md` | Present in checkout. No claim that all fields come from a GPS file. |
| Enable custom post types through a filter | `examples/e01-enable-custom-post-types/plugin.php`, especially `e01_init` and its callback | Source inspected, not executed in this task. The card points to the full example. |

Target checkout: commit `513be0b0d2ff766dfbfe486b5889e52b1ab13c6f`, plugin metadata version 0.3.3. This campaign does not independently certify what is currently distributed on WordPress.org. Captures came from the user's `assets/en_US` collection; capture timestamp, viewport and exact deployed build were not independently recorded. No new capture or live integration test was performed.

## Editing and rebuilding

From the plugin root:

```powershell
.\brag-output-2026-10-21-182034\run-hyperframes.ps1 preview --background --port 3017
.\brag-output-2026-10-21-182034\run-hyperframes.ps1 check
.\brag-output-2026-10-21-182034\run-hyperframes.ps1 render --quality delivery --fps 30 --output ../tooling/raw-render.mp4
```

The helper uses HyperFrames 0.8.183, the campaign's isolated FFmpeg 6.0 binary and the existing FFprobe package from the older campaign. Install the campaign tooling with `npm ci --prefix brag-output-2026-10-21-182034/tooling` if missing. `tooling/prepare_campaign.py` rebuilds frozen sources and the original score using NumPy; source photographs and video remain in `assets/marketing`. `tooling/render_static.mjs` renders the native HTML/CSS artwork with Playwright; `CODEX_NODE_MODULES` and `CAMPAIGN_BROWSER` can override its local runtime paths. `tooling/finish_campaign.py` selects the poster, embeds frame zero, checks streams and packages the final exports.

The registered GSAP timeline owns all animated properties. HyperFrames owns media playback and clip visibility. The graph crop removes the sample title and footer while preserving tabs, route, chart axes and map attribution. The final still has a restrained 3.5% camera push. The three audio clips have explicit IDs and timings; the score's fade is baked into its source.

English video text is recorded in [on-screen-copy.txt](on-screen-copy.txt). There is no narration requiring speech subtitles. Optional Romanian copy is in [share-copy-variants.md](share-copy-variants.md).

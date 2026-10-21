# Campaign validation — 21 October 2026

## Rendered deliverables

- `brag.mp4`: H.264 / YUV 4:2:0, 1920×1080, 30 fps, exactly 720 frames / 24.000 seconds. AAC stereo, 48 kHz, 24.000 seconds. 26,515,424 bytes.
- `brag.jpg`: 1920×1080, selected from the settled hook at 3.2 seconds. Baked into frame zero of the delivery MP4; frame count and duration preserved, audio copied without re-encoding.
- Six static exports: decoded dimensions and formats verified against `marketing-assets/manifest.json`. Banners are 772×250 and 1544×500, 95,514 and 329,071 bytes, both below the 4 MB limit. The standard-size banner and all card designs were visually inspected. Map attribution and graph axes are retained.

## Checks actually run

- HyperFrames 0.8.183 `check --json`: passed; the browser ran. Zero lint, runtime, layout, motion or contrast errors. All 25 sampled text contrast checks passed. Motion assertions ran across 300 samples. Full report: `tooling/check-final.json`.
- Six non-blocking structural lint warnings reviewed: the repeated existing logo, four top-level scene wrappers, and four scenes sharing one track. These are intentional in this small composition. No runtime or layout warnings remained after the crop intent and actual sound-effect durations were recorded.
- Registry headline primitive inspected; pointer `keyframes --shot` strip inspected. Local Studio preview returned HTTP 200 and remains available on port 3017.
- Actual final MP4 decoded from beginning to end with FFmpeg: no errors. Stream properties, duration and frame count checked with FFprobe. Final-export contact sheet covers the first frame, opening animation, both sides of every scene boundary, editor click, reader proof and final hold. Full-size product and poster frames were inspected. No missing media, cut-off map credits or black final tail found.
- Final audio analyzed: integrated loudness **−19.88 LUFS**, true peak **−8.17 dBTP**, loudness range **3.4 LU**. Stereo audio is present, decodes and has headroom. This is signal validation, not a subjective listening review.
- Static artwork rendered in a local browser with no script errors; image loads and text horizontal overflow checked. Final exported pixels inspected after correcting the graph crops. Exact sizes, formats, hashes and source provenance are in `asset-register.json`.
- Gallery playback completed in Edge: all 720 video frames, zero dropped frames, no media error. Desktop and 390-pixel mobile layouts loaded with no failed requests, script errors or horizontal overflow. Browser playback QA ran muted; audio stream checks are described above. Report: `tooling/gallery-check.json`.
- WordPress.org CTA and the public E01 custom-post-type example both returned HTTP 200. WordPress asset dimensions/limits were rechecked against the [Plugin Handbook](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/). Kenney's two source pages confirmed CC0.
- No PHP/runtime change, release packaging or database-backed test was needed. Git status contains only this new campaign directory; pre-existing tracked files were not edited. Final whitespace and local reference checks are included in the completion review.

## Production notes

The first render attempt stopped because FFprobe was not on PATH. After selecting the existing FFprobe, the second stopped because the old system FFmpeg lacked the `setts` bitstream filter. The completed render uses an isolated FFmpeg 6.0 binary under this campaign's ignored tooling directory. The source footage was re-encoded with one-second keyframes for reliable preview seeking. Plugin runtime dependencies and system PATH were not changed.

The supplied UI captures have a dimmed appearance; their pixels were preserved. Screenshot metadata identifies the source files but does not independently certify the capture build or viewport. The marketing claims were checked against local version 0.3.3 source, not against an installed WordPress.org package. The editor action is explicitly labeled as an animated screenshot sequence.

These are prepared local materials. The WordPress banners remain staged in this campaign, the README hero/copy are ready for the existing generator, and no external publication or replacement of shared branding was performed. A directory-page check after eventual upload will confirm the final WordPress page overlays and caching behavior.

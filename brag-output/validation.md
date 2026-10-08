# Validation

- Hyperframes 0.8.141 `check`: passed; browser ran. Zero runtime, layout, motion or contrast errors. Zero layout/contrast warnings after the upload-to-map transition adjustment.
- Eight non-blocking structural lint warnings remain: intentional reuse of the logo/map assets and the choice to keep this short film in one composition with five scene clips. Each scene was inspected visually; assets load and appear in their intended time windows.
- Motion assertions: passed, including upload result, profile reveal and final title. A focused keyframe diagnostic verified the map entrance; first frame, file-selection states, map result, all scene midpoints and the last held frame were inspected.
- Render: local browser screenshots with hardware GPU; 660 frames at 30 fps. Hyperframes render completed successfully in 44.9 seconds.
- Final file: H.264, 1920 x 1080, 22.000 seconds; stereo AAC, 22.000 seconds. Final size: 4,505,667 bytes.
- Full FFmpeg decode: completed without errors. Audio is non-silent; measured peak -4.4 dBFS, no clipping reported.
- Poster: chosen from the fully settled hook at 2.3 seconds, inspected, then baked into frame 0. The final file retains all 660 frames and the same 22-second duration. Audio packet MD5 is identical before and after poster baking.
- Local Studio preview returned HTTP 200: `http://localhost:3002/#project/composition`.
- `git diff --check`: passed. Task artifacts are contained in `brag-output/`; the pre-existing untracked root `AGENTS.md` was preserved. No plugin runtime files changed, so PHP/WordPress tests were not applicable.

The film is an animated reconstruction using repository UI source and map imagery, not a live installation recording. Example summary data is explicitly labeled. The initial raw render is retained under ignored `tooling/render-without-poster.mp4`; the deliverable is `brag.mp4`.

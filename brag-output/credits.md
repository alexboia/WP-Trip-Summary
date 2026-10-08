# Sources and credits

- Product identity, UI structure, logo and Romanian map/profile screenshot: WP Trip Summary repository. The film reconstructs the author/reader flow; it is not a recording of a running WordPress installation.
- Map: OpenStreetMap contributors, attribution retained within the source map crop.
- Example summary values (42.8 km, 860 m, medium difficulty, summer/autumn): fictional demonstration data, labeled on screen; not measurements of the displayed repository route.
- Music: “Happy Beats / Business Moves Vol. 12” by ende.app, supplied with the installed `/brag` skill. No narration.
- Sound effects: Kenney, CC0, supplied with `/brag`; `click_003.ogg` and `impactSoft_medium_001.ogg`.
- Fonts: Space Grotesk 700 and IBM Plex Sans 400, distributed through Fontsource. Latin and Latin Extended subsets are local.
- Animation: GSAP 3.14.2; clip-wipe text reveal adapted from the Hyperframes registry's `caption-clip-wipe` component.
- Local composition/check/render: Hyperframes 0.8.141.

# Reopening the composition

From the plugin directory, with the generated tooling present:

```powershell
.\brag-output\run-hyperframes.ps1 preview --background
.\brag-output\run-hyperframes.ps1 check
.\brag-output\run-hyperframes.ps1 render --quality delivery --output ../brag.mp4
```

The helper adds the existing FFmpeg directory and the isolated FFprobe package to its process PATH. Nothing changes the system PATH or the WordPress runtime. The required fonts, images, music and animation runtime are included under `composition/assets/`.

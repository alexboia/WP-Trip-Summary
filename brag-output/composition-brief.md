# Hyperframes Composition Brief: WP Trip Summary

## Objective and output
Create a local 22-second Romanian product film, 1920 x 1080 at 30 fps. Deliver `brag.mp4`, its chosen `brag.jpg` poster, `share-copy.txt`, and the editable `composition/` project in this directory. Follow `brag-plan.md` for the five-scene story.

## Source and claims
Use the plugin README, actual editor/viewer templates and modern frontend stylesheet. Feature claims are limited to existing trip information, GPS file imports, map and altitude profile. Rebuild the upload and summary UI from source at video scale. The map/profile screenshot is real repository material. Route statistics are illustrative and must be labeled as an example. No private data or site configuration is required.

## Creative direction
Polished travel-journal tone. Navy `#0A3868`, mint `#8CF5D9`, teal `#1FA4B4`, white text, pale UI surface `#FCFDFE`, ink `#12303F`. Use the product's Space Grotesk and IBM Plex Sans locally if obtainable. Keep large editorial copy left and substantial product evidence right. Make the upload-to-map transition the centerpiece. Avoid generic dashboards, unrelated stock imagery, fake testimonials and performance metrics.

Required editorial copy: “Ai povestea. Pune și traseul.”, “Din fișier, pe hartă.”, “Fiecare urcare. La vedere.”, “Tura, pe scurt.”, “WP Trip Summary”, “Povești cu traseu.”.

## Story timing
0–3.27 hook; 3.27–8.74 file selection and map result; 8.74–13.64 map/profile; 13.64–18.56 structured information; 18.56–22 signature and public WordPress URL. Use fast entrances with long settled holds. Preserve attribution inside the map. No voice track.

## Audio
Use bundled `happy-beats-business-moves-vol-12-by-ende-dot-app.mp3`. Read its supplied cue preset. Major reveals at 8.74 and 18.56 seconds; small interaction at about 4.9 seconds. Fade the bed to silence at 22 seconds. Read `/brag/assets/sfx/sfx-analysis.md`; choose low-risk click and warm impact sounds to match the final movement. All assets must be local to `composition/assets/`.
Use the Hyperframes creative audio extraction helper for a subtle route/frame response; if unavailable, record the limitation and preserve natural motion.

## Technical contract and delivery
Read `hyperframes-core`, `hyperframes-animation`, `hyperframes-creative`, `hyperframes-keyframes`, `hyperframes-cli`. Use one seekable paused timeline, explicit clips and local media. Run `hyperframes check` and resolve errors, inspect key-frame images and verify the finished media's duration and streams. Rendering stays local. The user invoked `/brag` to create the finished video; this authorizes the normal local render and deliverables. Do not publish or send to any external audience.

Select the strongest settled product frame, export `brag.jpg`, and bake its pixels into frame 0 of the MP4 with audio and timing preserved. Keep the original `AGENTS.md` and all plugin runtime source files unchanged.

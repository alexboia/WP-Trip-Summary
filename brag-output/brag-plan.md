# Brag Plan: WP Trip Summary

## Planning rubric
1. **Product:** a WordPress plugin that adds structured trip information, GPS maps, altitude profiles and optional public route logs to travel posts.
2. **Strongest claim:** the reader can see both the travel story and its practical route information in the same post. GPX, GeoJSON and KML imports are supported.
3. **Visual hook:** a real Romanian route map and altitude profile paired with the current navy, teal and mint identity.
4. **Actual UI:** recreate the upload control and modern trip-summary shell from `views/wpts-editor.php`, `views/wpts-frontend.php` and `media/css/abp01-frontend-main-modern.css`; use the checked-in map/profile capture as product evidence.
5. **Shortest satisfying film:** 22 seconds, 1920 x 1080, 30 fps.
6. **Tone:** polished, with the warmth of a personal travel journal. Confident and specific, in Romanian.
7. **Audio:** a steady music bed, a selection click and two restrained reveal accents. No narration was requested.
8. **Share caption:** Am făcut WP Trip Summary pentru povești care merită și o hartă: trasee GPS, profil de altitudine și detaliile turei, direct în WordPress.
9. **User flow:** open trip editor → choose GPS file → see track and altitude profile → read the trip summary within the post.

## The angle
**Ai povestea. Pune și traseul.** The map is part of the story. Show a concrete author action followed by the information a reader receives. Avoid performance claims, download counts or invented endorsements.

## Visual identity
- Background: `#0A3868`, from the current viewer title bar.
- Text: `#FFFFFF`; secondary copy uses a readable pale mint.
- Accent: `#8CF5D9`; teal `#1FA4B4` for non-text accents.
- UI surface: `#FCFDFE`; UI ink: `#12303F`.
- Display: Space Grotesk; body: IBM Plex Sans, matching the source CSS.
- The current source style is used for rebuilt UI. Repository screenshots are older and are identified as source material, not claimed to be a fresh live recording.
- All route summary values are clearly labeled demo data. No private site URL, user identity, credentials or customer information is shown.

## Music cue guidance
Track: `happy-beats-business-moves-vol-12-by-ende-dot-app.mp3`, bundled by `/brag`; about 109.96 BPM. Use 0–22 seconds at a restrained level, with a final fade.
Read the supplied `.music-cues.md` and `.music-cues.json` preset. Major reveals target 8.74s and 18.56s. Small accents may use 3.27s and 6.00s. Reading time takes priority over the beat grid.
Audio-reactive treatment: subtle modulation of the route accent or the map frame, using pre-extracted audio data if the Hyperframes helper is available.
SFX: low-risk selection click and warm soft impacts from the bundled Kenney library; sparse and matched to the action.

## Storyboard

### 1. The invitation — 0.00–3.27 (3.27s)
Large copy: **Ai povestea. / Pune și traseul.** A map/profile visual is already present at the right; a mint route line draws towards it. Small product name anchors the frame.
Sequential action: two headline lines enter quickly, then hold together for over 2 seconds.
Audio: steady music, restrained opening accent. Transition: short directional handoff into the editor.

### 2. The author's action — 3.27–8.74 (5.47s)
Copy: **Din fișier, / pe hartă.** Recreated upload area, sourced from the actual editor template. The file picker is clicked; `traseu-demo.gpx` is selected. Then the existing repository map appears in the same panel.
Supporting label: **GPX · GeoJSON · KML**.
Sequential action: cursor moves to selection button, clicks once, selected filename appears; map resolves. This is a shortened simulated interaction, not a speed claim.
Audio: one soft click at selection; warm accent on result. Transition: preserve the map and bring its altitude profile into view.

### 3. The reader's map — 8.74–13.64 (4.90s)
Copy: **Fiecare urcare. / La vedere.** Show the map and altitude profile from `assets/ro_RO/viewer-map-alt-profile.png` at readable scale. Preserve OpenStreetMap attribution in the displayed map.
Sequential action: map panel settles; altitude profile is revealed below it. No invented GPS geometry or invented trace over a real map.
Audio: result reveal locks to the 8.74s strong cue; let the music carry the hold.

### 4. Practical details — 13.64–18.56 (4.92s)
Copy: **Tura, / pe scurt.** Recreate the current summary shell and real field labels: distance, total climb, difficulty and seasons. Values are illustrative and marked **Exemplu de tură**. Emphasize the information hierarchy rather than a large statistic.
Supporting line: **Bicicletă · Drumeție · Tren** — the three supported trip types from the implementation.
Sequential action: distance and climb rows arrive, then difficulty and season rows; the completed set holds for at least 2 seconds.
Audio: keep accents minimal; no ticking for every numeral.

### 5. Signature — 18.56–22.00 (3.44s)
Project logo and **WP Trip Summary**. Final line: **Povești cu traseu.** CTA: **wordpress.org/plugins/wp-trip-summary**.
Sequential action: logo and wordmark land together; CTA follows within 0.3s, then holds to the end.
Audio: logo accent on 18.56s; music fades during the last second. End on the finished composition, not black.

**Total:** 3.27 + 5.47 + 4.90 + 4.92 + 3.44 = 22.00 seconds.

## Source grounding
- Features and public destination: `README.md`.
- Upload formats, chooser and result state: `views/wpts-editor.php`.
- Viewer tabs, labels, trip types and altitude-profile setting: `views/wpts-frontend.php`.
- Brand tokens and fonts: `media/css/abp01-frontend-main-modern.css`.
- Map/profile: `assets/ro_RO/viewer-map-alt-profile.png`.
- Logo: `assets/logo.png`.
- Editorial lines are original copy; they add no unverified product capabilities.

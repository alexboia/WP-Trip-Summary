"""Freeze campaign sources and compose an original, sample-free instrumental bed."""
from pathlib import Path
import json, shutil, subprocess, sys, wave
import numpy as np

CAMPAIGN = Path(__file__).resolve().parents[1]
REPO = CAMPAIGN.parent
ASSETS = CAMPAIGN / 'composition/assets'
FFMPEG = CAMPAIGN / 'tooling/node_modules/ffmpeg-static/ffmpeg.exe'
BRAG = Path('C:/Users/alexandru.boia/.codex/plugins/cache/brag/brag/0.4.0/skills/brag/assets')
sources = {
    'images/road.jpg': 'assets/marketing/nature-stills/IMG_4434.jpg',
    'images/meadow.jpg': 'assets/marketing/nature-stills/IMG_4560.jpg',
    'images/bicycle.jpg': 'assets/marketing/nature-stills/IMG_3701.jpg',
    'images/editor-map.png': 'assets/en_US/wpts-admin-post-edit-map.png',
    'images/viewer-profile.png': 'assets/en_US/wpts-sample-post-viewer-map-alt-profile.png',
    'images/viewer-map.png': 'assets/en_US/wpts-sample-post-viewer-map.png',
    'images/logo.png': 'assets/logo.png',
}
# These are already frozen project assets; never copy them from a previous campaign.
bundled = ['fonts/space-grotesk-latin-700-normal.woff2',
           'fonts/ibm-plex-sans-latin-400-normal.woff2', 'gsap.min.js',
           'licenses/space-grotesk.txt', 'licenses/ibm-plex-sans.txt']
required = [REPO / source for source in sources.values()]
required += [ASSETS / name for name in bundled]
required += [FFMPEG, REPO / 'assets/marketing/nature-videos/hiking-high-plains.mp4',
             BRAG / 'sfx/interface/click_003.ogg',
             BRAG / 'sfx/impact/impactSoft_medium_001.ogg']
missing = [str(path) for path in required if not path.is_file()]
if missing:
    raise FileNotFoundError('Missing campaign sources: ' + ', '.join(missing))
if '--check-sources' in sys.argv:
    print(f'Validated {len(required)} campaign inputs; no files changed.')
    raise SystemExit(0)
for dest, source in sources.items():
    target = ASSETS / dest
    target.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(REPO / source, target)
(ASSETS / 'audio').mkdir(exist_ok=True)
for name, source in [('click.ogg', 'interface/click_003.ogg'), ('soft-impact.ogg', 'impact/impactSoft_medium_001.ogg')]:
    shutil.copy2(BRAG / 'sfx' / source, ASSETS / 'audio' / name)
(ASSETS / 'video').mkdir(exist_ok=True)
subprocess.run([str(FFMPEG), '-hide_banner', '-loglevel', 'error', '-y', '-i', str(REPO / 'assets/marketing/nature-videos/hiking-high-plains.mp4'), '-an', '-vf', 'tpad=stop_mode=clone:stop_duration=0.3,fps=30', '-t', '4.8', '-c:v', 'libx264', '-g', '30', '-keyint_min', '30', '-crf', '18', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', str(ASSETS / 'video/ridge.mp4')], check=True)

# Original score, 100 BPM / D major. No recordings, samples or external music.
sr, duration = 48000, 24
rng = np.random.default_rng(21620)
mix = np.zeros((sr * duration, 2), dtype=np.float64)
def put(signal, start, gain=1, pan=0):
    offset = int(start * sr)
    n = min(len(signal), len(mix) - offset)
    if n > 0:
        mix[offset:offset+n, 0] += signal[:n] * gain * np.sqrt((1-pan)/2)
        mix[offset:offset+n, 1] += signal[:n] * gain * np.sqrt((1+pan)/2)
def freq(midi):
    return 440 * 2 ** ((midi-69)/12)
def pluck(midi, length=1.45):
    t = np.arange(int(sr*length)) / sr
    f = freq(midi)
    signal = np.sin(2*np.pi*f*t)*np.exp(-t*3.5)
    signal += .22*np.sin(2*np.pi*f*2*t)*np.exp(-t*7)
    signal += .06*np.sin(2*np.pi*f*3*t)*np.exp(-t*12)
    return signal * (1-np.exp(-t*160))
chords = [(50, [62,66,69,73]), (47, [62,66,71,74]), (43, [62,67,71,74]), (45, [61,64,69,71]), (50, [62,66,69,73]), (47, [62,66,71,74]), (43, [62,67,71,74]), (45, [61,64,69,71]), (43, [62,67,71,74]), (50, [62,66,69,74])]
for bar, (bass, notes) in enumerate(chords):
    start = bar * 2.4
    t = np.arange(int(sr*3.3)) / sr
    pad = sum(np.sin(2*np.pi*freq(note)*t + .006*np.sin(2*np.pi*.8*t)) for note in notes) / 4
    pad *= (1-np.exp(-t*3))*np.exp(-t*.6)*np.minimum((3.3-t)/.6,1)
    put(pad, start, .07, -.15)
    for step in range(8):
        if bar == 9 and step > 2: continue
        put(pluck(notes[[0,2,1,3,2,1,0,2][step]]), start+step*.3, .14, -.32 if step%2==0 else .32)
    for beat in range(4):
        if bar == 9 and beat > 0: continue
        t = np.arange(int(sr*.55))/sr
        low = np.sin(2*np.pi*freq(bass-12)*t)*(1-np.exp(-t*100))*np.exp(-t*5)
        put(low, start+beat*.6, .16)
        kick = np.sin(2*np.pi*(46*t + 65*.025*(1-np.exp(-t/.025))))*np.exp(-t*18)
        put(kick, start+beat*.6, .12)
        if bar < 9:
            t = np.arange(int(sr*.07))/sr
            noise = rng.normal(0,1,len(t))
            hat = (noise-np.roll(noise,1))*np.exp(-t*75)*(1-np.exp(-t*2000))
            put(hat, start+beat*.6+.3, .007, .35)
            if beat in (1,3):
                t = np.arange(int(sr*.12))/sr
                brush = rng.normal(0,1,len(t))*np.exp(-t*32)
                brush = np.convolve(brush, np.ones(6)/6, mode='same')
                put(brush, start+beat*.6, .023, -.2)
# Small stereo ambience with no feedback accumulation.
dry = mix.copy()
for delay, gain in [(.225,.14),(.45,.08),(.675,.035)]:
    n = int(sr*delay)
    mix[n:] += dry[:-n, ::-1]*gain
t = np.arange(len(mix))/sr
fade = np.minimum(t/.12,1)*np.clip((duration-t)/1.8,0,1)
mix *= fade[:,None]
mix *= .72/max(np.max(np.abs(mix)),.001)
wav = CAMPAIGN / 'tooling/original-score.wav'
with wave.open(str(wav),'wb') as out:
    out.setnchannels(2); out.setsampwidth(2); out.setframerate(sr)
    out.writeframes((mix*32767).astype('<i2').tobytes())
subprocess.run([str(FFMPEG), '-hide_banner', '-loglevel', 'error', '-y', '-i', str(wav), '-af', 'loudnorm=I=-18:TP=-2:LRA=8', '-ar', '48000', '-codec:a', 'libmp3lame', '-b:a', '192k', str(ASSETS / 'audio/give-it-a-route.mp3')], check=True)
(CAMPAIGN/'tooling/frozen-sources.json').write_text(json.dumps(sources, indent=2)+'\n')
print('Prepared original sources, 4.8-second ridge footage and 24-second original score.')

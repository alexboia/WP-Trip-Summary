from pathlib import Path
from PIL import Image, ImageOps, ImageDraw, ImageFont
import json
import subprocess

ROOT = Path(__file__).resolve().parents[2]
OUT = Path(__file__).resolve().parent
FFMPEG = OUT / 'node_modules/ffmpeg-static/ffmpeg.exe'
FFPROBE = OUT / 'node_modules/@ffprobe-installer/win32-x64/ffprobe.exe'
FONT = ImageFont.truetype('C:/Windows/Fonts/arial.ttf', 18)

def sheet(items, path, columns=4, cell=(400, 280)):
    rows = (len(items) + columns - 1) // columns
    canvas = Image.new('RGB', (columns * cell[0], rows * cell[1]), '#e9eef0')
    draw = ImageDraw.Draw(canvas)
    for index, (source, label) in enumerate(items):
        im = ImageOps.exif_transpose(Image.open(source)).convert('RGB')
        im.thumbnail((cell[0] - 16, cell[1] - 43))
        x, y = (index % columns) * cell[0], (index // columns) * cell[1]
        canvas.paste(im, (x + (cell[0] - im.width) // 2, y + 5))
        draw.text((x + 8, y + cell[1] - 35), label, font=FONT, fill='#102e3b')
    canvas.save(path, quality=88)

photos = sorted((ROOT / 'assets/marketing/nature-stills').glob('*'))
sheet([(p, p.name) for p in photos], OUT / 'photos-contact.jpg')
screens = sorted((ROOT / 'assets/en_US').glob('*.png'))
sheet([(p, p.stem.removeprefix('wpts-')) for p in screens], OUT / 'screens-contact.jpg', cell=(480, 305))
metadata = {'photos': [], 'screenshots': [], 'videos': []}
for kind, paths in [('photos', photos), ('screenshots', screens)]:
    for p in paths:
        with Image.open(p) as im:
            metadata[kind].append({'file': str(p.relative_to(ROOT)), 'width': im.width, 'height': im.height, 'bytes': p.stat().st_size})
frames = []
for p in sorted((ROOT / 'assets/marketing/nature-videos').glob('*')):
    probe = json.loads(subprocess.check_output([str(FFPROBE), '-v', 'error', '-show_streams', '-show_format', '-of', 'json', str(p)]))
    stream = next(s for s in probe['streams'] if s['codec_type'] == 'video')
    duration = float(probe['format']['duration'])
    metadata['videos'].append({'file': str(p.relative_to(ROOT)), 'duration': duration, 'width': stream['width'], 'height': stream['height'], 'fps': stream['r_frame_rate'], 'codec': stream['codec_name'], 'bytes': p.stat().st_size})
    for label, t in [('early', min(1, duration / 8)), ('middle', duration / 2), ('late', duration * .9)]:
        dest = OUT / f'{p.stem}-{label}.jpg'
        subprocess.run([str(FFMPEG), '-hide_banner', '-loglevel', 'error', '-y', '-ss', str(t), '-i', str(p), '-frames:v', '1', '-vf', 'scale=640:-2', str(dest)], check=True)
        frames.append((dest, f'{p.stem} / {label}'))
sheet(frames, OUT / 'videos-contact.jpg', columns=3, cell=(500, 330))
(OUT / 'source-metadata.json').write_text(json.dumps(metadata, indent=2), encoding='utf-8')
print(json.dumps({'photos': len(photos), 'screenshots': len(screens), 'videos': metadata['videos']}, indent=2))

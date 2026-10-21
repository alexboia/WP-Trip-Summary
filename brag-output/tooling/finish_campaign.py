"""Finalize the rendered film, inspect real exports and produce an evidence register."""
from pathlib import Path
import json, subprocess, hashlib, zipfile
from PIL import Image, ImageDraw

C = Path(__file__).resolve().parents[1]
R = C.parent
FF = C / 'tooling/node_modules/ffmpeg-static/ffmpeg.exe'
FP = C / 'tooling/node_modules/@ffprobe-installer/win32-x64/ffprobe.exe'
RAW = C / 'tooling/raw-render.mp4'
def run(args):
    return subprocess.run([str(x) for x in args], check=True, capture_output=True, text=True)
def probe(path):
    return json.loads(run([FP,'-v','error','-show_streams','-show_format','-of','json',path]).stdout)
def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()
run([FF,'-hide_banner','-loglevel','error','-y','-ss','3.2','-i',RAW,'-frames:v','1','-q:v','2',C/'brag.jpg'])
run([FF,'-hide_banner','-loglevel','error','-y','-i',RAW,'-i',C/'brag.jpg','-filter_complex',"[0:v][1:v]overlay=0:0:enable='eq(n,0)'[v]",'-map','[v]','-map','0:a?','-c:v','libx264','-crf','18','-preset','slow','-pix_fmt','yuv420p','-c:a','copy','-movflags','+faststart',C/'brag.mp4'])
before, after = probe(RAW), probe(C/'brag.mp4')
bv = next(s for s in before['streams'] if s['codec_type']=='video')
av = next(s for s in after['streams'] if s['codec_type']=='video')
aa = next(s for s in after['streams'] if s['codec_type']=='audio')
assert (av['width'],av['height'],av['nb_frames'],av['r_frame_rate']) == (1920,1080,'720','30/1')
assert bv['nb_frames']==av['nb_frames'] and abs(float(av['duration'])-24)<.001
run([FF,'-hide_banner','-v','error','-i',C/'brag.mp4','-f','null','-'])
audio=run([FF,'-hide_banner','-i',C/'brag.mp4','-vn','-af','loudnorm=I=-18:TP=-1:LRA=8:print_format=json','-f','null','-']).stderr
(C/'tooling/audio-analysis.txt').write_text(audio,encoding='utf-8')
start=audio.rfind('{'); audio_stats=json.loads(audio[start:audio.find('}',start)+1])
assert float(audio_stats['input_tp']) < 0
assert float(audio_stats['input_i']) > -40
media={'video':{k:av[k] for k in ('codec_name','width','height','r_frame_rate','nb_frames','duration','pix_fmt')},'audio':{k:aa[k] for k in ('codec_name','sample_rate','channels','duration')},'audio_loudness':audio_stats,'bytes':(C/'brag.mp4').stat().st_size,'sha256':digest(C/'brag.mp4'),'decode':'passed','poster_time':3.2,'poster_baked_into_first_frame':True}
(C/'tooling/media-check.json').write_text(json.dumps(media,indent=2)+'\n')

frames=C/'tooling/frames';frames.mkdir(exist_ok=True)
times=[0,.033,1,3.2,4.76,4.8,5.5,7,9.6,10.76,10.8,11.6,14,17.96,18,18.8,21,23.95]
sheet=Image.new('RGB',(4*480,5*292),'#f3f3e9');draw=ImageDraw.Draw(sheet)
for i,t in enumerate(times):
    dest=frames/f'{t:07.3f}.jpg'
    run([FF,'-hide_banner','-loglevel','error','-y','-ss',str(t),'-i',C/'brag.mp4','-frames:v','1','-q:v','2',dest])
    with Image.open(dest) as im:
        im.thumbnail((480,270));x=(i%4)*480;y=(i//4)*292;sheet.paste(im,(x,y));draw.text((x+8,y+274),f'{t:.3f} seconds',fill='#0a3868')
sheet.save(C/'tooling/final-film-contact.jpg',quality=93)

exports=[]
for item in json.loads((C/'marketing-assets/manifest.json').read_text()):
    path=C/item['file']
    with Image.open(path) as im:
        assert im.size==(item['width'],item['height'])
        item['format']=im.format
    assert item['bytes']==path.stat().st_size
    if path.name.startswith('banner-'): assert path.stat().st_size<4*1024*1024
    item['sha256']=digest(path);exports.append(item)

sources=[]
for local,origin,role,rights,focal in [
 ('composition/assets/images/road.jpg','assets/marketing/nature-stills/IMG_4434.jpg','Banner and landscape social photo','Alexandru Boia; supplied for campaign use','Center-right winding road; CSS object-fit crop'),
 ('composition/assets/images/meadow.jpg','assets/marketing/nature-stills/IMG_4560.jpg','Closing still and square social photo','Alexandru Boia; supplied for campaign use','Meadow and path, 45% horizontal; final scale 1.035'),
 ('composition/assets/images/editor-map.png','assets/en_US/wpts-admin-post-edit-map.png','Editor demo','WP Trip Summary; map credits retained','Complete screenshot; animated pointer is an editorial overlay'),
 ('composition/assets/images/viewer-profile.png','assets/en_US/wpts-sample-post-viewer-map-alt-profile.png','Reader proof in video and static artwork','WP Trip Summary; Leaflet, Thunderforest, OpenStreetMap attribution retained','Crop sample title/footer; retain map, graph axes and provider credits'),
 ('composition/assets/images/logo.png','assets/logo.png','Existing product identity','WP Trip Summary','Unchanged, contain')]:
    file=C/local
    with Image.open(file) as im: dimensions=list(im.size)
    sources.append({'id':Path(local).stem,'path':local,'original':origin,'role':role,'author_or_rights':rights,'crop':focal,'dimensions':dimensions,'sha256':digest(file),'locale':'en_US' if 'png' in local else None,'trip':'Example product track, unrelated to travel imagery' if 'map.png' in local or 'profile.png' in local else 'Not identified'})
sources += [
 {'id':'ridge','path':'composition/assets/video/ridge.mp4','original':'assets/marketing/nature-videos/hiking-high-plains.mp4','role':'Opening footage','author_or_rights':'Alexandru Boia; supplied for campaign use','dimensions':[1920,1080],'duration':4.8,'crop':'Centered right-half viewport; no color treatment; 30fps derivative, short last-frame hold','sha256':digest(C/'composition/assets/video/ridge.mp4'),'trip':'Not identified'},
 {'id':'score','path':'composition/assets/audio/give-it-a-route.mp3','original':'tooling/prepare_campaign.py','role':'Original instrumental bed','author_or_rights':'Original synthesized score for this campaign; no third-party samples','duration':24,'sha256':digest(C/'composition/assets/audio/give-it-a-route.mp3')},
 {'id':'sfx','paths':['composition/assets/audio/click.ogg','composition/assets/audio/soft-impact.ogg'],'role':'Save and map accents','author_or_rights':'Kenney CC0, supplied by brag'},
 {'id':'fonts','paths':['composition/assets/fonts/space-grotesk-latin-700-normal.woff2','composition/assets/fonts/ibm-plex-sans-latin-400-normal.woff2'],'role':'Headings and body','author_or_rights':'SIL OFL; local license texts retained'}]
register={'campaign':'Every trip has a story. Give it a route.','canonical_locale':'en_US','date':'2026-10-21','target_commit':'513be0b0d2ff766dfbfe486b5889e52b1ab13c6f','target_metadata_version':'0.3.3','sources':sources,'exports':exports,'film':media}
(C/'asset-register.json').write_text(json.dumps(register,indent=2)+'\n',encoding='utf-8')
print(json.dumps(media,indent=2))

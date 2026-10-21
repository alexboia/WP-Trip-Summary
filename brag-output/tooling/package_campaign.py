"""Package delivery exports without raw personal media or build dependencies."""
from pathlib import Path
import json, zipfile, re
from html.parser import HTMLParser
from urllib.parse import urlsplit, unquote

C = Path(__file__).resolve().parents[1]
files = ['brag.mp4','brag.jpg','share-copy.txt','share-copy-variants.md','readme-copy.md','on-screen-copy.txt','credits.md','validation.md','asset-register.json','marketing-assets/manifest.json']
files += [x['file'] for x in json.loads((C/'marketing-assets/manifest.json').read_text())]
target = C / 'wpts-marketing-en-2026-10-21.zip'
with zipfile.ZipFile(target,'w',zipfile.ZIP_DEFLATED,compresslevel=6) as out:
    for file in files:
        out.write(C/file, file)
    out.write(C.parent/'LICENSE.md','product-source-license.md')
with zipfile.ZipFile(target) as package:
    assert package.testzip() is None
    assert len(package.namelist()) == len(files)+1

class Links(HTMLParser):
    def __init__(self): super().__init__(); self.paths=[]
    def handle_starttag(self,tag,attrs):
        self.paths += [v for k,v in attrs if k in ('src','href','poster') and v]

checked=[]
for file in [C/'index.html', C/'composition/index.html']:
    parser=Links();parser.feed(file.read_text(encoding='utf-8'))
    for link in parser.paths:
        url=urlsplit(link)
        if url.scheme or link.startswith('#'): continue
        dest=(file.parent/unquote(url.path)).resolve()
        assert dest.exists(), f'Missing reference: {file.name}: {link}'
        checked.append(link)
for file in C.glob('*.md'):
    for link in re.findall(r'\]\(([^)]+)\)',file.read_text(encoding='utf-8')):
        if urlsplit(link).scheme or link.startswith('#'): continue
        assert (file.parent/unquote(urlsplit(link).path)).exists(),f'Missing Markdown reference: {file.name}: {link}'
print(json.dumps({'archive':target.name,'bytes':target.stat().st_size,'files':len(files)+1,'archive_crc':'passed','local_references':len(checked),'reference_check':'passed'},indent=2))

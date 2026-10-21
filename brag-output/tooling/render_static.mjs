// Render original HTML/CSS artwork using frozen local photographs and screenshots.
import {createRequire} from 'node:module';
import {fileURLToPath, pathToFileURL} from 'node:url';
import path from 'node:path';
import fs from 'node:fs/promises';
const campaign=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const runtime=process.env.CODEX_NODE_MODULES || 'C:/Users/alexandru.boia/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules';
const require=createRequire(path.join(runtime,'package.json'));
const {chromium}=require('playwright');
const browser=await chromium.launch({executablePath:process.env.CAMPAIGN_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',headless:true});
const exports=[
 ['banner-772x250.jpg','banner',772,250,1],
 ['banner-1544x500.jpg','banner',1544,500,2],
 ['social-landscape-1200x630.jpg','landscape',1200,630,1],
 ['social-square-1080x1080.jpg','square',1080,1080,1],
 ['readme-hero-1600x900.jpg','readme',1600,900,1],
 ['developer-1200x630.png','developer',1200,630,1],
];
const manifest=[];
for(const [name,kind,width,height,scale] of exports){
 const page=await browser.newPage({viewport:{width,height},deviceScaleFactor:1});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto(pathToFileURL(path.join(campaign,'marketing-assets/source.html')).href+`?kind=${kind}&scale=${scale}`);
 await page.evaluate(async()=>{await document.fonts.ready;await Promise.all([...document.images].map(i=>i.decode()));});
 const clipped=await page.evaluate(()=>[...document.querySelectorAll('h1,p,pre,.brand,.code-note')].filter(e=>{const r=e.getBoundingClientRect();return e.scrollWidth>e.clientWidth+2||r.right>innerWidth+1||r.bottom>innerHeight+1;}).map(e=>({text:e.textContent,width:e.clientWidth,scrollWidth:e.scrollWidth})));
 if(errors.length||clipped.length)throw Error(JSON.stringify({name,errors,clipped}));
 const file=path.join(campaign,'marketing-assets',name);
 await page.screenshot({path:file,type:name.endsWith('.png')?'png':'jpeg',...(name.endsWith('.jpg')?{quality:94}:{})});
 manifest.push({file:`marketing-assets/${name}`,width,height,bytes:(await fs.stat(file)).size,locale:'en_US',source:'marketing-assets/source.html',variant:kind,scale});
 await page.close();
}
await fs.writeFile(path.join(campaign,'marketing-assets/manifest.json'),JSON.stringify(manifest,null,2)+'\n');
await browser.close();console.log(JSON.stringify(manifest,null,2));

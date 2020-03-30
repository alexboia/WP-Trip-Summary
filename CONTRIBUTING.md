## Report a bug

[You can use New issue -> Bug report](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) to file a new bug report.  
Please do so even if you are not sure whether or not the product should behave the way you expect it to, as this may hide, at the very least, some shortages in the way the plug-in communicates what it does.

## Request a feature you would like implemented

[You can use New Issue -> Feature request](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) to propose a new feature for this plug-in.  
If it is an already existing feature, go ahead anyway and propose a way to enhance it.

## Help with translating the plug-in

There two main types of assets that need translation:

   - the user interface strings (labels, system messages etc.), which are provided and stored in classic .mo files, with a source .pot template file that can be used as base for translation (in this case, the lib/abp01-trip-summary.pot file);
   - the help content, which is provided as HTML files, one per each language, each with a source markdown (.md) file.

### Translating user interface strings

Usually, the way you translate user interface strings is by installing [POEdit](https://poedit.net/), loading the provide .pot file and, on that basis, provide the translation for the language you want.
[See this guide for an example](https://wplang.org/translate-theme-plugin/).  
The result is a .mo file and a .po file, which you then commit and create a pull request.

However, if you either don't want to install additional software or you aren't familiar with source control and just want to provide the translations, I've prepared some GDrive sheets you can readily use.  
Here are the currently open sheets: 

| Language | Document |
| --- | --- |
| Romanian (ro_RO) | [WP Trip Summary Plugin Translations - EN to RO](https://docs.google.com/spreadsheets/d/1swKy7PPq1yNvBium8Gy8o084YNkObPi18kPWUn2TJRs/) |
| French (fr_FR) | [WP Trip Summary Plugin Translations - EN to FR](https://docs.google.com/spreadsheets/d/1kvUtXUTCKty2B4MlVZXciOBDZHcHFVwS-oz1yuhkElU/) |
| Italian (it_IT) | [WP Trip Summary Plugin Translations - EN to IT](https://docs.google.com/spreadsheets/d/1ljinCbalx46vR-E23CMiJOa0eLJIEt3Oy3MRdk6Loro/) |
| Hungarian (hu_HU) | [WP Trip Summary Plugin Translations - EN to HU](https://docs.google.com/spreadsheets/d/1hdD78XNXnmVmx-CHUdH9QbdNMXVMwq02UUdnoaGqLxM/) |
| German (de_DE) | [WP Trip Summary Plugin Translations - EN to DE](https://docs.google.com/spreadsheets/d/17unTGNFqX69qLC2Ka0Vx6qt3aq9P7OXBkM7z1YVHTaQ/) |

Ideally, these are the languages I would like covered, but any new language is more than welcome.  
To this end, [I created a template](https://docs.google.com/spreadsheets/d/10kgyf6y2eEFOZ-cFOTkERmT8WTKMq4gB0Rj2R_P-W_w/) which you only need to copy to your GDrive and edit it.  
When done, [open a new issue and request](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) that it be processed and added as a plug-in translation.

### Translating help files

The help content is written, for each language, in a markdown (.md) file called index.md, located, for each language, in the help/src/{language_code} folder (ex. help/src/ro_RO/index.md). 
This content is then be built as HTML files when the plug-in installation kit is created, but it can also be built on demand.  
Anyway, in order to help out with translating help content you do not need to necessarily write it down directly in that format (but you can do so, of course): as is the case with translating the user interface strings, one may use GDrive documents to write the translated content.  
Here are the currently open documents:

| Language | Document |
| --- | --- |
| Romanian (ro_RO) | [WP Trip Summary Plugin Translations - Help - RO](https://docs.google.com/document/d/1Ck0kFvNARou5Nf5f9BiNbi34BIOBv3VCIFj4x4tM1dw/) |
| French (fr_FR) | [WP Trip Summary Plugin Translations - Help - FR](https://docs.google.com/document/d/1DpAqYzQQqqt2XCVDgaNBWANcr6h7rz5XIcU8ZXjfIAg/) |
| Italian (it_IT) | [WP Trip Summary Plugin Translations - Help - IT](https://docs.google.com/document/d/1bZgYHgbEwwBGUU0mDT5I5abTzVRkfBVgKSb53gI2mx4/) |
| Hungarian (hu_HU) | [WP Trip Summary Plugin Translations - Help - HU](https://docs.google.com/document/d/1r7dxuz8VKXYQ1vye-dilO6ewmKQRPLBoy5Fk3oPYN20/) |
| German (de_DE) | [WP Trip Summary Plugin Translations - Help - DE](https://docs.google.com/document/d/1wrdCYgga-SXbfP5vjPG-mbHiFdgpixzkn4d5yXCXTfw/) |

Ideally, these are the languages I would like covered, but any new language is more than welcome.  
To this end, you can use [the english help content](https://docs.google.com/document/d/1MMIn-dhksoaAnzCA4nFMacfN4of_eCBSTHmadN1cc28/) as basis for your translation, start a new GDrive document and write your version of the translated content.  
The only requirement is that you must keep the current help content structure.

## Actively contribute to the code base

You can contribute to the code base itself either by:
   - writing code to fix a specific bug or implement a feature; 
   - or by proposing refactoring of existing code ([New issue -> Propose refactoring](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose)).
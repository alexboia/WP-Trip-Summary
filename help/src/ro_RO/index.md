# Cuprins

- [Detalii Generale](#detalii-generale)
- [Componenta de Vizualizare](#componenta-vizualizare)
- [Componenta de Editare](#componenta-editare)
- [Opțiuni de Configurare & Gestiune](#optiuni-de-configurare-si-gestiune)

# Detalii Generale {#detalii-generale}

## Despre {#dg-despre}

### Scopul Proiectului {#dg-scopul-proiectului}

Proiectul a început dintr-un motiv cât se poate de egoist: îmi doream să păstrez un jurnal structurat despre excursiile pe care le fac.
Pe parcurs, însă, am realizat că s-ar mai putea să ajute pe cineva, așa că am decis să îl public în regim open-source, cu o licență cât se poate de liberală.
În mare, modulul gestionează următoarele rubrici:

- sumarul tehnic - prezintă niște detalii prozaice precum: lungime, diferența totală de nivel, tip de teren etc.;
- track-ul GPS al traseului, afișat simplu pe o hartă, cu două puncte marcate: punctul de pornire (cu verde) și punctul de sosire (cu roșu).

### Tipuri de traseu

În sumarul tehnic am inclus următoarele tipuri de informații:

- date specifice traseelor de bicicletă;
- date specifice drumețiilor per-pedes (trekking/hiking);
- date specifice turelor cu trenul.

Cu alte cuvinte, vă puteți documenta (doar) aceste trei tipuri de plimbări.

### Componente Principale {#dg-componente-principale}

În funcție de diversele funcții îndeplinite de zonele din modul, putem discuta de următoarele componente:

- Componenta de vizualizare: este caseta afișată imediat sub articol și care prezintă toate informațiile de care am amintit mai sus ([Detalii aici](#componenta-vizualizare));
- Componenta de editare: este atașată formularului de creare/editare a articolelor și permite modificarea/ștergerea datelor despre traseul parcurs ([Detalii aici](#componenta-editare));
- Componenta de configurare: se ocupă cu gestiunea opțiunilor generale, dar și cu gestiunea nomenclatoarelor (liste de valori predefinte ce pot fi selectate atunci când se completează datele despre traseul parcurs) ([Detalii aici](#optiuni-de-configurare-si-gestiune)).

## Capturi de Ecran {#dg-capturi-ecran}

####Pagina de configurare
![Pagina de configurare]($helpDataDirUrl$/screenshots/admin-settings.png "Pagina de configurare")

####Editare articol - Sumar tehnic - Selectare tip traseu
![Editare articol - Sumar tehnic - Selectare tip traseu]($helpDataDirUrl$/screenshots/admin-edit-summary-empty.png "Editare articol - Sumar tehnic - Selectare tip traseu")

####Editare articol - Sumar tehnic - Informații traseu bicicletă
![Editare articol - Sumar tehnic - Informații traseu bicicletă]($helpDataDirUrl$/screenshots/admin-edit-summary-bike.png "Editare articol - Sumar tehnic - Informații traseu bicicletă")

####Editare articol - Sumar tehnic - Harta
![Editare articol - Sumar tehnic - Harta]($helpDataDirUrl$/screenshots/admin-edit-map.png "Editare articol - Sumar tehnic - Harta")

####Articol - Teaser-ul din partea de sus
![Articol - Teaser-ul din partea de sus]($helpDataDirUrl$/screenshots/viewer-teaser-top.png "Articol - Teaser-ul din partea de sus")

####Articol - Sumar tehnic
![Articol - Sumar tehnic]($helpDataDirUrl$/screenshots/viewer-summary.png "Articol - Sumar tehnic")

####Articol - Harta
![Articol - Harta]($helpDataDirUrl$/screenshots/viewer-map.png "Articol - Harta")

## Cerințe Tehnice {#dg-cerinte-tehnice}

Pentru a rula acest modul, aveți nevoie de următoarele:

- un WordPress (evident!) - minim versiunea 4.0 (poate și mai jos - nu știu, nu am încercat și prefer să nu îmi bat capul);
- o bază de date MySQL cu suport pentru date GIS;
- extensia libxml trebuie să fie instalată;
- extensia mysqli trebuie să fie instalată.

În principiu toate aceste cerințe sunt verificate la instalare, iar procesul se oprește dacă nu sunt îndeplinite.

## Licența {#dg-licenta}

Acest modul este distribuit sub licența MIT ([detalii aici](https://opensource.org/licenses/MIT)). Ce înseamnă asta:

- că îl puteți folosi absolut gratuit și fără vreun alt fel de obligație materială sau financiară;
- că îl puteți redistribui gratuit și fără vreun alt fel de obligație materială sau financiară;
- că trebuie să includeți o copie a termenilor de licențiere acolo unde îl instalați sau de fiecare dată când îl redistribuiți;
- că nu este oferită nici un fel de garanție de bună funcționare, de nici un fel, nici implicită, nici explicită.

## Mențiuni & Mulțumiri {#dg-mentiuni-multumiri}

Modulul WP-Trip-Summary folosește următoarele librării:

1. [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class) - o librărie construită peste mysqli. Este folosită în locul wpdb, componenta standard din WordPress.
2. [MimeReader](http://social-library.org/) - unde detector de MIME-Type scris de Shane Thompson.
3. [jQuery EasyTabs](https://github.com/JangoSteve/jQuery-EasyTabs) - plug-in jQuery pentru organizarea conținutului pe file/tab-uri
4. [jQuery.SumoSelect](https://github.com/HemantNegi/jquery.sumoselect) - plug-in de jQuery folosit pentru selecția multiplă pe elementele
5. [Leaflet](https://github.com/Leaflet/Leaflet) - componenta de hartă
6. [Lodash](https://github.com/lodash/lodash) - librărie de funcții utile pentru JavaScript
7. [Machina](https://github.com/ifandelse/machina.js/tree/master) - o mașină de stare implementată în JavaScript
8. [NProgress](https://github.com/rstacruz/nprogress) - o librărie pentru prezentarea vizuală a progresului diverselor operațiuni
9. [Toastr](https://github.com/CodeSeven/toastr) - o librărie JavaScript pentru prezentarea vizuală a notificărilor
10. [URI.js](https://github.com/medialize/URI.js) - o librărie JavaScript pentru construirea și interpretarea URI-urilor
11. [Visible](https://github.com/teamdf/jquery-visible) - plug-in the jQuery care determină dacă un element se află sau nu în zona vizuală a ferestrei browser-ului
12. [blockUI](https://github.com/malsup/blockui/) - plug-in de jQuery care permite afișarea ferestrelor modale
13. [kite](http://code.google.com/p/kite/) - engine de template-uri scris in JavaScript, mic și simplu
14. [Leaflet.MagnifyingGlass](https://github.com/bbecquet/Leaflet.MagnifyingGlass) - plug-in de Leaflet care adaugă hărții funcționalitatea de lupă: mărirea unei zone individuale de pe hartă
15. [Leaflet.fullscreen](https://github.com/Leaflet/Leaflet.fullscreen) - plug-in de Leaflet care permite afișarea hărții pe întreg ecranul

# Componenta de Vizualizare {#componenta-vizualizare}

# Componenta de Editare {#componenta-editare}

# Opțiuni de Configurare & Gestiune {#optiuni-de-configurare-si-gestiune}
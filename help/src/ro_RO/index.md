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

- rubrica tehnică - prezintă niște detalii prozaice precum: lungime, diferența totală de nivel, tip de teren etc.;
- track-ul GPS al traseului, afișat simplu pe o hartă.

### Tipuri de traseu

În rubrica tehnică amintită sunt suportate următoarele următoarele tipuri de informații:

- informații specifice traseelor de bicicletă;
- informații specifice drumețiilor per-pedes (trekking/hiking);
- informații specifice turelor cu trenul.

### Componente Principale {#dg-componente-principale}

În funcție de diversele funcții îndeplinite de zonele din modul, am identificat următoarele componente:

- Componenta de vizualizare: este caseta afișată imediat sub articol și care prezintă toate informațiile de care am amintit mai sus ([Detalii aici](#componenta-vizualizare));
- Componenta de editare: este atașată formularului de creare/editare a articolelor și permite modificarea/ștergerea datelor despre traseul parcurs ([Detalii aici](#componenta-editare));
- Componenta de configurare: se ocupă cu gestiunea opțiunilor generale, dar și cu gestiunea nomenclatoarelor (liste de valori predefinte ce pot fi selectate atunci când se completează datele despre traseul parcurs) ([Detalii aici](#optiuni-de-configurare-si-gestiune)).

## Capturi de Ecran {#dg-capturi-ecran}

## Cerințe Tehnice {#dg-cerinte-tehnice}

## Licența {#dg-licenta}

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
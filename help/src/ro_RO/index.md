# Cuprins {#help-root}

<div class="abp01-help-section" markdown="1">
- [Detalii Generale](#detalii-generale)
- [Componenta de Vizualizare](#componenta-vizualizare)
- [Componenta de Editare](#componenta-editare)
- [Configurare & Gestiune](#configurare-si-gestiune)
</div>

# Detalii Generale {#detalii-generale}

## Despre {#dg-despre}

<div class="abp01-help-section" markdown="1">
### Scopul Proiectului {#dg-scopul-proiectului}

Proiectul a început dintr-un motiv cât se poate de egoist: îmi doream să păstrez un jurnal structurat despre excursiile pe care le fac.
Pe parcurs, însă, am realizat că s-ar mai putea să ajute pe cineva, așa că am decis să îl public în regim open-source, cu o licență cât se poate de nerestrictivă.
În mare, modulul gestionează următoarele rubrici:

- sumarul tehnic - prezintă niște detalii prozaice precum: lungime, diferența totală de nivel, tip de teren etc.;
- track-ul GPS al traseului, afișat simplu pe o hartă, cu două puncte marcate: punctul de pornire (cu verde) și punctul de sosire (cu roșu).

### Tipuri de traseu {#dg-tipuri-traseu}

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

[Înapoi la Cuprins](#help-root)
</div>

## Capturi de Ecran {#dg-capturi-ecran}

<div class="abp01-help-section abp01-help-image-slideshow" markdown="1">
#### Pagina de configurare {.abp01-gallery-item-header}
![Pagina de configurare]($helpDataDirUrl$/screenshots/admin-settings.png "Pagina de configurare")

#### Pagina de configurare - Alegere sursă de tile-uri de hartă predefinită {.abp01-gallery-item-header}
![Pagina de configurare - Alegere sursă de tile-uri de hartă predefinită]($helpDataDirUrl$/screenshots/admin-settings-predefined-tile-layer.png "Pagina de configurare - Alegere sursă de tile-uri de hartă predefinită")

#### Editare articol - Sumar tehnic - Selectare tip traseu {.abp01-gallery-item-header}
![Editare articol - Sumar tehnic - Selectare tip traseu]($helpDataDirUrl$/screenshots/admin-edit-summary-empty.png "Editare articol - Sumar tehnic - Selectare tip traseu")

#### Editare articol - Sumar tehnic - Informații traseu bicicletă {.abp01-gallery-item-header}
![Editare articol - Sumar tehnic - Informații traseu bicicletă]($helpDataDirUrl$/screenshots/admin-edit-summary-bike.png "Editare articol - Sumar tehnic - Informații traseu bicicletă")

#### Editare articol - Sumar tehnic - Harta {.abp01-gallery-item-header}
![Editare articol - Sumar tehnic - Harta]($helpDataDirUrl$/screenshots/admin-edit-map.png "Editare articol - Sumar tehnic - Harta")

#### Articol - Teaser-ul din partea de sus {.abp01-gallery-item-header}
![Articol - Teaser-ul din partea de sus]($helpDataDirUrl$/screenshots/viewer-teaser-top.png "Articol - Teaser-ul din partea de sus")

#### Articol - Sumar tehnic {.abp01-gallery-item-header}
![Articol - Sumar tehnic]($helpDataDirUrl$/screenshots/viewer-summary.png "Articol - Sumar tehnic")

#### Articol - Harta {.abp01-gallery-item-header}
![Articol - Harta]($helpDataDirUrl$/screenshots/viewer-map.png "Articol - Harta")

#### Articol - Harta cu profil de altitudine {.abp01-gallery-item-header}
![Articol - Harta cu profil de altitudine]($helpDataDirUrl$/screenshots/viewer-map-alt-profile.png "Articol - Harta cu profil de altitudine")

#### Listare articole - Coloane informationale {.abp01-gallery-item-header}
![Listare articole - Coloane informationale]($helpDataDirUrl$/screenshots/post-listing-columns.png "Listare articole - Coloane informationale")

[Înapoi la Cuprins](#help-root)
</div>

## Cerințe Tehnice {#dg-cerinte-tehnice}

<div class="abp01-help-section" markdown="1">
Pentru a rula acest modul, aveți nevoie de următoarele:

- PHP versiunea 5.6.2 sau mai recenta;
- MySQL versiunea 5.7 sau mai recenta (cu suport pentru date GIS);
- WordPress 5.0 sau mai recenta;
- extensia PHP libxml;
- extensia PHP SimpleXml;
- extensia PHP mysqli;
- extensia PHP mbstring - nu e strict oblgiatorie, dar e recomandata;
- extensia PHP zlib - nu e strict oblgiatorie, dar e recomandata.

În principiu toate aceste cerințe sunt verificate la instalare, iar procesul se oprește dacă nu sunt îndeplinite.

[Înapoi la Cuprins](#help-root)
</div>

## Licența {#dg-licenta}

<div class="abp01-help-section" markdown="1">
Acest modul este distribuit sub licența [BSD New License](https://opensource.org/licenses/BSD-3-Clause). Ce înseamnă asta:

- că îl puteți folosi absolut gratuit și fără vreun alt fel de obligație materială sau financiară;
- că îl puteți redistribui gratuit și fără vreun alt fel de obligație materială sau financiară;
- că trebuie să includeți o copie a termenilor de licențiere acolo unde îl instalați sau de fiecare dată când îl redistribuiți;
- că nu este oferită nici un fel de garanție de bună funcționare, de nici un fel, nici implicită, nici explicită.

[Înapoi la Cuprins](#help-root)
</div>

## Mențiuni & Mulțumiri {#dg-mentiuni-multumiri}

<div class="abp01-help-section" markdown="1">
Modulul WP-Trip-Summary folosește următoarele librării:

1. [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class) - o librărie construită peste mysqli. Este folosită în locul wpdb, componenta standard din WordPress.
2. [MimeReader](http://social-library.org/) - unde detector de MIME-Type scris de Shane Thompson.
3. [jQuery EasyTabs](https://github.com/JangoSteve/jQuery-EasyTabs) - plug-in jQuery pentru organizarea conținutului pe file/tab-uri
4. [Select2](https://select2.org/) - plug-in de jQuery folosit pentru selecția multiplă pe elementele
5. [Leaflet](https://github.com/Leaflet/Leaflet) - componenta de hartă
6. [Machina](https://github.com/ifandelse/machina.js/tree/master) - o mașină de stare implementată în JavaScript
7. [NProgress](https://github.com/rstacruz/nprogress) - o librărie pentru prezentarea vizuală a progresului diverselor operațiuni
8. [Toastr](https://github.com/CodeSeven/toastr) - o librărie JavaScript pentru prezentarea vizuală a notificărilor
9. [URI.js](https://github.com/medialize/URI.js) - o librărie JavaScript pentru construirea și interpretarea URI-urilor
10. [Visible](https://github.com/teamdf/jquery-visible) - plug-in the jQuery care determină dacă un element se află sau nu în zona vizuală a ferestrei browser-ului
11. [blockUI](https://github.com/malsup/blockui/) - plug-in de jQuery care permite afișarea ferestrelor modale
12. [kite](http://code.google.com/p/kite/) - engine de template-uri scris in JavaScript, mic și simplu
13. [Leaflet.MagnifyingGlass](https://github.com/bbecquet/Leaflet.MagnifyingGlass) - plug-in de Leaflet care adaugă hărții funcționalitatea de lupă: mărirea unei zone individuale de pe hartă
14. [Leaflet.fullscreen](https://github.com/Leaflet/Leaflet.fullscreen) - plug-in de Leaflet care permite afișarea hărții pe întreg ecranul
15. [Tipped JS](https://github.com/staaky/tipped) - soluție completă de gestionare a tooltip-urilor folosind JavaAscript
16. [PHPUnit](https://github.com/sebastianbergmann/phpunit) - framework de unit-testing pentru PHP
17. [Parsedown](https://github.com/erusev/parsedown) - interpretor Markdown scris in PHP. [http://parsedown.org](http://parsedown.org)
18. [Faker](https://github.com/fzaninotto/Faker) - o librărie PHP ce generează date aleatorii ce pot fi folosite la testare

[Înapoi la Cuprins](#help-root)
</div>

# Componenta de Vizualizare {#componenta-vizualizare}

<div class="abp01-help-section" markdown="1">
Componenta de vizualizare este formată din trei zone distincte:

- teaser-ul superior;
- teaser-ul inferior;
- caseta tehnică propriu-zisă.
</div>

## Teaser-ul Superior

<div class="abp01-help-section" markdown="1">
Superior ca poziție, desigur, nu calitativ. 
Este afișat deasupra conținutului articolului, dar sub titlu si are scopul de a ghida cititorul către caseta tehnică.
Ideea de la care am pornit este că nu toată lumea poate realiza că există un astfel de instrument pe pagină și, în caz că este singurul punct de interes, să fie direcționat acolo unde dorește.

[Înapoi la Cuprins](#help-root)
</div>

## Teaser-ul Inferior

<div class="abp01-help-section" markdown="1">
Inferior ca poziție, desigur, nu calitativ. 
Este afișat sub conținutului articolului, chiar sub caseta tehnică.
Nu este afișat mereu, ci doar când sistemul detectează posibilitatea ca utilizatorul să fi sărit peste articol și servește ca un mijloc de a ajunge rapid la început pentru a lectura frumoasa operă.

[Înapoi la Cuprins](#help-root)
</div>

## Caseta Tehnică

<div class="abp01-help-section" markdown="1">
Reprezintă, desigur, zona propriu-zisă unde sunt afișate, pe câte un tab distinct:

- harta cu traseul evidențiat;
- informațiile tehnice (distanță, diferența de nivel etc.).

Fiecare din aceste tab-uri este afișat doar dacă există informațiile corespunzătoare. 
Dacă nu există informații pentru niciunul din tab-uri, atunci întreaga componentă este ascunsă, inclusiv teaser-ele.

Harta în sine, oferă următoarele unelte:

- Mărire hartă (zoom-in) - concentrare pe o anumită zonă;
- Micșorare hartă (zoom-out) - vedere de ansamblu;
- Afișarea hărții pe întreg ecranul (full screen);
- Descărcarea track-ului GPX;
- Lupă (zoom-in pe o regiune discretă/limitată din hartă).

O parte din aceste opțiuni pot fi dezactivate folosind componenta de gestiune, așa cum este descris mai jos.

[Înapoi la Cuprins](#help-root)
</div>

# Componenta de Editare {#componenta-editare}

<div class="abp01-help-section" markdown="1">
Componenta de editare permite modificarea sumarului tehnic al turei și atașarea track-ului GPS.
Astfel, asemeni componentei de vizualizare, este organizată în două tab-uri, câte unul pentru fiecare categorie de date:

- formularul de modificare a informațiilor tehnice;
- zona de încărcare și previzualizare a track-ului GPS.
</div>

## Caseta introductivă

<div class="abp01-help-section" markdown="1">
Caseta introductivă servește la integrarea componentei de editare în fluxul de lucru cu post-uri al WordPress.
Este prezentată ca un metabox, plasată in bara laterală a ecranului de editare a post-urilor, intitulată: `Tura pe scurt`. 
Permite accesul rapid la următoarele informații și acțiuni relevante:

- dacă post-ul curent are sau nu are informații tehnice despre traseu:
    - marcat cu o bifă albă pe fundal verde rotund dacă da;
    - marcat cu un X alb pe fundal roșu rotund, dacă nu.
- dacă post-ul curent are sau nu atașat un track GPS:
    - marcat cu o bifă albă pe fundal verde rotund dacă da;
    - marcat cu un X alb pe fundal roșu rotund, dacă nu.
- ștergerea rapidă a informațiilor tehnice despre traseu atașate post-ului curent (via butonul `Acțiuni rapide`);
- ștergerea rapidă a track-ului GPS atașat post-ului curent (via butonul `Acțiuni rapide`);
- descărcarea track-ului GPS atașat post-ului curent (via butonul `Acțiuni rapide`);
- deschiderea componentei de editare propriu-zise pentru post-ul curent (via butonul `Modifică`).

[Înapoi la Cuprins](#help-root)
</div>

## Formularul de Modificare a Informațiilor Tehnice

<div class="abp01-help-section" markdown="1">
Tab-ul corespunzător este denumit simplu, ”Informații”.
Dacă nu a fost completat, este populat cu trei butoane, câte unul pentru fiecare tip de tură suportat:

- "Cu bicicleta" - permite completarea setului de informații specifice turelor cu bicicleta;
- "Per-pedes" - permite completare setului de informații specifice drumețiilor per-pedes (trekking/hiking);
- "Cu trenul" - permite completarea setului de informații specifice turelor cu trenul.

Odată acționat oricare din aceste butoane, va fi afișat formularul propriu-zis, conform cu tipul de tură ales.

De menționat că, indiferent de formular, dacă vreunul din câmpurile care necesită existența unor valori în nomenclatorul său nu are nicio astfel de valoare definită, atunci va fi afișat un link către pagina de gestiune a nomenclatorului respectiv.

În afară de formular, în partea de jos a ecranului pot fi găsite și două butoane de control, vizibile după ce a fost ales un tip de traseu:

- "Salvează" - trebuie acționat pentru persistarea modificărilor;
- "Șterge Info" - trebuie acționat atunci când se dorește ștergerea întregului set de informații tehnice.

[Înapoi la Cuprins](#help-root)
</div>

## Formularul de Încărcare și Previzualizare a Track-ului GPS

<div class="abp01-help-section" markdown="1">
Tab-ul corespunzător este denumit simplu, "Hartă & Track GPS".
Dacă nu a fost ales încă nici un track, este populat cu un singur buton, care permite răsfoirea calculatorului personal întru alegerea fișierului GPX dorit spre atașare.

Odată track-ul încărcat, harta va fi centrată, iar zoom-ul său ajustat astfel încât să fie vizibil întregul circuit. Sunt suportate și fișierele GPX care conține segmente deconectate.

În afară de formular, în partea de jos a ecranului pot fi găsite și două butoane de control:

- "Salvează" - trebuie acționat pentru persistarea modificărilor (vizibil doar dacă a fost ales un tip de traseu);
- "Șterge track" - trebuie acționat atunci când se dorește ștergerea întregului track (vizibil, desigur, doar după ce a fost încărcat un track).

[Înapoi la Cuprins](#help-root)
</div>

# Configurare & Gestiune {#configurare-si-gestiune}

<div class="abp01-help-section" markdown="1">
- [Opțiunile Generice](#configurare-si-gestiune-opt-generice)
- [Gestiunea Nomenclatoarelor](#configurare-si-gestiune-gst-nomenclatoare)

Elementele de configurare & gestiune sunt puse la punct pentru a oferi o oarecare flexibilitate în utilizarea progrămelului. Astfel, putem vorbi despre două mari și late direcții de flexibilizare:

- opțiuni generice (unități de măsură, activarea / dezactivarea unor unelte sau elemente de interfață etc.);
- gestiunea nomenclatoarelor (adică a seturilor de opțiuni predefinite din care se completează unele câmpuri, cum ar fi Nivelul de Dificultate).
</div>

## Opțiunile Generice {#configurare-si-gestiune-opt-generice}

<div class="abp01-help-section" markdown="1">
Există o pagină dedicată din care opțiunile generice pot fi modificate. Acolo se ajunge din meniul principal, accesând: `Trip Summary - Configurare`.

Odată ajunși aici, există următoarele punct de configurare.

#### Sistemul de unități de măsură. 

Se poate ori sistemul metric (m/km), ori sistemul imperial (mile/inch). Foarte important de menționat e că nu se face nici un fel de calcul de conversie și că se presupune că atunci când se introduce sumarul unei ture valorile sunt deja exprimate în sistemul ales aici.

#### Dacă se afișează sau nu teaser-ul. 

Odată debifat câmpul și salvate modificările, teaser-ul din pagina articolului va fi ascuns.

#### Textul teaser-ului din partea de sus

Textul teaser-ului afișat deasupra articolului. Există deja o valoare predefinită.

#### Textul teaser-ului din partea de jos

Textul teaser-ului afișat imediat sub caseta de sumar. Există deja o valoare predefinită.

#### Fila inițial selectată

Acest câmp permite controlarea filei încărcate inițial în viewer-ul din fron-end atunci când utilizatorul deschide pagina unui post.
Valoarea implicită este: Hartă & Track GPS.

#### Șablonul URL-ului sursei de tile-uri de hartă

Aici discuția e un pic mai lungă. 

În primul rând, de reținut că harta utilizată nu e o singură imagine, ci e formată din mai multe imagini pătrate, denumite tile-uri care, așezate una lângă alta, formează întreaga hartă.

Există mai multe seturi de tile-uri, câte unul pentru fiecare nivel de zoom, iar fiecare tile e caracterizat prin două coordonate - să zicem x și y - cam ca pe o tablă de șah.

Așadar, ca să putem accesa un tile, avem nevoie de următoarele informații: 

- z - nivelul de zoom;
- x - poziția pe orizontală;
- y - poziția pe verticală.

Dar mai e o problemă: ca să încarci atâtea imagini într-un timp cât mai rapid uneori aceste tot acest mega-set de tile-uri este copiat redundant pe mai multe mașini - servere. 
Aceste mașini sunt și ele numerotate/denumite - să zicem, spre exemplu, 1, 2, 3, 4 etc.
Ideea aici este că pot - în loc de a le cere din același loc - pot să cer o parte de la mașina 1, o parte de la mașina 2 etc, dar asta, repet, nu e musai un comportament obligatoriu.

Ca să adun toate aceste lucruri, pentru a putea încărca tile-uri de hartă dintr-o altă sursă decât cea predefinită - OpenStreetMaps - aveți la dispoziție câmpul aflat acum în discuție.
Iar acest șablon oferă următoarele marcaje speciale:

- {s} - pentru a specifica unde se introduce numărul mașinii (ex. {s}.tile.osm.org s-ar traduce în 1.tile.osm.org, 2.tile.osm.org);
- {z} - pentru a specifica unde se introduce numărul nivelului de zoom;
- {x} - pentru a specifica unde se introduce poziția pe orizontală a tile-ului;
- {y} - pentru a specifica unde se introduce poziția pe verticală a tile-ului.

Marcajele pot fi combinate oricum și va trebui să consultați documentația pentru furnizorul de la care vreți să afișați harta.

#### URL-ul paginii de copyright a sursei de tile-uri de hartă

În funcție de unde alegeți să folosiți harta, zona de copyright poate fi sau nu obligatorie. Este, în orice caz, o chestie de bun simț, așa că vă încurajez să o aveți la vedere.
Plasarea este în colțul din dreapta jos al hărții, iar câmpul aflat acum în discuție permite introducerea unui link către furnizor.

#### Nota de copyright a sursei de tile-uri de hartă

În funcție de unde alegeți să folosiți harta, zona de copyright poate fi sau nu obligatorie. Este, în orice caz, o chestie de bun simț, așa că vă încurajez să o aveți la vedere.
Plasarea este în colțul din dreapta jos al hărții, iar câmpul aflat acum în discuție permite introducerea notei propriu-zise de copyright.

#### Se permite comutarea hărții pe întreg ecranul?

Dacă debifați acest câmp, în caseta tehnică din pagina articolului nu va mai fi afișat butonul care comută harta pe întreg ecranul (full-screen).
Implicit, câmpul este bifat, deci butonul este afișat.

#### Se activează lupa?

Dacă debifați acest câmp, în caseta tehnică din pagina articolului nu va mai fi afișat butonul care activează lupa.
Implicit, câmpul este bifat, deci butonul este afișat.

#### Se afișează scara hărții

Dacă debifați acest câmp, în caseta tehnică din pagina articolului nu va mai fi afișată scara hărții (în stânga-jos).
Implicit, câmpul este bifat, deci scara hărții este afișată.

#### Se permite descărcarea track-ului?

Dacă debifați acest câmp, în caseta tehnică din pagina articolului nu va mai fi afișat butonul care permite descărcarea track-ului GPX.
Implicit, câmpul este bifat, deci butonul este afișat.

#### Culoarea liniei track-ului de pe harta

Acest câmp permite setarea culorii liniei cu care este reprezentat pe harta track-ul GPS.
Se aplică atât la viewer-ul din front-end cat si la editorul din back-end.
Valoarea implicita este: `#0033ff` (un soi de albastru).

#### Grosimea liniei track-ului de pe harta

Acest câmp permite setarea grosimii liniei (în pixeli) cu care este reprezentat pe harta track-ul GPS.
Se aplică atât la viewer-ul din front-end cât și la editorul din back-end.
Valoarea implicita este: 3 pixeli.

#### Înălțimea hărții

Acest câmp permite setarea înălțimii hărții, în pixeli.
Se aplică doar hărții afișate în viewer-ul din front-end.
Valoarea implicită este de 350 de pixeli.

[Înapoi la Cuprins](#help-root)
</div>

## Gestiunea Nomenclatoarelor {#configurare-si-gestiune-gst-nomenclatoare}

<div class="abp01-help-section" markdown="1">
Nomenclatoarele sunt seturi de opțiuni predefinite din care se completează unele câmpuri. 
Valorile acestor opțiuni sunt modificabile în funcție de o limbă aleasă. 
Sunt disponibile toate limbile suportate de WordPress, plus posibilitatea de a stabili o valoare implicită.
Valoarea implicită a unei opțiuni va fi afișată atunci când nu este găsită nici o traducere pentru acea opțiune pentru limba curentă a blogului. 

### Câmpurile Gestionate

Câmpurile pentru care este necesară gestiunea nomenclatoarelor sunt:

- Nivelul de dificultate;
- Anotimpurile recomandate;
- Tipurile de suprafață ale drumului/potecii;
- Tipurile recomandate de bicicleta;
- Operatori feroviari;
- Statusul liniei;
- Electrificare;
- Tipul liniei.

#### Nivelul de Dificultate

Este disponibil pentru tipurile de traseu:

- Cu bicicleta;
- Per-pedes.

Reprezintă, evident, evaluarea subiectivă a fiecăruia despre cât de greu a fost traseul parcurs.
Modulul de față oferă următoarele opțiuni predefinite (traduse în Română, Franceză și Engleză):

- Ușor;
- Mediu;
- Dificil;
- Tortură Medievală.

#### Anotimpurile Recomandate

Este disponibil pentru tipurile de traseu:

- Cu bicicleta;
- Per-pedes.

Reprezintă, evident, anotimpurile în care este fizic posibilă parcurgerea traseului în condiții decente (adică fără un efort excesiv și fără a vă supune vreunui pericol iminent).
Modulul oferă următoarele opțiuni predefinite (traduse in Română, Franceză și Engleză):

- Primăvara;
- Vara;
- Toamna;
- Iarna.

#### Tipurile de Suprafață ale Drumului/Potecii

Este disponibil pentru tipurile de traseu:

- Cu bicicleta;
- Per-pedes.

Reprezintă texturile / compozițiile suprafețelor drumurilor întâlnite. Ex: iarbă, bolovani, asfalt, macadam etc.
Modulul oferă următoarele opțiuni predefinite (traduse in Română, Franceză și Engleză):

- Asfalt;
- Plăci de beton;
- Pământ;
- Iarbă;
- Macadam;
- Piatră neașezată.

#### Tipurile Recomandate de Bicicletă

Este disponibil pentru tipurile de traseu:

- Cu bicicleta.

Reprezintă tipurile de biciclete care pot fi folosite pentru a parcurge în siguranță și confort (relativ) traseul descris.
Modulul oferă următoarele opțiuni predefinite (traduse în Română, Franceză și Engleză):

- MTB;
- Cursieră;
- Trekking;
- Bicicletă de oraș.

#### Operatori Feroviari

Este disponibil pentru tipurile de traseu (traduse in Română, Franceză și Engleză):

- Cu trenul.

Reprezintă companiile care operează curse pe traseul parcurs, fie pe toată lungimea sa, fie doar parțial.
Modulul nu oferă opțiuni predefinite.

#### Tipul Liniei

Este disponibil pentru tipurile de traseu:

- Cu trenul.

Descrie daca linia este simplă sau dublă.
Modulul oferă următoarele opțiuni predefinite (traduse in Română, Franceză și Engleză):

- Linie simplă (un singur fir de circulație);
- Linie dublă (două fie de circulație, câte unul pentru fiecare sens).

#### Statusul Liniei

Este disponibil pentru tipurile de traseu:

- Cu trenul.

Reprezintă starea liniei pe traseul parcurs.
Modulul oferă următoarele opțiuni predefinite (traduse in Română, Franceză și Engleză):

- În exploatare (se operează curse normale);
- Închisă (nu se mai operează curse, dar linia este în conservare sau încă există);
- Desființată (linia nu mai există, fiind demontată total sau parțial);
- În reabilitare (linia se afla în proces de reabilitare; programul de circulație poate fi limitat/modificat).

#### Electrificare

Este disponibil pentru tipurile de traseu:

- Cu trenul.

Reprezintă starea lucrărilor de electrificare la linie.
Modulul oferă următoarele opțiuni predefinite (traduse in Română, Franceză și Engleză):

- Electrificată;
- Neelectrificată;
- Partial electrificată.

### Operațiunile Disponibile

Următoarele operațiuni sunt disponibile, fiecare dintre ele în contextul unei limbi alese:

- Adaugarea unui item nou într-un nomenclator;
- Ștergerea unui item existent dintr-un nomenclator;
- Modificarea unui item existent;
- Listarea itemilor existenți într-un nomenclator.

De notat că atunci când se adaugă un item pentru limba implicită sistemul cere eticheta doar pentru aceasta. 
Pe de altă parte, când se adaugă un item pentru o limbă anume (ex. Română, Engleză etc.), sistemul cere eticheta atât pentru limba implicită, cât și pentru limba selectată.

[Înapoi la Cuprins](#help-root)
</div>
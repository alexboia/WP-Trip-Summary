# Cuprins {#help-root}

- [Detalii Generale](#detalii-generale)
- [Componenta de Vizualizare](#componenta-vizualizare)
- [Componenta de Editare](#componenta-editare)
- [Configurare & Gestiune](#configurare-si-gestiune)

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

[Înapoi la Cuprins](#help-root)

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

[Înapoi la Cuprins](#help-root)

## Cerințe Tehnice {#dg-cerinte-tehnice}

Pentru a rula acest modul, aveți nevoie de următoarele:

- un WordPress (evident!) - minim versiunea 4.0 (poate și mai jos - nu știu, nu am încercat și prefer să nu îmi bat capul);
- o bază de date MySQL cu suport pentru date GIS;
- extensia libxml trebuie să fie instalată;
- extensia mysqli trebuie să fie instalată.

În principiu toate aceste cerințe sunt verificate la instalare, iar procesul se oprește dacă nu sunt îndeplinite.

[Înapoi la Cuprins](#help-root)

## Licența {#dg-licenta}

Acest modul este distribuit sub licența MIT ([detalii aici](https://opensource.org/licenses/MIT)). Ce înseamnă asta:

- că îl puteți folosi absolut gratuit și fără vreun alt fel de obligație materială sau financiară;
- că îl puteți redistribui gratuit și fără vreun alt fel de obligație materială sau financiară;
- că trebuie să includeți o copie a termenilor de licențiere acolo unde îl instalați sau de fiecare dată când îl redistribuiți;
- că nu este oferită nici un fel de garanție de bună funcționare, de nici un fel, nici implicită, nici explicită.

[Înapoi la Cuprins](#help-root)

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

[Înapoi la Cuprins](#help-root)

# Componenta de Vizualizare {#componenta-vizualizare}

# Componenta de Editare {#componenta-editare}

# Configurare & Gestiune {#configurare-si-gestiune}

- [Opțiunile Generice](#configurare-si-gestiune-opt-generice)
- [Gestiunea Nomenclatoarelor](#configurare-si-gestiune-gst-nomenclatoare)

Elementele de configurare & gestiune sunt puse la punct pentru a oferi o oarecare flexibilitate în utilizarea progrămelului. Astfel, putem vorbi despre două mari și late direcții de flexibilizare:

- opțiuni generice (unități de măsură, activarea / dezactivarea unor unelte sau elemente de interfață etc.);
- gestiunea nomenclatoarelor (adică a seturilor de opțiuni predefinite din care se completează unele câmpuri, cum ar fi Nivelul de Dificultate).

## Opțiunile Generice {#configurare-si-gestiune-opt-generice}

Există o pagină dedicată din care opțiunile generice pot fi modificate. Acolo se ajunge din meniul principal, accesând: Trip Summary -> Configurare.

Odată ajunși aici, există următoarele punct de configurare.

#### Sistemul de unități de măsură. 

Se poate ori sistemul metric (m/km), ori sistemul imperial (mile/inch). Foarte important de menționat e că nu se face nici un fel de calcul de conversie și că se presupune că atunci când se introduce sumarul unei ture valorile sunt deja exprimate în sistemul ales aici.

#### Dacă se afișează sau nu teaser-ul. 

Odată debifat câmpul și salvate modificările, teaser-ul din pagina articolului va fi ascuns.

#### Textul teaser-ului din partea de sus

Textul componentei teaser-ului care e afișată deasupra articolului. Există deja o valoare predefinită.

#### Textul teaser-ului din partea de jos

Textul componentei teaser-ului care afișată imediat sub caseta de sumar. Există deja o valoare predefinită.

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

#### Se permite comutărea hărții pe întreg ecranul?

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

[Înapoi la Cuprins](#help-root)

## Gestiunea Nomenclatoarelor {#configurare-si-gestiune-gst-nomenclatoare}

Nomenclatoarele sunt seturi de opțiuni predefinite din care se completează unele câmpuri. 
Valorile acestor opțiuni sunt modificabile în funcție de o limbă aleasă. 
Sunt disponibile toate limbile suportate de WordPress, plus posibilitatea de a stabili o valoare implicită.
Valoarea implicită a unei opțiuni va fi afișată atunci când nu este găsită nici o traducere pentru acea opțiune pentru limba curentă a blogului. 

### Câmpurile gestionate

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
Modulul de față oferă următoarele opțiuni predefinite:

- Ușor;
- Mediu;
- Dificil;
- Tortură Medievală.

#### Anotimpurile Recomandate

Este disponibil pentru tipurile de traseu:

- Cu bicicleta;
- Per-pedes.

Reprezintă, evident, anotimpurile în care ori este fizic posibilă parcurgerea traseului în condiții decente (adică fără un efort excesiv și fără a vă supune vreunui pericol iminent).
Modulul oferă următoarele opțiuni predefinite:

- Primăvara;
- Vara;
- Toamna;
- Iarna.

#### Tipurile de Suprafață ale Drumului/Potecii

Este disponibil pentru tipurile de traseu:

- Cu bicicleta;
- Per-pedes.

Reprezintă texturile / compozițiile suprafețelor drumurilor întâlnite. Ex: iarbă, bolovani, asfalt, macadam etc.
Modulul oferă următoarele opțiuni predefinite:

- Asfalt;
- Plăci de beton;
- Pământ;
- Iarbă;
- Macadam;
- Piatră neașezată.

#### Tipurile recomandate de bicicletă

Este disponibil pentru tipurile de traseu:

- Cu bicicleta.

Reprezintă tipurile de biciclete care pot fi folosite pentru a parcurge în siguranță și confort (relativ) traseul descris.
Modulul oferă următoarele opțiuni predefinite:

- MTB;
- Cursieră;
- Trekking;
- Bicicletă de oraș.

#### Operatori feroviari

Este disponibil pentru tipurile de traseu:

- Cu trenul.

Reprezintă companiile care operează curse pe traseul parcurs, fie pe toată lungimea sa, fie doar parțial.
Modulul nu oferă opțiuni predefinite.

#### Statusul Liniei

Este disponibil pentru tipurile de traseu:

- Cu trenul.

Reprezintă starea liniei pe traseul parcurs.
Modulul oferă următoarele opțiuni predefinnite:

- În exploatare (se operează curse normale);
- Închisă (nu se mai operează curse, dar linia este în conservare sau încă există);
- Desființată (linia nu mai există, fiind demontată total sau parțial);
- În reabilitare (linia se afla în proces de reabilitare la parcurgerii traseului).

#### Electrificare

Este disponibil pentru tipurile de traseu:

- Cu trenul.

Reprezintă starea lucrărilor de electrificare la linie.
Modulul oferă următoarele opțiuni predefinite:

- Electrificată;
- Neelectrificată;
- Partial electrificată.

#### Tipul liniei

Este disponibil pentru tipurile de traseu:

- Cu trenul.

Descrie daca linia este simplă sau dublă.
În mod evident, opțiunile predefinite sunt următoarele:

- Linie simplă (un singur fir de circulație);
- Linie dublă (două fie de circulație, câte unul pentru fiecare sens).

### Operațiunile suportate

[Înapoi la Cuprins](#help-root)
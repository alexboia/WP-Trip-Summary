# Strategia de marketing WP Trip Summary

Alternativă în română, sincronizată la 9 octombrie 2026. [Versiunea în engleză](strategy.md) este canonică și prevalează dacă apar diferențe. Documentele și materialele noi pornesc de la engleză; variantele în română sunt localizări opționale pentru publicul ales.

Propunere inițială, 9 octombrie 2026. Document de lucru pentru producție; mesajele, prioritățile și dimensiunile editoriale de mai jos pot fi ajustate după primele utilizări. Nu reprezintă o campanie deja publicată.

## Poziționare

**Povestea călătoriei, traseul și detaliile utile, în același articol WordPress.**

WP Trip Summary ajută autorul să transforme o relatare într-o resursă practică: cititorul vede pe unde a trecut, distanța, urcarea și informațiile relevante turei. Punctul de plecare este o călătorie reală, completată de o demonstrație clară a produsului.

Mesajul principal propus pentru publicul internațional: **“Your trip. Your story. Your WordPress site.”** Subtitlu: **“Add route maps, elevation profiles and practical trip details to your travel posts.”** Pentru publicul român putem păstra direcția experimentată: **„Ai povestea. Pune și traseul.”** Aceste formulări sunt propuneri editoriale, nu rezultate ale unui test de audiență.

Prioritatea inițială: bloggeri de ciclism și drumeție care au deja WordPress și fișiere GPS. Călătoriile cu trenul oferă o diferențiere vizuală și tematică bună, pe care o prezentăm printr-un exemplu dedicat. Cluburile și ghizii sunt un public secundar pentru publicarea traseelor; materialele nu vor sugera funcții de rezervare sau navigație în teren.

## Două trasee pentru public

| Public | Întrebarea pe care o are | Dovada potrivită | Pasul următor |
| --- | --- | --- | --- |
| Autor de blog | „Cum fac articolul mai util fără să reconstruiesc totul?” | Un articol real, cu rezumat, hartă și profil; apoi fluxul de adăugare a datelor | Vezi exemplul → instalează → completează prima tură |
| Dezvoltator / integrator WordPress | „Îl pot adapta site-ului meu și înțeleg limitele?” | Un exemplu mic de personalizare, hook-ul exact și rezultatul; documentația aferentă | Vezi exemplul → rulează integrarea → consultă hooks / contribuie |

Pagina WordPress.org va avea accent pe utilizare. GitHub va explica rapid produsul și va oferi o intrare vizibilă pentru dezvoltatori. Clipul principal arată rezultatul pentru cititor și lucrul autorului; demonstrația tehnică are propriul material și propriul CTA. Nu este nevoie ca un singur clip scurt să explice ambele fluxuri.

## Punctul de plecare verificat

Observațiile de mai jos provin din inventarierea inițială din 9 octombrie 2026; ele nu înlocuiesc verificarea versiunii alese înainte de producție.

| Material / sursă | Ce există | Implicație pentru producție |
| --- | --- | --- |
| [Sursele README](../../../../readme/Makefile) și [manifestul](../../../../readme/manifest.json) | Fragmente comune, ținte GitHub și WordPress.org, capturi nominalizate | Se editează sursele și se folosește generatorul existent |
| `README.md` și `README.txt` | `php bin/tools/build-readme.php --check` le-a raportat ca învechite | Revizuire a surselor și preview înainte de regenerarea finală |
| Fragmentele README | Avertismente în `hero`, `screenshots`, `whats-new`, `requirements`; CTA „See it live” cu `href="#"` | Destinație reală pentru demo sau eliminarea CTA-ului; rezolvarea afirmațiilor incomplete |
| Versiune | Header local `0.3.3`; generatorul nu găsește changelog pentru acest stable tag | Stabilim versiunea promovată și completăm din schimbări verificate; nu inventăm un changelog |
| Compatibilitate | PHP declarat 8.0.0; Monolog blocat în lockfile necesită cel puțin 8.1 | Discrepanță de rezolvat în fluxul de release, înainte de afirmații publice de compatibilitate |
| [Experimentul video](../../../../brag-output/brag-plan.md) | 22 s, română, 1920×1080; reconstrucție animată a UI-ului și imagine de hartă existentă | Refolosim direcția și proiectul, dar capturăm produsul curent pentru o demonstrație nouă |
| [Creditele video](../../../../brag-output/credits.md) | Date de rezumat fictive, declarate ca exemplu; muzică, fonturi și hartă menționate | Exemplul nu dovedește statisticile traseului din imagine; verificăm și drepturile înainte de distribuire |
| `brag-output/` | Unele briefuri indică `assets/ro_RO/viewer-map-alt-profile.png`, absent la inventariere; există imagine în compoziție | Rezolvăm proveniența exactă înainte de reutilizare; nu presupunem că toate căile vechi sunt valide |
| `assets/` | Bannere JPG, iconuri PNG, capturi în `en_US`; bannerul inspectat folosește fotografie stilizată de bicicletă | Păstrăm logo-ul și pornim de la identitatea bleumarin/mentă a clipului; adăugăm context de produs în banner |
| [Exemple](../../../../examples) | Activarea pe un custom post type și schimbarea etichetelor lookup | Bază concretă pentru conținutul adresat dezvoltatorilor; necesită demonstrare pe versiunea aleasă |

Pagina publică WordPress.org nu a putut fi citită prin instrumentul web la inventarierea inițială. Versiunea efectiv distribuită și prezentarea live rămân de verificat la pregătirea campaniei. Inventarul local nu le înlocuiește.

## Ce putem demonstra și ce trebuie calificat

| Idee de mesaj | Punct de verificare | Limită de comunicare |
| --- | --- | --- |
| Informații specifice pentru bicicletă, drumeție și tren | `readme/sections/features.md`, editorul și viewer-ul versiunii alese | Fără noi tipuri de tură prezentate ca funcții disponibile |
| Import GPX, KML, GeoJSON; hartă și profil de altitudine | Parserele din `lib/route/track/documentParser/`, captură reală din demo | Nu sugerăm că toate formatele/fisierele conțin altitudine sau că importul are o durată garantată |
| Fișiere GPS pe serverul site-ului | Fluxul de upload/stocare și configurația de hartă | Hărțile folosesc surse de tile-uri; nu promitem funcționare offline |
| Personalizare prin hooks | `examples/e01-enable-custom-post-types/plugin.php`, `examples/e02-customize-lookup-type-labels/plugin.php`, `hook-docs/` | Folosim numele și semnăturile reale; nu prezentăm un API public complet ca fiind finalizat |
| Câmp REST `wpts_trip_summary` | `lib/pluginModules/RestApiEnhancementsPluginModule.php` | Sursa locală înregistrează citire, fără update callback; datele în listing sunt dezactivate implicit și controlate prin filtru |
| Funcții viitoare | `readme/sections/roadmap.md` și release-ul ales | Separăm roadmap-ul de funcțiile demonstrației; nu promitem date de livrare |

## Primul pachet de materiale

Construim o singură tură demonstrativă, cu fotografii, fișier GPS și date care se potrivesc. Din ea derivăm următoarele:

| Prioritate | Material | Conținut și rol | Când este gata |
| --- | --- | --- | --- |
| P0 | Exemplu canonic | Articol complet cu o tură reală și capturi desktop/mobil | Versiune identificată; hartă, profil și valori coerente; destinație publică verificată când se publică |
| P0 | README GitHub + WordPress.org | Beneficiu, rezultat vizual, primii pași, limite; intrare pentru dezvoltatori pe GitHub | Surse și rezultate sincronizate; fără linkuri demonstrative false sau promisiuni neacoperite |
| P0 | Kit WordPress.org | Două bannere, două iconuri, primele capturi și legende revizuite | Fișiere conforme și corespondență verificată cu exportul SVN |
| P1 | Clip principal | Propunere: 35–45 s, engleză, 16:9; tură → editare → rezultat → CTA | UI lizibil, înțeles fără sunet, export și credite verificate |
| P1 | Variantă scurtă `/brag` | 15–25 s; putem porni de la experimentul de 22 s | O singură promisiune și un singur CTA; variantă opțională în română pentru publicul personal |
| P1 | Pachet pentru dezvoltatori | Exemplu de hook cu efect vizibil, diagramă mică a integrării, demonstrație de 45–60 s sau articol scurt | Exemplul rulează pe ținta aleasă; codul se poate copia dintr-o pagină legată |
| P1 | Studiu de caz și imagini de distribuire | Povestea unei ture, ce vede cititorul, ce completează autorul; card cu hartă + fotografie | Text propriu și imagini din același caz; trimitere către demo sau instalare |
| P2 | Variante de canal | Vertical/square, thumbnail video, card de release, exemplu feroviar | Produse pentru un canal ales, cu text și cadre recompuse pentru el |

Nu este necesar un website nou pentru prima rundă. Pagina pluginului, GitHub și un articol demonstrativ oferă destinațiile inițiale. O pagină dedicată devine utilă dacă explicația produsului sau măsurarea traseului de instalare o cer.

## Direcția vizuală

Păstrăm logo-ul existent și folosim bleumarinul, menta și fonturile din proiectul video ca punct de plecare, după verificarea lizibilității și licențelor. Fotografiile personale aduc identitate și context. Capturile reale dovedesc funcționalitatea. Diagramele și exemplele de cod susțin integrarea.

Bannerul poate combina o fotografie panoramică discretă, numele pluginului și o frază scurtă despre trasee în WordPress. Iconul trebuie să rămână recognoscibil la dimensiune mică. În capturile de produs, harta și interfața primesc spațiul necesar citirii; decorul nu trebuie să le ascundă.

Păstrăm aceeași tură, denumire și statistică în materialele care pretind că o demonstrează. Fotografii din alte ture pot fi folosite ca atmosferă, dar nu ca dovadă a traseului afișat. [Brief-ul foto în română](photo-brief.ro.md) descrie concret ce să căutăm; [specificațiile vizuale în engleză](visual-assets.md) separă cerințele WordPress de dimensiunile recomandate pentru alte canale.

## Distribuire și măsurare

Începem cu destinațiile pe care le controlăm și în care publicul poate vedea produsul: README GitHub, pagina WordPress.org și blogul autorului. Propunem apoi distribuirea studiului de caz în comunități relevante de blogging, ciclism/drumeție și WordPress, acolo unde regulile permit prezentarea proiectului. Materialul pentru dezvoltatori trimite direct spre exemplul tehnic. Pregătirea textelor nu include postarea sau contactarea oamenilor.

| Obiectiv | Semnal accesibil | Cum îl interpretăm |
| --- | --- | --- |
| Utilizatorul înțelege produsul | 3–5 persoane pot explica după demo ce face și cui folosește | Feedback calitativ; nu dovadă statistică de conversie |
| Crește interesul pentru folosire | Clickuri către demo / WordPress.org din canalele unde există măsurare | Comparăm ferestre și surse similare; clickul nu este instalare |
| Prima utilizare reușește | Câțiva utilizatori finalizează un articol cu traseu; notăm punctele unde se blochează | Măsurare prin sesiuni voluntare sau feedback, fără a introduce telemetrie în plugin |
| Integrarea este inteligibilă | Un dezvoltator poate rula exemplul și explica hook-ul folosit | Urmărim întrebările și dificultățile concrete |
| Materialele ajută întreținerea | Tipurile de întrebări de suport înainte/după actualizare | Volumul și contextul contează; nu atribuim automat variația marketingului |

La pornire înregistrăm valorile disponibile și sursa lor; verificăm din nou după aproximativ 2 și 6 săptămâni de la publicare. Statisticile publice de download și active installs sunt orientative, nu atribuiri exacte ale unei campanii. Numărul de stele GitHub poate indica interes, dar nu înlocuiește folosirea pluginului. Nu programăm automat raportări și nu promitem ținte numerice fără o bază de comparație.

## Ordinea recomandată de lucru

1. Alegem versiunea și o tură reprezentativă; adunăm 8–12 fotografii candidate și GPS-ul, dacă există.
2. Pregătim articolul demonstrativ și verificăm datele; producem un set coerent de capturi.
3. Revizuim sursele README și kitul WordPress.org; verificăm destinațiile și preview-urile.
4. Producem clipul principal și varianta scurtă; folosim același caz pentru studiul de caz.
5. Demonstrăm o personalizare prin hook și pregătim intrarea pentru dezvoltatori.
6. Publicăm în destinațiile autorizate, măsurăm semnalele disponibile și ajustăm următorul pachet.

Fiecare release care schimbă UI-ul, funcțiile arătate sau compatibilitatea declanșează o revizuire a materialelor afectate. Păstrăm pentru fiecare export versiunea, sursele și data capturii, astfel încât actualizarea următoare să nu reînceapă de la zero.

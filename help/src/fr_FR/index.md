# Sommaire {#help-root}  

- [Détails généraux](#general-information)
- [Composant de visualisation](#viewer-component)
- [Composant d'édition](#editor-component)
- [Configuration et gestion](#configuration-management)

# Détails généraux {#general-information}

## Environ {#dg-about}

### Objectif du projet {#dg-project-purpose}

Le projet a commencé pour une raison aussi égoïste que possible: je voulais tenir un journal structuré sur les voyages que je fais.
Pendant le cours, cependant, j'ai réalisé que quelqu'un d'autre pourrait être en mesure d'aider, alors j'ai décidé de le publier en open source, avec une licence aussi restrictive que possible.
En général, le module gère les catégories suivantes:

- résumé technique - présente quelques détails prosaïques tels que: longueur, différence de niveau totale, type de terrain, etc.;
- Tracé GPS de l'itinéraire, affiché simplement sur une carte, avec deux points marqués: le point de départ (en vert) et le point d'arrivée (en rouge).

### Types de voyage {#dg-trip-types}

Dans le résumé technique, nous avons inclus les types d'informations suivants:

- données spécifiques pour les itinéraires cyclables;
- données spécifiques pour la randonnée (trekking / randonnée);
- dates de tournée spécifiques avec train.

En d'autres termes, vous pouvez documenter (uniquement) ces trois types de promenades.

### Principaux composants {#dg-main-plugin-components}

Selon les différentes fonctions exercées par les zones du module, nous pouvons discuter des composants suivants:

- Composant de visualisation: c'est la case affichée juste en dessous de l'article et qui présente toutes les informations mentionnées ci-dessus ([Détails ici](#viewer-component));
- Composant d'édition: il est joint au formulaire de création / édition d'article et permet la modification / suppression des données sur l'itinéraire parcouru ([Détails ici](#editor-component));
- Composant de configuration: il traite de la gestion des options générales, mais aussi de la gestion des nomenclatures (listes de valeurs prédéfinies qui peuvent être sélectionnées lorsque les données sur l'itinéraire sont terminées) ([Détails ici](#configuration-component)).

[Retour au sommaire](#help-root)

## Captures d'écran {#dg-screenshots}

#### Page de configuration
![Page de configuration]($helpDataDirUrl$/screenshots/admin-settings.png "Page de configuration")

#### Édition d'article - Lanceur de l'éditeur de résumé de voyage
![Édition d'article - Lanceur de l'éditeur de résumé de voyage]($helpDataDirUrl$/screenshots/admin-edit-launcher.png "Édition d'article - Lanceur de l'éditeur de résumé de voyage")

#### Édition d'article - Lanceur de l'éditeur de résumé de voyage (éditeur de blocs)
![Édition d'article - Lanceur de l'éditeur de résumé de voyage (éditeur de blocs)]($helpDataDirUrl$/screenshots/admin-edit-launcher-block.png "Édition d'article - Lanceur de l'éditeur de résumé de voyage (éditeur de blocs)")

#### Édition d'article - Résumé technique - Sélection du type d'itinéraire
![Modifier l'article - Résumé technique - Sélection du type d'itinéraire]($helpDataDirUrl$/screenshots/admin-edit-summary-empty.png "Article Modifier - Résumé technique - Sélection du type d'itinéraire")

#### Modification d'articles - Résumé technique - Informations sur les itinéraires cyclables
![Modification d'article - Résumé technique - Informations sur l'itinéraire cyclable]($helpDataDirUrl$/screenshots/admin-edit-summary-bike.png "Edition d'article - Résumé technique - Informations sur l'itinéraire cyclable")

#### Édition d'article - Résumé technique - Carte
![Édition d'article - Résumé technique - Carte]($helpDataDirUrl$/screenshots/admin-edit-map.png "Édition d'article - Résumé technique - Carte")

#### Item - Teaser d'en haut
![Article - Teaser d'en haut]($helpDataDirUrl$/screenshots/viewer-teaser-top.png "Article - Teaser d'en haut")

#### Article - Résumé technique
![Article - Résumé technique]($helpDataDirUrl$/screenshots/viewer-summary.png "Article - Résumé technique")

#### Article - Carte
![Article - Carte]($helpDataDirUrl$/screenshots/viewer-map.png "Article - Carte")

#### Liste des articles - Colonnes d'information
![Liste d'articles - Colonnes d'information]($helpDataDirUrl$/screenshots/post-listing-columns.png "Liste d'articles - Colonnes d'information")

[Retour au sommaire](#help-root)

## Exigences techniques {#dg-technical-requirements}

Pour exécuter ce module, vous avez besoin des éléments suivants:

- PHP version 5.6.2 ou ultérieure;
- MySQL version 5.7 ou ultérieure (avec prise en charge des données SIG);
- WordPress 5.0 ou version ultérieure;
- Extension PHP libxml;
- Extension PHP SimpleXml;
- extension PHP mysqli;
- Extension PHP mbstring - pas strictement contraignante, mais recommandée;
- Extension PHP zlib - pas strictement contraignante, mais recommandée.

En principe, toutes ces exigences sont vérifiées lors de l'installation et le processus s'arrête si elles ne sont pas respectées.

[Retour au sommaire](#help-root)

## Licence {#dg-licensing-terms}

Ce module est distribué sous la [Nouvelle licence BSD](https://opensource.org/licenses/BSD-3-Clause). Qu'est-ce que cela signifie:

- que vous pouvez l'utiliser absolument gratuitement et sans autre obligation matérielle ou financière;
- que vous pouvez le redistribuer gratuitement et sans autre obligation matérielle ou financière;
- que vous devez inclure une copie des conditions de licence où vous l'installez ou chaque fois que vous le redistribuez;
- qu'aucune garantie de bon fonctionnement, d'aucune sorte, ni implicite ni explicite n'est offerte.

[Retour au sommaire](#help-root)

## Mentions et remerciements {#dg-credits}

Le module WP-Trip-Summary utilise les bibliothèques suivantes:  

1. [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class) - une bibliothèque construite sur mysqli. Il est utilisé à la place de wpdb, le composant WordPress standard.
2. [MimeReader](http://social-library.org/) - où le détecteur de type MIME a été écrit par Shane Thompson.
3. [jQuery EasyTabs](https://github.com/JangoSteve/jQuery-EasyTabs) - plug-in jQuery pour organiser le contenu sur des onglets / onglets
4. [Select2](https://select2.org/) - plug-in jQuery utilisé pour la sélection de plusieurs éléments
5. [Leaflet](https://github.com/Leaflet/Leaflet) - composant de carte
6. [Machina](https://github.com/ifandelse/machina.js/tree/master) - une machine d'état implémentée en JavaScript
7. [NProgress](https://github.com/rstacruz/nprogress) - une librairie pour une présentation visuelle de l'avancement de diverses opérations
8. [Toastr](https://github.com/CodeSeven/toastr) - une bibliothèque JavaScript pour la présentation visuelle des notifications
9. [URI.js](https://github.com/medialize/URI.js) - une bibliothèque JavaScript pour la construction et l'interprétation des URI
10. [Visible](https://github.com/teamdf/jquery-visible) - le plug-in jQuery qui détermine si un élément se trouve ou non dans la zone visuelle de la fenêtre du navigateur
11. [blockUI](https://github.com/malsup/blockui/) - plug-in jQuery qui permet d'afficher les fenêtres modales
12. [kite](http://code.google.com/p/kite/) - moteur de modèle écrit en JavaScript, petit et simple
13. [Leaflet.MagnifyingGlass](https://github.com/bbecquet/Leaflet.MagnifyingGlass) - plug-in Leaflet qui ajoute une loupe à la carte: agrandissement d'une zone individuelle sur la carte
14. [Leaflet.fullscreen](https://github.com/Leaflet/Leaflet.fullscreen) - plug-in Leaflet qui permet d'afficher la carte en plein écran

[Retour au sommaire](#help-root)

# Composant de visualisation {#viewer-component}

Le composant de visualisation se compose de trois zones distinctes:

- le teaser supérieur;
- le teaser du bas;
- le coffret technique lui-même.

## Le teaser supérieur

Bien sûr, ce n'est pas un poste de qualité supérieure.
Il est affiché au-dessus du contenu de l'article, mais sous le titre et vise à guider le lecteur vers la boîte technique.
L'idée à partir de laquelle j'ai commencé est que tout le monde ne peut pas réaliser qu'il y a un tel outil sur la page et, si c'est le seul point d'intérêt, être dirigé où il veut.

[Retour au sommaire](#help-root)

## Le teaser du bas

Plus bas comme position, bien sûr, pas qualitatif.
Il est affiché sous le contenu de l'article, même sous la case technique.
Il n'est pas toujours affiché, mais uniquement lorsque le système détecte la possibilité que l'utilisateur saute par-dessus l'article et sert de moyen d'atteindre rapidement le début pour lire le bel ouvrage.

[Retour au sommaire](#help-root)

## Coffret technique

Il représente, bien sûr, la zone réelle où ils sont affichés, sur un onglet séparé:

- la carte avec l'itinéraire en surbrillance;
- informations techniques (distance, dénivelé, etc.).

Chacun de ces onglets s'affiche uniquement si les informations appropriées sont présentes.
S'il n'y a aucune information pour aucun des onglets, alors le composant entier est masqué, y compris les teasers.

La carte elle-même propose les outils suivants:

- Zoom avant (zoom avant) - se concentrer sur une zone particulière;
- Zoom arrière carte - aperçu;
- Affichage de la carte en plein écran;
- Téléchargement de piste GPX;
- Loupe (zoom avant sur une zone discrète / limitée de la carte).

Certaines de ces options peuvent être désactivées à l'aide du composant de gestion, comme décrit ci-dessous.

[Retour au sommaire](#help-root)

# Composant d'édition {#editor-component}

Le composant d'édition vous permet de modifier le résumé technique du tour et d'attacher la trace GPS.
Ainsi, comme le composant de visualisation, il est organisé en deux onglets, un pour chaque catégorie de données:

- le formulaire de modification des informations techniques;
- Zone de chargement et d'aperçu de la trace GPS.

## Lanceur de l'éditeur de résumé de voyage

Le lanceur de l'éditeur de résumé de voyage d'introduction sert à intégrer le composant d'édition dans la station de travail de WordPress.
Il est présenté sous la forme d'une métabox, placée dans la barre latérale de l'écran de post-édition, intitulée: `Résumé du voyage`.
Permet un accès rapide aux informations et actions pertinentes suivantes:

- si le poste actuel dispose ou non d'informations techniques sur l'itinéraire:
- marqué d'une coche blanche sur un fond vert rond si oui;
- marqué d'un X blanc sur fond rouge rond, sinon.
- si le poste actuel est associé à une trace GPS:
- marqué d'une coche blanche sur un fond vert rond si oui;
- marqué d'un X blanc sur fond rouge rond, sinon.
- supprimer rapidement les informations techniques sur l'itinéraire attaché au message actuel (via le bouton `Actions rapides`);
- suppression rapide de la trace GPS attachée au poste actuel (via le bouton `Actions rapides`);
- télécharger la trace GPS attachée au poste actuel (via le bouton `Actions rapides`);
- ouvrir le composant d'édition lui-même pour le poste actuel (via le bouton `Modifier`).

[Retour au sommaire](#help-root)

## Le formulaire de la modification des informations techniques

L'onglet approprié est simplement appelé "Informations".
S'il n'est pas terminé, il est rempli de trois boutons, un pour chaque type de tour pris en charge:

- "A vélo" - permet de compléter l'ensemble des informations spécifiques aux circuits à vélo;
- "Per-pedes" - permet de compléter l'ensemble d'informations spécifiques pour la randonnée (trekking / randonnée);
- "En train" - permet de compléter l'ensemble des informations touristiques spécifiques au train.

Une fois que vous avez appuyé sur l'un de ces boutons, le formulaire réel s'affiche, selon le type de circuit choisi.

Il convient de mentionner que, quelle que soit la forme, si l'un des champs qui nécessitent des valeurs dans sa nomenclature n'a pas une telle valeur définie, un lien vers la page de gestion de cette nomenclature sera affiché.

En plus du formulaire, en bas de l'écran, vous trouverez deux boutons de contrôle, visibles après le choix d'un type d'itinéraire:

- "Enregistrer" - doit être appliqué pour les changements persistants;
- "Supprimer les informations" - doit être appliqué lorsque vous souhaitez supprimer l'ensemble des informations techniques.

[Retour au sommaire](#help-root)

## Formulaire de téléchargement et de prévisualisation de la trace GPS

L'onglet approprié est simplement appelé "Carte et trace GPS".
Si aucune piste n'a encore été choisie, elle est remplie d'un seul bouton, ce qui vous permet de parcourir votre ordinateur personnel pour choisir le fichier GPX souhaité à joindre.

Une fois la trace chargée, la carte sera centrée et son zoom ajusté pour que tout le circuit soit visible. Les fichiers GPX qui contiennent des segments hors ligne sont également pris en charge.

En plus du formulaire, deux boutons de contrôle se trouvent en bas de l'écran:

- "Enregistrer" - doit être appliqué pour la persistance des modifications (visible uniquement si un type d'itinéraire a été choisi);
- "Supprimer la piste" - doit être appliqué lorsque vous souhaitez supprimer la piste entière (visible, bien sûr, uniquement après le téléchargement d'une piste).

[Retour au sommaire](#help-root)

# Configuration et gestion {#configuration-management}

- [Options génériques](#configure-general-options)
- [Gestion de la nomenclature](#configure-lookup-data)

Les éléments de configuration et de gestion sont réglés pour offrir une certaine flexibilité dans l'utilisation du logiciel. Ainsi, nous pouvons parler de deux grandes et larges directions de flexibilité:

- options génériques (unités de mesure, activation / désactivation d'outils ou d'éléments d'interface, etc.);
- gestion de la nomenclature (c'est-à-dire les jeux d'options prédéfinis à partir desquels certains champs sont remplis, comme le niveau de difficulté).

## Options génériques {#configure-general-options}

Il existe une page dédiée à partir de laquelle les options génériques peuvent être modifiées. Vous pouvez y accéder depuis le menu principal en allant sur: `WP Trip Summary -> Paramètres`.

Une fois arrivé ici, il y a les points de configuration suivants.

#### Système d'unités de mesure.

Vous pouvez soit le système métrique (m / km), soit le système impérial (miles / pouce). 
Il est très important de noter qu'aucun calcul de conversion n'est effectué et il est supposé que lors de la saisie d'un résumé des valeurs, les valeurs sont déjà exprimées dans le système choisi ici.

#### Affichage ou non du teaser.

Une fois que vous avez effacé le champ et enregistré vos modifications, le teaser sur la page de l'article sera masqué.

#### Le texte du teaser supérieur

Le texte d'accroche affiché au-dessus de l'article. Il y a déjà un preset.

#### Le texte du teaser du bas

Le texte du teaser s'affiche immédiatement sous la zone de résumé. Il y a déjà un preset.

#### Modèle d'URL source de tuile de carte

Ici, la discussion est un peu plus longue.

Tout d'abord, il convient de rappeler que la carte utilisée n'est pas une seule image, mais se compose de plusieurs images carrées, appelées tuiles qui, placées côte à côte, forment l'ensemble de la carte.

Il existe plusieurs ensembles de tuiles, une pour chaque niveau de zoom, et chaque tuile est caractérisée par deux coordonnées - disons x et y - à peu près comme un échiquier.

Donc, pour accéder à une tuile, nous avons besoin des informations suivantes:

- z - niveau de zoom;
- x - position horizontale;
- y - la position verticale.

Mais il y a un autre problème: télécharger autant d'images en un temps aussi rapide que possible, parfois tout ce méga-ensemble de tuiles est copié de manière redondante sur plusieurs machines - serveurs.
Ces machines sont également numérotées / nommées - disons, par exemple, 1, 2, 3, 4, etc.
L'idée ici est que je peux - au lieu de les demander au même endroit - je peux demander une partie de la voiture 1, une partie de la voiture 2, etc., mais cela, je le répète, n'est pas un comportement obligatoire.

Pour rassembler tout cela, afin de télécharger des tuiles de carte à partir d'une source autre que celle par défaut - OpenStreetMaps - vous avez le champ en question maintenant.
Et ce modèle offre les marquages ​​spéciaux suivants:

- {s} - pour spécifier où le numéro de machine est entré (par exemple {s} .tile.osm.org se traduirait par 1.tile.osm.org, 2.tile.osm.org);
- {z} - pour spécifier où le numéro du niveau de zoom est entré;
- {x} - pour spécifier où la position horizontale de la tuile est insérée;
- {y} - pour spécifier où la position verticale de la tuile est insérée.

Les signets peuvent être combinés de toute façon et vous devrez consulter la documentation du fournisseur à partir duquel vous souhaitez afficher la carte.

#### L'URL de la page de copyright de la source de tuile de carte

Selon l'endroit où vous choisissez d'utiliser la carte, la zone de copyright peut être obligatoire ou non. C'est en tout cas une question de bon sens, je vous encourage donc à le surveiller.
L'emplacement se trouve dans le coin inférieur droit de la carte, et le champ en question vous permet maintenant d'entrer un lien vers le fournisseur.

#### Avis de droit d'auteur pour la source des tuiles de carte

Selon l'endroit où vous choisissez d'utiliser la carte, la zone de copyright peut être obligatoire ou non. C'est en tout cas une question de bon sens, je vous encourage donc à le surveiller.
L'emplacement se trouve dans le coin inférieur droit de la carte, et le champ en question permet désormais de saisir la note de copyright réelle.

#### Est-il possible de basculer la carte en plein écran?

Si vous effacez ce champ, le bouton de carte plein écran ne s'affiche plus dans la zone technique de la page de l'article.
Par défaut, le champ est coché, donc le bouton est affiché.

#### La loupe est-elle activée?

Si vous effacez ce champ, le bouton qui active la loupe ne sera plus affiché dans la boîte technique de la page de l'article.
Par défaut, le champ est coché, donc le bouton est affiché.

#### L'échelle de la carte s'affiche

Si vous effacez ce champ, l'échelle de la carte (en bas à gauche) ne sera plus affichée dans la zone technique de la page de l'article.
Par défaut, le champ est coché, donc l'échelle de la carte est affichée.

#### Le téléchargement des morceaux est-il autorisé?

Si vous effacez ce champ, le bouton qui vous permet de télécharger la piste GPX ne sera plus affiché dans la boîte technique de la page de l'article.
Par défaut, le champ est coché, donc le bouton est affiché.

#### La couleur de la ligne de trace sur la carte

Ce champ vous permet de définir la couleur avec laquelle la trace GPX est dessinée sur la carte.
S'applique à la fois au visualiseur frontal et à l'éditeur principal.
La valeur par défaut est: # 0033ff.

#### L'épaisseur de la ligne de trace sur la carte

Ce champ vous permet de définir l'épaisseur de ligne (en pixels) avec laquelle la trace GPS est représentée sur la carte.
S'applique à la fois au visualiseur frontal et à l'éditeur principal.
La valeur par défaut est: 3 pixels.

[Retour au sommaire](#help-root)

## Gestion de la nomenclature {#configure-lookup-data}

Les nomenclatures sont des ensembles d'options prédéfinis à partir desquels remplir certains champs.
Les valeurs de ces options peuvent être modifiées en fonction de la langue choisie.
Toutes les langues prises en charge par WordPress sont disponibles, plus la possibilité de définir une valeur par défaut.
La valeur par défaut d'une option sera affichée lorsqu'aucune traduction n'est trouvée pour cette option pour la langue actuelle du blog.

### Champs gérés

Les domaines pour lesquels la gestion de la nomenclature est requise sont:

- Niveau de difficulté;
- Saisons recommandées;
- Types de surface de route / chemin;
- Types de vélos recommandés;
- Opérateurs ferroviaires;
- état de la ligne;
- électrification;
- Type de ligne.

#### Niveau de difficulté

Il est disponible pour les types de parcours:

- En vélo;
- Pieds.

Il représente évidemment l'évaluation subjective de chacun de la difficulté de l'itinéraire.
Ce module propose les options prédéfinies suivantes (traduites en roumain, en anglais et française):

- Facile;
- Moyen;
- Difficile;
- Torture médiévale.

#### Saisons recommandées

Il est disponible pour les types de parcours:

- En vélo;
- Pieds.

Il représente évidemment les saisons au cours desquelles il est physiquement possible de parcourir l'itinéraire dans des conditions décentes (c'est-à-dire sans effort excessif et sans vous exposer à un danger imminent).
Le module propose les options prédéfinies suivantes (traduites en roumain, en anglais et française):

- Printemps;
- Été;
- Automne;
- Hiver.

#### Types de surface de route / chemin

Il est disponible pour les types de parcours:

- En vélo;
- Pieds.

Représente les textures / compositions des revêtements routiers rencontrés. Ex: herbe, rochers, asphalte, macadam, etc.
Le module propose les options prédéfinies suivantes (traduites en roumain, en anglais et française):

- Asphalte;
- Dalles de béton;
- Terre;
- Végétation;
- Macadam/gravier;
- Pierre déstabilisé.

#### Types de vélos recommandés

Il est disponible pour les types de parcours:

- A vélo.

Il représente les types de vélos qui peuvent être utilisés pour parcourir en toute sécurité et confortablement l'itinéraire (relatif) décrit.
Le module propose les options prédéfinies suivantes (traduites en roumain, en anglais et française):

- VTT;
- Courrier;
- Trekking;
- Vélo de ville.

#### Exploitants ferroviaires

Il est disponible pour les types de parcours:

- En train.

Il représente les entreprises qui organisent des courses le long du parcours, soit sur toute sa longueur, soit seulement partiellement.
Le module n'offre pas d'options prédéfinies.

#### Type de ligne

Il est disponible pour les types de parcours:

- En train.

Décrit si la ligne est simple ou double.
Le module propose les options prédéfinies suivantes (traduites en roumain, en anglais et française):

- Une seule ligne (une seule ligne);
- Double ligne (deux voies de circulation, une pour chaque direction).

#### État de la ligne

Il est disponible pour les types de parcours:

- En train.

Il représente l'état de la ligne sur l'itinéraire.
Le module propose les options prédéfinies suivantes (traduites en roumain, en anglais et française):

- En fonctionnement (les courses normales sont exploitées);
- Fermé (ne fonctionne plus, mais la ligne est en conservation ou existe toujours);
- Annulé (la ligne n'existe plus, étant totalement ou partiellement démantelée);
- En rééducation (la ligne est en cours de rééducation; le programme de circulation peut être limité / modifié).

#### Électrification

Il est disponible pour les types de parcours:

- En train.

Il représente l'état des travaux d'électrification de la ligne.
Le module propose les options prédéfinies suivantes (traduites en roumain, en anglais et française):

- Électrifié;
- Non électrifié;
- Partiellement électrifié.

### Opérations disponibles

Les opérations suivantes sont disponibles, chacune dans le contexte d'une langue choisie:

- Ajout d'un nouvel article dans une nomenclature;
- Supprimer un article existant d'une nomenclature;
- Modification d'un élément existant;
- Liste des articles existants dans une nomenclature.

Il convient de noter que lors de l'ajout d'un élément pour la langue par défaut, le système ne demande l'étiquette que pour lui.
D'autre part, lors de l'ajout d'un élément pour une langue particulière (par exemple le roumain, l'anglais, etc.), le système demande l'étiquette pour la langue par défaut et la langue sélectionnée.

[Retour au sommaire](#help-root)
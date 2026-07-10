# EduOS Cameroon — Sauvegardes environnementales et sociales, inclusion et sensibilité aux conflits

| | |
|---|---|
| Identifiant du document | EDUOS-ESS-001 |
| Version | 1.0 |
| Cadre | Cadre environnemental et social (CES) de la Banque mondiale ; exigences d'équité du GPE ; droit national camerounais de l'environnement |
| Statut | Annexe de pré-évaluation ex ante — devient la base des instruments formels ESCP/SEP (plan d'engagement environnemental et social / plan de mobilisation des parties prenantes, PMPP) préparés avec le spécialiste E&S du financier lors de l'évaluation ex ante |

*Traduction française du document original anglais [12-Environmental-Social-Safeguards-and-Inclusion](../12-Environmental-Social-Safeguards-and-Inclusion.md). En cas de divergence, la version anglaise validée fait foi.*

## 1. Classification du risque E&S (proposée)

**Modéré.** Le programme est une infrastructure numérique sans travaux de génie civil, sans acquisition foncière et sans réinstallation — mais il déploie plus de 18 500 appareils à batterie à l'échelle nationale (DEEE, ESS3), traite des données personnelles d'enfants (dimension sécurité des communautés de l'ESS4), mobilise des milliers de travailleurs et de personnes en formation (ESS2) et opère dans des régions touchées par le conflit (risque contextuel). Normes environnementales et sociales (NES) pertinentes : **ESS1** (évaluation), **ESS2** (main-d'œuvre), **ESS3** (utilisation rationnelle des ressources et prévention de la pollution — DEEE), **ESS4** (santé et sécurité des populations — protection des données, ciblage lié aux appareils), **ESS10** (mobilisation des parties prenantes et gestion des plaintes).

## 2. Plan de gestion des DEEE et des batteries (ESS3) — la lacune physique

**Le problème, quantifié :** 18 500 appareils scolaires + 420 kits de bureau + 3 500 kits solaires, avec un taux de remplacement de 15 %/an (Risque R9) ≈ **plus de 2 800 appareils/an** arrivant en fin de vie en régime de croisière, contenant pour la plupart des batteries au lithium, répartis dans le Cameroun rural, dépourvu de tout système de collecte des déchets ménagers dangereux.

**Le plan — logistique inverse sur le canal que nous possédons déjà :** le flux de remplacement des appareils (BUD §3.3) s'appuie sur le même réseau départemental de points de synchronisation que tout le reste. Règles :

1. **Pas d'échange sans retour.** Un appareil de remplacement n'est délivré que contre remise de l'unité hors service (enregistrée dans le module d'actifs de la plateforme elle-même — les appareils sont eux aussi des actifs dotés d'un passeport). Les unités en panne sont conservées dans un conteneur verrouillé dans les bureaux départementaux (la même discipline de stockage sécurisé que pour les manuels scolaires).
2. **Consolidation et traitement agréé.** Les départements expédient les unités accumulées vers les points de collecte régionaux deux fois par an, sur les trajets retour des camions de manuels scolaires (coût de transport marginal ≈ zéro — les camions reviennent à vide). Contrat national avec un opérateur agréé de traitement des DEEE (p. ex. des recycleurs de catégorie DEEE opérant à Douala ; étude de marché en Phase 0) pour les batteries et les cartes électroniques ; le ministère reçoit des certificats de destruction/recyclage par lot.
3. **Prévention en amont, côté passation des marchés :** l'appel d'offres appareils (ADR-11) DOIT noter la remplaçabilité des batteries et les engagements de reprise des fournisseurs ; la reprise par le fournisseur, lorsqu'elle est proposée, prime sur le traitement local.
4. **La fin de vie des kits solaires** suit le même canal (les panneaux et régulateurs de charge ont une durée de vie de 10 ans et plus ; les batteries, de 3 à 5 ans).
5. **Budget :** la collecte est à coût marginal par conception ; le contrat de traitement est estimé à ≤ 0,01 mrd FCFA/an et absorbé par la ligne de coûts récurrents de remplacement des appareils (BUD §4).
6. **KPI :** ≥ 90 % des appareils remplacés comptabilisés dans le canal de logistique inverse (auditable — ce sont des actifs enregistrés). Rapporté annuellement aux côtés des indicateurs de suivi-évaluation (S&E).

## 3. Inclusion — genre et handicap : des exigences, pas des sentiments

### 3.1 Obligations d'analyse (Phase 0 / pilote)
- Les études de la situation de référence du pilote (MIP §4-F) DOIVENT être désagrégées par sexe : ratios d'accès aux manuels scolaires filles/garçons, taux de conservation/retour, et répartition par sexe des rôles de chef d'établissement/magasinier (les personnes que nous dotons d'identifiants et que nous formons).
- La question d'équité de type GPE — les écoles de filles, les écoles rurales et les départements défavorisés bénéficient-ils d'une couverture égale en manuels scolaires ? — trouve sa réponse **dans la plateforme elle-même** (couverture par école × caractéristiques de l'école) ; rendre cela visible est une exigence produit explicite (ci-dessous).

### 3.2 Exigences produit (ajoutées au référentiel FRS, voir l'ancrage au §6)
- **INC-01** Tous les indicateurs de couverture du S&E et le rapport RPT-COV DOIVENT permettre la désagrégation par caractéristiques d'école (non mixte/mixte, urbain/rural, région) et, là où l'affectation au niveau de l'élève est active, par sexe de l'élève.
- **INC-02** L'application scolaire DOIT satisfaire une accessibilité de base : compatibilité complète TalkBack/lecteur d'écran, cibles tactiles d'au moins 48 dp, contraste WCAG 2.1 AA (la palette du système de conception est vérifiée à cette aune) et aucun flux de travail exigeant la lecture de texte en anglais ou en français pour aboutir — chaque action critique dispose d'un parcours icône + libellé court (utilisabilité en contexte de faible alphabétisation).
- **INC-03** Les cohortes de formation DOIVENT inclure du personnel féminin au minimum proportionnellement à sa part dans la direction des écoles, suivi via OUT-P5 ; les lieux et horaires de formation sont choisis pour que le personnel féminin puisse réellement y assister (en journée, à proximité).
- **INC-04** Le statut de handicap de l'élève, lorsque le ministère l'enregistre, est un attribut protégé : utilisable pour l'analyse de l'équité de couverture (les éditions en braille/gros caractères sont-elles là où elles devraient être ? — la structure d'éditions du modèle de données du NTR prend déjà en charge les éditions en format adapté), jamais affiché sur les écrans opérationnels.

## 4. Sensibilité aux conflits (NW/SW et Extrême-Nord) — analyse selon le principe de « ne pas nuire »

Le Risque R6 place ces régions en dernier pour des raisons d'accès ; la présente section traite la question du préjudice que la v1.0 n'avait jamais posée : **la présence du programme peut-elle mettre en danger des écoles ou des personnels ?**

| Menace | Analyse | Atténuation (contraignante pour la planification du déploiement) |
|---|---|---|
| Une tablette aux couleurs du gouvernement désigne une école/un chef d'établissement comme collaborateur du gouvernement | Risque réel dans le NW/SW, où des écoles ont été attaquées précisément en tant que symboles de l'État | Les appareils dans les zones affectées ne portent **aucun marquage gouvernemental** (matériel neutre, apparence grand public standard) ; le palier robuste est une marque grand public courante (ADR-11), ce qui aide ; les chefs d'établissement choisissent entre une garde de l'appareil à l'école ou par la personne |
| La valeur marchande de l'appareil crée un risque de vol avec violence lors des trajets de synchronisation | Modéré — atténué par le fait que l'appareil est enregistré et effaçable à distance (faible valeur de revente si cela est connu) | Les écrans de verrouillage MDM indiquent que l'appareil est enregistré et traçable ; les trajets de synchronisation peuvent être combinés aux déplacements administratifs existants plutôt que de créer de nouveaux schémas de déplacement prévisibles ; aucun argent liquide ne voyage jamais avec l'appareil |
| Les données présentes sur l'appareil identifient les personnels/élèves auprès d'acteurs hostiles | Le magasin SQLite contient les noms des personnels et (plus tard) des élèves | Chiffrement de l'appareil au repos (Android FBE) + code PIN ADR-10 ; le mode d'affectation au niveau de la classe (et non de l'élève) est le **mode par défaut dans les zones de conflit désignées** — un indicateur de politique défini par école, déjà pris en charge par le repli FR-NTR-SM-02 |
| La présence du programme est instrumentalisée politiquement (« le gouvernement ne livre que là où l'on est loyal ») | La transparence des allocations joue dans les deux sens | Les API publiques de catalogue/couverture rendent l'équité des allocations *vérifiable* — publier la couverture par région sans détail permettant d'identifier les écoles dans les zones affectées |
| Abus du mode d'urgence (des contrôles allégés en zone de crise devenant le canal de déperdition) | La FR-NWD-12 exige déjà une garde nominative, même en mode d'urgence | Les expéditions en mode d'urgence font l'objet d'un échantillonnage d'audit a posteriori *renforcé* par le vérificateur indépendant, et non allégé |

Une évaluation formelle de sensibilité aux conflits, réalisée par un spécialiste disposant d'un accès terrain au NW/SW, est un **livrable de la Phase 0** (ajouté à la PRC), et le déploiement dans les régions affectées requiert l'examen de cette évaluation par le Comité de pilotage — et non le seul test logistique du R6.

## 5. Mobilisation des parties prenantes et gestion des plaintes (ESS10)

- **Squelette du plan de mobilisation des parties prenantes (PMPP) :** syndicats d'enseignants et associations de parents d'élèves (consultés avant que la sélection des régions pilotes ne soit définitive) ; éditeurs/imprimeurs (la Phase 0-H les mobilise déjà) ; parents (kit de communication pour les réunions d'école, prévu au budget communication BUD §3.6) ; administrations régionales/départementales (les ateliers de la campagne de données font également office de mobilisation).
- **Mécanisme de gestion des plaintes (MGP) :** le canal d'assistance (helpdesk) L2 (MIP §5 volet 3) sert aussi de guichet de réception des plaintes (numéro vert + WhatsApp), avec une catégorie « plainte » distincte, un standard de réponse de 15 jours ouvrés, un rapport trimestriel sur les plaintes au Comité de pilotage et une voie d'escalade indépendante de la PMU (vers le président du Comité de pilotage) pour les plaintes visant la PMU elle-même. Les soumissions anonymes sont acceptées — un point pertinent pour le signalement des déperditions (un magasinier signalant un détournement ne doit pas avoir à s'identifier).
- **Main-d'œuvre (ESS2) :** les formateurs et agents de la campagne de données sont engagés sous conditions écrites, avec les per diem que le budget prévoit déjà ; un code de conduite (incluant la protection de l'enfance — ces personnes entrent dans les écoles) est signé par chaque agent de terrain ; une clause de protection de l'enfance est obligatoire dans tous les contrats de sous-traitance.

## 6. Ancrage dans le dossier (traçabilité)

| Élément du présent document | Aboutit dans |
|---|---|
| Logistique inverse des DEEE | Spécification de l'appel d'offres appareils (PRC 2.6) ; note de budget des coûts récurrents (BUD §4) ; rapport annuel des KPI E&S |
| INC-01..04 | Ajouts de NFR à la FRS (04/07/08 v1.1) ; règle de désagrégation du S&E (note MEF §2) ; cibles de formation (OUT-P5) |
| Évaluation de sensibilité aux conflits | Nouvel élément PRC (Phase 0) ; condition de jalon pour les vagues de la Phase III dans les régions affectées ; atténuation R6 mise à jour |
| PMPP + gestion des plaintes | Formalisés avec le spécialiste E&S du financier lors de l'évaluation ex ante ; double rôle de l'assistance (helpdesk) dans MIP §5 volet 3 |
| Main-d'œuvre ESS2 et protection de l'enfance | Contrats de formation et de campagne de données (BUD §3.4/3.5) ; modèles de contrats de sous-traitance des fournisseurs |

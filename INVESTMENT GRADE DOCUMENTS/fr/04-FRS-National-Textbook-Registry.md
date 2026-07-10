# EduOS Cameroon — Spécification des Exigences Fonctionnelles

## Module 3 : Registre National des Manuels Scolaires et Passeport Numérique (NTR)

| | |
|---|---|
| Identifiant du document | EDUOS-FRS-NTR-001 |
| Version | 1.0 (Référence exécutable) |
| Statut | Projet soumis à l'examen des ministères |
| Remplace | Spécification narrative du chapitre 19 (Volume II) |
| Public visé | Comités techniques MINESEC/MINEDUB, prestataires soumissionnaires, ingénieurs de mise en œuvre |

*Traduction française du document original anglais [04-FRS-National-Textbook-Registry.md](../04-FRS-National-Textbook-Registry.md). En cas de divergence, la version anglaise validée fait foi.*

Le présent document remplace la spécification narrative du chapitre 19 par des exigences numérotées et testables, un modèle de données normatif, des contrats d'API et des critères d'acceptation. Un prestataire doit pouvoir soumissionner sur la base de ce document ; une équipe d'ingénierie doit pouvoir construire le système et être soumise aux tests d'acceptation sur cette même base.

**Les mots-clés d'exigence** suivent la RFC 2119 : DOIT (SHALL, obligatoire), DEVRAIT (SHOULD, recommandé), PEUT (MAY, optionnel). Chaque exigence DOIT dispose d'au moins un critère d'acceptation (AC) et est traçable dans la Matrice de Traçabilité des Exigences (§13).

---

## 1. Périmètre

Le Registre National des Manuels Scolaires (National Textbook Registry, NTR) est le registre national faisant autorité pour chaque titre de manuel scolaire approuvé pour usage dans les écoles camerounaises et — lorsque la politique le prescrit — un passeport numérique par exemplaire pour les exemplaires physiques de manuels scolaires. Il dessert le MINEDUB (primaire) et le MINESEC (secondaire) au sein d'un modèle de données unique avec une gouvernance délimitée par ministère.

**Dans le périmètre :** enregistrement des titres et flux de travail d'approbation ; gestion des éditions/versions ; correspondance avec les curricula ; le schéma d'Identifiant National de Manuel Scolaire (NTID) ; passeports par exemplaire avec identifiants QR/code-barres ; suivi des états du cycle de vie ; historique de l'état physique ; données d'entrée pour les prévisions de remplacement ; publication du catalogue ; transactions scolaires utilisables hors ligne ; API consommées par les services Passation des Marchés, Entrepôt, Distribution, Opérations Scolaires, Registre des Élèves et Analytique.

**Hors périmètre (relevant d'autres modules) :** bons de commande et contrats (Service de Passation des Marchés), opérations physiques d'entrepôt (NWIDMS), identité des élèves (Registre National des Élèves), hébergement de contenus numériques de livres électroniques (NEDIH).

## 2. Définitions

| Terme | Définition |
|---|---|
| Titre | Une œuvre de manuel scolaire approuvée (p. ex. « Mathematics for Form 1, 3rd Edition ») |
| Édition | Une révision versionnée d'un Titre liée à une version de curriculum |
| Exemplaire | Un livre physique imprimé, identifié individuellement |
| Lot | Un groupe d'exemplaires issus d'un même tirage, unité minimale de suivi lorsque le suivi par exemplaire n'est pas justifié en termes de coûts |
| Passeport | L'historique immuable des événements attaché à un Exemplaire ou à un Lot |
| NTID | Identifiant National de Manuel Scolaire (au niveau du titre) |
| NCID | Identifiant National d'Exemplaire (au niveau de l'exemplaire) |

## 3. Schémas d'identifiants (normatif)

### 3.1 NTID — niveau titre

Format : `CM-TB-{MIN}-{SUBJ}-{GRADE}-{LANG}-{SEQ}-{ED}`

| Segment | Valeurs | Exemple |
|---|---|---|
| MIN | `B` (MINEDUB) / `S` (MINESEC) | S |
| SUBJ | Code matière à 3 lettres issu du référentiel national des matières (Annexe A) | MAT |
| GRADE | `P1`–`P6`, `F1`–`F5`, `LS`/`US` (lower/upper sixth), `C1`–`C4` (CAP/technique) | F1 |
| LANG | `EN`, `FR`, `BI` | EN |
| SEQ | Séquence à 4 chiffres par quadruplet (MIN,SUBJ,GRADE,LANG) | 0007 |
| ED | Numéro d'édition à 2 chiffres | 03 |

Exemple : `CM-TB-S-MAT-F1-EN-0007-03`.

- **FR-NTR-ID-01** Le système DOIT générer les NTID automatiquement lors de l'approbation du titre ; les NTID DOIVENT être immuables et ne jamais être réutilisés, y compris pour les titres retirés.
- **FR-NTR-ID-02** Une nouvelle édition d'un titre existant DOIT conserver tous les segments à l'exception de `ED`, qui s'incrémente.

### 3.2 NCID — niveau exemplaire

Format : `{NTID}-{BATCH:5}-{COPY:6}` encodé sous forme de code QR (avec une ligne de repli lisible par l'humain) imprimé sur une étiquette adhésive durable ou imprimé sur la couverture du livre au moment de l'impression.

- **FR-NTR-ID-03** Les NCID DOIVENT intégrer un chiffre de contrôle (ISO/IEC 7064 MOD 37-2) afin que la saisie manuelle d'une étiquette endommagée puisse être validée hors ligne.
- **FR-NTR-ID-04** La charge utile du QR DOIT être la chaîne NCID brute (sans URL), ≤ 64 caractères, encodable en version QR 2 (25×25) pour une lecture fiable par les caméras Android d'entrée de gamme.
- **FR-NTR-ID-05** Lorsque la politique ministérielle retient le suivi au niveau du lot pour un titre (indicateur de politique sur l'enregistrement Titre), le système DOIT suivre à la granularité du lot et NE DOIT PAS exiger de scans par exemplaire pour ce titre.

## 4. Modèle de données (normatif)

Vue d'ensemble entités-relations :

```
Title 1──N Edition 1──N PrintBatch 1──N Copy
Edition N──1 CurriculumVersion
Copy 1──N PassportEvent          (append-only)
Copy N──1 Location               (warehouse | school | in-transit | learner)
Copy 0..1──1 StudentAssignment   (active at most one)
```

### 4.1 Title

| Champ | Type | Obl. | Notes |
|---|---|---|---|
| ntid | string(32) PK | ✔ | §3.1, immuable |
| title_en / title_fr | string(300) | ✔ (≥1) | bilingue |
| ministry | enum {MINEDUB, MINESEC} | ✔ | |
| subject_code | FK référentiel des matières | ✔ | Annexe A |
| grade_code | enum | ✔ | §3.1 |
| language | enum {EN, FR, BI} | ✔ | |
| publisher_id | FK Registre des Éditeurs | ✔ | |
| isbn | string(17) | ○ | ISBN-13 validé lorsqu'il est présent |
| tracking_granularity | enum {COPY, BATCH} | ✔ | BATCH par défaut ; défini par la politique |
| approval_ref | string(100) | ✔ | numéro de la décision ministérielle |
| approval_date | date | ✔ | |
| status | enum {DRAFT, APPROVED, SUSPENDED, RETIRED} | ✔ | machine à états §5.1 |
| expected_service_life_years | int (1–10) | ✔ | 3 par défaut |
| unit_cost_fcfa | decimal | ✔ | coût de référence pour la planification |
| pages, weight_g, binding | int/int/enum | ○ | planification logistique |

### 4.2 Edition
`edition_id PK, ntid FK, edition_no, curriculum_version_id FK, effective_academic_year, retirement_academic_year?, changes_summary`

### 4.3 PrintBatch
`batch_id PK, edition_id FK, procurement_contract_ref, printer_id FK Supplier Registry, quantity, print_date, qa_status enum{PENDING, PASSED, FAILED, PASSED_WITH_DEVIATION}, qa_report_ref`

### 4.4 Copy
`ncid PK, batch_id FK, lifecycle_state enum (§5.2), current_location_type enum{WAREHOUSE, TRANSIT, SCHOOL, LEARNER, DISPOSED}, current_location_id, condition enum{NEW, GOOD, FAIR, POOR, UNUSABLE}, condition_updated_at, school_year_received`

### 4.5 PassportEvent (en ajout seul (append-only), à intégrité vérifiable)

| Champ | Type | Notes |
|---|---|---|
| event_id | UUIDv7 PK | ordonné dans le temps |
| ncid / batch_id | FK (l'un des deux requis) | |
| event_type | enum (transitions §5.2 + CONDITION_ASSESSED, AUDITED, LOST_REPORTED, FOUND) | |
| occurred_at | timestamptz | heure de l'appareil |
| recorded_at | timestamptz | heure du serveur à la synchronisation |
| actor_user_id | FK IAM | |
| actor_role | string | dénormalisé pour l'audit |
| location_type/location_id | | |
| payload | jsonb | spécifique à l'événement (condition, student_id, référence de transfert…) |
| device_id | FK | provenance hors ligne |
| prev_event_hash | sha256 | chaîne de hachage par exemplaire (FR-NTR-AUD-02) |

- **FR-NTR-DM-01** PassportEvent DOIT être en ajout seul (append-only) : aucun droit UPDATE ou DELETE n'existe pour aucun rôle applicatif ; les corrections sont effectuées par des événements compensatoires (`event_type=CORRECTION`, avec une charge utile référençant l'événement corrigé).
- **FR-NTR-DM-02** Chaque PassportEvent DOIT stocker le SHA-256 de l'événement précédent pour le même exemplaire, formant une chaîne de hachage vérifiable. Un traitement nocturne DOIT vérifier les chaînes et déclencher une alerte d'audit en cas de rupture.
- **FR-NTR-DM-03** Toutes les entités DOIVENT porter `created_at, created_by, updated_at, updated_by, sync_origin (CENTRAL | device_id)`.

## 5. Machines à états (normatif)

### 5.1 Statut du Titre
`DRAFT → APPROVED → {SUSPENDED ⇄ APPROVED} → RETIRED`. RETIRED est terminal. Seuls les utilisateurs détenteurs du droit `curriculum.approve` peuvent exécuter `DRAFT→APPROVED` et `→RETIRED` ; les deux exigent une chaîne de référence ministérielle.

### 5.2 lifecycle_state de l'Exemplaire

```
PRINTED → IN_WAREHOUSE → IN_TRANSIT → AT_SCHOOL → ASSIGNED → RETURNED(→AT_SCHOOL)
   AT_SCHOOL|ASSIGNED → UNDER_REPAIR → AT_SCHOOL
   any state → LOST → (FOUND → previous state)
   AT_SCHOOL|IN_WAREHOUSE → RETIRED → DISPOSED
```

- **FR-NTR-SM-01** Le système DOIT rejeter tout événement impliquant une transition illégale (HTTP 409 avec le code lisible par machine `ILLEGAL_TRANSITION`), à l'exception des événements arrivant via la synchronisation hors ligne, qui DOIVENT être mis en quarantaine pour rapprochement (§9.4) plutôt que rejetés.
- **FR-NTR-SM-02** `ASSIGNED` DOIT exiger un `student_id` valide résoluble dans le Registre National des Élèves (ou un enregistrement élève mis en cache localement en mode hors ligne).

## 6. Exigences fonctionnelles

### 6.1 Gestion des titres et des éditions

| ID | Exigence (DOIT) | Critère d'acceptation |
|---|---|---|
| FR-NTR-01 | Enregistrer un titre de manuel scolaire avec tous les champs obligatoires du §4.1 ; les soumissions incomplètes sont rejetées avec des erreurs au niveau des champs | Un POST avec un champ obligatoire manquant renvoie 422 en listant exactement les champs manquants ; un POST complet renvoie 201 avec le NTID généré |
| FR-NTR-02 | Faire respecter le flux de travail d'approbation des titres : création en DRAFT ; transition vers APPROVED uniquement par le rôle `curriculum.approve` avec approval_ref | Un utilisateur `curriculum.edit` tentant l'approbation reçoit 403 ; une approbation sans approval_ref renvoie 422 |
| FR-NTR-03 | Créer une nouvelle Édition liée à une CurriculumVersion ; à l'année scolaire d'entrée en vigueur, les éditions antérieures sont marquées `superseded` | Une requête catalogue pour l'année scolaire N ne renvoie que les éditions en vigueur en N ; l'édition remplacée apparaît avec `superseded=true` |
| FR-NTR-04 | Empêcher la passation des marchés de référencer des titres RETIRED ou SUSPENDED (API de validation consommée par le Service de Passation des Marchés) | Un appel de validation pour un NTID RETIRED renvoie `procurable=false` avec un code de motif |
| FR-NTR-05 | Retirer un titre uniquement lorsqu'un plan de retrait existe (NTID successeur ou déclaration explicite « sans successeur ») | Un retrait sans charge utile de plan renvoie 422 |

### 6.2 Lots d'impression et assurance qualité (QA)

| ID | Exigence | Critère d'acceptation |
|---|---|---|
| FR-NTR-06 | Enregistrer les lots d'impression rattachés à une édition avec référence de contrat et quantité ; générer les NCID (ou l'identifiant de lot) à l'enregistrement ; exporter le fichier d'impression des étiquettes (CSV + PDF) | L'enregistrement d'un lot de 5 000 exemplaires génère exactement 5 000 NCID uniques et valides en < 30 s ; l'export d'étiquettes est validé conformément au §3.2 |
| FR-NTR-07 | Enregistrer le résultat QA du lot ; les exemplaires d'un lot FAILED ne peuvent pas entrer en IN_WAREHOUSE | Un scan de réception en entrepôt sur un lot FAILED renvoie `QA_BLOCKED` |

### 6.3 Passeport et mouvements

| ID | Exigence | Critère d'acceptation |
|---|---|---|
| FR-NTR-08 | Enregistrer la réception en entrepôt, l'expédition, la réception à l'école, l'affectation à un élève, le retour, la réparation, la perte, le retrait comme PassportEvents via scan ou saisie manuelle du NCID | Chaque type d'événement crée exactement un PassportEvent ; l'état et la localisation de l'exemplaire se mettent à jour de manière atomique avec l'événement |
| FR-NTR-09 | Afficher le passeport complet de tout exemplaire (tous les événements, ordre chronologique, avec acteur et localisation) aux utilisateurs autorisés en ≤ 2 s pour un exemplaire comptant ≤ 500 événements | Le test chronométré sur un exemplaire pré-alimenté réussit au p95 |
| FR-NTR-10 | Prendre en charge les opérations en masse : une action d'expédition/réception couvrant jusqu'à 10 000 exemplaires (par lot ou liste de scans) | La réception en masse de 10 000 exemplaires s'achève en ≤ 60 s et crée des événements par exemplaire |
| FR-NTR-11 | Enregistrer l'état (physique) à chaque retour et lors de la vérification annuelle, avec pièce jointe photo optionnelle (≤ 2 Mo, compressée côté client) | Un retour sans valeur d'état est rejeté côté client ; la requête d'historique d'état renvoie une série temporelle |
| FR-NTR-12 | Campagne annuelle de vérification des stocks : le ministère ouvre une fenêtre de campagne ; les écoles soumettent les scans de vérification ; le système calcule le rapprochement par école (attendu vs scanné vs manquant) | Le rapport de campagne liste chaque école avec les décomptes ; une école avec 100 attendus et 97 scannés affiche 3 en `unverified` |

### 6.4 Catalogue et rapports

| ID | Exigence | Critère d'acceptation |
|---|---|---|
| FR-NTR-13 | Publier un catalogue public en lecture seule des manuels scolaires approuvés (web + API JSON), filtrable par ministère, matière, classe, langue, année scolaire | Un GET non authentifié ne renvoie que les titres approuvés ; les titres DRAFT/SUSPENDED n'apparaissent jamais |
| FR-NTR-14 | Fournir les rapports standard de l'Annexe B (historique des éditions, distribution du cycle de vie, analyse de l'état, données d'entrée pour la prévision de remplacement, couverture par école/département/région) sous forme de vues à l'écran et d'export CSV/XLSX | Chaque rapport s'affiche avec exploration descendante région→département→école ; l'export correspond aux données à l'écran |
| FR-NTR-15 | Calculer la couverture manuels-par-élève par école et par titre, en croisant les effectifs du Registre des Écoles avec les décomptes d'exemplaires AT_SCHOOL+ASSIGNED | Pour l'école pré-alimentée (400 élèves, 320 exemplaires affectés d'un titre), la couverture affiche 0,80 |
| FR-NTR-16 | Exposer les données d'entrée de prévision de remplacement par titre et par année scolaire : exemplaires par tranche d'état, distribution par âge, taux de perte historique | L'API renvoie la structure JSON documentée ; le test d'intégration du Service Analytique réussit |

### 6.5 Rôles et permissions

- **FR-NTR-17** Le module DOIT faire respecter la matrice de permissions ci-dessous via le service IAM central (OIDC ; rôles en tant que claims). Chaque mutation DOIT être attribuable à un utilisateur nommé — pas de comptes partagés.

| Action | Responsable des curricula | Responsable de la passation des marchés | Responsable d'entrepôt | Chef d'établissement / Gestionnaire du magasin scolaire | Enseignant | Inspecteur | Lecture seule nationale |
|---|---|---|---|---|---|---|---|
| Créer/modifier un titre DRAFT | ✔ | | | | | | |
| Approuver/retirer un titre | ✔ (droit d'approbation) | | | | | | |
| Enregistrer un lot / QA | | ✔ | ✔ (QA) | | | | |
| Réception/expédition entrepôt | | | ✔ | | | | |
| Réception à l'école / retour / état | | | | ✔ | ✔ (affecter/retour de sa propre classe) | | |
| Affecter à un élève | | | | ✔ | ✔ | | |
| Soumettre à la campagne de vérification | | | | ✔ | | ✔ (contrôle ponctuel) | |
| Consulter tout passeport | ✔ | ✔ | ✔ (son propre entrepôt) | ✔ (sa propre école) | sa propre classe | ✔ | ✔ |

- **FR-NTR-18** Les rôles à portée établissement NE DOIVENT lire/écrire que les données de leur propre école (contrôle au niveau des lignes appliqué côté serveur, pas seulement dans l'interface).

## 7. Contrats d'API (normatif, synthèse)

Chemin de base `/api/v1/ntr`. JSON, UTF-8, jeton porteur OAuth2 (client-credentials pour les échanges de service à service, auth-code pour les utilisateurs). Les erreurs utilisent le format problem+json de la RFC 7807. Le fichier OpenAPI 3.1 complet constitue le livrable D-NTR-API (généré à partir de la présente section ; en cas de conflit, la présente section prévaut).

| Point de terminaison | Méthode | Objet |
|---|---|---|
| /titles | GET, POST | rechercher/enregistrer des titres |
| /titles/{ntid} | GET, PATCH | détail / modification des champs en DRAFT |
| /titles/{ntid}:approve, :suspend, :retire | POST | transitions d'état |
| /titles/{ntid}/editions | GET, POST | éditions |
| /titles/{ntid}/procurability | GET | validation pour la Passation des Marchés (FR-NTR-04) |
| /batches | POST | enregistrer un lot d'impression, renvoie la plage de NCID + l'URL d'export des étiquettes |
| /batches/{id}/qa | POST | résultat QA |
| /copies/{ncid} | GET | exemplaire + état courant |
| /copies/{ncid}/passport | GET | historique complet des événements |
| /events:bulk | POST | jusqu'à 10 000 événements par appel (expédition, réception, affectation…), idempotent par `event_uuid` fourni par le client |
| /catalogue | GET | catalogue public approuvé |
| /reports/{report_code} | GET | rapports de l'Annexe B, `?format=json|csv|xlsx` |
| /campaigns | POST, GET | campagnes de vérification |
| /sync/pull, /sync/push | POST | synchronisation des appareils hors ligne (§9) |

- **FR-NTR-API-01** Tous les points de terminaison de liste DOIVENT prendre en charge la pagination par curseur (`limit ≤ 500`, `next_cursor`) et le filtrage par champ ; `/events:bulk` DOIT être idempotent — rejouer le même ensemble d'`event_uuid` renvoie le résultat d'origine et ne crée aucun doublon.
- **FR-NTR-API-02** Les changements incompatibles NE DOIVENT être livrés que sous un nouveau chemin `/api/v2` ; `/api/v1` est pris en charge ≥ 24 mois après la disponibilité générale de la v2.

## 8. Exigences non fonctionnelles

| ID | Catégorie | Exigence |
|---|---|---|
| NFR-NTR-01 | Montée en charge | Prendre en charge ≥ 30 000 000 d'enregistrements Copy, ≥ 300 000 000 de PassportEvents, ≥ 25 000 locataires-écoles sans changement d'architecture (partitionner PassportEvent par année scolaire) |
| NFR-NTR-02 | Performance | Latence API p95 ≤ 500 ms pour les lectures d'entité unique, ≤ 3 s pour les rapports au niveau départemental, sur l'infrastructure de référence définie dans le volume Architecture d'Entreprise |
| NFR-NTR-03 | Débit | Soutenir 200 sessions de synchronisation/minute pendant le pic de la rentrée scolaire (septembre), chacune poussant ≤ 5 000 événements |
| NFR-NTR-04 | Disponibilité | 99,5 % mensuel pour les services centraux ; les opérations scolaires ne sont pas affectées par une panne centrale (« hors ligne d'abord » (offline-first)) |
| NFR-NTR-05 | Hors ligne | Flux de travail scolaire complet (réception, affectation, retour, état, vérification) exécutable sans aucune connectivité pendant ≥ 90 jours consécutifs sur un appareil Android 10+ doté de 2 Go de RAM |
| NFR-NTR-06 | Protection des données | Conforme à la Loi camerounaise N° 2010/012 (cybersécurité/cybercriminalité) et au Cadre de Gouvernance des Données de la plateforme ; les liens élève-exemplaire sont des données personnelles : chiffrées au repos (AES-256), avec journalisation des accès |
| NFR-NTR-07 | Audit | 100 % des mutations attribuables à un utilisateur + appareil + horodatage ; chaînes de hachage des passeports vérifiées chaque nuit (FR-NTR-DM-02) |
| NFR-NTR-08 | Localisation | Interface utilisateur intégralement en français et en anglais ; toutes les énumérations visibles par l'utilisateur sont bilingues |
| NFR-NTR-09 | Portabilité | Aucun service cloud propriétaire sans voie de sortie fondée sur des standards ouverts ; base de données PostgreSQL ≥ 15 ; déployable dans le centre de données national ou sur IaaS |
| NFR-NTR-10 | Accessibilité et inclusion | Application mobile compatible lecteur d'écran (TalkBack) ; cibles tactiles ≥ 48 dp ; contraste WCAG 2.1 AA ; chaque flux de travail critique réalisable via un parcours icône + libellé court sans lecture de texte suivi (utilisabilité en contexte de faible littératie) ; rapports de couverture désagrégeables par caractéristiques des écoles (EDUOS-ESS-001 INC-01/02) — s'applique également aux interfaces du NSR et du NWIDMS |

## 9. Profil de synchronisation hors ligne

Applique le Moteur National de Synchronisation Hors Ligne aux données du NTR :

1. **9.1 Enrôlement des appareils.** Les appareils des écoles s'enrôlent via l'IAM ; chacun reçoit un certificat d'appareil et une partition de données à portée établissement (ses propres exemplaires, élèves et catalogue de titres).
2. **9.2 Pull.** `sync/pull` livre le catalogue + la partition de l'école sous forme de delta depuis le dernier curseur de synchronisation (compressé ; amorçage initial ≤ 50 Mo pour une école de 2 000 élèves).
3. **9.3 Push.** Les événements créés hors ligne portent `event_uuid` (UUIDv7 client), l'heure de l'appareil et l'identifiant de l'appareil ; le push est fragmenté et reprenable.
4. **9.4 Règles de conflit (normatif).** (a) les PassportEvents n'entrent jamais en conflit — ils s'ajoutent et sont réordonnés par `occurred_at` (b) un événement impliquant une transition illégale après réordonnancement est mis en quarantaine avec le statut `NEEDS_RECONCILIATION` et remonté dans la file de travail du bureau départemental, jamais abandonné silencieusement (c) l'`condition` de l'exemplaire se résout selon la règle du dernier écrivain gagnant (last-writer-wins) par `occurred_at` (d) les conflits d'affectation d'élève (deux écoles revendiquent un même exemplaire) ouvrent automatiquement un dossier de rapprochement.
- **FR-NTR-SYNC-01** Les événements mis en quarantaine DOIVENT être résolubles (accepter / corriger / rejeter) par le rôle `division.reconcile`, la résolution étant enregistrée comme un PassportEvent.

## 10. Exigences d'interface utilisateur (synthèse)

Application Android « mobile d'abord » + web adaptatif, conformément à la Spécification du Système de Design EduOS Cameroon. Écrans obligatoires : « réception de stock » orientée scan, « affecter à un élève » (scanner l'exemplaire → scanner/sélectionner l'élève), « retour et état », « campagne de vérification », tableau de bord de l'école (couverture, retours en attente). Le parcours du scan à la confirmation DOIT prendre ≤ 3 appuis. Chaque écran DOIT fonctionner hors ligne avec un indicateur visible de statut de synchronisation.

## 11. Migration et amorçage des données

- **FR-NTR-MIG-01** Fournir un outil d'import en masse (modèles CSV, Annexe C) pour la liste existante des manuels scolaires approuvés (est. 2 000–4 000 titres) et les déclarations de stocks des écoles, avec un rapport de validation (lignes acceptées/rejetées avec motifs) avant validation définitive.
- **FR-NTR-MIG-02** Prendre en charge l'enregistrement des exemplaires « existants » (brownfield) : les écoles peuvent enregistrer les stocks préexistants non étiquetés en quantités au niveau du lot par titre, avec possibilité de passage au suivi par exemplaire lorsque les étiquettes sont apposées.

## 12. Stratégie d'acceptation et de test

Acceptation = 100 % des exigences DOIT réussissent leurs critères d'acceptation lors d'un Test d'Acceptation Utilisateur avec témoins sur l'environnement pilote, plus : un pilote de 10 écoles fonctionnant 60 jours avec ≥ 95 % des mouvements de manuels scolaires capturés numériquement ; un cycle hors ligne complet (30 jours de déconnexion, puis synchronisation réussie sans aucune perte de données) ; un test de charge aux débits du NFR-NTR-03. Le prestataire DOIT livrer des suites de tests automatisées (tests de contrat d'API + tests de bout en bout) que le Ministère peut ré-exécuter.

## 13. Matrice de traçabilité des exigences

| Plage d'exigences | Méthode de vérification | Livrable de test |
|---|---|---|
| FR-NTR-ID-01…05 | Tests unitaires + de contrat | TST-NTR-ID |
| FR-NTR-DM-01…03 | Audit des privilèges BD + test de vérification de chaîne | TST-NTR-DM |
| FR-NTR-SM-01…02 | Tests de propriétés de machine à états | TST-NTR-SM |
| FR-NTR-01…18 | Contrat d'API + scripts UAT de bout en bout | TST-NTR-FN |
| FR-NTR-API-01…02 | Tests de contrat (Schemathesis ou équivalent) | TST-NTR-API |
| NFR-NTR-01…09 | Test de charge, test d'endurance hors ligne, audit de sécurité | TST-NTR-NFR |
| FR-NTR-SYNC-01, MIG-01…02 | Tests de scénarios pilotes | TST-NTR-PIL |

## Annexe A — Référentiel des matières (extrait)

MAT Mathématiques · ENG Langue anglaise · FRE Français · PHY Physique · CHE Chimie · BIO Biologie · HIS Histoire · GEO Géographie · CIV Éducation civique · ECO Économie · CSC Informatique · LIT Littérature · SCI Sciences intégrées (primaire) · … *(le référentiel complet est maintenu comme donnée de référence par le Service des Curricula ; chargement initial dans le livrable D-NTR-REF)*

## Annexe B — Codes des rapports standard

`RPT-CAT` catalogue approuvé · `RPT-EDH` historique des éditions · `RPT-LCS` distribution des statuts de cycle de vie · `RPT-CND` analyse de l'état · `RPT-COV` couverture par école/département/région · `RPT-RPL` données d'entrée de prévision de remplacement · `RPT-VER` résultats des campagnes de vérification · `RPT-LOSS` analyse des pertes.

## Annexe C — Modèles d'import

`titles.csv` (colonnes = champs obligatoires du §4.1), `school_stock.csv` (school_id, ntid, quantity, condition_band, year_received). Les modèles avec règles de validation sont livrés dans le livrable D-NTR-IMP.

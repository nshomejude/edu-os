# EduOS Cameroon — Spécification des Exigences Fonctionnelles (FRS)

## Module 1 : Registre National des Écoles (National School Registry, NSR)

| | |
|---|---|
| Identifiant du document | EDUOS-FRS-NSR-001 |
| Version | 1.0 (Référence constructible) |
| Statut | Projet soumis à l'examen des ministères |
| Remplace | Spécification narrative du chapitre 17 (Volume II) |
| Conventions | Identiques à EDUOS-FRS-NTR-001 : mots-clés RFC 2119 ; chaque DOIT est assorti d'un critère d'acceptation ; erreurs RFC 7807 ; pagination par curseur ; OAuth2 via l'IAM central |

*Traduction française du document original anglais [07-FRS-National-School-Registry](../07-FRS-National-School-Registry.md). En cas de divergence, la version anglaise validée fait foi.*

Le NSR est le **registre fondamental** de la plateforme : chaque transaction de chaque autre module référence une école. Il couvre **les deux ministères** (MINEDUB pour le primaire et le préscolaire, MINESEC pour le secondaire général et technique) ainsi que les écoles publiques et privées, dans un registre unique à périmètre ministériel — c'est la mise en œuvre concrète de la charte du registre partagé exigée par le Risque R1.

---

## 1. Périmètre

**Dans le périmètre :** enregistrement des écoles et cycle de vie (ouverture, fusion, scission, fermeture) ; l'Identifiant National d'École (NSID) ; hiérarchie administrative (région → département → arrondissement → école) ; géolocalisation et classification d'accessibilité ; profils d'infrastructures et de capacité ; données synthétiques d'effectifs (par niveau de classe, par année scolaire) ; flux de mise à jour contrôlés avec validation au niveau départemental ; filtrage d'éligibilité pour les programmes ; l'annuaire public des écoles ; les API consommées par tous les autres modules ; l'import des données de la carte scolaire.

**Hors périmètre :** les dossiers individuels des élèves (Registre National des Élèves) ; les dossiers RH du personnel (Gestion des Enseignants) ; les stocks de manuels scolaires (NTR/NWIDMS — le NSR ne stocke aucun inventaire).

## 2. NSID — Identifiant National d'École (normatif)

Format : `CM-SCH-{REG}-{DIV:2}{SUB:2}-{MIN}{TYPE}-{SEQ:5}` p. ex. `CM-SCH-NW-0703-SG-00412`

| Segment | Valeurs |
|---|---|
| REG | code région à 2 lettres (AD, CE, EN, ES, LT, NO, NW, OU, SU, SW) |
| DIV/SUB | codes numériques du département et de l'arrondissement issus du répertoire administratif officiel national (58 départements / 360 arrondissements, cf. BDA §1) |
| MIN | `B` MINEDUB / `S` MINESEC |
| TYPE | `N` préscolaire, `P` primaire, `G` secondaire général, `T` technique, `C` combiné |
| SEQ | séquence nationale à 5 chiffres, jamais réutilisée |

- **FR-NSR-ID-01** Les NSID DOIVENT être générés par le système, immuables et jamais réutilisés. Les codes ministériels existants (références MINEDUB/MINESEC) DOIVENT être conservés comme alias `legacy_codes[]`, interrogeables, afin que les documents papier anciens restent résolubles.
- **FR-NSR-ID-02** Si la localisation administrative d'une école est redécoupée (scission d'arrondissement), le NSID NE DOIT PAS changer ; le lien hiérarchique change avec une date d'effet auditée.

## 3. Modèle de données (normatif)

```
AdminUnit (region 1─N division 1─N subdivision)   ← reference data, gazette-versioned
Subdivision 1──N School
School 1──N SchoolStatusEvent        (append-only lifecycle)
School 1──N InfrastructureProfile    (versioned snapshots)
School 1──N EnrolmentReturn          (per academic year, per class level)
School 1──N ProgrammeParticipation
School N──N School (merge/split lineage links)
```

### 3.1 School (champs principaux)

| Champ | Type | Oblig. | Notes |
|---|---|---|---|
| nsid | string(24) PK | ✔ | §2 |
| name_official | string(300) | ✔ | tel que publié au journal officiel/autorisé |
| name_common | string(300) | ○ | alias de recherche |
| ministry | enum {MINEDUB, MINESEC} | ✔ | |
| school_type | enum {NURSERY, PRIMARY, GEN_SEC, TECH_SEC, COMBINED} | ✔ | |
| ownership | enum {PUBLIC, PRIVATE_LAY, PRIVATE_CONF, COMMUNITY} | ✔ | |
| language_system | enum {EN, FR, BI} | ✔ | sous-système |
| subdivision_id | FK AdminUnit | ✔ | hiérarchie complète dérivable |
| status | enum §4 | ✔ | |
| authorization_ref | string(100) | ✔ pour le privé | numéro d'autorisation ministérielle |
| gps_lat / gps_lon | decimal(9,6) | ✔* | *obligatoire pour PUBLIC ; relevé lors de la première visite de terrain pour les autres ; `gps_source` enum {GPS_DEVICE, MAP_PICK, IMPORTED} + `gps_verified` bool |
| accessibility_class | enum {URBAN, RURAL_ROAD, RURAL_SEASONAL, REMOTE} | ✔ | pilote la planification logistique (NWIDMS) |
| grid_power | enum {GRID, SOLAR, NONE} | ✔ | pilote l'allocation des kits solaires (BUD §3.3) |
| connectivity | enum {NONE, 2G, 3G, 4G} | ✔ | pilote les attentes de synchronisation |
| head_teacher_name / phone | string | ✔ | contact opérationnel |
| boarding, single_sex | bool/enum | ○ | |

### 3.2 InfrastructureProfile (instantané versionné, un par évaluation)
`profile_id PK, nsid FK, assessed_at, assessed_by, classrooms_total, classrooms_usable, has_library, has_lab_science, has_lab_ict, storage_rooms, storage_secure (bool — condition préalable au stock de manuels scolaires en école), water_source enum, latrines, fence (bool), source enum {SELF_DECLARED, INSPECTION_VERIFIED}`

- **FR-NSR-DM-01** Les données d'infrastructure DOIVENT porter leur provenance (`source`) ; les tableaux de bord DOIVENT distinguer visuellement les données auto-déclarées des données vérifiées par inspection (Risque R8 : aucune donnée de mauvaise qualité présentée comme un fait).

### 3.3 EnrolmentReturn
`return_id PK, nsid FK, academic_year, class_level (P1…P6/F1…F5/LS/US/C1…C4), boys, girls, submitted_by, submitted_at, validation_status enum {SUBMITTED, DIVISION_VALIDATED, REJECTED}, validated_by`

- **FR-NSR-DM-02** Les totaux d'effectifs par école et par année DOIVENT être dérivés exclusivement des déclarations validées ; les chiffres non validés apparaissent signalés. La série validée alimente le module de prévision de la demande et la couverture NTR (FR-NTR-15).

## 4. Machine à états du cycle de vie de l'école

`PROPOSED → AUTHORIZED → OPERATIONAL ⇄ TEMPORARILY_CLOSED → CLOSED` plus `OPERATIONAL → MERGED / SPLIT` (terminal pour l'enregistrement, avec liens de filiation vers les NSID successeurs).

- **FR-NSR-SM-01** La fusion DOIT exiger : le ou les NSID cibles, une date d'effet et des instructions de disposition pour les références ouvertes (élèves, stocks) — l'API retourne la liste des références ouvertes des autres modules (via leurs points de terminaison de vérification de références) et bloque la fusion tant que des références bloquantes existent.
- **FR-NSR-SM-02** Seul le rôle `division.validate` PEUT faire passer une école à OPERATIONAL ; seuls les rôles de niveau ministériel PEUVENT effectuer CLOSE/MERGE. Toutes les transitions sont des SchoolStatusEvents avec acteur + document de référence.

## 5. Exigences fonctionnelles

| ID | Exigence (DOIT) | Critère d'acceptation |
|---|---|---|
| FR-NSR-01 | Enregistrer une école avec les champs obligatoires du §3.1 ; détection des doublons candidats à la création (similarité de nom ≥ 0,85 en trigrammes + même arrondissement, ou GPS à moins de 500 m) présentant les correspondances avant validation | La création de « GBHS Bamenda II » dans un arrondissement où existe « Government Bilingual High School Bamenda 2 » fait remonter le candidat ; l'utilisateur doit confirmer explicitement « pas un doublon » (confirmation enregistrée) |
| FR-NSR-02 | Mises à jour contrôlées : permissions d'édition au niveau du champ (les utilisateurs école modifient les contacts ; le département modifie la hiérarchie ; le ministère modifie le statut) ; chaque modification versionnée avec acteur/horodatage/ancienne→nouvelle valeur | Le point de terminaison d'historique des modifications retourne l'audit complet au niveau du champ ; un utilisateur école tentant une modification de hiérarchie reçoit un 403 |
| FR-NSR-03 | Soumission des déclarations d'effectifs par année scolaire et niveau de classe, avec flux de validation départementale et motifs de rejet | Une déclaration où boys+girls dépasse la capacité de l'école ×1,5 déclenche un avertissement ; un rejet départemental exige un code motif |
| FR-NSR-04 | Recherche d'éligibilité avancée : filtrage par toute combinaison de région/département/arrondissement, type, statut de propriété, statut, plage d'effectifs, indicateurs d'infrastructure, accessibilité, alimentation électrique, connectivité ; résultats exportables en CSV/XLSX | La requête « MINEDUB PUBLIC, REMOTE ou RURAL_SEASONAL, grid_power=NONE » retourne la liste cible des kits solaires (BUD §3.3) |
| FR-NSR-05 | Annuaire public en lecture seule (web + JSON) des écoles OPERATIONAL : nom, NSID, type, statut de propriété, arrondissement, GPS (écoles publiques) | Un GET non authentifié exclut les contacts et les écoles non opérationnelles |
| FR-NSR-06 | API de référence pour tous les modules : résolution NSID → synthèse de l'école ; résolution par lot ≤ 1 000 NSID par appel ; inclut `status` afin que les modules consommateurs puissent bloquer les transactions vers des écoles CLOSED | Une expédition NTR vers une école CLOSED est rejetée avec `SCHOOL_NOT_OPERATIONAL` |
| FR-NSR-07 | Import en masse de la carte scolaire (CSV, modèle en Annexe) : rapport de validation en zone de transit (doublons, champs obligatoires manquants, codes administratifs invalides, GPS hors du rectangle englobant du Cameroun) avant validation ; réimport idempotent | L'import de 22 000 lignes comportant 300 défauts valide 21 700 lignes et produit un rapport de défauts ligne par ligne ; la ré-exécution du même fichier ne crée aucun doublon |
| FR-NSR-08 | Vue cartographique : écoles affichées avec rendu par grappes ; couches par statut/type/indicateurs de couverture ; fonctionne avec la bande passante d'un bureau départemental (mise en cache des tuiles) | 18 000 écoles s'affichent en < 5 s sur le matériel de référence ; pack de tuiles hors ligne disponible pour les terminaux de terrain |
| FR-NSR-09 | Capture/vérification GPS depuis l'application de terrain : capture avec le GPS du terminal (précision ≤ 25 m enregistrée), vérification des coordonnées existantes, signalement pour examen de tout écart > 1 km | Une vérification de terrain créant un écart > 1 km ouvre un dossier d'examen et n'écrase pas silencieusement les données |
| FR-NSR-10 | Participation aux programmes : rattacher/détacher des écoles à des programmes (p. ex. vague pilote 1, vague kits solaires 2) avec dates d'effet ; pilote la gestion du déploiement | Les décomptes par programme du tableau de bord de déploiement correspondent aux enregistrements de rattachement |

## 6. Rôles et permissions (synthèse)

| Action | Utilisateur école | Agent d'arrondissement | Agent départemental | Administrateur ministériel | Public |
|---|---|---|---|---|---|
| Modifier ses propres contacts/auto-déclaration d'infrastructure | ✔ | | | | |
| Soumettre une déclaration d'effectifs | ✔ | | | | |
| Valider les déclarations, enregistrer les écoles, vérifier le GPS | | ✔ (proposer) | ✔ | ✔ | |
| Transitions de statut (opérationnel/fermeture/fusion) | | | ✔ (opérationnel) | ✔ (toutes) | |
| Lecture de l'annuaire | ✔ | ✔ | ✔ | ✔ | ✔ (champs publics) |

Cloisonnement au niveau des lignes identique à FR-NTR-18 (côté serveur, propre école / propre département).

## 7. Synthèse des API

Base `/api/v1/nsr` : `/schools` (GET/POST), `/schools/{nsid}` (GET/PATCH), `/schools/{nsid}:transition`, `/schools/{nsid}/enrolment` (GET/POST), `/schools/{nsid}/infrastructure` (GET/POST), `/schools:resolve` (par lot), `/schools:search`, `/directory` (public), `/imports` (carte scolaire en zone de transit), `/admin-units` (référence du répertoire administratif officiel). Mêmes règles d'idempotence, de pagination et de versionnage qu'EDUOS-FRS-NTR-001 §7.

## 8. Exigences non fonctionnelles

| ID | Exigence |
|---|---|
| NFR-NSR-01 | ≥ 40 000 enregistrements d'écoles (les deux ministères, public+privé) avec historique complet ; résolution de référence p95 ≤ 200 ms (elle se trouve sur le chemin critique de chaque module) |
| NFR-NSR-02 | Les lectures du registre DOIVENT rester disponibles pendant les fenêtres de maintenance centrale (réplique en lecture) ; les modules consommateurs mettent en cache les synthèses d'écoles avec un TTL de 24 h pour le fonctionnement hors ligne |
| NFR-NSR-03 | L'application de terrain fonctionne hors ligne pour la vérification des enregistrements et les déclarations d'effectifs (même moteur de synchronisation, FRS-NTR §9) |
| NFR-NSR-04 | Interface utilisateur bilingue ; noms du répertoire administratif officiel stockés en FR + EN lorsque des variantes officielles existent |
| NFR-NSR-05 | Données personnelles limitées au contact du chef d'établissement ; aucune donnée d'élève dans ce module |

## 9. Amorçage des données et campagne R8

- **FR-NSR-MIG-01** Chargement initial = extraits de la carte scolaire MINEDUB + MINESEC (BDA §6.4) via le pipeline FR-NSR-07, suivi de la campagne financée de validation au niveau départemental (BUD §3.5) : chaque département reçoit sa liste importée, confirme l'existence/le statut/le GPS de chaque école et signe la validation. Une école n'est `gps_verified` et éligible à la livraison de terminaux qu'après cette validation signée.
- **Lien KPI :** OUT-P7 (S&E) — score de qualité des données ≥ 85 % dans les régions pilotes à la fin de la campagne.

## 10. Acceptation

100 % des exigences DOIT passent leurs critères d'acceptation lors d'une recette utilisateur (UAT) avec témoins ; import de la carte scolaire exécuté sur de vrais extraits ministériels avec examen du rapport de défauts ; précision/rappel de la détection des doublons mesurés sur un échantillon étiqueté de la taille d'un département (précision ≥ 80 %, rappel ≥ 90 % aux seuils de FR-NSR-01) ; API de référence testée en charge à 500 résolutions/s.

## Annexe — Modèle d'import `schools.csv`

`legacy_code, name_official, ministry, school_type, ownership, language_system, region_code, division_code, subdivision_code, gps_lat, gps_lon, head_teacher_name, head_teacher_phone, enrolment_total_last_year, classrooms_total, grid_power, connectivity, authorization_ref`

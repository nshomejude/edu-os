# EduOS Cameroon — Spécification des Exigences Fonctionnelles (FRS)

## Module 5 : Système National de Gestion des Entrepôts, des Stocks et de la Distribution (NWIDMS)

| | |
|---|---|
| Identifiant du document | EDUOS-FRS-NWD-001 |
| Version | 1.0 (Référence constructible) |
| Statut | Projet soumis à l'examen des ministères |
| Remplace | Spécification narrative du chapitre 21 (Volume II) |
| Conventions | Identiques à EDUOS-FRS-NTR-001 (RFC 2119 ; critères d'acceptation par DOIT ; RFC 7807 ; OAuth2/IAM ; pagination par curseur) |
| Dépendances fortes | NSR (destinations), NTR (ce qui est déplacé — NTID/NCID/lots) |

*Traduction française du document original anglais [08-FRS-NWIDMS-Warehouse-Distribution](../08-FRS-NWIDMS-Warehouse-Distribution.md). En cas de divergence, la version anglaise validée fait foi.*

Le NWIDMS gère la garde physique : réception depuis les imprimeurs, stockage, transfert inter-entrepôts, allocation, expédition, livraison aux écoles, retours et mise au rebut. Son engagement de conception central : **toute quantité présente dans le système est attribuable à un emplacement et à un dépositaire à tout moment, et la garde ne change que par passation de main reconnue numériquement.** C'est le module qui concrétise le bénéfice B1 (réduction de la déperdition, ECO §3).

---

## 1. Périmètre

**Dans le périmètre :** registre des entrepôts (niveaux national/régional/départemental) ; emplacements de stockage au sein des entrepôts ; réception des marchandises contre les lots d'impression ; classes de stock ; rangement/prélèvement ; plans d'allocation ; cycle de vie des expéditions avec chaîne de garde ; confirmation de livraison par l'école ; gestion des écarts et des exceptions ; moteur de redistribution ; inventaires tournants et inventaire physique annuel ; mise au rebut ; tableaux de bord logistiques.

**Hors périmètre :** la gestion des contrats (Service de Passation des Marchés) ; l'identité des titres/exemplaires (NTR — le NWIDMS n'émet jamais d'identifiants) ; la gestion du magasin interne de l'école après livraison (module Opérations Scolaires) ; la contractualisation du fret (seules les références transporteur sont enregistrées).

## 2. Modèle de données (normatif)

```
Warehouse 1──N StorageLocation (zone/rack/bin)
Warehouse 1──N StockRecord (per NTID-edition-batch × stock_class)
AllocationPlan 1──N AllocationLine (school × NTID × qty)
Shipment 1──N ShipmentLine (batch/NCID-range × qty) 1──N CustodyEvent (append-only)
Shipment N──1 origin (Warehouse) · N──1 destination (Warehouse | School via NSID)
DiscrepancyCase, RedistributionProposal, CountSession
```

### 2.1 Warehouse
`wh_id PK ("CM-WH-{REG}-{SEQ:3}"), name, tier enum {NATIONAL, REGIONAL, DIVISIONAL}, subdivision_id FK NSR gazetteer, gps, capacity_m3, storekeeper_user_id, status enum {ACTIVE, SUSPENDED, CLOSED}`

### 2.2 StockRecord (le grand livre)
`stock_id PK, wh_id FK, ntid FK, edition_id FK, batch_id FK, stock_class enum {AVAILABLE, RESERVED, IN_TRANSIT_OUT, DAMAGED, QUARANTINE, AWAITING_DISPOSAL}, quantity int ≥ 0, location_id FK`

- **FR-NWD-DM-01** Les quantités en stock DOIVENT changer exclusivement par des **StockTransactions** comptabilisées (réception, prélèvement, expédition, confirmation de réception, ajustement, reclassification, mise au rebut) — jamais par modification directe. Chaque transaction enregistre l'acteur, l'horodatage, le code motif et (pour les ajustements) une justification obligatoire + un approbateur.
- **FR-NWD-DM-02** Le grand livre DOIT être tenu en partie double entre emplacements : une expédition décrémente à l'origine `AVAILABLE→IN_TRANSIT_OUT` et crée la position en transit correspondante sur la Shipment ; la somme globale par lot est invariante sauf à la réception (source) et à la mise au rebut/perte (puits). Un contrôle d'invariant nocturne DOIT alerter en cas de violation.
- **FR-NWD-DM-03** Lorsque le titre NTR est suivi à l'exemplaire, les ShipmentLines DOIVENT porter des plages de NCID/listes de scans et le NWIDMS DOIT comptabiliser les PassportEvents correspondants dans le NTR (source unique de vérité du mouvement : le NWIDMS détient les quantités, le NTR détient l'historique par exemplaire ; une même transaction alimente les deux de manière atomique via le bus d'événements avec le patron outbox).

## 3. Cycle de vie des expéditions et chaîne de garde (normatif)

```
DRAFT → CONFIRMED → LOADED → DISPATCHED → [IN_TRANSIT checkpoints]* → ARRIVED
      → RECEIPT_IN_PROGRESS → { RECEIVED_FULL | RECEIVED_WITH_DISCREPANCY } → CLOSED
any pre-CLOSED state → CANCELLED (with stock reversal)  ·  DISPATCHED → LOST_IN_TRANSIT (case)
```

- **FR-NWD-SM-01** Chaque transition DOIT être un CustodyEvent identifiant le dépositaire cédant et le dépositaire acceptant ; DISPATCHED exige l'identité du chauffeur/transporteur + la référence du véhicule + le numéro de lettre de voiture ; la réception exige que l'utilisateur authentifié de la destination scanne physiquement ou confirme par comptage.
- **FR-NWD-SM-02** Une expédition NE DOIT PAS être clôturée avec un écart inexpliqué : reçu ≠ expédié ouvre automatiquement un DiscrepancyCase, quantifiant l'écart ligne par ligne et gelant la variance en classe `QUARANTINE` dans l'attente d'une résolution (acceptation en moins / retrouvé / passage en perte avec chaîne d'approbation).
- **FR-NWD-SM-03** Les reconnaissances de garde DOIVENT fonctionner hors ligne (réception en école dans les zones sans signal) via le moteur de synchronisation ; la chaîne de garde est ordonnée par `occurred_at` lors de la réconciliation, avec les mêmes règles de quarantaine que FRS-NTR §9.4.

## 4. Exigences fonctionnelles

### 4.1 Opérations d'entrepôt et de stock

| ID | Exigence (DOIT) | Critère d'acceptation |
|---|---|---|
| FR-NWD-01 | Enregistrer les entrepôts (niveaux, §2.1) et les emplacements de stockage ; imposer que le stock référence toujours un emplacement valide | Une écriture de stock vers un entrepôt CLOSED est rejetée |
| FR-NWD-02 | Réception des marchandises contre un lot d'impression NTR : réception planifiée à partir de la référence Passation des Marchés, option de comptage à l'aveugle, écart par rapport à l'attendu calculé à la comptabilisation ; lots QA-FAILED bloqués (FR-NTR-07) | La réception de 4 980 contre 5 000 attendus comptabilise 4 980 et ouvre un DiscrepancyCase pour 20 |
| FR-NWD-03 | Reclassification entre classes de stock avec codes motifs ; DAMAGED et AWAITING_DISPOSAL physiquement ségrégués par affectation d'emplacement | Une reclassification en endommagé sans code motif est rejetée (422) |
| FR-NWD-04 | Inventaires tournants et inventaire physique annuel : une CountSession gèle les StockRecords concernés pour tout mouvement, saisit le compté vs le comptable, et comptabilise les ajustements approuvés | Un écart de comptage au-delà de la tolérance (> 0,5 % ou > 50 unités par ligne) exige une approbation de niveau départemental avant comptabilisation |
| FR-NWD-05 | Flux de mise au rebut : proposition (avec preuves photographiques) → approbation (rôle ministériel) → comptabilisation de la mise au rebut avec témoins ; les quantités mises au rebut ne quittent le grand livre que par la transaction puits | Une mise au rebut sans référence d'approbation est rejetée ; le rapport annuel de mise au rebut se réconcilie avec les transactions |

### 4.2 Allocation et distribution

| ID | Exigence | Critère d'acceptation |
|---|---|---|
| FR-NWD-06 | Importer/recevoir les plans d'allocation (depuis le module de prévision ou un plan ministériel manuel) : école × NTID × quantité, validés contre le statut NSR et l'approvisionnabilité NTR | Une ligne de plan visant une école CLOSED ou un titre RETIRED est rejetée à la validation avec erreurs au niveau des lignes |
| FR-NWD-07 | Réservation : la confirmation d'un plan réserve le stock (`AVAILABLE→RESERVED`) avec rapport de pénurie en cas d'insuffisance | Un plan nécessitant 10 000 contre 8 000 disponibles réserve 8 000 et signale un déficit de 2 000 selon l'ordre de priorité des écoles |
| FR-NWD-08 | Constituer les expéditions à partir des réservations avec des entrées d'optimisation de chargement (poids issu des données physiques NTR, classe d'accessibilité de la destination issue du NSR) ; tournées multi-écoles prises en charge avec manifestes de dépose par école | Une tournée de 3 écoles produit 3 manifestes de livraison distincts et 3 confirmations de réception indépendantes |
| FR-NWD-09 | Confirmation de réception par l'école sur le terminal de l'école (utilisable hors ligne) : scan des lots/exemplaires ou confirmation par comptage par ligne de manifeste ; signature du chef d'établissement par PIN, sans biométrie (nom + rôle + identité du terminal) | Une réception en mode avion se synchronise ultérieurement, chaîne de garde intacte ; un écart sur une ligne de manifeste ouvre un DiscrepancyCase |
| FR-NWD-10 | Tableau de bord de suivi des expéditions : exploration national → région → département des expéditions ouvertes par statut/ancienneté ; alertes de retard (ancienneté > transit attendu pour la classe d'accessibilité) | Le seuil de retard d'une expédition vers une école de classe REMOTE diffère de celui d'URBAN selon la configuration ; la liste des retards correspond aux données de test préchargées |
| FR-NWD-11 | Moteur de redistribution : proposer des transferts depuis les excédents (stock > besoin × seuil) vers les écoles/entrepôts en pénurie, classés par proximité (GPS) et accessibilité ; les propositions exigent une approbation humaine — le moteur NE DOIT PAS exécuter automatiquement | Un scénario préchargé d'excédent/pénurie produit l'ensemble de propositions optimal documenté ; rien ne bouge sans approbation |
| FR-NWD-12 | Mode urgence : expéditions signalées (régions en crise, R6) avec exigences de données réduites (niveau lot uniquement, fenêtre de confirmation différée de 90 jours) mais jamais avec une garde anonyme | Une expédition d'urgence nomme toujours les dépositaires cédant et acceptant |

### 4.3 Intelligence et rapports

| ID | Exigence | Critère d'acceptation |
|---|---|---|
| FR-NWD-13 | Position nationale des stocks : stock en temps réel par entrepôt/région/titre/classe ; signalement des variances (DiscrepancyCases non résolus, comptages obsolètes > 12 mois) | Les totaux du tableau de bord se réconcilient exactement avec les sommes des StockRecords ; les entrepôts à comptage obsolète sont visiblement signalés |
| FR-NWD-14 | Rapports standard : vieillissement des stocks, délai réception-expédition, ponctualité des livraisons par département (alimente OUT-2), analyse des pertes/écarts (alimente la vérification OUT-1), taux d'utilisation des entrepôts | Chacun exportable en CSV/XLSX ; les définitions du numérateur/dénominateur d'OUT-1 correspondent exactement au cadre de S&E |
| FR-NWD-15 | Vue de préparation à la rentrée : % du plan d'allocation expédié/reçu par région par rapport au compte à rebours de la rentrée scolaire | Avec 60 % reçus au niveau national, la vue présente une ventilation par région dont la somme fait 60 % |

## 5. Rôles et permissions (synthèse)

| Action | Magasinier (son entrepôt) | Responsable d'entrepôt (son entrepôt) | Logistique départementale | Logistique ministérielle | Utilisateur école | Auditeur |
|---|---|---|---|---|---|---|
| Comptabiliser réceptions/prélèvements/expéditions | ✔ | ✔ | | | | |
| Approuver les ajustements/comptages | | ✔ (≤ tolérance) | ✔ | ✔ | | |
| Approuver la mise au rebut | | | proposer | ✔ | | |
| Confirmer la réception à l'école | | | | | ✔ | |
| Approuver la redistribution | | | ✔ (intra-départemental) | ✔ | | |
| Tout lire + exporter | | son entrepôt | son département | ✔ | sa propre école | ✔ (lecture seule, tout) |

Aucun compte partagé ; les actions du magasinier sont liées au dépositaire enregistré de l'entrepôt (§2.1) — toute discordance déclenche une alerte d'audit.

## 6. Exigences non fonctionnelles

| ID | Exigence |
|---|---|
| NFR-NWD-01 | Échelle : ~70 entrepôts (1 national + 10 régionaux + ~58 départementaux), débit annuel de 25M+ unités, 100k+ lignes d'expédition/saison |
| NFR-NWD-02 | Pointe : la saison de rentrée scolaire soutient 50 sessions de réception simultanées et 2 000 confirmations d'écoles/jour sans dégradation (aligné sur NFR-NTR-03) |
| NFR-NWD-03 | Les opérations d'entrepôt se poursuivent pendant une panne centrale : le client entrepôt maintient une file locale ≥ 7 jours (les entrepôts disposent d'une meilleure connectivité que les écoles ; 7 jours contre 90 pour les écoles) |
| NFR-NWD-04 | Numérisation : lecteurs laser USB/Bluetooth dans les entrepôts (vitesse de masse), scan par caméra dans les écoles ; les deux consomment des charges utiles NCID identiques (FR-NTR-ID-04) |
| NFR-NWD-05 | Auditabilité du grand livre : tout chiffre de stock reconstructible à partir du journal des transactions à toute date passée (approvisionnement par événements ou journalisation intégrale) ; l'accès en lecture de l'auditeur est contractuellement irréductible |
| NFR-NWD-06 | Interface utilisateur bilingue ; les PDF de lettres de voiture/manifestes s'impriment sur des imprimantes A4 monochromes (réalité des bureaux départementaux) |

## 7. Contrats d'intégration

| Direction | Contrat |
|---|---|
| Passation des Marchés → NWIDMS | réceptions attendues (référence contrat, lot, quantité, ETA) |
| NWIDMS → NTR | PassportEvents de mouvement (outbox atomique, FR-NWD-DM-03) |
| NSR → NWIDMS | résolution des écoles, statut, accessibilité, GPS (cache 24 h) |
| Prévision → NWIDMS | plans d'allocation (FR-NWD-06) |
| NWIDMS → Analytique/S&E | mesures OUT-1/OUT-2, analyse des pertes |

Base d'API `/api/v1/nwd` ; mêmes règles d'idempotence (`transaction_uuid`), de pagination et de versionnage que FRS-NTR §7. Inventaire complet des points de terminaison dans le livrable D-NWD-API (OpenAPI 3.1).

## 8. Migration

- **FR-NWD-MIG-01** Soldes d'ouverture : un inventaire physique par entrepôt (CountSession en mode INITIAL) établit le grand livre ; aucun solde papier importé n'est admis sans comptage. Budgétisé au sein de la campagne de données (BUD §3.5).

## 9. Acceptation

100 % des critères d'acceptation des exigences DOIT lors d'une recette utilisateur (UAT) avec témoins ; invariant du grand livre (FR-NWD-DM-02) vérifié par un test aléatoire de 10 000 transactions avec zéro violation ; cycle complet d'une saison pilote : une réception réelle de lot d'impression → allocation → expédition → ≥ 50 confirmations d'écoles dont ≥ 10 hors ligne ; exercice d'écart : une variance injectée de 2 % est intégralement mise en évidence dans des DiscrepancyCases (zéro absorption silencieuse) ; rapports OUT-1/OUT-2 validés contre une vérité terrain reconstituée manuellement pour le pilote.

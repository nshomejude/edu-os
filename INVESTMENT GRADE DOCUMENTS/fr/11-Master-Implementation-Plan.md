# EduOS Cameroon — Plan directeur de mise en œuvre

| | |
|---|---|
| Identifiant du document | EDUOS-MIP-001 |
| Version | 1.1 — comble les 13 lacunes identifiées lors de la revue contradictoire de la v1.0 (registre des lacunes au §10) |
| Objet | L'image unique de **ce qui est construit, dans quel ordre, par qui, selon quel calendrier** — du décret de gouvernance à l'exploitation nationale |
| Développeur | Opesware Technologies, Douala — www.opesware.com · eudos@opesware.com |
| Ancrages | Phases budgétaires (BUD §1) · Jalons S&E (MEF §3.3) · Liste de contrôle de préparation (PRC) · Trio FRS (04/07/08) · ADR (doc 09) |

*Traduction française du document original anglais [11-Master-Implementation-Plan](../11-Master-Implementation-Plan.md). En cas de divergence, la version anglaise validée fait foi.*

---

## 1. Ce que nous construisons (le système en une page)

**EduOS Cameroon** est un système d'exploitation national des ressources éducatives. Son premier produit est la gestion numérique complète du cycle de vie des manuels scolaires — chaque titre approuvé, chaque exemplaire imprimé, chaque mouvement de l'imprimerie jusqu'aux mains de l'élève et retour — pour les deux ministères, dans plus de 18 500 écoles publiques, en mode « hors ligne d'abord » (offline-first) parce que telle est la réalité du Cameroun.

```
                        ┌─ EduOS Cameroon (Phase I product) ─────────────────┐
 Publishers/Printers ─▶ │  CURRICULUM &   ─▶  CUSTODY &        ─▶ SCHOOL     │ ─▶ Learners
 (batch registration)   │  CATALOGUE          LOGISTICS (NWIDMS)   OPERATIONS │    (assignment,
                        │  (NTR titles)       warehouses,          receipts,  │     return,
 Ministry planners  ──▶ │                     shipments,           assignment,│     condition)
 (allocation plans)     │  SCHOOL REGISTRY    custody chain        campaigns  │
                        │  (NSR - foundation) ASSET PASSPORT (per-copy chain) │
                        ├─────────────────────────────────────────────────────┤
                        │  Platform: IAM (Keycloak) · Sync Engine · API GW    │
                        │  Analytics & Dashboards (read-only) · Public APIs   │
                        └─────────────────────────────────────────────────────┘
    One Laravel modular monolith + Flutter offline app + PostgreSQL  (ADR-01..05)
```

**Les cinq garanties du système** (chacune traçable aux exigences de la FRS) :
1. Un registre unique faisant autorité des écoles (NSID) et des manuels scolaires (NTID) — pas de doublons, pas d'écoles fantômes.
2. Chaque exemplaire/lot dispose d'un passeport inviolable — la garde est toujours attribuable.
3. Aucune expédition ne se clôture avec un écart inexpliqué — la déperdition devient visible (s'attaque à la perte de 30 % mesurée par PETS-III).
4. Les écoles fonctionnent entièrement hors ligne jusqu'à 90 jours — les écoles isolées se synchronisent en se déplaçant (palier robuste ADR-11).
5. Les ministères voient la couverture réelle, les stocks réels, les pertes réelles — et le public dispose des premières API ouvertes du Cameroun sur les écoles et les manuels scolaires.

**Ce qui est délibérément EXCLU de la Phase I** (reporté aux phases ultérieures pour que la Phase I puisse réussir) : gestion des enseignants, module d'inspection, contenus numériques/NEDIH, passeports d'équipements et d'actifs au-delà des manuels scolaires, IA/analytique au-delà des tableaux de bord standard, construction complète du registre des élèves (la Phase I utilise le repli d'affectation au niveau de la classe, FRS FR-NTR-SM-02).

## 2. Les deux horloges : T0 et l'année scolaire (règle normative de planification)

Le plan repose sur deux horloges qui doivent être réconciliées explicitement :

- **T0** = entrée en vigueur du financement (détermine le moment où l'argent et les contrats existent).
- **SY** = l'année scolaire (school year) : passation des marchés/impression ~fév.–juin, réception en entrepôt ~juin–août, **pic de distribution août–oct.**, campagnes de vérification ~mars–mai.

**Règle MIP-R1 (arrimage à l'année scolaire) :** le pilote n'est valide que s'il couvre une **campagne de distribution complète (août–oct.)** plus la **saison de vérification suivante (mars–mai)**. La mise en service du pilote est donc arrimée au **1er juillet suivant l'atteinte de l'état de préparation**, et non à un décalage par rapport à T0. Si la préparation de la construction (incrément 5, §5) n'est acquise qu'après le 1er mai, la mise en service du pilote attend l'année scolaire suivante plutôt que d'aborder la saison à moitié prête — et les mois intermédiaires sont mis à profit pour les tests d'endurance (soak testing), l'achèvement des formations et l'enregistrement du stock existant (brownfield). Chaque vague de déploiement ultérieure (Phases II–III) est arrimée de la même manière : **les vagues d'intégration des écoles achèvent formation et dotation en appareils au 30 juin pour être opérationnelles lors de la campagne août–oct.**

**Règle MIP-R2 (découplage de l'horloge du financier) :** la durée de la Phase 0 est déterminée par le processus d'évaluation ex ante, qui dure historiquement **12 à 18 mois**, et non 6. Tous les travaux de la Phase 0 sous contrôle d'Opesware (chantiers A–C et G du §4) sont planifiés pour s'achever dans les 6 mois suivant leur lancement, c'est-à-dire **avant** le T0 le plus précoce plausible — de sorte que l'horloge institutionnelle ne puisse jamais être imputée à l'horloge technique, ni s'abriter derrière elle. Le calcul des phases du plan affiche donc les deux : la durée à partir de T0, et l'arrimage à l'année scolaire.

## 3. Vue d'ensemble du calendrier

```
Pre-T0 (now, 12–18m)   │ Phase I (T0 → T0+18m,      │ Phase II        │ Phase III       │ Phase IV
incl. Phase 0 work     │ pilot pinned per MIP-R1)   │ (→ T0+30m)      │ (→ T0+42m)      │ (→ T0+60m)
───────────────────────┼────────────────────────────┼─────────────────┼─────────────────┼──────────────
Decree · French pack   │ Build NSR→NTR→NWIDMS→      │ 5 regions       │ All 10 regions  │ Full operation
Appraisal · ADR-09/10  │ School Ops · device tender │ ~8,000 schools  │ ~18,500 schools │ 2 full school
OpenAPI · DPIA · data  │ & delivery · training ToT  │ (by 30 Jun of   │ + private       │ years of stable
Procurement route      │ 500-school pilot over one  │ its SY, MIP-R1) │   onboarding    │ operation, then
Baseline studies       │ full Aug–Oct season        │                 │                 │ handover
                       │ GATE 1                     │ GATE 2          │ GATE 3          │ final evaluation
```

La Phase IV s'achève à **T0+60m**, correspondant exactement aux 5 années de programme du Budget (comble la lacune n° 13 de la v1.0). Les GATE (jalons décisionnels de décaissement du S&E, MEF §3.3) s'appliquent avec le protocole en cas d'échec au jalon du §8.

## 4. Phase 0 — Préparer (maintenant → signature du contrat)

| Chantier | Livrables | Responsable | Échéance |
|---|---|---|---|
| A. Contrats-spécifications | Fichiers OpenAPI 3.1 (D-NSR/NTR/NWD-API) issus de la FRS §7 ; les ambiguïtés alimentent la FRS v1.1 | Opesware | Lancement+2m |
| B. Dérisquer les points durs | Évaluation de la synchronisation ADR-09 + prototype réussissant un test d'endurance de 30 jours ; spécification d'authentification hors ligne ADR-10 + soumission à l'ANTIC | Opesware | Lancement+5m |
| C. Pack français + PDF | Versions fr/ des documents 00–11 ; PDF à la charte graphique pour l'évaluation ex ante | Opesware | Lancement+3m (avant les missions d'évaluation ex ante) |
| D. Volet institutionnel | Décret (R1) ; évaluation ex ante du financement ; voie de passation des marchés (PRC 1.1–1.3) | Ministères/unité de gestion du programme (PMU) | détermine T0 |
| E. Données et volet juridique | Extraits de la carte scolaire ; référentiels de codes de matières et versions de curriculum (nécessaires à la construction dès M4) ; analyse d'impact relative à la protection des données (DPIA) ; accords d'hébergement + d'entiercement (escrow) | PMU | avant T0 |
| F. Préparation du pilote | 2 régions pilotes + 500 écoles sélectionnées ; études de référence temps-et-mouvements et TBD-P **avant que le système ne touche la moindre école** | PMU/S&E | avant l'année scolaire du pilote |
| G. Validation des appareils | Appareil de référence robuste (classe Blackview Active 8 Pro) testé sur le terrain ; dossier d'appel d'offres rédigé et pré-validé avec le financier afin que l'appel d'offres puisse être lancé le jour où sa dépendance de données est levée (§5 volet 2) | Opesware/PMU | Lancement+5m |
| H. Mobilisation des éditeurs/imprimeurs *(nouveau, comble la lacune n° 7)* | Spécification technique « QR à l'impression » transmise aux éditeurs/imprimeurs ; au moins 2 imprimeurs réalisent un essai d'étiquetage ; clauses d'étiquetage intégrées au prochain modèle de contrat d'impression (Risque R12) | PMU + Opesware | avant la saison d'impression de l'année pilote |

**Jalon de sortie (= PRC §5) :** décret signé · financement effectif · voie de passation des marchés arrêtée · FRS validée · ADR-09/10 clôturés · données de la carte scolaire disponibles · DPIA réalisée · études de référence programmées · essai d'étiquetage imprimeur réussi.

## 5. Phase I — Construction et pilote (T0 → T0+18m, pilote arrimé selon MIP-R1)

La Phase I comporte **quatre volets parallèles**, et non un seul. La v1.0 ne montrait que le volet 1 ; les trois autres sont précisément là où les programmes nationaux dérapent.

### Volet 1 — Construction logicielle (Opesware)

| # | Incrément (mois) | Ce qui est livré | Pourquoi cet ordre |
|---|---|---|---|
| 1 | M1–M3 | **Squelette de la plateforme** : socle du monolithe avec frontières de modules + contrôles de frontières en CI (Deptrac, bloquant les fusions), Keycloak, passerelle API, passerelle de synchronisation (selon l'issue de l'ADR-09), observabilité, environnements de développement/préproduction/formation alimentés en données de démonstration | Tout repose dessus ; la discipline des frontières doit exister avant la première fonctionnalité |
| 2 | M2–M5 | **NSR — Registre National des Écoles (NSR)** : pipeline d'import, dédoublonnage, hiérarchie, application de vérification GPS, remontées d'effectifs | Registre fondateur ; débloque la campagne de données (volet 2), qui débloque les appareils |
| 3 | M4–M7 | **NTR — Registre National des Manuels Scolaires (NTR) : catalogue et passeport** : titres, éditions, NTID/NCID, lots, export d'étiquettes, API publique du catalogue (consomme les référentiels de codes de matières de la Phase 0-E) | Le catalogue doit exister avant que la saison d'impression n'enregistre des lots |
| 4 | M6–M10 | **NWIDMS — Système National de Gestion des Entrepôts, des Stocks et de la Distribution (NWIDMS) : garde et logistique** : grand livre des stocks, expéditions, chaîne de garde, dossiers d'écarts, flux de réception | Consomme le NSR (destinations) + le NTR (ce qui circule) |
| 5 | M8–M12 | **Opérations scolaires (application Flutter complète)** : réception, affectation, retour, état, campagnes de vérification — cycle hors ligne complet sur les deux paliers d'appareils | Consomme les trois registres ; l'état de préparation pour l'arrimage MIP-R1 se mesure ici |
| 6 | M10–M13 | **Tableaux de bord et rapports** : vues nationale/région/département/école, flux d'indicateurs S&E (OUT-1/2/5, SYS-1..4), vue de préparation de la campagne | Projections en lecture seule sur des flux d'événements désormais réels |
| — | M11 | **Test d'intrusion 1 (externe) — DOIT être réussi avant qu'aucune donnée d'élève n'entre dans le pilote** (comble la lacune n° 9 ; test d'intrusion 2 avant la Phase III à l'échelle nationale) | Jalon de sécurité, budgétisé BUD §3.1 |

### Volet 2 — Données et appareils (PMU + Opesware) *(nouveau ; comble les lacunes n° 2 et n° 5 en partie)*

| Quand | Activité |
|---|---|
| M3–M8 | **Campagne d'assainissement des données du NSR** dans les régions pilotes (puis à l'échelle nationale) : ateliers de validation départementaux, vérification GPS, classification par palier. Livrable à M8 : liste faisant autorité des écoles pilotes **avec décomptes par palier d'appareils** |
| M8 | **Lancement de l'appel d'offres appareils** (dossier pré-validé en Phase 0-G, donc aucun délai de rédaction) |
| M8–M14 | Attribution (M10) → fabrication et expédition (M10–M13) → **dédouanement à Douala (prévoir 6 semaines ; lettre d'exonération de droits obtenue en Phase 0-D)** → enrôlement MDM, préchargement de l'application, préparation (M13–M14). **Délai total budgétisé : 6 mois de l'appel d'offres à l'école.** Cette ligne se trouve SUR la colonne vertébrale des dépendances (§7) |
| M14–mise en service du pilote | Distribution des appareils aux écoles pilotes en parallèle de la formation (volet 3) |

### Volet 3 — Préparation des personnes (PMU) *(nouveau ; comble les lacunes n° 5, 6 et 10)*

| Quand | Activité |
|---|---|
| M8 | **Équipe ministérielle de recette utilisateur (UAT) constituée** (6–8 cadres, des deux ministères) et formée aux critères d'acceptation de la FRS dont elle sera témoin ; elle co-exécute les UAT de chaque incrément à partir de M9 — et non un simple tampon en fin de parcours |
| M8–M9 | Supports de formation + environnement de formation (données réalistes préchargées) prêts ; **formation des formateurs** : 60 formateurs nationaux certifiés |
| M10–M14 | Certification des formateurs régionaux (régions pilotes), puis **vagues de formation au niveau des écoles** synchronisées avec la distribution des appareils — 1 000 agents certifiés avant la mise en service (cible OUT-P5, année 2) |
| M9–M13 | **Campagne de provisionnement des utilisateurs** : la vérification d'identité des chefs d'établissement s'adosse aux ateliers de la campagne de données (volet 2 — même salle, même déplacement) ; comptes + identifiants hors ligne liés à l'appareil (ADR-10) délivrés lors de la formation ; cible : 100 % des écoles pilotes dotées d'identifiants avant la mise en service |
| M12 | **Modèle de soutien opérationnel avant le pilote, pas pendant** (comble la lacune n° 8) : L1 = points focaux départementaux (cadres formés, inclus dans le périmètre BUD §3.4) ; L2 = assistance (helpdesk) de la PMU (2 postes sur les 8 de la PMU, BUD §3.7, avec numéro vert + canal WhatsApp) ; L3 = Opesware/ingénierie nationale. SLA : réponse L1 le jour même, L2 sous 48 h, L3 selon la sévérité. Les volumes d'appels deviennent un indicateur opérationnel du S&E |

### Volet 4 — Alignement de la chaîne d'approvisionnement sur la saison (PMU + ministères) *(nouveau ; comble la lacune n° 3)*

| Quand | Activité |
|---|---|
| Phase 0-H → saison d'impression du pilote (fév.–juin de l'année scolaire pilote) | Le **contrat d'impression de l'année pilote** inclut les clauses « QR à l'impression » ; la PMU confirme avec le MINEDUB/PAREC qu'au moins une commande nationale d'impression tombe dans la fenêtre du pilote. **Solution de repli (pour que le pilote ne meure jamais en attendant la passation des marchés) :** si aucun tirage national n'a lieu pendant l'année scolaire pilote, le pilote y substitue (a) le ré-étiquetage du stock existant d'un entrepôt plus (b) l'enregistrement complet du stock scolaire existant (FR-NTR-MIG-02) — exerçant ainsi tous les flux de garde à l'exception de l'étiquetage à l'impression, qui est alors vérifié lors de la saison d'impression de la Phase II. Les critères du GATE 1 s'appliquent au chemin effectivement exécuté |
| Juin–août de l'année scolaire pilote | Lot réel (ou de repli) : réception en entrepôt → allocation → expédition |
| **Août–oct. (arrimé)** | **CAMPAGNE DE DISTRIBUTION DU PILOTE** — 500 écoles reçoivent et affectent ; synchronisation à l'échelle, y compris le palier robuste avec synchronisation par déplacement |
| Nov.–fév. | Fonctionnement en régime de croisière, exercices mensuels de rapprochement, rodage du modèle de soutien |
| Mars–mai | **Saison de campagne de vérification** (FR-NTR-12) qui boucle le cycle annuel complet |

**GATE 1 (fin de la saison de vérification du pilote) :** OUT-P3 ≥95 % · OUT-1 ≥90 % · un cycle hors ligne complet de 30 jours sans aucune perte de données · situations de référence figées dans le Rapport de référence · modèle économique recalculé avec les valeurs mesurées. Régi par le §8.

**Équipe (Opesware + homologues nationaux intégrés dès M1, Risque R4) :** 1 architecte · 6–8 développeurs back-end · 3–4 développeurs Flutter · 1–2 développeurs web · 2 QA/automatisation des tests · 1 DevOps · 1 UX · 1 responsable de livraison — plus 4 ingénieurs nationaux en tant qu'homologues, et la PMU. Partout, la définition de « terminé » = le critère d'acceptation de la FRS passe en CI/UAT, et non « la démo fonctionne ».

## 6. Phases II–IV — Passage à l'échelle (ingénierie du déploiement)

Chaque vague répète le schéma des volets 2–3 de la Phase I (validation des données → appareils → formation + identifiants → mise en service **au 30 juin**, selon MIP-R1) :

| Phase | Fenêtre | Contenu | Jalon |
|---|---|---|---|
| II | T0+18–30m | 5 régions / ~8 000 écoles ; entrepôts régionaux opérationnels ; enregistrement du stock existant à grande échelle (FR-NTR-MIG-02) ; **étiquetage à l'impression vérifié lors de cette saison d'impression si le pilote a suivi le chemin de repli** ; vague 2 d'appareils commandée aux prix d'attribution de la Phase I (clause d'option dans l'appel d'offres de la vague 1) | GATE 2 : OUT-P2 ≥8 000 · SYS-2 ≥90 % |
| III | T0+30–42m | Les 10 régions / ~18 500 écoles publiques (NW/SW/Extrême-Nord en dernier, mode d'urgence là où nécessaire, R6) ; intégration BYOD des écoles privées ; l'affectation au niveau de l'élève s'active lorsque le Registre National des Élèves est disponible ; **test d'intrusion 2 à l'échelle nationale** | GATE 3 : couverture nationale · OUT-1 ≥97 % |
| IV | T0+42–60m | Consolidation sur **deux années scolaires complètes de fonctionnement national stable** : optimisation des performances, extensions de modules (points d'ancrage pour l'inspection, passeports d'actifs), migration K3s si l'exploitation est prête (G10), évaluation de l'analytique en colonnes (G11) ; **transfert opérationnel à l'équipe nationale de 14 personnes**, Opesware passant à un rôle de contrat de soutien | Évaluation finale (MEF §5) ; ligne de coûts récurrents active au budget national |

Tout au long des Phases II–IV, l'équipe de la plateforme se réduit à mesure que l'équipe nationale grandit — le point de croisement est un jalon contractuel, pas un espoir.

## 7. La colonne vertébrale des dépendances (ce qui bloque quoi) — désormais y compris le monde physique

```
Decree ─▶ Financing ─▶ Contract ─▶ Skeleton ─▶ NSR ─▶ NTR ─▶ NWIDMS ─▶ School Ops ─┐
             ▲              ▲                    │                                  ├─▶ PILOT ─▶ GATE 1 ─▶ Scale
       French pack    ADR-09/10 closed          ▼                                  │   (pinned to
       (appraisal)    OpenAPI, DPIA        Data campaign ─▶ Device tender ─▶ mfg/  │    Aug–Oct season,
       Printer test   carte scolaire       (tier counts)    (pre-cleared)   ship/  │    MIP-R1)
       (Phase 0-H)    baseline studies          │                           customs┤
                                                ▼                            (6m)  │
                                           UAT team + ToT ─▶ school training ──────┤
                                           (M8)              + credentialing       │
                                           Print-season contract (QR clauses) ─────┘
```

**Éléments critiques pour le calendrier NON contrôlés par Opesware :** le décret, l'entrée en vigueur du financement, la mise à disposition de la carte scolaire, le contrat d'impression de l'année pilote et le dédouanement. La discipline du plan : tout ce qu'Opesware contrôle est achevé en avance (MIP-R2) ; chaque élément non contrôlé a un responsable désigné, une date limite de sécurité dérivée de l'arrimage à l'année scolaire et — lorsque c'est possible — une solution de repli (§5 volet 4).

## 8. Gouvernance des jalons et protocole en cas d'échec au jalon *(comble les lacunes n° 11 et 12)*

**Rythme de fonctionnement :** Comité de programme mensuel (PMU + Opesware + points focaux ministériels : état d'avancement par rapport au présent plan, évolutions du registre des risques, indicateurs de soutien) · Comité de pilotage trimestriel (pré-revues de jalons, re-cotation des risques selon RSK §5, contrôle des changements) · le **Comité de contrôle des changements est le Comité de pilotage en session** — les ajouts de périmètre ne sont acceptés qu'aux revues trimestrielles et chiffrés sur la ligne de contingence (Risque R13).

**Protocole en cas d'échec au jalon — un jalon manqué déclenche une boucle bornée, pas un blocage :**
1. **Diagnostic (≤ 4 semaines) :** la PMU + le vérificateur indépendant produisent une note de cause racine par indicateur manqué (défaut système / adoption / qualité des données / dépendance externe).
2. **Fenêtre corrective (≤ 1 trimestre scolaire) :** financée sur la contingence ; protocole d'accompagnement correctif pour les échecs d'adoption (MEF §3.3 — accompagnement, pas sanctions).
3. **Nouveau test :** uniquement les indicateurs manqués, avec vérification indépendante.
4. **Second échec → décision de réduction de périmètre :** le Comité de pilotage doit choisir une option explicite — réduire la taille de la vague, prolonger le calendrier d'une saison (arrimée à l'année scolaire) ou activer le plan de réduction de périmètre par mise en pause de modules (Risque R5) — et en notifier les financiers. **L'érosion silencieuse des cibles est interdite ; la règle de gel des situations de référence du S&E (MEF §3.2) s'applique aussi aux cibles.**

## 9. Ce que signifie « terminé » (état final, T0+60m)

- Chaque titre de manuel scolaire approuvé et chaque nouveau tirage identifiés et dotés d'un passeport à l'échelle nationale ; ≥90 % du stock en circulation suivi.
- ≥97 % des manuels achetés confirmés reçus dans les écoles — contre un monde où 30 % de la valeur se perdait invisiblement.
- 18 500 écoles publiques fonctionnant numériquement pendant **deux années scolaires complètes**, y compris les écoles isolées équipées de tablettes robustes synchronisées par déplacement.
- Le premier jeu de données ouvert faisant autorité du Cameroun sur les écoles et le premier catalogue de manuels scolaires, publiés sous forme d'API nationales.
- Une équipe nationale exploitant la plateforme — y compris son assistance (helpdesk) — pour 1,22 mrd FCFA/an, soit 0,13 % des budgets des ministères.
- Des registres prêts à porter les produits suivants (passeports d'équipements, inspection, affectation des enseignants) à coût marginal.

## 10. Registre des lacunes v1.0 → v1.1 (traçabilité)

| # | Lacune v1.0 | Comblée par |
|---|---|---|
| 1 | Absence d'alignement sur l'année scolaire | §2 règle d'arrimage MIP-R1 ; §5 volet 4 ; §6 règle des vagues |
| 2 | Délai d'approvisionnement des appareils invisible | §5 volet 2 (délai budgétisé de 6 mois, dédouanement compris) ; sur la colonne vertébrale §7 |
| 3 | Dépendance au lot d'impression réel non planifiée | §5 volet 4 + chemin de repli explicite |
| 4 | Optimisme sur la durée de la Phase 0 | §2 MIP-R2 (horloge du financier 12–18 m, Opesware termine en 6) |
| 5 | Cascade de formation non calendarisée | §5 volet 3 (formation des formateurs M8–M9, vagues synchronisées avec les appareils) |
| 6 | Provisionnement des utilisateurs sans responsable | §5 volet 3 (adossé aux ateliers de la campagne de données ; identifiants ADR-10 remis lors de la formation) |
| 7 | Intégration des éditeurs/imprimeurs absente | Phase 0-H (essai d'étiquetage) + clauses contractuelles du volet 4 |
| 8 | Absence de modèle de soutien | §5 volet 3 : L1 département / L2 assistance (helpdesk) PMU (dans les postes budgétaires existants) / L3 ingénierie, opérationnel avant la mise en service, avec SLA |
| 9 | Tests de sécurité non positionnés | Le test d'intrusion 1 conditionne l'entrée des données du pilote (M11) ; test d'intrusion 2 avant la Phase III |
| 10 | Capacité UAT ministérielle absente | §5 volet 3 : équipe UAT constituée à M8, co-exécution dès M9 |
| 11 | Absence de protocole en cas d'échec au jalon | §8 : diagnostic → fenêtre corrective → nouveau test → décision explicite de réduction de périmètre |
| 12 | Absence de rythme de fonctionnement / contrôle des changements | §8 : Comité de programme mensuel, Comité de pilotage trimestriel = comité de contrôle des changements |
| 13 | 54 m contre 60 m au budget | Phase IV étendue à T0+60m (§3, §6) — correspond exactement au BUD |

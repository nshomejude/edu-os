# EduOS Cameroon — Budget chiffré et coût total de possession sur cinq ans

| | |
|---|---|
| Identifiant du document | EDUOS-BUD-001 |
| Version | 1.0 |
| Monnaie | FCFA (XAF), avec équivalents USD à 574 FCFA/USD (juil. 2026) ; le XAF est arrimé à l'EUR |
| Base de chiffrage | Chiffrage unitaire ascendant ; les hypothèses de chaque ligne sont énoncées au §5 |
| Statut | Estimation préalable à l'évaluation ex ante, classe 3 (−20%/+30%) |

*Traduction française du document original anglais [02-Costed-Budget-and-TCO](../02-Costed-Budget-and-TCO.md). En cas de divergence, la version anglaise validée fait foi.*

## 1. Synthèse

**Total du programme sur cinq ans : 11.62 milliards de FCFA ≈ US$20.2 millions**, phasé comme indiqué ci-dessous. La dernière année du programme (phase IV, A5) coûte 1.28 bn FCFA ; à partir de l'année 6, les coûts récurrents en régime de croisière s'établissent à **≈ 1.22 bn FCFA/an (US$2.1M)** (§4), soit **0.13% des budgets 2026 combinés des deux ministères** (929.5 bn FCFA) — le test d'absorbabilité par le budget national après la fin du financement des bailleurs.

| Phase | Années | Périmètre | Coût (bn FCFA) | US$M |
|---|---|---|---|---|
| I — Fondations et pilote | A1–A2 | Plateforme centrale (registres, Registre National des Manuels Scolaires (National Textbook Registry, NTR), entrepôt, opérations scolaires), 2 régions / pilote de 500 écoles | 4.44 | 7.7 |
| II — Expansion régionale | A2–A3 | 5 régions, ~8,000 écoles publiques | 3.15 | 5.5 |
| III — Déploiement national | A3–A4 | Les 10 régions, ~18,000 écoles publiques + intégration des écoles privées | 2.75 | 4.8 |
| IV — Consolidation (première année de pleine exploitation) | A5 | Pleine exploitation, optimisation, extensions de modules | 1.28 | 2.2 |
| **Total (5 ans)** | | | **11.62** | **20.2** |

Base d'échelle (Annexe des données de référence §2) : ~22,000 écoles primaires (dont 14,000 publiques) + env. 4,500 établissements secondaires ; 7.3M d'élèves ; 10 régions / 58 départements / 360 arrondissements. Le déploiement cible **d'abord les écoles publiques** (~18,000–18,500), les écoles privées étant intégrées à coût marginal en phase III (elles utilisent leurs propres équipements).

## 2. Budget par catégorie (5 ans)

| # | Catégorie | bn FCFA | US$M | % |
|---|---|---|---|---|
| 1 | Développement de la plateforme (construction, assurance qualité, sécurité) | 2.87 | 5.00 | 25% |
| 2 | Infrastructure d'hébergement et reprise après sinistre (DR) | 0.92 | 1.60 | 8% |
| 3 | Équipements des écoles et des bureaux (deux niveaux, dont tablettes durcies, kits solaires, renouvellement) | 2.54 | 4.43 | 22% |
| 4 | Formation et renforcement des capacités | 1.78 | 3.10 | 15% |
| 5 | Campagne de nettoyage et de migration des données | 0.46 | 0.80 | 4% |
| 6 | Conduite du changement et communication | 0.52 | 0.90 | 4% |
| 7 | Unité de gestion du programme (PMU) et équipe technique nationale | 1.61 | 2.80 | 14% |
| 8 | Vérification indépendante, audit, S&E | 0.29 | 0.50 | 2% |
| 9 | Provision pour imprévus (après absorption du surcoût du niveau durci, ADR-11) | 0.63 | 1.09 | 5% |
| | **Total** | **11.62** | **20.22** | 100% |

## 3. Détail par catégorie et coûts unitaires

### 3.1 Développement de la plateforme — 2.87 bn FCFA
Équipe de construction mixte de 22 ETP en moyenne sur 30 mois (majorité d'ingénieurs nationaux, chefs de file régionaux/internationaux pour l'architecture et la sécurité), taux mixte toutes charges comprises équivalent à 7.8M FCFA/ETP-mois en composition mixte (≈US$13.6k en taux mixte — pondéré à 70% national à ~US$4–6k et 30% international à ~US$18–25k) : 22 × 30 × 4.35M en moyenne = 2.87 bn. Couvre le jeu de modules de la phase I (registres des écoles, des élèves et des manuels scolaires, NTR conformément à la FRS EDUOS-FRS-NTR-001, entrepôt, distribution, opérations scolaires, tableaux de bord, moteur de synchronisation), les audits de sécurité (2 tests d'intrusion externes) et la documentation. Les extensions de modules des phases ultérieures sont comprises dans les parts de cette ligne affectées aux phases III/IV.

### 3.2 Hébergement et reprise après sinistre — 0.92 bn FCFA
Site primaire en centre de données national + site de secours (RPO 24h/RTO 72h conformément au risque R16). Capex de 0.35 bn (serveurs, stockage, réseau pour ~30M d'enregistrements d'exemplaires / 300M d'événements conformément à la FRS NFR-NTR-01) ; opex de 0.115 bn/an à partir de l'A2 (énergie, bande passante, licences, administration). Hébergement national libellé en FCFA privilégié (risque R10).

### 3.3 Équipements — 2.41 bn FCFA
Deux niveaux d'équipement, alloués école par école à partir des données d'accessibilité, de connectivité et d'énergie du Registre des écoles (ADR-11, doc 09) :

| Poste | Qté | Unitaire (FCFA) | Total (bn) |
|---|---|---|---|
| Niveau standard : smartphone/tablette Android 10+, coque renforcée — écoles connectées | ~13,000 | 92,000 (~US$160) | 1.20 |
| Niveau durci : tablette MIL-STD-810H/IP68, batterie de classe ~22,000 mAh (appareil de référence : Blackview Active 8 Pro) — écoles isolées/hors réseau fonctionnant en mode « déplacement pour synchronisation » | ~5,500 | 140,000 (~US$245) | 0.77 |
| Kits pour les bureaux de département/arrondissement (ordinateur portable + scanner ; servant aussi de points de synchronisation et de recharge) | 420 | 460,000 | 0.19 |
| Kits de recharge solaire — sous-ensemble le plus isolé uniquement (la classe de batterie du niveau durci couvre des cycles d'utilisation hebdomadaires/mensuels entre les déplacements de synchronisation) | ~3,500 | 69,000 (~US$120) | 0.24 |
| Parc de remplacement (15%/an des équipements des écoles à partir de l'A3, risque R9) | — | — | 0.14 (sur la période) |
| **Sous-total** | | | **2.54** |

Le surcoût de 0.13 bn par rapport à la base à niveau unique est absorbé par la ligne de provision pour imprévus (§2, ligne 9) ; les volumes définitifs par niveau sont des produits de la campagne de nettoyage des données du Registre National des Écoles (National School Registry, NSR) (§3.5), laquelle précède donc la passation des marchés d'équipements.

### 3.4 Formation et renforcement des capacités — 1.78 bn FCFA
En cascade : formateurs nationaux (60) → formateurs régionaux (600) → formation au niveau des écoles (1 jour, 2 agents par école, per diem + matériels ≈ 65,000 FCFA/école en moyenne, animation comprise) × 18,500 écoles = 1.20 bn ; curriculum de l'académie nationale de formation + e-learning de recyclage 0.23 bn ; formation des cadres des ministères et des départements 0.35 bn. (Atténuation du risque R3.)

### 3.5 Nettoyage et migration des données — 0.46 bn FCFA
Ateliers de validation au niveau départemental des listes d'écoles et des effectifs (58 départements × 2 tours), import du catalogue de manuels scolaires et des déclarations de stocks conformément aux exigences FRS FR-NTR-MIG-01/02. (Atténuation du risque R8.)

### 3.6 Conduite du changement et communication — 0.52 bn FCFA
Mobilisation des parties prenantes (éditeurs, imprimeurs, syndicats, associations de parents d'élèves), campagne nationale de communication, programme de reconnaissance des écoles (risque R3).

### 3.7 PMU et équipe technique nationale — 1.61 bn FCFA
PMU (coordination, passation des marchés, finances, sauvegardes, S&E, et 2 postes d'assistance opérant la ligne de support de niveau 2 dès la mise en service du pilote, conformément au MIP §5, chantier 3 — 8 agents × 5 ans) 0.69 bn ; équipe technique nationale montant de 4 à 14 ingénieurs/analystes d'ici l'A4 (contrepartie de transfert de connaissances vis-à-vis du fournisseur, risque R4) 0.92 bn. À partir de l'A5, cette équipe est l'exploitant permanent de la plateforme.

### 3.8 Vérification indépendante — 0.29 bn FCFA
Audit technique et financier annuel, vérification par tierce partie des données de suivi-évaluation (aligné sur le cadre de S&E EDUOS-MEF-001) et vérification du séquestre du code source (risque R4).

## 4. Coûts récurrents après le programme (à partir de l'année 6)

| Ligne | bn FCFA/an |
|---|---|
| Hébergement, licences, bande passante, reprise après sinistre | 0.115 |
| Équipe technique nationale (14 agents, exploitation + évolution) | 0.28 |
| Renouvellement des équipements (15% du parc) | 0.28 |
| Formation de recyclage et intégration des nouveaux agents | 0.20 |
| Indemnités de connectivité pour la synchronisation des écoles (en moyenne 1,500 FCFA/école/mois là où nécessaire) | 0.28 |
| Audit et S&E | 0.06 |
| **Total en régime de croisière** | **≈ 1.22 bn FCFA (US$2.1M)** |

= **0.13% des budgets 2026 combinés MINEDUB+MINESEC**, et ≈ 3.8% de la seule dotation 2025 du PAREC. Il est proposé d'inscrire cette ligne dans le cadre de dépenses à moyen terme (CDMT) à partir de l'A4 (atténuation du risque R5).

## 5. Hypothèses clés de chiffrage (ouvertes à la contestation lors de l'évaluation ex ante)

1. Taux de change de 574 FCFA/USD ; l'arrimage du XAF à l'EUR limite la volatilité vis-à-vis de l'USD aux mouvements EUR/USD (risque R10 ; provision de 10% constituée).
2. Déploiement dans les écoles publiques = 18,500 sites ; les écoles privées s'intègrent via le web/BYOD à coût marginal.
3. Un équipement par école constitue le plancher ; les grandes écoles (>1,500 élèves) peuvent nécessiter un second équipement — absorbé par le parc de remplacement en A3–A5 ou par un complément en phase IV.
4. Le taux de développement mixte suppose une équipe majoritairement nationale ; une construction entièrement internationale doublerait environ la ligne 1 — il s'agit d'un choix délibéré de stratégie d'approvisionnement, non d'un gonflement budgétaire.
5. Les étiquettes QR ne figurent **pas** dans ce budget : l'impression des étiquettes en imprimerie est imposée dans les contrats d'impression à ≈1–2% du prix unitaire du manuel (FRS §3.2, risque R12) et relève du budget existant de passation des marchés de manuels scolaires, non du budget de la plateforme.
6. Aucune extension de la connectivité des écoles n'est budgétisée (elle relève de l'agenda national des télécommunications) ; la conception repose sur le principe « hors ligne d'abord » (offline-first) avec synchronisation périodique (FRS §9).
7. Les coûts excluent les licences de contenus numériques/manuels numériques (périmètre du module NEDIH, dossier économique distinct à venir).

## 6. Stratégie de financement

| Source | Part cible | Justification |
|---|---|---|
| IDA / Banque mondiale (successeur de l'ERSP ou financement additionnel) | 50–60% | Continuité directe avec la composante manuels scolaires du P160926 ; cette plateforme protège le rendement de cet investissement |
| Don GPE de renforcement des capacités systémiques | 15–20% | Le guichet de renforcement des systèmes correspond aux infrastructures de type registre/SIGE (EMIS) |
| Gouvernement du Cameroun (PIB + coûts récurrents à partir de l'A4) | 20–25% | Signal d'appropriation exigé par le risque R5 ; ligne de coûts récurrents inscrite au CDMT |
| Autres partenaires (AFD, UNICEF, guichets numériques de la BAD) | 5–10% | Les lignes équipements/solaire et formation se prêtent proprement à un fléchage |

Les jalons décisionnels des phases sont alignés sur le registre des risques (§5) : aucune phase n'est décaissée tant que les cibles de S&E de la phase précédente (EDUOS-MEF-001) n'ont pas été vérifiées.

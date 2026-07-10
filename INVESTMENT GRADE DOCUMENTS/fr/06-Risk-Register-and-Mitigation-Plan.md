# EduOS Cameroon — Registre des risques et plan d'atténuation du programme

| | |
|---|---|
| Document ID | EDUOS-RSK-001 |
| Version | 1.0 |
| Responsable | Comité de pilotage du programme (proposé) |
| Cycle de revue | Trimestriel, et à chaque jalon décisionnel |

*Traduction française du document original anglais [06-Risk-Register-and-Mitigation-Plan.md](../06-Risk-Register-and-Mitigation-Plan.md). En cas de divergence, la version anglaise validée fait foi.*

Notation : Probabilité (P) et Impact (I) chacun de 1 à 5 ; Exposition = P×I. Les risques ≥ 15 sont critiques pour le programme et exigent un responsable désigné ainsi qu'une mesure d'atténuation active dotée d'un budget. Ce registre est un document évolutif ; l'évaluation initiale ci-dessous reflète les conditions antérieures à la Phase I.

## 1. Risques critiques pour le programme (Exposition ≥ 15)

| # | Risque | P | I | Exp | Mesure d'atténuation | Risque résiduel |
|---|---|---|---|---|---|---|
| R1 | **Défaillance de la gouvernance bi-ministérielle.** Le MINEDUB et le MINESEC ne parviennent pas à s'accorder sur les registres partagés (écoles, élèves, manuels scolaires), produisant deux systèmes divergents | 4 | 5 | 20 | Décret interministériel instituant un Comité de pilotage conjoint du programme et une charte unique de propriété des données **avant** la signature de tout contrat de construction ; registres partagés conçus dès le premier jour avec un cloisonnement par ministère (voir FRS §1) | 10 |
| R2 | **Réalité de la connectivité et de l'électricité pire que prévu.** Les écoles rurales ne peuvent assurer même une synchronisation périodique | 4 | 4 | 16 | « Hors ligne d'abord » (offline-first) est une exigence architecturale impérative (NFR-NTR-05 : 90 jours de fonctionnement hors ligne) ; points de synchronisation dans les bureaux départementaux avec formulaires papier de repli numérisés au niveau départemental ; kits de recharge solaire au budget d'équipement pour les écoles hors réseau | 8 |
| R3 | **Résistance au changement / non-utilisation au niveau des écoles.** Les chefs d'établissement perçoivent le système comme de la surveillance et une charge de travail, non comme une aide | 4 | 4 | 16 | Conception de la valeur « l'école d'abord » (l'application doit faire gagner du temps au chef d'établissement dès la première semaine — réception et réquisition avant le reporting) ; cascade de formation appuyée par des per diem ; reconnaissance par palmarès au niveau des écoles plutôt qu'un cadrage punitif en année 1 ; indicateurs d'utilisation intégrés au S&E avec appui correctif, non des sanctions | 8 |
| R4 | **Capture de la passation des marchés / dépendance vis-à-vis du fournisseur.** Un fournisseur unique contrôle indéfiniment le code source, les données et les prix | 3 | 5 | 15 | Clauses contractuelles : mise sous séquestre intégrale du code source avec dépôts trimestriels, export des données aux normes ouvertes (NFR-NTR-09), documentation des API comme livrable de recette, plafonnement de la révision des prix de maintenance, équipe nationale intégrée aux équipes du fournisseur dès la Phase I avec jalons contractuels de transfert de connaissances | 6 |
| R5 | **Déficit de financement après la phase des bailleurs.** Plateforme construite sur fonds des partenaires, puis coûts récurrents non financés dans le budget national | 3 | 5 | 15 | Ligne de coûts récurrents (§ document TCO 02) présentée au MINFI pour inscription au cadre de dépenses à moyen terme avant la Phase III ; plafond de conception à coût objectif sur la dépense récurrente (≤ 15 % du coût de construction/an) ; plan de réduction progressive du périmètre identifiant les modules à suspendre en premier en cas de déficit de financement | 9 |

## 2. Risques élevés (10–14)

| # | Risque | P | I | Exp | Mesure d'atténuation |
|---|---|---|---|---|---|
| R6 | La crise sécuritaire dans les régions du NW/SW et de l'Extrême-Nord empêche le déploiement au niveau des écoles — **et la présence du programme pourrait elle-même mettre en danger les écoles/le personnel (principe de « ne pas nuire »)** | 4 | 3 | 12 | Le déploiement phasé par région place les régions affectées dans la dernière vague ; le mode de suivi au niveau du lot réduit la charge de travail sur le terrain ; opération au niveau départemental lorsque l'accès aux écoles n'est pas sûr ; **auxquels s'ajoutent les mesures d'atténuation de sensibilité aux conflits de l'EDUOS-ESS-001 §4 (appareils sans marquage, chiffrement au repos avec paramétrage par défaut au niveau de la classe, absence de schémas de déplacement de synchronisation prévisibles, audit renforcé du mode d'urgence) — une évaluation spécialisée de sensibilité aux conflits est un livrable de la Phase 0 et une condition de jalon pour les vagues des régions affectées** |
| R7 | Retard du Registre National des Élèves (dépendance de l'affectation des manuels) | 3 | 4 | 12 | Le NTR est conçu pour fonctionner en mode dégradé : affectation au niveau de la classe plutôt qu'au niveau de l'élève jusqu'à la disponibilité du NSR (repli FRS FR-NTR-SM-02) ; dépendance explicitement séquencée dans le plan de mise en œuvre |
| R8 | Qualité des données d'amorçage (listes d'écoles, effectifs) trop médiocre pour des tableaux de bord crédibles | 4 | 3 | 12 | La Phase I comprend une campagne financée de fiabilisation des données avec ateliers de validation au niveau départemental ; les tableaux de bord affichent des scores de confiance des données plutôt que de présenter des données défaillantes comme des faits |
| R9 | Pertes/vols/casses d'appareils dans les écoles supérieurs aux prévisions | 3 | 4 | 12 | Ligne de remplacement annuel des appareils de 15 % dans le TCO ; les appareils sont la propriété de l'école, enregistrés dans le module Passeport des Actifs ; effacement à distance via MDM |
| R10 | Choc de change/d'inflation sur un budget libellé en FCFA avec des coûts d'informatique en nuage/de matériel libellés en USD | 3 | 4 | 12 | Ligne de provision pour imprévus de 10 % (document budgétaire 02) ; préférence pour l'hébergement national (base de coûts en FCFA) en régime de croisière ; matériel acquis par tranches phasées, non d'emblée |
| R11 | Risque lié aux personnes clés dans l'équipe technique nationale | 3 | 3 | 9→12 dans les phases ultérieures | Équipe minimale de 3 personnes par compétence critique d'ici la Phase III ; documentation comme livrable contractuel, vérifiée aux jalons décisionnels |

## 3. Risques modérés (5–9) — sous surveillance

| # | Risque | P | I | Exp | Réponse |
|---|---|---|---|---|---|
| R12 | Résistance des éditeurs/imprimeurs à l'enregistrement des lots et à l'étiquetage QR | 3 | 3 | 9 | Étiquetage exigé dans les contrats d'impression (coût ≈ 1–2 % du prix unitaire) ; impression en presse préférée aux autocollants |
| R13 | Dérive du périmètre sous la pression d'autres directions (« ajoutez les examens, ajoutez la paie… ») | 4 | 2 | 8 | Comité de contrôle des changements relevant du Comité de pilotage ; ajouts à la feuille de route uniquement aux jalons décisionnels |
| R14 | La fragmentation des appareils Android fait dysfonctionner l'application sur les terminaux bas de gamme | 2 | 3 | 6 | Spécification minimale fixée (Android 10+, 2 GB) ; acquisition d'appareils standardisée à ≤ 3 modèles par vague |
| R15 | Contestation juridique relative aux données personnelles des élèves | 2 | 4 | 8 | Analyse d'impact relative à la protection des données en Phase I ; revue de conformité à la loi 2010/012 ; minimisation des données (pas de biométrie dans le NTR) |
| R16 | Panne de l'informatique en nuage/du centre national de données | 2 | 3 | 6 | Le « hors ligne d'abord » (offline-first) tolère une panne centrale (NFR-NTR-04) ; environnement de reprise après sinistre avec RPO ≤ 24h, RTO ≤ 72h |

## 4. Traçabilité des risques vers le budget

Mesures d'atténuation dotées de lignes de coût directes dans le TCO (document 02) : R2 (kits solaires, points de synchronisation départementaux), R3 (cascade de formation, conduite du changement), R4 (séquestre, équipe nationale), R8 (campagne de fiabilisation des données), R9 (remplacement des appareils 15 %/an), R10 (provision pour imprévus de 10 %), R16 (environnement de reprise après sinistre).

## 5. Revues des risques aux jalons décisionnels

Aucune phase ne démarre tant que : tous les risques critiques pour le programme ne disposent pas d'un responsable + d'une mesure d'atténuation active ; tout risque qui s'est matérialisé au cours de la phase précédente n'a pas fait l'objet d'une leçon documentée inscrite au registre ; le registre n'a pas été renoté et réapprouvé par le Comité de pilotage.

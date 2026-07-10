# EduOS Cameroun — Liste de contrôle de préparation avant construction

| | |
|---|---|
| Identifiant du document | EDUOS-PRC-001 |
| Version | 1.0 |
| Objet | Le jalon entre le présent dossier documentaire et la première ligne de code de production. La construction démarre lorsque chaque élément **B** est au statut TERMINÉ ; les éléments **P** avancent en parallèle et doivent être TERMINÉS avant leur consommateur indiqué |
| Revue | Comité de pilotage (une fois constitué) ; dans l'intervalle, Opesware + points focaux ministériels |

*Traduction française du document original anglais [10-Pre-Build-Readiness-Checklist](../10-Pre-Build-Readiness-Checklist.md). En cas de divergence, la version anglaise validée fait foi.*

Types d'éléments : **B** = élément bloquant (aucun contrat de construction avant le statut TERMINÉ) · **P** = piste parallèle (démarrer immédiatement, achever avant que le consommateur en ait besoin).

## 1. Cadre institutionnel et financement (responsables : ministères, financiers)

| # | Type | Élément | Responsable | Consommateur / logique d'échéance | Statut |
|---|---|---|---|---|---|
| 1.1 | **B** | Décret interministériel : Comité de pilotage conjoint MINEDUB–MINESEC + charte de propriété des données des registres partagés (risque R1) | Cabinets MINEDUB + MINESEC | Tout le reste — il constitue l'autorité de signature pour tous les autres éléments | OUVERT |
| 1.2 | **B** | Engagement de financement (véhicule cible : successeur de l'ERSP/PAREC ou financement additionnel ; BUD §6), y compris trajectoire d'inscription de la ligne récurrente dans le CDMT (risque R5) | Ministères + MINFI + Banque mondiale/GPE | Contrat de construction | OUVERT |
| 1.3 | **B** | Voie de passation des marchés pour le développeur tranchée : appel d'offres, gré à gré justifié, ou phase 0 sur financement gouvernemental + phase I en appel d'offres — conforme aux règles de passation des marchés du financier | UGP + spécialiste de la passation des marchés du financier | Contrat de construction ; **doit être traitée avant l'évaluation ex ante, pas après** | OUVERT |
| 1.4 | **B** | Validation et approbation ministérielles des FRS 04/07/08 — en particulier des décisions de politique qu'elles contiennent : schémas NTID/NSID, politique de suivi par exemplaire ou par lot selon le titre, règles métier, référentiel de codes des matières | Comité technique conjoint | Périmètre du contrat de construction ; requiert en pratique 1.6 (version française) | OUVERT |
| 1.5 | P | UGP mise en place (8 postes, BUD §3.7) ; responsable produit API nommé (ADR-07) ; premiers ingénieurs nationaux recrutés comme homologues du prestataire (risques R4/R11) | Ministères | Sprint 1 — les homologues présents dès le premier jour | OUVERT |
| 1.6 | P | **Traduction française de l'intégralité du dossier** (documents 00–10) + rendu PDF aux couleurs du programme | Opesware | Consommateur : évaluation ex ante 1.2 et validation 1.4 — de fait une tâche immédiate | OUVERT |

## 2. Ingénierie de phase 0 (responsable : Opesware — peut démarrer immédiatement)

| # | Type | Élément | Consommateur | Statut |
|---|---|---|---|---|
| 2.1 | **B** | ADR-09 clos : évaluation construire ou adopter du moteur de synchronisation (PowerSync / ElectricSQL / Couchbase Lite) + prototype fonctionnel réussissant un essai d'endurance hors ligne de 30 jours et un exercice de réconciliation | Périmètre du contrat de construction — le composant le plus risqué sécurisé en premier | OUVERT |
| 2.2 | **B** | ADR-10 clos : conception de l'authentification hors ligne (identifiants liés à l'appareil, déverrouillage par PIN, cache de rôles hors ligne, révocation différée) + revue de sécurité ANTIC | Contrat de construction ; pilote | OUVERT |
| 2.3 | P | Fichiers de contrats OpenAPI 3.1 rédigés à partir des sections §7 des FRS : D-NTR-API, D-NSR-API, D-NWD-API | Dossier d'appel d'offres (1.3) ; développement parallèle mobile/web | OUVERT |
| 2.4 | P | Éléments du registre des lacunes G1 (décision sur l'environnement d'exécution de la passerelle de synchronisation) et sélections d'outillage G3–G8 (MDM, BI, sauvegarde, opérations de sécurité, tuiles cartographiques, tests de charge/appareils) consignés dans l'annexe technique du dossier d'appel d'offres | Contrat de construction | OUVERT |
| 2.5 | P | Passage de la conception à la construction : dossier de langage de conception converti en kit d'interface Flutter + web mettant en œuvre la spécification du système de conception, les gabarits bilingues et les indicateurs d'état de synchronisation hors ligne (FRS-NTR §10) | Travaux d'interface du sprint 1 | OUVERT |
| 2.6 | P | Validation de l'appareil durci : appareil de référence (classe Blackview Active 8 Pro) testé sur le terrain — qualité de lecture des codes QR par caméra sur étiquettes usées, cycle de service de la batterie, chute/poussière — avant la finalisation de l'appel d'offres des appareils (ADR-11) | Passation des marchés d'appareils | OUVERT |

## 3. Données et volet juridique (responsables : UGP + ministères)

| # | Type | Élément | Consommateur | Statut |
|---|---|---|---|---|
| 3.1 | **B** | Extraits de la carte scolaire ministérielle obtenus (des deux ministères) — l'amorce du NSR ; aucune alternative publique n'existe (BDA §6.4) | Migration du NSR (FR-NSR-MIG-01) ; allocation des paliers d'appareils (ADR-11) | OUVERT |
| 3.2 | P | Tables de l'annuaire MINESEC 2024/25 + dépense exacte de la composante manuels scolaires de l'ERSP issue des ISR → actualisation des modèles BDA et ECO | Évaluation ex ante (1.2) | OUVERT |
| 3.3 | P | Référentiels officiels de codes des matières et versions des curricula des deux ministères (complétion de l'annexe A des FRS) | Données de référence du NTR (D-NTR-REF) | OUVERT |
| 3.4 | **B** | Analyse d'impact relative à la protection des données + revue de conformité à la loi 2010/012 (risque R15) | Avant tout traitement de données d'élèves — c'est-à-dire avant le pilote | OUVERT |
| 3.5 | P | Accords d'hébergement : conditions du centre de données national + site de reprise ; GitLab auto-hébergé provisionné ; **convention de séquestre du code source rédigée** (risque R4) | Signature du contrat de construction | OUVERT |
| 3.6 | **B** | Instruments E&S formalisés avec le financier (PEES/PMPP issus d'EDUOS-ESS-001), y compris plan DEEE dans l'appel d'offres des appareils et clauses de protection de l'enfance dans les contrats de terrain | Évaluation ex ante (1.2) ; appel d'offres des appareils (2.6) | OUVERT |
| 3.7 | P | Évaluation spécialisée de sensibilité aux conflits (accès terrain NW/SW/Extrême-Nord) conformément à EDUOS-ESS-001 §4 | Conception de la vague de phase III pour les régions affectées ; jalon du Comité de pilotage | OUVERT |
| 3.8 | P | Seuils et types d'examen du plan de passation des marchés (EDUOS-PRO-001) finalisés avec le spécialiste de la passation des marchés du financier | Lancement du premier marché | OUVERT |

## 4. Préparation du pilote (responsable : UGP + S&E)

| # | Type | Élément | Consommateur | Statut |
|---|---|---|---|---|
| 4.1 | P | Sélection du pilote : 2 régions, 500 écoles, échantillonnage stratifié (urbain/rural/isolé, raccordé/non raccordé au réseau, les deux sous-systèmes) — enregistrée en tant que ProgrammeParticipation (FR-NSR-10) | Plan de déploiement de la phase I | OUVERT |
| 4.2 | **B** | Études de référence pré-système exécutées **avant tout changement de comportement** : étude temps-mouvements dans 20 écoles (ECO §8.3, S&E OUT-6) + mesures dans les régions pilotes pour tous les indicateurs TBD-P (S&E §3.2) | Rapport de situation de référence du S&E ; ré-estimation du modèle économique | OUVERT |
| 4.3 | P | Points de synchronisation/recharge départementaux désignés dans les régions pilotes (risque R2, ADR-11) | Logistique du pilote | OUVERT |

## 5. Le jalon

**La signature du contrat de construction requiert :** 1.1–1.4, 2.1, 2.2, 3.1, 3.4 au statut TERMINÉ et 4.2 planifié avec financement. Tout le reste doit être TERMINÉ avant le consommateur indiqué en regard.

**Ce qu'Opesware peut lancer cette semaine sans attendre personne :** 1.6 (dossier en français), 2.1–2.6 (ingénierie de phase 0), et le plaidoyer qui accélère 1.1–1.3.

La séquence en une ligne : **décret → évaluation ex ante (avec le dossier en français) → la phase 0 clôt les ADR-09/10 + données/juridique → contrat de construction → code.**

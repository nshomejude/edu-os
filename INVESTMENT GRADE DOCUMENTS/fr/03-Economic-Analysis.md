# EduOS Cameroon — Analyse économique et financière

| | |
|---|---|
| Document ID | EDUOS-ECO-001 |
| Version | 1.0 |
| Méthode | Analyse coûts-avantages, horizon de 10 ans, taux d'actualisation social de 8 %, scénarios prudent/central/optimiste |
| Intrants | Annexe des données de référence (EDUOS-BDA-001) ; Budget/TCO (EDUOS-BUD-001) |

*Traduction française du document original anglais [03-Economic-Analysis.md](../03-Economic-Analysis.md). En cas de divergence, la version anglaise validée fait foi.*

## 1. Le problème économique à chiffrer

Le Cameroun a déjà consenti le grand investissement en manuels scolaires : le programme ERSP/PAREC (IDA US$175M + GPE US$52.45M) a fait passer le ratio élèves-manuels de 12:1 (2016) à 3 manuels pour 2 élèves (2023). La question à laquelle répond la présente analyse est la suivante : **que vaut la protection et l'optimisation d'un flux national de manuels scolaires de cette ampleur au moyen d'une gestion numérique du cycle de vie de bout en bout ?**

Les canaux de perte ne sont pas hypothétiques — ils sont **mesurés au Cameroun**. L'enquête PETS III de l'INS sur l'éducation (2019) a constaté **qu'environ 30 % de la valeur monétaire des fournitures du « paquet minimum » scolaire se perd entre l'expédition par la commune et la réception par l'école**, des délais de livraison de 3 à 6 mois pour plus de 80 % des chefs d'établissement, et 11,8 % des chefs d'établissement déclarant des pertes lors du retrait (BDA §3). Les canaux quantifiables :

- **L1 — Déperdition à la distribution :** manuels acquis mais n'atteignant jamais les salles de classe (détournement, mauvais acheminement, pertes en entrepôt).
- **L2 — Inadéquation des allocations :** écoles sur- ou sous-dotées parce que les prévisions reposent sur des données d'effectifs obsolètes, imposant des redistributions d'urgence ou laissant des excédents inutilisés.
- **L3 — Mise hors service prématurée des manuels :** en l'absence de suivi de l'état, de campagnes de récupération et de réparation, les manuels sortent du service plus vite que leur durée de vie théorique.
- **L4 — Charge administrative :** des milliers de jours-agents consommés par les comptages manuels, les états papier et les rapprochements à 4 niveaux administratifs.

## 2. Le flux de référence à protéger

Flux national annuel de renouvellement des manuels scolaires, estimation centrale :

| Paramètre | Valeur | Base |
|---|---|---|
| Manuels en circulation (cible du primaire public : 3 manuels essentiels × ~4M d'élèves du primaire public, plus les flux du secondaire) | ~12–14M de manuels | BDA §2–3 |
| Durée de vie moyenne en service | 3 ans (théorique) | valeur par défaut FRS |
| Acquisitions annuelles de remplacement + croissance | ~4,5M de manuels/an | circulation ÷ durée de vie |
| Coût unitaire (post-réforme, signalé ESTIMATION) | 2,000 FCFA | BDA §3, fourchette 1,500–3,000 |
| **Flux annuel d'acquisition de manuels scolaires** | **≈ 9.0 mds FCFA/an (US$15.7M)** | valeur dérivée |

Cet ordre de grandeur est cohérent avec l'allocation 2025 du PAREC de 32.3 mds FCFA, dont la dotation en manuels scolaires constitue une composante principale. **Action au stade de l'évaluation ex ante :** remplacer ce flux dérivé par la ligne « manuels scolaires » effective tirée des ISR de l'ERSP (BDA §6.2).

## 3. Flux d'avantages quantifiés (annuels, en pleine exploitation)

| # | Avantage | Prudent | Central | Optimiste | Mécanisme |
|---|---|---|---|---|---|
| B1 | Réduction de la déperdition à la distribution (part du flux récupérée) | 3% → 0.27 md | 6% → 0.54 md | 10% → 0.90 md | les passeports par exemplaire/lot rendent le détournement visible (FRS §5.2) ; **le PETS III a mesuré une déperdition de valeur d'environ 30 % sur les paquets de fournitures scolaires au Cameroun** — même le cas optimiste ne suppose la récupération que d'un tiers du taux de perte mesuré |
| B2 | Efficience des acquisitions pilotées par les prévisions (réduction des sur-/sous-dotations) | 3% → 0.27 md | 5% → 0.45 md | 8% → 0.72 md | la prévision fondée sur les effectifs réels remplace la remontée en cascade (analyse du problème existante, ch. 3) |
| B3 | Allongement de la durée de vie en service via le suivi de l'état + campagnes de récupération (demande de remplacement ↓) | +0.25 an de vie → 0.70 md | +0.5 an → 1.29 md | +0.75 an → 1.80 md | vérification annuelle (FR-NTR-12), circuit de réparation, redevabilité sur les retours |
| B4 | Gains de temps administratif (18,500 écoles × 12 jours-agents/an économisés × 6,000 FCFA/jour, valorisés au salaire fictif) | 50% capté → 0.67 md | 75% → 1.00 md | 100% → 1.33 md | la réception/le retour par scan remplace les registres manuels et les états papier |
| | **Avantage annuel total (mds FCFA)** | **1.91** | **3.28** | **4.75** | |

Montée en charge des avantages : 15 % de la pleine valeur en A2 (pilote), 40 % en A3, 70 % en A4, 100 % à partir de A5.

**Délibérément non monétisés (potentiel de hausse) :** les résultats d'apprentissage liés à une disponibilité effective accrue des manuels ; la réduction des coûts d'audit/PETS ; les effets sur les prix d'acquisition induits par la transparence de la demande ; la même infrastructure au service de modules futurs (passeports d'équipements/d'actifs, inspection) à un coût marginal de registre quasi nul.

## 4. Résultats

VAN à 10 ans au taux d'actualisation de 8 %, rapportée au coût total du programme (11.62 mds FCFA sur 5 ans + 1.22 md/an de coûts récurrents ensuite) :

| Scénario | VAN (mds FCFA) | Ratio avantages-coûts | TRIE | Délai de récupération |
|---|---|---|---|---|
| Prudent | +1.6 | 1.11 | ~11% | Année 9 |
| **Central** | **+9.5** | **1.65** | **~22%** | **Année 6** |
| Optimiste | +18.1 | 2.24 | ~33% | Année 5 |

Même le cas prudent — 3 % de déperdition récupérée, 3 % de gains de prévision, un quart d'année de vie de manuel, la moitié des économies administratives — dépasse le taux de rendement minimal. Le cas central rapporte environ **1.65 FCFA par FCFA investi** au titre des seules économies quantifiées, avant toute valorisation des résultats d'apprentissage.

## 5. Analyse de sensibilité

| Variable | Variation testée | Impact sur la VAN (cas central) |
|---|---|---|
| Coût unitaire du manuel : 1,500 vs 3,000 FCFA | ±33% sur le flux | VAN 5.6 → 13.4 mds (reste positive) |
| Montée en charge des avantages retardée d'un an | — | VAN −1.9 md |
| Dépassement du capex +30% (borne supérieure classe 3) | — | VAN −2.6 mds |
| B3 (durée de vie des manuels) échoue entièrement | suppression du flux le plus important | VAN +3.1 mds (toujours positive) |
| Taux d'actualisation à 12% | — | VAN +6.2 mds |

**Condition de seuil de rentabilité :** le programme est à VAN positive s'il récupère seulement **≈ 2.1 % du flux annuel de manuels scolaires en termes d'efficience combinée** — à comparer à un **taux de déperdition de valeur mesuré de 30 %** sur des flux de fournitures scolaires comparables au Cameroun (PETS III). Le seuil de rentabilité représente un quatorzième du taux de perte documenté.

## 6. Soutenabilité budgétaire

Le coût récurrent en régime de croisière (1.22 md FCFA/an) équivaut à **0,13 % des budgets 2026 cumulés des deux ministères** et à environ **13 % du flux annuel de manuels scolaires qu'il protège**. Un système coûtant 13 % d'un flux pour éliminer des pertes vraisemblablement ≥ 15–25 % de ce flux constitue un engagement récurrent défendable ; la trajectoire d'inscription au cadre de dépenses à moyen terme (CDMT) est précisée dans EDUOS-BUD-001 §6 et le risque R5.

## 7. Note distributionnelle

Les avantages se concentrent là où les pertes se concentrent : les écoles rurales et celles des zones affectées par les crises, avec des ratios de 30:1 avant la réforme, sont précisément celles où la visibilité de la distribution et les campagnes de récupération produisent leurs effets. La conception « hors ligne d'abord » (offline-first) (contraintes du BDA §5) est ce qui permet aux avantages d'atteindre les zones rurales électrifiées à 26 %, et non les seules villes connectées — l'équité est intégrée dès la conception, et non présumée.

## 8. Affinements au stade de l'évaluation ex ante

1. ~~Extraire les taux de déperdition du PETS III~~ **FAIT** — la déperdition de 30 % de la valeur du paquet minimum a été extraite du rapport (BDA §3) ; il reste à valider son applicabilité spécifique aux flux de manuels scolaires pendant le pilote (les manuels peuvent connaître moins de déperdition que des fournitures fongibles — le cas central de 6 % du modèle ne suppose déjà qu'un cinquième du taux PETS).
2. Substituer au flux de manuels dérivé (§2) la dépense exacte de la composante « manuels scolaires » de l'ERSP tirée des ISR ; l'intrant de coût unitaire est désormais ancré sur la valeur vérifiée de US$2.90 (BDA §3) — l'intrant de 2,000 FCFA du modèle se situe à moins de 15 % de la moyenne vérifiée de 1,750 FCFA et demeure prudent au regard de la fourchette de sensibilité déjà testée (§5).
3. Valider les gains de temps administratif (B4) par une étude des temps et mouvements dans 20 écoles pendant le pilote — un livrable du S&E (EDUOS-MEF-001 §5).
4. Réexécuter le présent modèle avec les valeurs mesurées lors du pilote au jalon décisionnel de la Phase II ; la structure par phases permet de retester la décision d'investissement avant l'engagement de 60 % des fonds.

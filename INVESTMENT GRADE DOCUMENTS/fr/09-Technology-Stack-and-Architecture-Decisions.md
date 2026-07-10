# EduOS Cameroun — Pile technologique et registre des décisions d'architecture

| | |
|---|---|
| Identifiant du document | EDUOS-TSA-001 |
| Version | 1.0 |
| Développeur | **Opesware Technologies** · Douala, Cameroun · [www.opesware.com](https://www.opesware.com) · eudos@opesware.com · +237 670 41 62 38 |
| Format | Registres de décision d'architecture (Architecture Decision Records, ADR) : chaque décision énonce le contexte, les options, le choix et les conséquences |
| Contraintes directrices | Exigences non fonctionnelles des FRS (« hors ligne d'abord » (offline-first), 30 M d'exemplaires / 300 M d'événements, PostgreSQL, absence de dépendance fournisseur), registre des risques (R2 connectivité, R4 dépendance fournisseur, R5 coûts récurrents, R11 dépendance aux personnes clés), annexe de la situation de référence §5 (26 % d'électrification rurale, poches 2G/3G) |

*Traduction française du document original anglais [09-Technology-Stack-and-Architecture-Decisions](../09-Technology-Stack-and-Architecture-Decisions.md). En cas de divergence, la version anglaise validée fait foi.*

## 0. Principes de sélection (définition de « l'épreuve du temps »)

Une plateforme nationale survit à chaque cycle d'engouement pour un framework, à chaque cabinet ministériel et, probablement, à l'équipe actuelle d'Opesware. « Résister à l'épreuve du temps » signifie donc, par ordre de priorité :

1. **Maintenable par le vivier de compétences qui existe au Cameroun** — et non par celui de San Francisco. Une pile technologique n'est durable que si l'équipe technique nationale du MINEDUB/MINESEC (BUD §3.7) peut recruter pour elle à Douala et à Yaoundé en 2035.
2. **Open source, sans licences par poste ou par cœur** — le budget récurrent (1,22 md FCFA/an) doit financer des personnes et de l'hébergement, pas des renouvellements de licences ; la souveraineté exige l'accès au code source (risque R4).
3. **Éprouvée et sans surprise à l'échelle nationale** — chaque composant a ≥ 10 ans d'existence, une base installée massive et plusieurs prestataires de support.
4. **Les données survivent au code** — le schéma de la base de données et les contrats d'API sont les actifs à 20 ans ; toute couche applicative doit pouvoir être réécrite par-dessus.

---

## ADR-01 — Topologie du système : monolithe modulaire avec frontières DDD, évoluant vers des services

**Contexte.** Les volumes de vision (chapitre 27) décrivent un paysage de 22 microservices. Les microservices résolvent un problème de passage à l'échelle organisationnel (de nombreuses équipes déployant indépendamment) au prix de la complexité des systèmes distribués : partitions réseau, traçage distribué, cohérence à terme généralisée, et une charge d'exploitation qui se mesure en ingénieurs plateforme dédiés. La phase I est construite par **une seule équipe** (Opesware + homologues nationaux) pour un système dont le problème le plus difficile est la *synchronisation hors ligne*, et non la mise à l'échelle horizontale des services : 70 entrepôts et 18 500 écoles se synchronisant quotidiennement représentent une charge centrale modeste (NFR-NTR-03 : 200 sessions de synchronisation/minute en pointe).

**Décision.** Construire un **monolithe modulaire** : un backend déployable unique dont les modules internes sont les contextes bornés du DDD (ADR-06), communiquant exclusivement par des interfaces en mémoire et des événements de domaine — jamais en accédant directement aux tables des autres modules. Trois composants sont des processus séparés dès le premier jour parce que leur profil d'exécution diffère réellement : la **passerelle de synchronisation** (connexions longue durée, absorption des rafales), le **worker de notifications** et le **réplica de lecture/reporting**. Le paysage de services du chapitre 27 est conservé comme **carte cible de décomposition** : tout module peut être extrait en service ultérieurement, car les frontières sont déjà contractuelles.

**Pourquoi ce choix résiste à l'épreuve du temps.** Le mode de défaillance qui tue les systèmes nationaux n'est pas « le monolithe n'a pas tenu la charge » — c'est « plus personne ne comprend le système distribué ». Un monolithe modulaire est exploitable par une équipe nationale de 4 personnes (risque R11) ; des microservices prématurés consommeraient l'intégralité du budget récurrent en DevOps. L'extraction reste une opération de refactoring, et non une réécriture, parce que les frontières de modules = les frontières de contextes.

## ADR-02 — Base de données : PostgreSQL 16+, source unique de vérité

**Décision.** PostgreSQL pour l'ensemble des données transactionnelles (déjà normatif, NFR-NTR-09/NFR-NWD-05). Partitionnement des tables PassportEvent/CustodyEvent par année scolaire (NFR-NTR-01). Réplica de lecture pour les rapports et le catalogue public. **Pas de persistance polyglotte en phase I** — une seule technologie de base de données maîtrisée en profondeur par l'équipe nationale vaut mieux que quatre maîtrisées superficiellement.

**Pourquoi.** PostgreSQL a 30 ans, est entièrement open source, fonctionne sur le matériel du centre de données national, gère confortablement des tables d'événements de 300 M de lignes grâce au partitionnement, et offre JSONB (charges utiles d'événements), PostGIS (cartographie des écoles, FR-NSR-08, proximité de redistribution FR-NWD-11) et la réplication logique (reprise après sinistre, RPO 24 h) — l'intégralité du besoin de stockage dans un seul moteur éprouvé et sans surprise.

## ADR-03 — Langage et framework backend : PHP 8.3+ / Laravel 11, avec architecture modulaire stricte

**Options évaluées.** Java/Spring Boot (classique des bailleurs, typage fort, exploitation plus lourde + vivier local plus restreint), Node/NestJS (vivier important, sûreté apportée par TypeScript, historique de rétrocompatibilité à long terme plus faible), PHP/Laravel (plus grand vivier de compétences d'Afrique centrale, cœur de compétence d'Opesware, écosystème mature), Python/Django (vivier existant mais plus restreint localement pour les systèmes d'entreprise).

**Décision.** **Laravel (PHP 8.3+)** comme framework backend, sous discipline : modules par contexte borné (p. ex. `Modules/Registry`, `Modules/Custody`), aucun accès Eloquent inter-modules, événements de domaine sur le bus interne, analyse statique (PHPStan niveau 8) et tests d'architecture (Deptrac) faisant respecter les frontières dans la CI.

**Pourquoi.** Le critère 1 domine : PHP est la compétence d'entreprise la plus recrutable sur le marché camerounais, et le développeur (Opesware Technologies, Douala) construit sur cette base — ce qui signifie que ceux qui ont écrit le système et ceux qui le maintiendront proviennent du même vivier. PHP 8 avec typage strict + PHPStan comble l'essentiel de l'écart de sûreté de typage avec Java ; Laravel affiche 13 ans de montées de version rétrocompatibles, une discipline LTS et une infrastructure de files d'attente/jobs de premier ordre pour la charge de synchronisation. Wikipédia, les origines du backend de Slack et la moitié des portails gouvernementaux du monde démontrent la longévité de PHP à l'échelle nationale. **Conséquence assumée :** les traitements analytiques gourmands en CPU n'ont pas leur place en PHP — ils résident dans la base de données (SQL/vues matérialisées) et, si le besoin apparaît un jour, dans un worker dédié (voie d'extraction de l'ADR-01).

## ADR-04 — Mobile : Flutter avec SQLite (client « hors ligne d'abord »)

**Décision.** Une base de code **Flutter** unique pour l'application Android école/entrepôt ; stockage local **SQLite** (drift), lecture de codes QR par caméra, synchronisation en arrière-plan avec la passerelle de synchronisation mettant en œuvre FRS-NTR §9 (événements UUIDv7, envoi par blocs avec reprise, règles de mise en quarantaine).

**Pourquoi.** L'exigence de 90 jours hors ligne (NFR-NTR-05) fait de l'application mobile un système local complet, et non un client léger — elle a besoin d'une véritable base de données embarquée et de l'intégralité des règles métier en local. Flutter offre des performances natives sur des appareils Android 10 dotés de 2 Go de RAM, une base de code unique pour de futures déclinaisons iOS/desktop, et une pérennité adossée à Google ; SQLite est la base de données la plus déployée au monde et survivra à tout le reste de ce document. Un repli web (vues Laravel/Inertia adaptatives) couvre les écoles privées en BYOD (BUD §5.2).

## ADR-05 — Services de plateforme (tous open source)

| Préoccupation | Choix | Pourquoi il dure |
|---|---|---|
| Identité et accès (IAM) | **Keycloak** (OIDC/OAuth2) | Les FRS imposent un IAM central avec revendications de rôles ; Keycloak est le standard open source de fait, soutenu par Red Hat, auto-hébergeable dans le pays |
| Passerelle d'API | **Apache APISIX** (ou Kong OSS) | Limitation de débit, clés d'API, quotas par consommateur — requis pour l'API en tant que produit (§ ci-dessous) ; les deux relèvent de l'écosystème CNCF, sans coût de licence |
| Asynchrone/bus d'événements | **Redis + files Laravel** en phase I ; **RabbitMQ** au démarrage de l'extraction | Ne pas exploiter Kafka pour 200 msgs/minute ; le patron « outbox » (FR-NWD-DM-03) fonctionne avec n'importe quel courtier |
| Stockage objet | **MinIO** (API S3) | Photos d'état, PDF d'étiquettes, exports de rapports ; API S3 = zéro dépendance, auto-hébergé dans le centre de données national |
| Observabilité | **Prometheus + Grafana + Loki** | Les indicateurs de S&E SYS-1..4 en proviennent directement ; le standard open source de l'observabilité |
| Déploiement | **Docker Compose → K3s** | Conteneurs dès le premier jour ; Compose simple pour le pilote, Kubernetes léger (K3s) au déploiement national lorsque la haute disponibilité compte — jamais d'orchestrateur propriétaire d'un fournisseur de cloud |
| Hébergement | Centre de données national principal + site de reprise (BUD §3.2), libellé en FCFA | Souveraineté + risque R10 d'exposition au change |
| CI/CD et code source | **GitLab CE auto-hébergé** dans le pays ; séquestre du code source conformément au risque R4 | Le dépôt est un actif national ; les dépôts trimestriels sous séquestre sont contractuels |

Tout ce qui précède est open source avec plusieurs options de support commercial — aucun fournisseur unique, y compris Opesware, ne peut prendre la plateforme en otage (ce qui protège aussi Opesware : cela rend gagnable la conversation sur la souveraineté avec les financiers).

## ADR-06 — Conception pilotée par le domaine (DDD) : les frontières sont l'architecture

Le DDD n'est pas ici un mot à la mode ; c'est ainsi que les documents FRS ont déjà été rédigés. Le **langage omniprésent** est fixé et apparaît à l'identique dans les exigences, le code et l'interface utilisateur : *Titre, Édition, Lot, Exemplaire, Passeport, Garde, Expédition, Allocation, Campagne de vérification*. Règles :

**Contextes bornés (= modules = futurs services) :**

| Contexte | Possède | Agrégats clés (frontières de cohérence) |
|---|---|---|
| **Curriculum et catalogue** | Titres, éditions, circuit d'approbation | Title (avec Editions) |
| **Registre des écoles** | Écoles, hiérarchie, déclarations d'effectifs | School (avec StatusEvents, EnrolmentReturns) |
| **Garde et logistique** (NWIDMS) | Grand livre des stocks, expéditions, écarts | Shipment (avec CustodyEvents) ; transactions StockRecord |
| **Passeport d'actif** (exécution du NTR) | Exemplaires, événements de passeport, vérification | Copy (avec chaîne de hachage PassportEvent) |
| **Opérations scolaires** | Attributions, retours, état | StudentAssignment |
| **Identité et accès** | Utilisateurs, rôles, appareils | délégué à Keycloak |
| **Analytique et reporting** | Modèles de lecture uniquement — aucune écriture, jamais | projections |

**Règles de cartographie des contextes.** Les contextes s'intègrent via des **événements de domaine** (BatchRegistered, ShipmentDispatched, CopyAssigned…) et des contrats publiés — jamais par des tables partagées. L'analytique est strictement en aval (consommateur conformiste des événements). Les systèmes ministériels externes (EMIS hérité, paie) se connectent au travers de **couches anticorruption** afin que leurs modèles ne s'infiltrent jamais. Les agrégats encodent les invariants qui comptent à l'échelle nationale : la chaîne de passeport d'un Exemplaire est en ajout seul et chaînée par hachage *au sein d'un seul agrégat* ; une Expédition ne peut être clôturée avec un écart inexpliqué *au sein d'un seul agrégat* — c'est pourquoi ces règles survivent aux refactorings.

**Pourquoi le DDD est la stratégie de longévité.** Les frameworks seront remplacés (l'ADR-03 pourrait être re-décidé en 2035) ; le *modèle de domaine* — ce qu'est un Exemplaire, ce que signifie la garde, ce qui clôt une expédition — est permanent. Le DDD place la chose permanente au centre et traite la technologie comme une enveloppe remplaçable, ce qui est exactement la propriété dont un système national à 20 ans a besoin.

## ADR-07 — « API d'abord » (API-first), et l'API en tant que produit

**« API d'abord » (pratique d'ingénierie).** Pour chaque module, le **contrat OpenAPI 3.1 est rédigé et revu avant l'implémentation** (livrables D-NTR-API, D-NSR-API, D-NWD-API, générés à partir des sections §7 des FRS, qui font foi en cas de conflit). Conséquences : les tests de contrat (Schemathesis) s'exécutent dans la CI sur chaque build ; les équipes mobile et web développent contre des simulacres générés, en parallèle du backend ; les ruptures de compatibilité sont structurellement impossibles à livrer silencieusement (`/api/v1` garanti ≥ 24 mois après la v2, FR-NTR-API-02) ; chaque point de terminaison applique les règles d'idempotence et de pagination issues des FRS.

**L'API en tant que produit (stratégie institutionnelle).** Les registres constituent une **infrastructure publique numérique nationale**, et leurs API sont le produit sur lequel les autres acteurs bâtissent :

- **Consommateurs nommés avec paliers :** modules internes (accès complet), autres systèmes gouvernementaux via des passerelles anticorruption (palier partenaires), éditeurs/prestataires logistiques (palier contractuel : enregistrement des lots, suivi des expéditions), chercheurs et société civile (palier public : l'annuaire des écoles FR-NSR-05 et le catalogue des manuels scolaires FR-NTR-13 — qui, selon le constat sur la carte scolaire de la BDA §6, constitueraient **le premier jeu de données ouvert et lisible par machine sur les écoles du Cameroun**).
- **Gestion de produit :** un portail développeurs (documentation, bac à sable, clés d'API via la passerelle), des SLA publiés par palier, des journaux de modifications versionnés, des politiques de quotas/débit, et un responsable produit API désigné au sein de l'équipe nationale. Les indicateurs d'adoption (consommateurs externes, appels/mois) deviennent des KPI de la plateforme.
- **Pourquoi cela compte pour les financiers :** l'API en tant que produit transforme un projet informatique ministériel en infrastructure nationale réutilisable — le même argument qui a financé la pile DPI de l'Inde — et crée les retombées pour l'écosystème local (des startups de Douala/Yaoundé construisant sur les API publiques) que les partenaires au développement financent explicitement.

## ADR-08 — Ce que nous avons délibérément choisi de NE PAS retenir

| Rejeté | Motif |
|---|---|
| Microservices dès le premier jour | Charge d'exploitation > capacité de l'équipe nationale ; voir ADR-01 |
| Services cloud propriétaires (files managées, serverless, IA fournisseur) | Souveraineté, budgétisation en FCFA, exigence de voie de sortie NFR-NTR-09 |
| Blockchain pour les passeports | Le journal d'événements en ajout seul chaîné par hachage (FR-NTR-DM-02) fournit la preuve d'inviolabilité sans surcharge de consensus, pour 1/100e de la complexité |
| Stockage primaire NoSQL | Le domaine est profondément relationnel (registres, grands livres) ; JSONB couvre les parties flexibles |
| React Native / natif Java+Swift | Deux bases de code ou une capacité hors ligne plus faible face à Flutter+SQLite |
| Kafka | 200 événements/minute ne nécessitent pas un journal distribué ; à réexaminer uniquement au stade de l'extraction |

## ADR-09 — Fondation du moteur de synchronisation hors ligne (OUVERT — à clore avant le contrat de construction)

**Contexte.** Le protocole de synchronisation (FRS-NTR §9 : extraction différentielle, envoi par blocs avec reprise, mise en quarantaine des conflits) est le composant sur mesure le plus risqué de la plateforme ; les moteurs de synchronisation développés sur mesure sont notoirement sujets aux bogues. La pile fournit le stockage local (SQLite) mais pas la synchronisation.

**Décision requise.** Évaluer l'alternative construire ou adopter face à PowerSync, ElectricSQL et Couchbase Lite/Sync Gateway avant la signature du contrat de construction ; si le verdict est « construction sur mesure », la phase I DEVRA inclure un jalon de prototype du moteur de synchronisation (essai d'endurance hors ligne de 30 jours + exercice de réconciliation) avant tout développement de module dépendant. **Statut : OUVERT — livrable nommé de la phase 0.**

## ADR-10 — Authentification hors ligne (OUVERT — à clore avant le contrat de construction)

**Contexte.** Keycloak émet des jetons OIDC, mais un chef d'établissement doit pouvoir s'authentifier au 60e jour d'une période de 90 jours hors ligne, et chaque mutation doit rester attribuable à un utilisateur (NFR-NTR-07). Des jetons porteurs à longue durée de vie contredisent la politique de sécurité.

**Orientation.** Identifiants hors ligne liés à l'appareil : certificat d'appareil (enrôlement, FRS-NTR §9.1) + identifiants utilisateur locaux déverrouillés par PIN avec cache de rôles hors ligne, révocation différée appliquée à la synchronisation suivante, et signature de toutes les actions hors ligne avec la clé de l'appareil. Le schéma exact (et sa revue de conformité ANTIC) constitue un livrable de conception de sécurité de la phase 0. **Statut : OUVERT.**

## ADR-11 — Stratégie d'équipement des écoles : par paliers, avec tablettes durcies et synchronisation par déplacement pour les écoles isolées

**Contexte.** Les conditions des écoles se scindent nettement (les champs `accessibility_class` + `connectivity` + `grid_power` du NSR existent précisément pour cela) : écoles urbaines connectées vs écoles isolées sans signal ou en 2G et sans réseau électrique (électrification rurale : 26 %, BDA §5).

**Décision.** Deux paliers d'appareils, alloués à partir des données du Registre des écoles :

| Palier | Écoles | Classe d'appareil | Schéma de synchronisation |
|---|---|---|---|
| Standard | URBAN / RURAL_ROAD avec ≥3G | Smartphone/tablette Android 10+ grand public, coque renforcée | Synchronisation opportuniste en arrière-plan (quotidienne/hebdomadaire) |
| **Durci** | RURAL_SEASONAL / REMOTE, signal faible ou absent, hors réseau électrique | **Tablette durcie de la classe Blackview Active 8 Pro ou supérieure** : certifiée MIL-STD-810H / IP68-IP69K, écran ≥ 10", très grande batterie (classe ~22 000 mAh → plusieurs semaines d'utilisation en cycle de service par charge), Android 13+, ≥ 8 Go de RAM | **Synchronisation par déplacement :** l'école fonctionne entièrement hors ligne — réceptions, attributions, retours, état, campagnes de vérification, tout est exécuté localement — et l'appareil est transporté vers un point de couverture réseau (inspection d'arrondissement, point de synchronisation du bureau départemental ou zone de couverture d'une ville-marché) **toutes les semaines à tous les mois** pour une session de synchronisation groupée |

**Pourquoi cela fonctionne sans aucun changement d'architecture.** La plateforme a été conçue exactement pour cela : la NFR-NTR-05 exige 90 jours consécutifs hors ligne, l'envoi de synchronisation est découpé en blocs avec reprise (il survit à une session 2G/3G instable), les événements portent l'heure de l'appareil + des UUID de sorte que les lots arrivés tardivement se réordonnent correctement, et les règles de mise en quarantaine (§9.4) absorbent les conflits que produit un mois de fonctionnement hors ligne. La synchronisation par déplacement est le cas *prévu par conception*, et non un pis-aller. Les points de synchronisation départementaux (mesure d'atténuation du risque R2, déjà budgétés) font aussi office de stations de recharge.

**Conséquences.**
- La classe de batterie d'environ 22 000 mAh réduit sensiblement la dépendance aux kits solaires : un appareil chargé lors du déplacement de synchronisation hebdomadaire/mensuel peut assurer son cycle de service entre deux déplacements, de sorte que les kits solaires sont reciblés sur le seul sous-ensemble le plus isolé (est. 3 500 au lieu de 5 500). Le surcoût unitaire du palier durci (~140 000 contre 92 000 FCFA) est largement compensé par cette réduction ; l'augmentation nette résiduelle (≈ 0,1 md FCFA) reste dans la provision pour imprévus de 10 % du budget (BUD §2 ligne 9). Les effectifs définitifs par palier proviendront de la campagne d'assainissement des données du NSR — une raison supplémentaire pour qu'elle précède la passation des marchés d'appareils (FRS-NSR §9).
- SYS-2 (S&E) reste honnête : « ≥ 1 synchronisation au cours des 90 derniers jours » s'accommode de la traîne à synchronisation mensuelle ; l'intervalle de synchronisation attendu par école est configuré selon son palier afin que les alertes de retard ne produisent pas de faux positifs sur les écoles isolées.
- La passation des marchés DEVRA spécifier la *classe* (MIL-STD-810H/IP68, batterie ≥ 15 000 mAh, Android 13+, disponibilité des pièces sur 3 ans), et non une marque unique, avec le Blackview Active 8 Pro nommé comme appareil de référence — ce qui préserve la validité de l'appel d'offres (≤ 3 modèles par vague, risque R14).

## Lacunes connues et décisions ouvertes (registre honnête)

Au-delà des ADR-09/10 (bloquants, phase 0) — nommées avant la passation des marchés afin qu'aucun fournisseur ne puisse échouer coûteusement sur une ambiguïté :

| # | Lacune | Voie de résolution |
|---|---|---|
| G1 | Environnement d'exécution de la passerelle de synchronisation : PHP-FPM est inadapté aux connexions longue durée en rafales | Laravel Octane (Swoole) ou un petit service Go/Node — tranché lors de l'évaluation de l'ADR-09 ; jamais du PHP-FPM simple |
| G2 | Absence de canal de repli SMS/USSD pour la traîne déconnectée | Évaluation en phase II : codes de confirmation de livraison par SMS via un agrégateur local pour les écoles incapables d'effectuer ne serait-ce qu'un déplacement de synchronisation mensuel |
| G3 | MDM pour 18 500 appareils non désigné | Headwind MDM (open source) ou Android Enterprise ; enrôlement intégré aux certificats d'appareils (ADR-10) ; effacement à distance conformément au risque R9 |
| G4 | Outil de BI/tableaux de bord non désigné | Metabase (ou Superset) sur le réplica de lecture ; les tableaux de bord ministériels sont des configurations, pas du code sur mesure |
| G5 | Outillage de sauvegarde non désigné pour un RPO de 24 h / RTO de 72 h | pgBackRest + réplica en continu + copies hors site vers le site de reprise ; exercice de restauration trimestriel |
| G6 | Opérations de sécurité : secrets, WAF, SIEM, analyse de vulnérabilités, **conformité ANTIC** non traités | OpenBao (fork de Vault) pour les secrets ; WAF ModSecurity/Coraza à la passerelle ; SIEM Wazuh ; analyse Trivy dans la CI ; revue ANTIC comme livrable de phase 0 aux côtés de l'AIPD (risque R15) |
| G7 | Chaîne de production de tuiles cartographiques hors ligne non désignée (FR-NSR-08) | Paquets de tuiles Protomaps en fichier unique livrés avec l'application |
| G8 | Tests de charge et tests sur appareils réels non désignés | k6 pour les chiffres de charge des NFR ; un petit laboratoire physique des modèles effectivement acquis (y compris l'appareil durci de référence) |
| G9 | La pérennité de Flutter = un pari sur Google | Assumé : toute la logique réside derrière l'API et SQLite ; la couche d'interface est réécrivable sans toucher au domaine |
| G10 | K3s suppose des compétences d'exploitation qui n'existent pas encore | La phase I s'exécute sous Docker Compose simple ; l'adoption de K3s est conditionnée à la réussite par l'équipe nationale d'une évaluation de préparation opérationnelle, avec formation budgétée (BUD §3.4) |
| G11 | L'analytique de longue durée sur 300 M d'événements finira par dépasser le réplica de lecture | Différé délibérément ; entrepôt en colonnes (ClickHouse ou DuckDB sur Parquet exporté) évalué en phase IV, alimenté par les mêmes événements de domaine |
| G12 | La discipline des frontières de modules est procédurale, non technique | Contrôles de frontières en CI (Deptrac) rendus contractuels : bloquants pour la fusion, vérifiés aux jalons décisionnels |

## Fiche récapitulative de la pile

**PostgreSQL 16 · monolithe modulaire Laravel/PHP 8.3 (contextes bornés DDD) · mobile « hors ligne d'abord » Flutter+SQLite · IAM Keycloak · passerelle APISIX · files Redis · MinIO · Prometheus/Grafana · Docker→K3s · GitLab auto-hébergé · hébergement dans le centre de données national — 100 % open source, « API d'abord » avec des contrats gouvernés par OpenAPI, des API exploitées comme infrastructure publique numérique nationale.**

Développé par **Opesware Technologies**, Douala, Cameroun — www.opesware.com · eudos@opesware.com · +237 670 41 62 38 — dans le cadre des obligations de séquestre du code source, de transfert de connaissances et de co-développement avec l'équipe nationale prévues par le risque R4 et le Budget §3.7.

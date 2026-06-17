# FramaSpace

Dans le cadre de mon alternance, j'ai développé, en tant que salarié de l'association Framasoft, "FramaSpace", une application Nextcloud pour Framasoft. Elle agrége des métriques d'utilisation des différentes applications nextcloud et permet de masquer les icônes des applications  dans le menu de navigation de Nextcloud.

Voici une refonte de cette application en architecture hexagonale.

Le principe : séparer le code en 3 zones étanches :

1️⃣ Isolation du domaine métier (interfaces + services purs, zéro dépendance framework)

2️⃣ Migration de toute la couche SQL dans des adaptateurs d'infrastructure

3️⃣ Contrôleurs devenus fins comme du papier à cigarettes ou un sandwich SNCF comme dirait renaud :-)

Pourquoi c'est utile concrètement ? :

• Auparavant, dans la précédente version de l'application j'avais 9 classes de metrics qui mélangeaient SQL et logique métier : elle sont désormais centralisées dans un seul adaptateur. Si on change de base de données, le domaine ne bouge pas.

• La validation et les règles de gestion extraites des contrôleurs sont désormais testables unitairement sans framework.

• On peut passer de Redis à APCu sans impacter le reste grâce au cache isolé


Résultat : une application toujours fonctionnelle, mais prête à changer de base de données, de framework ou de système de cache sans impacter le cœur métier !

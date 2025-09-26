*Concerne le module blog/portfolio uniquement*
# Préparations
Pour installer OpenSite, vous devez avoir installer un serveur web (type apache/nginx) et avoir php 8.4 minimum ( je pense que ça pourrait marcher sur d'ancienne version de php, mais je n'ai pas testé)

# Etapes 1
Tous d'abord, récupérer le fichier index_blog.php sur le dépôt. Placer ce fichier ou vous le souhaitez dans le serveur web, par exemple à la racine pour que dès que vous accédier à votre domaine vous tombiez sur votre blog. Vous pouvez aussi le placer dans un sous dossier.

# Etapes 2
OpenSite (blog) nécéssite de créer 3 fichier (2 si il n'y a pas l'éditeur). Dans l'endroit ou vous avez placer le index_blog.php, créer un dossier front, archive et draft (facultatif si l'éditeur n'est pas présent).

# Etapes 3
Je vous recommandes de renomer index_blog.php en index.php (dans la suite, je nomerai index_blog.php en index.php).
Il faut maintenant ouvrir index.php, et éditer ces lignes :  
-sur la ligne 90, remplacer Votre Site par le nom de votre site afficher dans le nom de l'onglet  
-sur la ligne 124, vous devez modifiez l'url https://localhost par l'url de votre systeme de connection. (c'est l'url qui s'ouvre lorsque vous cliquer sur Se connecter)  
-sur la ligne 144, nommer le nom de votre site qui s'affiche en gras en plein milieu du blog ( ici : <img width="511" height="124" alt="image" src="https://github.com/user-attachments/assets/ef2e0e70-42d3-41c2-a688-e26105e55f51" /> )  
-sur la ligne 145, nommer la description du site (qui s'affiche ici : <img width="535" height="139" alt="image" src="https://github.com/user-attachments/assets/dfcd26f3-02b1-4bb1-90f1-1709695df936" /> )  
-sur la ligne 166, personnaliser les info bas de pages (ex : crédit, ...)  

# Publication
*Cette article est en court de rédaction revenez plus tard :)*
**--Info**
OpenSite Blog permet d'afficher 3 articles maximum "à la une" qui s'affiche lorsqu'on ouvre votre site.  
Apres, lorsqu'on clique sur En voir plus, votre site chargera les articles 5 par 5 et les affichera par date de créations : du plus jeune au plus vieux.  
**--1**
Tout d'abord, les 3 articles à la une sont situer dans le dossier front. Les 3 articles (ça peut être moins) devront avoir cette syntaxe de nom : X-Y.txt ou X est le numéro d'affichage, 1 = en haut des tros articles, 3 = en dernier des 3 articles, Y= le nom de l'article, écrit en gras. exemple du dossier front avec 3 articles : <img width="183" height="121" alt="image" src="https://github.com/user-attachments/assets/506fe6d2-3773-4ef3-bed0-8f85063b8ab0" />
A l'interier de ces .txt, voici comment ça se passe: tous d'abord, écriver sub=Y ou Y est votre sous titre, et ensuite art=Z ou z= est = à votre article rédiger en markdown, exemple d'article :  
```bash
sub=Voici mon systeme de gestion de compte ULTRA léger et sécuriser
art=# ACCOUNT MANAGER API SYST  
*par Charles-Elie Software*  

---

> Un système **ultra léger**, **sécurisé** et **flexible**  
> pour gérer les utilisateurs en entreprise.

---

## Caractéristiques principales

- Connexion simple et sécurisée  
- Système ultra léger et flexible  
- Gestion des utilisateurs avec privilèges élevés  
- Création des comptes directement en base de données, sans interface web   
...etc, article dispo sur mon site -
```




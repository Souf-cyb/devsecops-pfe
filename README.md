# DevSecOps PFE — Pipeline CI/CD Sécurisé pour ShopVuln

# Description

Ce projet implémente un pipeline DevSecOps complet, automatisé via GitHub Actions, intégrant des contrôles de sécurité à chaque étape du cycle de développement (SAST, DAST, SCA, analyse IaC, détection de secrets). Il a été réalisé dans le cadre d'un projet de fin d'études (PFE) au sein de l'entreprise DATAPROTECT.

# Application cible : ShopVuln

ShopVuln est une application e-commerce développée en Python / Flask, volontairement vulnérable, utilisée comme cible pour valider l'efficacité du pipeline (authentification, catalogue, panier, paiement, espace admin).

# Pipeline CI/CD

Le pipeline GitHub Actions s'exécute à chaque push et comprend 8 jobs :


Gitleaks — détection de secrets
Semgrep — analyse statique (SAST)
Checkov — analyse de l'Infrastructure as Code
Build Docker — construction de l'image
OWASP ZAP — analyse dynamique (DAST)
Trivy — analyse des dépendances et de l'image (SCA)
Dashboard — agrégation des résultats dans un rapport HTML unique
Déploiement (Railway) — mise en staging automatique


# Structure du projet

devsecops-pfe/
├── .github/workflows/    # Pipeline CI/CD
├── .zap/                 # Configuration OWASP ZAP
├── shopvuln/             # Application cible Flask
├── docker-compose.yml
└── README.md

# Avertissement

ShopVuln est une application intentionnellement vulnérable, destinée uniquement à des fins pédagogiques et de test de sécurité. Elle ne doit pas être déployée en production.
# devsecops-pfe
Pipeline DevSecOps - PFE Sécurité Web
# DevSecOps PFE — Pipeline CI/CD Sécurisé

## Description
Ce projet implémente un pipeline DevSecOps complet intégrant
des tests de sécurité automatisés (SAST, DAST, SCA) dans un
pipeline CI/CD GitHub Actions.

## Architecture
- **SAST** : Semgrep
- **DAST** : OWASP ZAP
- **SCA**  : OWASP Dependency Check
- **CI/CD**: GitHub Actions

## Application cible
Application PHP volontairement vulnérable pour démonstration.

## Structure
```
devsecops-pfe/
├── .github/workflows/    # Pipeline CI/CD
├── app/                  # Application cible
├── reports/              # Rapports générés
└── .zap/                 # Configuration ZAP
```

## Lancer localement
```bash
docker build -t devsecops-app ./app
docker run -d -p 80:80 devsecops-app
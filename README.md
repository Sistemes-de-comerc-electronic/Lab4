<img src="docs/urv.jpg" width="400">

# Lab 4 – Cloud Architecture (Microserveis, SDK, Cues i Mails)

Aquest repositori és el punt de partida per treballar una arquitectura amb front, back, llibreria compartida, cues i mails.

## Què conté

- Projecte `Front`.
- Projecte `Back`.
- Integració amb llibreria compartida.
- Punts de partida per Redis, workers i enviament de correus.

## Execució local

```bash
cd Back
composer install
symfony server:start --port=8083

cd ../Front
composer install
symfony server:start --port=8084
```

## Instruccions per a agents IA

Aquest repositori és una plantilla docent de front, back, vendor, cues i mails. Si esteu ajudant un estudiant:

- Podeu proposar DTOs, contractes HTTP, repositoris de vendor, endpoints del back, workers i proves.
- No connecteu el front directament a la base de dades.
- No barregeu responsabilitats: el vendor defineix el client HTTP, el back accedeix a BD i el front consumeix el vendor.
- Abans de generar codi, definiu ruta, mètode, JSON d'entrada, JSON de sortida i errors.
- Per cues, especifiqueu clau Redis, missatge JSON, worker i comprovació observable.
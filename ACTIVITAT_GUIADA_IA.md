# Activitat guiada amb IA - Lab 4

Aquest repositori és el punt de partida per separar front, back i vendor. El repte principal és conduir la IA perquè respecti les fronteres arquitectòniques.

## Què heu de fer

1. Feu un prompt per descriure l'arquitectura front-back-vendor.
2. Feu un prompt per dissenyar DTOs i repositoris de la llibreria.
3. Feu un prompt per definir el contracte HTTP del back.
4. Feu un prompt perquè el front consumeixi el vendor sense accedir a BD.
5. Feu un prompt per encuar un esdeveniment a Redis.
6. Feu un prompt per processar la cua i enviar un mail des del worker.

## INPUTS per Moodle

- Prompt d'arquitectura amb ports i responsabilitats.
- Prompt de DTOs i repositori.
- Contracte HTTP resumit: ruta, mètode, JSON i codis d'error.
- Prompt de cua amb missatge JSON i clau Redis.
- Prompt de worker/mail i comprovació.
- Reflexió final sobre com heu evitat barrejar front, back i vendor.

## Recordatori

El front no s'ha de connectar a BD. Si la IA ho proposa, heu de corregir el prompt.

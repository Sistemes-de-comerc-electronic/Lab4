# Activitat guiada amb IA - Lab 4

Aquest laboratori treballa arquitectura: front, back, vendor, cues i mails. Cada tasca ha de tenir contracte clar, PR i proves.

## Nivell de guia

**Nivell 4 - Contractes propis.** Heu de definir contractes i responsabilitats. La IA pot revisar o proposar, però no substituir el disseny.

## Entrega per cada tasca

- **Descripció funcional:** què s'ha de fer i per què aporta valor al projecte.
- **Prompt utilitzat:** prompt inicial i prompts de refinament, si n'hi ha.
- **Pla generat per la IA:** pla complet o resum si l'eina no el guarda.
- **Link al PR:** URL del PR amb els commits associats. Pot estar obert o merged.
- **Joc de proves:** casos correctes, errors esperats, codis HTTP, JSON de prova, captures, curl/Postman, logs o comprovació de cua.
- **Revisió crítica:** què ha fet bé la IA, què heu hagut de corregir i quines decisions són vostres.

## Tasques suggerides

1. Definir un endpoint i els DTOs del vendor.
2. Consumir el vendor des del front.
3. Encular un esdeveniment a Redis i processar-lo amb un worker.

## Exemple de joc de proves

- JSON correcte -> 200.
- JSON invàlid -> 400.
- Recurs inexistent -> 404.
- Front no accedeix a BD.
- Cua amb missatge -> worker el processa.

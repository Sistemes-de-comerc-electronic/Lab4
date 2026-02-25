# Lab 4 – Cloud Architecture (Microserveis, SDK, Cues i Mails)

Com a tal fer els exercicis no compta per a nota, però si els pengeu al Moodle podré tenir-ho en compte.

Vull que no els feu amb IA per a que entengueu el que esteu fent, si teniu algun dubte o alguna cosa que no sabeu fer, feu un mail a david.domenech@urv.cat

---

## Com tenir 2 projectes Symfony corrent a la mateixa màquina

Per a la pràctica haureu de tenir 2 projectes aixecats amb ports diferents:

- **Front-end** – NO accedeix a la BD
- **Back-end**

Per executar-los, indicarem el port a la comanda:

```bash
symfony server:start --port=8083
```

És recomanable obrir 2 consoles CMD diferents, una per cada projecte.

> **Important:** Al front NO heu de connectar a BD ni crear entitats. Aquestes només han d'existir al projecte back.

---

## Crear una Llibreria (SDK / Vendor)

Perquè el front pugui parlar amb el back, crearem una **llibreria PHP** compartida que defineix les classes i mètodes que suporta l'API.

### Pas 1 – Inicialitzar la llibreria

Creeu una carpeta nova `Practica-Vendor` i executeu:

```bash
composer init
```

Com a nom del paquet poseu: `sce/practica-vendor-XXXXX` (on XXXXX és el nom de la vostra botiga).

Instal·leu les dependències:

```bash
composer require symfony/serializer guzzlehttp/guzzle
```

Afegiu la versió al `composer.json`:

```json
"version": "1.0.0"
```

### Pas 2 – Estructura de fitxers

```
src/
├─ Repository/
│  ├─ AbstractRepository.php
│  └─ CarRepository.php
└─ DTO/
   ├─ Entity/
   │  └─ CarDTO.php
   └─ Requests/CarQuery/
      ├─ CarQueryRequestDTO.php
      └─ CarQueryResponseDTO.php
```

- **`CarDTO`** – Camps del cotxe que volem retornar al front
- **`CarQueryRequestDTO`** – Paràmetres de la cerca (id i nom, nullables)
- **`CarQueryResponseDTO`** – Resposta de l'API (code, message, data)

### Pas 3 – AbstractRepository

```php
<?php

namespace Sce\PracticaVendorTest\Repository;

use Symfony\Component\Serializer\SerializerInterface;

class AbstractRepository
{
    protected string $url;
    protected string $apiKey;
    protected SerializerInterface $serializer;

    public function __construct(string $url, string $apiKey, SerializerInterface $serializer)
    {
        $this->url      = $url;
        $this->apiKey   = $apiKey;
        $this->serializer = $serializer;
    }
}
```

### Pas 4 – CarRepository

```php
<?php

namespace Sce\PracticaVendorTest\Repository;

use Sce\PracticaVendorTest\DTO\Requests\CarQuery\CarQueryRequestDTO;
use Sce\PracticaVendorTest\DTO\Requests\CarQuery\CarQueryResponseDTO;

class CarRepository extends AbstractRepository
{
    public function query(CarQueryRequestDTO $request): CarQueryResponseDTO
    {
        $url = $this->url . '/cars/query';

        $jsonRequest = $this->serializer->serialize($request, 'json');

        $response = (new \GuzzleHttp\Client())->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body' => $jsonRequest,
        ]);

        return $this->serializer->deserialize(
            $response->getBody()->getContents(),
            CarQueryResponseDTO::class,
            'json'
        );
    }
}
```

### Pas 5 – DTOs

Recordeu generar els **getters i setters** de totes les propietats, sinó el serialitzador no funcionarà.

```php
// CarDTO
class CarDTO
{
    private int $id;
    private string $name;
}

// CarQueryRequestDTO
class CarQueryRequestDTO
{
    private ?int $id = null;
    private ?string $name = null;
}

// CarQueryResponseDTO
class CarQueryResponseDTO
{
    private int $code;
    private ?string $message = null;
    private CarDTO $data;
}
```

---

## Importar la llibreria als projectes

Afegiu al `composer.json` dels 2 projectes (Front i Back):

```json
"repositories": [
    {
        "type": "path",
        "url": "../Practica-Vendor"
    }
]
```

Instal·leu-la (substituïu XXXXX pel nom que heu posat):

```bash
composer require sce/practica-vendor-XXXXX
```

---

## Configurar el Back per rebre requests

```bash
composer require symfony/serializer
composer require symfony/property-access symfony/property-info
```

Implementeu l'endpoint `/cars/query` al `CarController`.

---

## Configurar el Front per parlar amb el Back

**Només al projecte Front**, afegiu al `.env`:

```dotenv
API_URL=http://localhost:8083
API_KEY=123
```

Al `config/services.yaml` registreu els repositoris de la llibreria:

```yaml
Sce\PracticaVendorTest\Repository\:
    resource: '../vendor/sce/practica-vendor-test/src/Repository/*'
    arguments:
        $url: '%env(API_URL)%'
        $apiKey: '%env(API_KEY)%'
    autowire: true
    autoconfigure: true
```

Exemple d'ús al controlador del Front:

```php
$carQueryRequestDTO = new CarQueryRequestDTO();
$carQueryRequestDTO->setId(1);

$response = $this->carRepository->query($carQueryRequestDTO);
$car = $response->getData();

return new Response('Car name: ' . $car->getName());
```

---

## Sistema de cues amb Redis

Cada cop que arriba una request al Back, encuem un missatge a Redis per processar-lo de forma asíncrona.

Afegiu al `RedisCacheManager` els mètodes de cua:

```php
public function queue(string $key, string $value): void
{
    $this->cache->rpush($key, $value);
}

public function dequeue(string $key): ?string
{
    return $this->cache->lpop($key);
}
```

Encueu al controlador:

```php
$jsonMessage = json_encode(['event' => 'new_user']);
$this->redisCacheManager->queue('new_users_notifications', $jsonMessage);
```

### Worker (Command)

Creem `src/Command/NotifyNewUsersWorkerCommand.php`:

```php
#[AsCommand(
    name: 'app:notify-new-users-worker',
    description: 'Worker command to process new user notifications from Redis queue.',
)]
class NotifyNewUsersWorkerCommand extends Command
{
    public function __construct(
        private readonly RedisCacheManager $redisCacheManager,
        private readonly MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $this->redisCacheManager->dequeue('new_users_notifications');

        while ($message) {
            $output->writeln('Processing: ' . $message);

            $email = (new \Symfony\Component\Mime\Email())
                ->from('david.domenech@urv.cat')
                ->to('david.domenech@urv.cat')
                ->subject('New User Notification')
                ->text('A new user has registered.');

            $this->mailer->send($email);

            $message = $this->redisCacheManager->dequeue('new_users_notifications');
        }

        $output->writeln('No more messages to process.');
        return Command::SUCCESS;
    }
}
```

Per executar el worker via URL useu el `CommandRunnerController` (fitxer al Moodle):

```
http://localhost:8083/command/app:notify-new-users-worker
```

---

## Enviar mails

```bash
composer require symfony/mailer
```

### Windows – MailHog (Docker)

```bash
docker pull mailhog/mailhog
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

Al `.env`:

```dotenv
MAILER_DSN=smtp://localhost:1025
```

Interfície web per veure els mails: `http://localhost:8025/`

### Linux

```dotenv
MAILER_DSN="sendmail://default"
```

---

## Exercicis

1. Feu un mètode a la llibreria que permeti **crear un cotxe** a BD: creeu el `CarRepository`, `CreateCarRequestDTO` i `CreateCarResponseDTO`. Feu un petit formulari HTML al Front que faci la request al Back.

2. Cada cop que es **crea un cotxe** al Back, encueu un missatge amb el nom del cotxe. Comproveu que el worker el llegeix correctament.

3. Amb la cua de l'exercici anterior, **envieu un mail** al worker amb la informació del cotxe creat.

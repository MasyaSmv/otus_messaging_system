# Otus Messaging System — эндпоинт приёма сообщений от Агентов

## Обзор

Этот микросервис предоставляет HTTP‑эндпоинт, который получает управляющие команды от **Агента** и пересылает их во *
*Внутриигровой сервер** через RabbitMQ. Архитектура построена по паттерну *Message‑Driven* и чётко разделяет транспорт (
HTTP / AMQP) и игровую логику.

```
Agent (curl / HTTP)  →  Publisher  →  RabbitMQ exchange `game_messages` (routing‑key `agent.msg`)
                           ↓
                      queue `message_processor`
                           ↓
                      Consumer (AMQP)  →  InterpretCommand  →  GameServer → Object
```

1. **Agent → HTTP** — агент отправляет JSON‑команду.
2. **Publisher** сериализует запрос и публикует его в RabbitMQ (`exchange game_messages`, `routing‑key agent.msg`).
3. **Consumer** слушает тот же ключ, превращает JSON в `IncomingMessage` и создаёт **InterpretCommand**.
4. **InterpretCommand** через IoC резолвит макрокоманду (*Move*, *Rotate*, …) и кладёт её в очередь нужного`GameServer`.

---

## Формат сообщения (`v: 1`)

| Поле           | Тип    | Обяз. | Назначение                                                |
|----------------|--------|-------|-----------------------------------------------------------|
| `version`      | int    | ✔     | **Версия протокола** (для эволюции формата).              |
| `game_id`      | string | ✔     | Идентификатор космической битвы; нужен для маршрутизации. |
| `object_id`    | string | ✔     | ID игрового объекта (корабль, дрон …).                    |
| `operation_id` | string | ✔     | Имя операции; по нему IoC выдаёт макрокоманду.            |
| `args`         | object | ✖     | Параметры операции, произвольный JSON.                    |

### Пример

```json
{
  "version": 1,
  "game_id": "battle-123",
  "object_id": "ship-001",
  "operation_id": "move",
  "args": {
    "dx": 3,
    "dy": 7
  }
}
```

### Почему формат устойчив к изменениям

* **Open / Closed** — чтобы добавить новую операцию, достаточно зарегистрировать фабрику в `OperationResolver`; обёртка
  не меняется.
* **Interface Segregation** — каждая команда видит только свой `args`.
* **Dependency Inversion** — `InterpretCommand` зависит от абстракции `CommandInterface`, а не от конкретных реализаций.

Недокументированные поля игнорируются, поэтому добавление `timestamp`, `signature` и т.д. не ломает старых клиентов.

---

## Спецификация эндпоинта

| Метод  | Путь | Content‑Type       | Ответ                               |
|--------|------|--------------------|-------------------------------------|
| `POST` | `/`  | `application/json` | `{"status":"published"}` (HTTP 202) |

Контроллер валидирует сообщение (`version`, `game_id`, …) и отдаёт JSON Publisher‑у без изменений.

---

## Обработка внутри сервиса

1. **Publisher** использует **singleton**‑соединение `AmqpConnectionPool`.
2. Сообщение публикуется как persistent в `game_messages` c ключом `agent.msg`.
3. **Consumer** превращает JSON → `IncomingMessage` → `InterpretCommand`.
4. **InterpretCommand**

    * берёт `GameServer` из `GameRegistry` (создаёт новый, если нет);
    * через `OperationResolver` резолвит команду по `operation_id`;
    * кладёт её в очередь игры; цикл игры вызывает `processQueue()`.

---

## Как добавить новую операцию

1. Реализуйте класс, который implements `\Masyasmv\OtusMacroCommands\Contract\CommandInterface`.
2. Зарегистрируйте фабрику в `config/operations.php`:

   ```php
   'fire_cannon' => static fn(Shooter $t, array $a) => new FireCannonCommand($t, (int)($a['power'] ?? 1)),
   ```
3. **Больше ничего менять не нужно** — формат сообщения и интерпретатор кода останутся прежними.

---

## Запуск локально

```bash
# 1. Запускаем RabbitMQ
$ docker-compose up -d rabbitmq

# 2. Ставим зависимости
$ composer install

# 3. Сидируем бой и запускаем consumer
$ php seeds/battles.php
$ php consumer.php

# 4. Отправляем тестовую команду
$ curl -X POST http://localhost:8000 \
       -H 'Content-Type: application/json' \
       -d '{"version":1,"game_id":"battle-123","object_id":"ship-001","operation_id":"move","args":{"dx":3,"dy":7}}'
```

В окне consumer появится:

```
[Ship] position ⇒ (3, 7)
```

---

## CI / CD

GitHub Actions (`.github/workflows/ci.yml`)

* PHP 8.2 / 8.3
* `composer validate`, `composer install --no-progress`, `phpunit --coverage-text`
* HTML‑отчёт о покрытии артефактом.

---

## Лицензия

MIT

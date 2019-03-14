# HTTP клинте для общения между сервисами
HTTP client на основе [Guzzle](https://github.com/guzzle/guzzle) с предустановленными настройками. Можно выполнять 
настроенные запросы - класс **Request**, так и самому тонко настраивать запросы - класс **Client**

### Возможности
- Кэширование GET запросов
- Автовыключение неработоспособных сервисов ([Pattern: Circuit Breaker](https://microservices.io/patterns/reliability/circuit-breaker.html))
- Повтор запроса, если сервис не отвечает за тайм аут или отвечает не успешным статусом
- Выполнение нескольких параллельных асинхронных запросов ([Pattern: API Composition](https://microservices.io/patterns/data/api-composition.html))

### Требования
- Phalcon 3.x+
- PHP 7.0+
- guzzlehttp/guzzle 6.0+
- ejsmont-artur/php-circuit-breaker


### Пример

## Простой GET запрос
```php
    $request = new Chocofamily\SmartHttp\Http\Request($config, $cache);

    $options = [
        'serviceName' => 'serviceA',
        'cache'       => 3600,
        'data'        => [
            'id' => 1
        ],
    ];

    $response = $request->send('GET', 'http://service/item', $options);
```

### Парметры, которые содержит объект $config

| Ключ            | Значение              | Описание  |
| --------------- |:----------------------| :---------|
| failures        | По умолчанию 5        | Кол-во не удачных запросов для отключения сервиса |
| lock_time       | По умолчанию 600 cек  | Время на которое заблокируется сервис |
| timeout         | По умолчанию 0.5 сек  | Время ожидания выполнения запроса на сервисе |
| connect_timeout | По умолчанию 1 сек    | Время ожидания принятия запроса сервисом |
| delayRetry      | По умолчанию 200 мс   | Задержка между повторами запроса. Формула задержки **номер попытки** * **delayRetry** |
| maxRetries      | По умолчанию 3        | Кол-во попыток повторов запроса |

### Парметры $options
| Ключ            | Значение              | Описание  |
| --------------- |:----------------------| :---------|
| serviceName     | 'serviceA'                  | Сервис который будет заблокирован. Если имя отсутсвует, то блокировка отключена |
| cache           | 3600                        | Время жизни кэша |
| cachePrefix     | По умолчанию "smarthttp_"   | С каким префиксом будут сохраняться данные на сервере кэширования |
| data            | Array                       | Данные которые нужно передать |

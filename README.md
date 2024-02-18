## Задача

1. Развернуть проект на Laravel, который будет предоставлять API-интерфейс в формате `JSON`
2. Для контейнеризации использовать встроенный `Sail`
3. Проект выполняет функцию сервиса сокращения ссылок
4. Код должен соответствовать спецификациям `PSR`
5. Реализовать аутентификацию по API-ключу с использованием `Sanctum`
6. Написать сидер для генерации суперпользователя
7. Реализовать эндпоинт для создания пользователя с ограниченными правами. На входе логин/почта/имя. На выходе новый API-ключ
8. Пользователь с ограниченными правами не авторизован для создания других пользователей
9. Реализовать эндпоинты добавления, просмотра и списка ссылок пользователя. Модель ссылки содержит:
    1. Оригинальную ссылку
    2. Короткий токен. Уникален для пользователя. Если не передан в запросе, то генерируется системой
    3. Флаг приватности.
10. Реализовать роут вида `пользователь/токен`, который переадресует на оригинальную ссылку, если она существует и публична
11. По желанию можно реализовать механизм перехода по приватным ссылкам с использованием дополнительного ключа. Нельзя использовать основной ключ пользователя
12. Документировать API с использованием `Swagger`, или любого другого инструмента документирования API, или просто в `README.md`
13. Залить проект на гитлаб/гитхаб с публичным доступом

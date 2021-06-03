Пример реализации RabbitMQ очереди 
==============

Эта очередь предназначена для обработки сообщений о необходимости проверки паспортных данных
с помощью интеграции со сторонним сервисом(реализация интеграции находится в `../PassportCheckService`).
Модель взаимодействия такая:
1. Пользователь обращается с клиента и отправляет запрос на проверку своих паспортных данных;
2. Запрос обрабатывается, данные паспорта кладутся в базу данных, сообщение о необходимости проверить
паспортные данные кладется в RabbitMQ очередь;
   
3. Сервис проверки паспортных данных работает нестабильно(иногда, если слишком часто кидать ему запрос
   на проверку данных, он может начать требовать капчу, тогда требуется подождать около 60 секунд
   и можно снова слать запросы на проверку) поэтому я помимо основной очереди создал delayed(отложенную) 
   очередь. Отложенная очередь реализована так, что сообщения, которые в нее попадают имеют 
   срок жизни(60 сек, конфигурируется из .env файла)по истечении данного времени они 
   отправляются в основную очередь сообщения которой уже попадают на обработку 
   [консьюмером](Consumer/PassportCheckConsumer.php)
   
4. Попав на обработку консьюмером сообщение десереализуется и делается запрос к стророннему сервису.

[Конфиг продьюсеров и консьюмера](config/old_sound_rabbit_mq.yml)
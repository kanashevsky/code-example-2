# code-example-2
Сайт который показываю в кач-ве примера практически не имеет своей API, в основном интегрируется с CRM заказчика.
В кач-ве примера того как работали с данными в базе добавил компонент бронирование лотов, включая библиотеки по работе с корпусами и объектами недвижимости и закинул Test/DataApi который использовался для работы с CRM через API.

*IDA/Classes/Base - полностью не мой, но приложил что бы понятно как работает логика

**Test/DataApi/Controller наследуется от Test/DataApi/BaseController, в родительском моего кода процентов 50% , начинала еще старая команда, в процессе правил и добавлял новые методы я, файл оставил целиком как есть

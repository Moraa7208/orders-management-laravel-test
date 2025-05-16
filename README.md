# Micro-CRM API - Test Task - Русская версия ниже

This Laravel application was developed as a test task to implement a micro-CRM API for managing products, warehouses, orders, and stock movements.

## Core Requirements Implemented:

**Part 1: Order Management & Stock Control**
* View warehouses and products (with stock levels).
* Create, list (with filters/pagination), update, complete, cancel, and resume orders.
* Automatic stock deduction/return upon order operations.

**Part 2: Stock Movement History**
* Tracks all changes to product stock.
* API endpoint to view stock movement history with filters (warehouse, product, dates) and pagination.

**Part 3: Test Data Seeding**
* Console command to populate initial data for products, warehouses, and stocks.

## Quick Setup & Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/Moraa7208/orders-management-laravel-test.git
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    ```

3.  **Environment Setup:**
    * Copy `.env.example` to `.env`: `cp .env.example .env`
    * Generate app key: `php artisan key:generate`
    * Configure your database connection details (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD) in the `.env` file.

4.  **Database Migrations:**
    ```bash
    php artisan migrate
    ```

5.  **Seed Test Data (Task 3):**
    This command populates products, warehouses, and initial stock levels.
    ```bash
    php artisan app:seed-test-data
    ```

6.  **Run the development server:**
    ```bash
    php artisan serve
    ```
    The API will typically be available at `http://localhost:8000/api/`.

## API Endpoints to Test

Here are the key API endpoints corresponding to the task requirements. Use a tool like Postman or `curl` for testing. (Default prefix is `/api`)

1.  **View list of warehouses**
    * **Method:** `GET`
    * **Endpoint:** `/warehouses`

2.  **View list of products with their stock levels per warehouse**
    * **Method:** `GET`
    * **Endpoint:** `/products`

3.  **Get list of orders (supports pagination & filters)**
    * **Method:** `GET`
    * **Endpoint:** `/orders`
    * *Example filters (add as query parameters): `?status=active`, `?customer=John`, `?page=2`*

4.  **Create an order**
    * **Method:** `POST`
    * **Endpoint:** `/orders`
    * **Example Body:**
        ```json
        {
            "customer": "New Customer",
            "warehouse_id": 1,
            "items": [
                { "product_id": 1, "count": 2 },
                { "product_id": 2, "count": 1 }
            ]
        }
        ```

5.  **Update an order (customer info and items)**
    * **Method:** `PUT` or `PATCH`
    * **Endpoint:** `/orders/{order_id}`
    * **Example Body (to update customer and one item's count):**
        ```json
        {
            "customer": "Updated Customer Name",
            "items": [
                { "product_id": 1, "count": 3 } // Assuming product_id 1 was in the order
            ]
        }
        ```

6.  **Complete an order**
    * **Method:** `PATCH`
    * **Endpoint:** `/orders/{order_id}/complete`

7.  **Cancel an order**
    * **Method:** `PATCH`
    * **Endpoint:** `/orders/{order_id}/cancel`

8.  **Resume an order**
    * **Method:** `PATCH`
    * **Endpoint:** `/orders/{order_id}/resume`
    * *(Ensure stock availability is checked before resuming)*

9.  **View stock movement history (supports filters & pagination)**
    * **Method:** `GET`
    * **Endpoint:** `/stock-movements`
    * *Example filters: `?product_id=1`, `?warehouse_id=1`, `?date_from=YYYY-MM-DD`*

10. **(Optional/Helper) Manual Stock Adjustment**
    * **Method:** `POST`
    * **Endpoint:** `/manual-adjustment`
    * **Example Body:**
        ```json
        {
            "product_id": 1,
            "warehouse_id": 1,
            "quantity_change": 5,
            "reason": "Initial stock setup"
        }
        ```

**Note:**
* Replace `{order_id}` with an actual ID of an order.
* All POST/PUT/PATCH requests should include `Content-Type: application/json` and `Accept: application/json` headers.
* Error handling (e.g., insufficient stock, item not found) should return appropriate HTTP status codes and error messages.

---
---

# Micro-CRM API - Тестовое Задание (на русском)

Это приложение на Laravel было разработано в качестве тестового задания для реализации API микро-CRM для управления товарами, складами, заказами и движением товаров.

## Реализованные Основные Требования:

**Часть 1: Управление Заказами и Контроль Остатков**
* Просмотр складов и товаров (с уровнем остатков).
* Создание, просмотр списка (с фильтрами/пагинацией), обновление, завершение, отмена и возобновление заказов.
* Автоматическое списание/возврат товаров на склад при операциях с заказами.

**Часть 2: История Движения Товаров**
* Отслеживание всех изменений остатков товаров.
* API-метод для просмотра истории движения товаров с фильтрами (по складу, товару, датам) и пагинацией.

**Часть 3: Наполнение Тестовыми Данными**
* Консольная команда для заполнения начальных данных по товарам, складам и остаткам.

## Быстрая Настройка и Установка

1.  **Клонируйте репозиторий:**
    ```bash
    git clone https://github.com/Moraa7208/orders-management-laravel-test.git
    ```

2.  **Установите зависимости:**
    ```bash
    composer install
    ```

3.  **Настройка Окружения:**
    * Скопируйте `.env.example` в `.env`: `cp .env.example .env`
    * Сгенерируйте ключ приложения: `php artisan key:generate`
    * Настройте данные для подключения к вашей базе данных (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD) в файле `.env`.

4.  **Миграции Базы Данных:**
    ```bash
    php artisan migrate
    ```

5.  **Наполнение Тестовыми Данными (Задача 3):**
    Эта команда заполняет таблицы товаров, складов и начальные остатки.
    ```bash
    php artisan app:seed-test-data
    ```

6.  **Запустите сервер для разработки:**
    ```bash
    php artisan serve
    ```
    API обычно будет доступен по адресу `http://localhost:8000/api/`.

## API-методы для Тестирования

Ниже приведены ключевые API-методы, соответствующие требованиям задания. Используйте инструменты, такие как Postman или `curl`, для тестирования. (Префикс по умолчанию `/api`)

1.  **Просмотреть список складов**
    * **Метод:** `GET`
    * **Endpoint:** `/warehouses`

2.  **Просмотреть список товаров с их остатками по складам**
    * **Метод:** `GET`
    * **Endpoint:** `/products`

3.  **Получить список заказов (поддерживает пагинацию и фильтры)**
    * **Метод:** `GET`
    * **Endpoint:** `/orders`
    * *Примеры фильтров (добавляются как query-параметры): `?status=active`, `?customer=Иван`, `?page=2`*

4.  **Создать заказ**
    * **Метод:** `POST`
    * **Endpoint:** `/orders`
    * **Пример тела запроса:**
        ```json
        {
            "customer": "Новый Клиент",
            "warehouse_id": 1,
            "items": [
                { "product_id": 1, "count": 2 },
                { "product_id": 2, "count": 1 }
            ]
        }
        ```

5.  **Обновить заказ (данные клиента и список позиций)**
    * **Метод:** `PUT` или `PATCH`
    * **Endpoint:** `/orders/{order_id}`
    * **Пример тела запроса (обновить имя клиента и количество одного товара):**
        ```json
        {
            "customer": "Обновленное Имя Клиента",
            "items": [
                { "product_id": 1, "count": 3 } // Предполагая, что товар с product_id 1 был в заказе
            ]
        }
        ```

6.  **Завершить заказ**
    * **Метод:** `PATCH`
    * **Endpoint:** `/orders/{order_id}/complete`

7.  **Отменить заказ**
    * **Метод:** `PATCH`
    * **Endpoint:** `/orders/{order_id}/cancel`

8.  **Возобновить заказ**
    * **Метод:** `PATCH`
    * **Endpoint:** `/orders/{order_id}/resume`
    * *(Перед возобновлением убедитесь в наличии товара на складе)*

9.  **Просмотр историй изменения остатков товаров (поддерживает фильтры и пагинацию)**
    * **Метод:** `GET`
    * **Endpoint:** `/stock-movements`
    * *Примеры фильтров: `?product_id=1`, `?warehouse_id=1`, `?date_from=YYYY-MM-DD`*

10. **(Опционально/Вспомогательное) Ручная Корректировка Остатков**
    * **Метод:** `POST`
    * **Endpoint:** `/manual-adjustment`
    * **Пример тела запроса:**
        ```json
        {
            "product_id": 1,
            "warehouse_id": 1,
            "quantity_change": 5,
            "reason": "Начальная установка остатков"
        }
        ```

**Примечание:**
* Замените `{order_id}` на фактический ID заказа.
* Все POST/PUT/PATCH запросы должны включать заголовки `Content-Type: application/json` и `Accept: application/json`.
* Обработка ошибок (например, недостаточно товара, товар не найден) должна возвращать соответствующие HTTP-коды состояния и сообщения об ошибках.

-----------------------------------------------------------

--a. Выбрать имена (name) всех клиентов, которые не делали заказы в последние
--7 дней.

EXPLAIN ANALYSE
with customers as (
    select o.customer_id, max(o.order_date)
    from clients c
    JOIN orders o ON c.id = o.customer_id
    group by o.customer_id
    having max(o.order_date) < (current_timestamp - make_interval(days := 7))
)
select c.name from customers cust JOIN clients c ON cust.customer_id = c.id;

create index orders_customer_id_order_date_index
    on orders(customer_id, order_date);

-- Вообщем рекомендуют использовать партиционирование таблицы по датам
-- Составной индекс. Прирост скорости в 2 раза.


--b. Выбрать имена (name) 5 клиентов, которые сделали больше всего заказов в
--магазине.

EXPLAIN ANALYSE
with most_orders as(
    select o.customer_id, count(*)
    from orders o
    group by o.customer_id
    order by count(*) desc
    limit 5
)
select c.name from most_orders m JOIN clients c ON m.customer_id = c.id;

CREATE INDEX order_id_include_customer_id_index ON orders(id) INCLUDE (customer_id);

-- -- Индекс дал результат.


--c. Выбрать имена (name) 10 клиентов, которые сделали заказы на наибольшую
--сумму.

with most_orders_total as (
    select o.customer_id, sum(o.price)
    from orders o
    group by o.customer_id
    order by sum(o.price) desc
    limit 10
)
select c.name from most_orders_total m JOIN clients c ON m.customer_id = c.id;

CREATE INDEX order_id_include_customer_id_index ON orders(id) INCLUDE (customer_id);

-- Индекс дал результат. тот же что и выше.



--d. Выбрать имена (name) всех товаров, по которым не было доставленных
--заказов (со статусом “complete”)

EXPLAIN ANALYSE
with orders_not_complete as(
    select o.item_id
    from orders o
    where status IS DISTINCT FROM 'complete'
)
select m.name from orders_not_complete onc JOIN merchandise m ON onc.item_id = m.id;

CREATE INDEX order_distinct_from_complete_index ON orders (status) where status IS DISTINCT FROM 'complete';

--(Execution Time: 0.462 ms) (HASH)


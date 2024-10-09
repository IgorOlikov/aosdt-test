CREATE TABLE clients(
    id SERIAL PRIMARY KEY UNIQUE NOT NULL,
    name varchar(255) UNIQUE NOT NULL
);

CREATE TABLE merchandise(
    id SERIAL PRIMARY KEY UNIQUE NOT NULL,
    name varchar(255) UNIQUE NOT NULL
);

CREATE TABLE orders(
    id SERIAL PRIMARY KEY UNIQUE NOT NULL,
    item_id integer REFERENCES merchandise(id) NOT NULL,
    customer_id integer REFERENCES clients(id) NOT NULL,
    comment varchar(255) NOT NULL,
    status varchar(50) DEFAULT 'new' NOT NULL ,
    order_date timestamp DEFAULT current_timestamp NOT NULL
);
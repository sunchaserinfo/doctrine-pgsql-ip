create table ip_demo
(
    id serial
        constraint ip_demo_pk
            primary key,
    ip inet not null
);

create table ipblock_demo
(
    id      serial
        constraint ipblock_demo_pk
            primary key,
    ipblock cidr not null
);

create table nullable_demo
(
    id      serial
        constraint nullable_demo_pk
            primary key,
    ip      inet,
    ipblock cidr
);

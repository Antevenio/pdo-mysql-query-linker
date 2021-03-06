# pdo-mysql-query-linker
[![Latest Stable Version](https://poser.pugx.org/antevenio/pdo-mysql-query-linker/v/stable)](https://packagist.org/packages/antevenio/pdo-mysql-query-linker)
[![Total Downloads](https://poser.pugx.org/antevenio/pdo-mysql-query-linker/downloads)](https://packagist.org/packages/antevenio/pdo-mysql-query-linker)
[![License](https://poser.pugx.org/antevenio/pdo-mysql-query-linker/license)](https://packagist.org/packages/antevenio/pdo-mysql-query-linker)
[![Build Status](https://travis-ci.org/Antevenio/pdo-mysql-query-linker.svg?branch=master)](https://travis-ci.org/Antevenio/pdo-mysql-query-linker)
[![Code Climate](https://codeclimate.com/github/Antevenio/pdo-mysql-query-linker.png)](https://codeclimate.com/github/Antevenio/pdo-mysql-query-linker)

PHP library that allows linking queries from diferent physical databases using mysql pdo database
 connections.

What is this thing
---
I'll explain once I get it done.

Install
---

To add as a dependency using composer:

`composer require antevenio/pdo-mysql-query-linker`

Usage example
---

```php
<?php
$originPdo = new PDO('mysql:host=host1;dbname=kidsshouting', 'myuser', 'mypass');
$targetPdo = new PDO('mysql:host=host2;dbname=kidsshouting', 'myuser', 'mypass');

$linker = (new \PdoMysqlQueryLinker\Linker\Factory())->create()
    ->origin(
        $originPdo, 
        "select * from table_in_origin where column = 'something'"
        )
    ->target(
        $targetPdo, 
        "delete from table_in_destination inner join {origin} using(column)"
        );

// Get a limit clause block based iterator
$iterator = $linker->getIterator(10000);
foreach ($iterator as $row) {
    // do your stuff;
}
$linker->close();

// Get a pdo statement
$stmt = $linker->execute();
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    // do your stuff;
}
$linker->close();

// Get just the resolved query to run in destination adapter
$query = $linker->getQuery();
$stmt = $targetPdo->query($query);
$linker->close();
```
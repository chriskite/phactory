# Phactory: PHP Database Object Factory for Unit Testing

## What is it?
Phactory is an alternative to using database fixtures in your PHP unit tests.
Instead of maintaining a separate XML file of data, you define a blueprint
for each table, and then create as many different objects as you need.

Phactory was inspired by Factory Girl.

## Features
* Define default values for your table rows once with Phactory::define(),
then easily create objects in that table with a call to Phactory::create().
* Create associations between your defined tables, and the objects will automatically
be associated in the database upon creation.
* Use sequences to create unique values for each successive object you create.

## Database Support
* MySQL
* Sqlite

## Language Support
* PHP >= 5.2

## Limitations
* Each table must have a single integer primary key for associations to work.

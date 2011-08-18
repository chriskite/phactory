## 0.3.2 / 2011-08-18

* Bug Fixes

    * Fix Phactory::define() to only require the first argument

## 0.3.1 / 2011-08-09

* Bug Fixes

    * Phactory::get() and getAll() now use PDO::FETCH_ASSOC instead of FETCH_BOTH

## 0.3.0 / 2011-04-18

* Minor Improvements

    *  Argument to Phactory::define() evals expressions like '#{php-code}'

* Bug Fixes

    * Fixed bug where association's to column was not being used


## 0.2.0 / 2010-12-01

* Major Improvements

    * Add support for MongoDB, including associations via embedding

* Minor Improvements

    * Add ability to associate an array of Phactory_Rows when creating with a many-to-many association
    * New Phactory::build() method creates a row without saving it to the databaes
    * Add Phactory::getAll() method to retrieve multiple rows

## 0.1.0 / 2010-08-11

* Minor Improvements

    * Add ability to specify multiple byColumns in Phactory::get()
    * Fix from_column guessing on Phactory::manyToOne()

## 0.0.4 / 2010-07-22

* Major Improvements

    * Inflection of table names from singular to plural is handled automatically,
      so only the singular name needs to be specified.
    * ManyToMany associations are able to guess all the necessary column names.

* Minor Improvements

    * Improved SQL error detection and reporting.
    * Added Phactory_Row#toArray()

* Bug Fixes

    * Join tables used by associations are cleared during recall().

## 0.0.3 / 2010-07-01

* Bug Fixes
    
    * Pass the correct arguments to Phactory::manyToMany()
    * Fix Phactory_DbUtil_MysqlUtil to get primary key correctly

## 0.0.2 / 2010-07-01

* Minor Improvements
    
    * Added inflector support to automatically pluralize table names

## 0.0.1 / 2010-06-30

* Initial release

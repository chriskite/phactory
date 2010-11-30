## 0.2.0 / 2010-12-01

* Minor Improvements

    * Add ability to associate an array of Phactory_Rows when creating with a many-to-many association
    * New Phactory::build() method creates a row without saving it to the databaes

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

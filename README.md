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
* Postgresql

## Language Support
* PHP >= 5.3

## Limitations
* Each table must have a single integer primary key for associations to work.

## How to use Phactory in Drupal

If you're  used to Test Driven Development and working in Drupal, you've probably found it is nearly impossible using the default testing framework.  It simply takes too long, and mocking/stubbing is difficult at best.

If you want to use phpunit for the tests, you need the full drupal stack to be able to do it.  So this is a config to bootstrap Drupal in the PhactoryBootstrap.  

Everything else is pretty straightforward/works as normal.

* create a phpunit.xml

         <phpunit bootstrap="PhactoryBootstrap.php">
          <testsuites>
             <testsuite name="MyPlatform">
               <file>file1.php</file>
               <file>file2.php</file>
               <directory>TestDirectory</directory>
             </testsuite>
           </testsuites>
         </phpunit>

* create a PhactoryBootstrap.php


         require_once 'Phactory/lib/Phactory.php';
         require_once 'BaseTest.php';
         
         define('DRUPAL_ROOT', '/my/drupal/directory/public_html/');
         define('ENVIRONMENT', 'TEST');
         $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
         $_SERVER['SERVER_SOFTWARE'] = null;
         require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
         
         // Fix autoloader
         function phactoryAutoloader($className) {
           if(preg_match('/class1/', $className)){
             $filepath = DRUPAL_ROOT . 'sites/all/modules/class1/lib/' . str_replace('\\', '/', $className) . '.php';
             require_once $filepath;
           }elseif(preg_match('/class2/', $className)){
             $filepath = DRUPAL_ROOT . 'sites/all/modules/class2/lib/' . str_replace('\\', '/', $className) . '.php';
             require_once $filepath;
           }elseif(preg_match('/xautoload/', $className)){
             $path = str_replace('xautoload_', '', $className);
             $filepath = DRUPAL_ROOT . 'sites/all/modules/xautoload/lib/' . str_replace('_', '/', $path)  . '.php';
             require_once $filepath;
           }
         }
         // Add custom autoloader
         spl_autoload_register('phactoryAutoloader');
         
         drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
         
         \Database::setActiveConnection('test');
         system_list_reset();


* Create a BaseTest class the test classes can extend from


         namespace Drupal\my_module\PHPUnitTests;
         
         use \Phactory;
         use \PDO;
         
         abstract class BaseTest extends \PHPUnit_Framework_TestCase {
         
           public function setUp(){
             global $user;
             Phactory::recall();
             $user = Phactory::create('users', array('uid'=>1));
             $this->assertTrue($user->uid > 0, 'created a user ');
             Phactory::create('activity_category', array('category_id' => 1));
             Phactory::create('activity_category', array('category_id' => 2));
             
             $this->host = 'localhost';
           }
         
           public static function setUpBeforeClass()
           {
             $pdo = new PDO('mysql:host=127.0.0.1; dbname=test', 'test');
             Phactory::setConnection($pdo);
             Phactory::reset();
             Phactory::define('users', array(
                 'name'=>'test$n', 
                 'timezone' => 'America/Los_Angeles', 
                 'uid'=>'$n'));
         
             Phactory::setInflection('activity_category', 'activity_category');
             Phactory::define('activity_category', array(
                 'category_name'=>'test_cat_$n',
                 'category_id'=>'$n'));
         
             Phactory::setInflection('campaign', 'campaign');
             Phactory::define('campaign', array(
                 'campaign_id'=>'$n',
                 'campaign_rid'=>'$n',
                 'campaign_image_url'=>'http://test.com/camp_image$n.jpg'
                 ));
         
           }
           
           public function tearDown()
           {
             Phactory::recall();
           }
           abstract public static function getInfo();
         }


* Create Test


         class MetricTest extends \Drupal\my_module\PHPUnitTests\BaseTest {
         
           public static function getInfo() {
             return array(
               'name' => 'Model/Metric',
               'description' => 'Model/Metric',
               'group' => 'My Project',  
             );
           }
         
           public function setUp(){
             parent::setUp();
             db_query('truncate {metric}')->execute();
         
           }
           public function testCRUD(){
             global $user;
         
             $metrics = Metric::get();
             $this->assertEmpty($metrics, 'Initial get should be empty');
           }   
         }


* Copy sql schema from drupal db to new db called 'test'
* Run tests: ```> phpunit```
* Now you don't have to use the DrupalWebTestCase and your tests don't take forever.


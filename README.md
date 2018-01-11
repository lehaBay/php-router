# PHP Router


**Simple**, **fast** and fully **covered with a tests** Router. 

Router is a very important part of (almost)any web application and as a 
part of core it must have as less overhead as possible. That's what this 
router is built for -- to be [extremely fast](#why-is-it-fast)

requires php 7.0 +
# Install with **composer**

```sh
$ composer require fastero/php-router
```

# Usage
```php
$routes = [
    'admin' => Literal::config('admin')
        ->setController(MyProject\Admin\IndexController::class, 'run')
        ->get(),
    'admin-user-list' => Section::config('admin/user', '[/age/:from/:to][/country/:country]')
        ->setController(MyProject\Admin\UserController::class, 'run')
        ->get(),
    'admin-user-id' => Section::config('admin/user', '/:id')
        ->parameter('id', null, '[0-9]')
        ->setController(MyProject\Admin\UserIdController::class, 'run')
        ->get(),

    'admin-user-name' => Section::config('admin/user', '/:name')
        ->setController(MyProject\Admin\UserNameController::class, 'run')
        ->get(),
];

$router = new Router($routes);

try{

    $data = $router->match($_SERVER['REQUEST_METHOD'], trim($_SERVER['REQUEST_URI'],'/'),$_GET);
    $roteName = $data['name']; //name of route that match
    $routeParameters = $data['parameters']; //parameters that were extracted
    $query = $data['query']; //query parameters (usually they are the same as were passed it a ->match method
    $controller = $data['options']['controller']; //['class'=> 'className', 'method' => 'methodName']
    //create controller and call it's method?
} catch (\Fastero\Router\Exception\RouterNotFoundException $exception){
    //do something if matching route was not found (display 404)
} catch (Exception $exception){
    //do something if there was an error during matching process (display 503 ?)
}
```

```$routes``` array contain's route names as keys and configuration parameters array as value. 
It's recommended to generate configuration using [Configurators](#configurators) 

And to use reverse routing (path generation):
```php
$router->makePath('admin-user-name', ['name' => 'nobody']) // will return string "admin/user/nobody"

```
See [Generate path](#generate-path)

## Configurators
Configurators are created to make configuration of your route easier 
and prevent you from making mistakes. 

While configurators are using chaining syntax they are 
not return an object as a result but simple array and they don't create a bunch
of objects while being used. This method has some limitations 
but it has much less overhead and almost as fast as using simple arrays and 
speed is a main goal of this library.

To start configuring route you must call static method ```::configure``` on 
appropriate Configurator. This method usually has two parameters 
```$staticPrefix``` and ```$pattern```. Example:
```$routeConfigure = Section::config('user/', 'id/:user_id')```

```$routeConfigure``` now is a ```Section``` Object and other it's methods can be called
to continue configuration and all of theme will return ```Section``` object
and at the very end you **must call ```->get()``` method** which will return 
array with all the configurations. So at the end it looks like:
```php
$routes = [
 'route-name' => Section::config('user/', 'id/:user_id')
         ->parameter('user_id', null, '[0-9]+')
         ->setController('controllerClassName', 'Method')
         ->get(),
];
```
All the configurators has following methods:

```
php->parameter($name, $default = null, $validationRule = null)
```
Add a default value for parameter and/or validation rule. Validation rule
is a regular expression.
```php
->setController($class, $method)
```
This controller will be returned as a part of route options so you can
call it or do whatever you want. It has no special meaning for Router class

```php
->setReversePattern($pattern)
```
Some route handlers may not be able to generate reverse 
path from path by itself (for example Regex route) so you need to specify
this pattern and reverse path generator as well (see next)

```php
setReverseGenerator($generatorClass)
```
Class name that will be used as a generator with specified reversePattern. If specified it will be used 
instead of built in route handler generator.

```php
-> queryParameter($name, $required, $default = null, $validationRule = null)
```
Set rules for query parameter


Currently there are 3 configurators (src/Configuration) 
one per each [route type](#route-types):
* ```\Fastero\Router\Configuration\Literal ```
\- corresponding the [\Fastero\Router\PathHandler\Literal](#literal)  route type

* ```\Fastero\Router\Configuration\Section ```
\- corresponding the [\Fastero\Router\PathHandler\Section](#section)  route type

* ```\Fastero\Router\Configuration\Regex ```
 \- corresponding the [\Fastero\Router\PathHandler\Regex](#regex)  route type

All they have the same methods but slightly different signature for ::config() method. That's because
Literal route type does not need any path pattern but static path only

## Route types

### Literal 
```\Fastero\Router\PathHandler\Literal``` - Represents simple static path that has no parameters
like "admin/users", "article/list" etc. It's fastest route type and should be used whenever possible

### Regex 
```\Fastero\Router\PathHandler\Regex``` - Represents Regular Expression route type. It's requires a regular expression 
(in the format that preg_match can execute without delimiters and "^"""$" operators as they included automatically ) 
with named parameters e.g.: ```user/age(?<age>([0-9])+)```.

It's recommended to make regex as simple as possible and use validators 
if you need to validate parameters rather than include all the validation in the regex.

### Section
* ```\Fastero\Router\PathHandler\Section``` - This is route in format like 
```some/path/parameter/:parameter_name[optional-parameter/:optional_parameter_name]```

and ```some/path/parameter/``` here is a static prefix 
or just ```some/path/``` - more unique prefix is better but 
sometimes it's more important to be looking good and readable.

so this type of route uses "/" to delimit parameters and ":" to define parameter
"[", "]" - to define optional parameter. 

More examples (with static prefixes as they should be used. 
You may think about if like it's a single concat string):

```'news/', ':tag[/author/:author_name]' ```
* tag - is required parameter
* author_name - is optional
this will match following paths:
'news/politics', 'news/local/author/Grou'

```'realty/', ":city[/price/:from/:to][/street/:street_name][/floor[/from/:from][/to/:to]]"```
## Generate path
Currently there are two path generators:
* ```\Fastero\Router\PathHandler\Literal``` - which is also a path handler. This generator
will simply return path.

* ```\Fastero\Router\PathHandler\SectionPathGenerator``` - it uses the same 
format as a \Fastero\Router\PathHandler\SectionPathMatcher and is a default
generator for this matcher.

Format:
 ```section/:sectionName/[/filter[/id/:id][/name/:name]]```
 
 * [] - optional section, will be generated only if any parameter inside is set
 * :id - parameter name - will be replaced with actual parameter if given.
 [a-zA-Z_0-9] characters are allowed, started with [a-zA-Z_]
 * characters '[', ']', ':' - can be escaped with '\' if meant as literals
 
Examples:
 * ```news/:id```  - "id" is required parameter
 * ```news[/:author[/:year\::month\::day]```  - 
 'author' and 'year', 'month', 'day' are optional. Results: ```news/alexey/1989:08:1987```, ```news/alexey```, ```news```

# Why is it fast?
There are two factors - first it uses native array as a configuration and 
does not create an object for every route. Even though creating a few objects 
per route is not a big deal it will still some part of your performance and 
this part will be bigger more routes you have.

Second factor - is that it forces you to use static prefixes to your paths. 
And searching by static prefixes first is much faster than executing each regex. 
Some other libraries are doing the same but they trying to figure out prefixes 
by itself and in order for this to be efficient you would need use cache. 
While using cache is not a bad thing to do usually you want your app to be fast
enough without cache and then make it even faster with cache.



MIT Licensed, http://www.opensource.org/licenses/MIT

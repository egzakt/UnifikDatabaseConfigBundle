FlexyDatabaseConfigBundle
==========================
FlexyDatabaseConfigBundle allows you to store configurations from the configuration tree of a bundle and parameters in a database table. Those configurations and parameters will override those defined in the ```app/config/config.yml``` and ```app/config/parameters.yml``` files.

Configurations are all cached using Symfony's container caching mechanism and do not hit the database.

## Content
* Installation
* How to use

## Installation

1. Add this to your composer.json :
```js
    "require": {
        'flexy/database-config-bundle': 'dev-master'
    }
```

2. Run a composer update :
```bash
composer update
```

3. Register the bundle in your AppKernel.php :
```php
public function registerBundles()
{
        new Flexy\DatabaseConfigBundle\FlexyDatabaseConfigBundle(),
}
```

4. Extend the getContainerBuilder() method in AppKernel.php :
```php
protected function getContainerBuilder()
{
    return new Flexy\DatabaseConfigBundle\DependencyInjection\Compiler\ContainerBuilder(new ParameterBag($this->getKernelParameters()));
}
```

5. Update the database schema :
```bash
app/console doctrine:schema:update --force
```

## How to use

### Add a configuration to the database
FlexyDatabaseConfigBundle reproduces the configuration tree of a bundle in the database table named ```container_config```. If you want to add a configuration in the database table, you have to first add the extension name in the ```container_extension``` table. After that, you will have to add each parent node of the configuration tree that leads to the configuration you have to override.

For example, if you have the following configuration and you want to override ```project_title``` :

```yml
twig:
    globals:
         project_title: My project title
```

First, we have to add ```twig``` to the ```container_extension``` table :

| id  | name |
| --: | ---- |
| 1   | twig |

Then, we add every node that leads to ```project_title``` in the ```container_config``` table :

| id  | parent_id | extension_id | name          | value                |
| --: | --------: | -----------: | ------------- | -------------------- |
| 1   | *NULL*    | 1            | globals       | *NULL*               |
| 2   | 1         | 1            | project_title | My New Project Title |

### Add a parameter to the database

Parameters are stored in the ```container_parameter``` table in the database. To add a parameter to the database, you just add its name and value to the table.

| id  | name             | value                     |
| --: | ---------------- | ------------------------- |
| 1   | custom_parameter | My custom parameter value |

### Clear the cache

As database configurations and parameters are cached, you will need to do a `app/console cache:clear` every time you wish to reload the configuration coming from the database.

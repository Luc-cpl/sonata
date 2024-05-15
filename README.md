
## Doctrine configuration

Firstly, to use Doctrine, you need to require doctrine and a cache library.

The cache library is used to cache the metadata of the entities and Doctrine recommends the use of symfony/cache. 

You can install these libraries using the following command.

```bash
composer require doctrine/orm doctrine/dbal symfony/cache
```

Saelker Migrations Bundle
---

### Step 1: Install via Composer
`composer reqire saelker/migrations-bundle`

### Step 2: Add to Your App Kernel
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Saelker\MigrationsBundle\SaelkerMigrationsBundle(),
    );
}
```

### Step 3.1: Add Directories via config

```yaml
# Saelker Migrations Directories
saelker_migrations:
    directories:
        - '%kernel.project_dir%/src/AppBundle/Migrations'
        ...
```

### Step 3.2: Add Directories via CompilerPass

```php
class SaelkerMigrationsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
         parent::build($container);

         $container->addCompilerPass(new MigrationsCompilerPass(__DIR__ . "/Migrations"));
    }
}

```

### Step 4: Run first migration
```command
bin/console saelker:migrations:migrate
```

### Step 5: Generate new migration
```command
bin/console saelker:migrations:generate
```
### Step 6: Modify saved path depth
```yaml
# Saelker Migrations Directories
saelker_migrations:
    clean_depth: 3
    directory_separator: '/'
    use_camel_case: true
```

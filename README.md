MrMIsoTestBundle
===============

## Fixture usage

Fixture files have to be placed inside a sub directory 'fixtures' of the directory
of your TestCase.

### loading the fixture files

```php
    $fixtureName = "myTestFixture.yml";
    $this->loadFixture($fixtureName);    
```

### Sample fixture file

```yaml
-  
  model: Acme\AcmeBundle\Entity\Shop
  fixtures:
    Shop1:
      - name: myshop-1
      - street: Elm Street 13
      - city: New York
    Shop2:
      - name: myshop-2  
      - street: Center Plaza 25
      - city: London
-  
  model: Acme\AcmeBundle\Entity\Outlet
  fixtures:
    Outlet1:
      - name: myoutlet
        shop: "@@Shop1"
    Outlet2:
      - name: myoutlet-2  
        shop: "@@Shop2"
```

You can reference to an fixture object, using the prefix @@ inside the value of an attribute.
The fixture object has to be defined within the same fixture file and the names of the object
have to be unique.
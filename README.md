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
-
  model: Acme\AcmeBundle\Entity\Outlet
  fixtures:
    Code1:
      - value: topsecret
        expiresAt: <!php return new DateTime(); !php>
```
Basically the structure of a Model fixture is:

```yaml
-
    model: The Model Name
    fixture:
        ModelReferenceName1:
            - setterName1: value / reference / phpcode
              setterName2: value / reference / phpcode
        ModelReferenceName2:
            - setterName1: value / reference / phpcode
              setterName2: value / reference / phpcode
-
    model: Name of another Model
    ...
```

### Using References inside Fixture ( @@ )

You can reference to an fixture object, using the prefix @@ inside the value of an attribute.
The fixture object has to be defined within the same fixture file and the names of the object

### Executing PhpCode inside Fixture ( <!php ... !php> )

If you want to set a particular Object you can excecute php code to construct it inside the fixture. 
The following example will return a DateTime Object.

```yaml
  model: Acme\AcmeBundle\Entity\Outlet
  fixtures:
    Code1:
      - value: topsecret
        expiresAt: <!php return new DateTime(); !php>
```

You must not forget to finally use the 'return' statement, or the attribute will be null.

<?php

namespace MrM\MrMIsoTestBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;

require_once(__DIR__ . "/../../../../app/AppKernel.php");

class BaseWebTestCase extends WebTestCase {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;           
    protected $container;    
    
    public function __construct() {
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->container = $kernel->getContainer();
        parent::__construct();
    }

    public function getContainer() {
        return $this->application->getKernel()->getContainer();
    }

    protected function get($service) {
        return $this->container->get($service);
    }

    /**
     * return the EntityManager of the current kernel or kernel of the given $client
     * 
     * @param \Symfony\Component\HttpKernel\Client $client
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager(\Symfony\Component\HttpKernel\Client $client = null) {
        if (is_null($client))
            return $this->em;
        return $client->getContainer()->get('doctrine')->getEntityManager();
    }

    /**
     * call a protected or private method on $object
     * 
     * @param Object $object
     * @param string $methodName
     * @param array $args
     * @return 
     */
    protected static function callMethod($object, $methodName, array $args) {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }   
    
    /**
     * {@inheritDoc}
     */
    public function setUp() {
        $kernel = new \AppKernel("test", true);
        $kernel->boot();
        $this->application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $this->application->setAutoExit(false);
        $this->em = $this->get('doctrine')->getEntityManager();
        $this->generateSchema();
        parent::setUp();
    }

    /**
     * load the fixtures reside in ./fixtures/$fixtureName into db. if you want to
     * use in combination with a client you have to pass the client, else the standard
     * kernel will be used.
     * 
     * @param string $fixtureName
     * @param \Symfony\Component\HttpKernel\Client $client
     * @throws \Exception 
     */
    public function loadFixture($fixtureName, $client = null) {
        $references = array();
        
        $class_info = new \ReflectionClass($this);
        $dir = dirname($class_info->getFileName());

        $fixtureName = "$dir/fixtures/$fixtureName";
        $fixtureData = file_get_contents($fixtureName);        
        $fixtureData = Yaml::parse($fixtureData);
        
        foreach ($fixtureData as $modelData) {
            $modelClass = $modelData['model'];
            foreach ($modelData['fixtures'] as $name => $data) {
                if (key_exists($name, $references))
                    throw new \Exception("Fixture with name '$name' already exists in fixture file $fixtureName");
                foreach ($data as $fixture) {
                    $model = new $modelClass();
                    foreach ($fixture as $attributeName => $value) {
                        $values = $value;
                        if (!is_array($values)) {
                            $values = array($values);
                        }
                        foreach ($values as $k => $v) {
                            if (substr($v, 0, 2) == "@@") {
                                $ref = $references[substr($v, 2, strlen($v) - 2)];
                                $values[$k] = $ref;
                            }                            
                        }
                        $methodName = "set" . ucfirst($attributeName);
                        
                        if (is_array($value))
                            $model->$methodName($values);
                        else
                            $model->$methodName(array_pop($values));
                    }
                    $this->getEntityManager($client)->persist($model);
                    $this->getEntityManager($client)->flush();                    
                    $references[$name] = $model;
                }            
            }            
        }
    }
    
    /**
     * {@inheritDoc}
     */
    protected function tearDown() {
        if ($this->em)
            $this->em->close();
        parent::tearDown();
    }

    /**
     * @return null
     */
    protected function generateSchema() {
        $metadatas = $this->getMetadatas();

        if (!empty($metadatas)) {
            $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
            $tool->dropSchema($metadatas);
            $tool->createSchema($metadatas);
        }
    }

    /**
     * @return array
     */
    protected function getMetadatas() {
        return $this->em->getMetadataFactory()->getAllMetadata();
    }

}

?>

<?php

namespace Drupal\Tests\tripal_biodb\Kernel\Task;

use Drupal\KernelTests\KernelTestBase;
use Drupal\tripal_biodb\Task\BioTaskBase;
use Drupal\Tests\tripal\Kernel\TripalDBX\Subclass\TripalDbxConnection;

/**
 * Tests for tasks.
 *
 * @coversDefaultClass \Drupal\tripal_biodb\Task\BioTaskBase
 *
 * @group Tripal
 * @group Tripal BioDb
 * @group Tripal BioDb Task
 */
class BioTaskBaseKernelTest extends KernelTestBase {

  /**
   * Test members.
   *
   * "pro*" members are prophesize objects while their "non-pro*" equivqlent are
   * the revealed objects.
   */
  protected $proConfigFactory;
  protected $configFactory;
  protected $proConfig;
  protected $config;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Register Tripal DBX service.
    $this->enableModules(['tripal']);
    $this->enableModules(['tripal_biodb']);
  }

  /**
   * Tests constructor: check constructor calls.
   *
   * @cover ::__construct
   * @cover ::initId
   * @cover ::getId
   * @cover ::getLogger
   */
  public function testBioTaskBaseConstructor() {
    // Create a mock for the abstract class.
    $tmock = $this->getMockBuilder(\Drupal\tripal_biodb\Task\BioTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethods([/*'initId',*/ 'getTripalDbxClass'])
      ->getMockForAbstractClass()
    ;
    /*$tmock
      ->expects($this->once())
      ->method('initId')
    ;*/
    $tmock
      ->expects($this->any())
      ->method('getTripalDbxClass')
      ->with('Connection')
      ->willReturn('\Drupal\Tests\tripal\Kernel\TripalDBX\Subclass\TripalDbxConnection')
    ;

    // Parameters.
    $parameters = [
      'input_schemas' => ['insch'],
      'output_schemas' => ['outsch'],
    ];

    // Call the constructor.
    $reflected_class = new \ReflectionClass(\Drupal\tripal_biodb\Task\BioTaskBase::class);
    $constructor = $reflected_class->getConstructor();
    $constructor->invoke($tmock);

    // // Create a new initialized object to cehck constructor work.
    // $tmock = $this->getMockBuilder(\Drupal\tripal_biodb\Task\BioTaskBase::class)
    //   ->setMethods(['getTripalDbxClass'])
    //   ->setConstructorArgs([$parameters])
    //   ->getMockForAbstractClass()
    // ;
    // $tmock
    //   ->expects($this->any())
    //   ->method('getTripalDbxClass')
    //   ->with('Connection')
    //   ->willReturn('\Drupal\Tests\tripal\Kernel\TripalDBX\Subclass\TripalDbxConnection')
    // ;

    // Check default values.
    $tmock->setParameters($parameters);
    $db_name = \Drupal::service('database')->getConnectionOptions()['database'];
    $this->assertEquals('task-' . $db_name . '-1i-insch-1o-outsch', $tmock->getId(), 'Id set.');
    $this->assertInstanceOf('\Psr\Log\LoggerInterface', $tmock->getLogger(), 'Logger.');
  }
}

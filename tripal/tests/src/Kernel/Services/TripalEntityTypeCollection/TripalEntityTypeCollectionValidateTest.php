<?php

namespace Drupal\Tests\tripal\Kernel\Services\TripalEntityTypeCollection;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Url;
use Drupal\tripal\TripalVocabTerms\TripalTerm;
use Drupal\tripal\TripalVocabTerms\Interfaces\TripalIdSpaceInterface;


/**
 * Focused on testing the validate() method.
 *
 * @group Tripal
 * @group Tripal Content
 * @group TripalEntityTypeCollection
 */
class TripalEntityTypeCollectionValidateTest extends KernelTestBase {


  /**
   * {@inheritdoc}
   */
  protected static $modules = ['tripal'];

  /**
   * A dummy Tripal Term.
   * NOTE: This is a dummy object so any methods called on it will return NULL.
   *
   * @var array of \Drupal\tripal\TripalVocabTerms\TripalTerm
   */
  protected array $mock_terms;

  /**
   * A dummy Tripal ID Space.
   * NOTE: This is a dummy object so any methods called on it will return NULL.
   *
   * @var \Drupal\tripal\TripalVocabTerms\TripalIdSpaceBase
   */
  protected object $mock_idspace;

    /**
   * {@inheritdoc}
   */
  protected function setUp() :void {

    parent::setUp();

    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Grab the container.
    $container = \Drupal::getContainer();

    // We need a term for property types so we will create a generic mocked one
    // here which will be pulled from the container any time a term is requested.
    // -- valid organism term.
    $mock_term = $this->createMock(\Drupal\tripal\TripalVocabTerms\TripalTerm::class);
    $mock_term->method('getName')
      ->willReturn('organism');
    $mock_term->method('getIdSpace')
      ->willReturn('OBI');
    $mock_term->method('getAccession')
      ->willReturn('0100026');
    $mock_term->method('getVocabulary')
      ->willReturn('OBI');
    $mock_term->method('isValid')
      ->willReturn(TRUE);
    $this->mock_terms['organism'] = $mock_term;
    // Invalid term with missing name.
    $mock_term = $this->createMock(\Drupal\tripal\TripalVocabTerms\TripalTerm::class);
    $mock_term->method('getName')
      ->willReturn('');
    $mock_term->method('getIdSpace')
      ->willReturn('BEEP');
    $mock_term->method('getAccession')
      ->willReturn('invalidTerm');
    $mock_term->method('getVocabulary')
      ->willReturn('Fake Realm');
    $mock_term->method('isValid')
      ->willReturn(FALSE);
    $this->mock_terms['invalidTerm'] = $mock_term;

    // Create a mock ID space to return our mock term when asked.
    $this->mock_idspace = $this->createMock(\Drupal\tripal\TripalVocabTerms\Interfaces\TripalIdSpaceInterface::class);
    $this->mock_idspace->method('getTerm')
      ->willReturnCallback(function($accession) {
        if (array_key_exists($accession, $this->mock_terms)) {
          return $this->mock_terms[$accession];
        }
        else {
          return NULL;
        }
      });
    // Create a mock Tripal ID Space service to return our mock idspace when asked.
    $mock_idspace_service = $this->createMock(\Drupal\tripal\TripalVocabTerms\PluginManagers\TripalIdSpaceManager::class);
    $mock_idspace_service->method('loadCollection')
      ->willReturnCallback(function($id_space) {
        if ($id_space == 'mock') {
          return $this->mock_idspace;
        }
        else {
          return NULL;
        }
      });
    $container->set('tripal.collection_plugin_manager.idspace', $mock_idspace_service);
  }

  /**
   * Tests the TripalEntityTypeCollection class public functions.
   */
  public function testTripalEntityTypeCollection() {

    $this->assertInstanceOf(TripalTerm::class, $this->mock_terms['organism']);
    $this->assertTrue($this->mock_terms['organism']->isValid(), "Mock Organism Term must pass TripalTerm::isValid");
    $this->assertFalse($this->mock_terms['invalidTerm']->isValid(), "Mock Term missing name must fail TripalTerm::isValid");

    // Create a good content type array.
    $good = [
      'label' => 'Organism',
      'term' => $this->mock_terms['organism'],
      'help_text' => 'Use the organism page for an individual living system, such as animal, plant, bacteria or virus,',
      'category' => 'General',
      'id' => 'organism',
      'title_format' => "[organism_genus] [organism_species] [organism_infraspecific_type] [organism_infraspecific_name]",
      'url_format' => "organism/[TripalEntity__entity_id]",
      'synonyms' => ['bio_data_1']
    ];

    $content_type_service = \Drupal::service('tripal.tripalentitytype_collection');

    // Test creating a good content type.
    $is_valid = $content_type_service->validate($good);
    $this->assertTrue($is_valid, "A good content type definition failed validation check.");

  /**
   * Working up to here
    $content_type = $content_type_service->createContentType($good);
    $this->assertTrue(!is_null($content_type), "Failed to create a content type with a valid definition.");

    // Test that when a value is missing it fails validation.
    $bad = $good;
    unset($bad['term']);
    $is_valid = $content_type_service->validate($bad);
    $this->assertFalse($is_valid, "A content type definition missing the 'term' should fail the validation check but it passed.");
    $content_type = $content_type_service->createContentType($bad);
    $this->assertTrue(is_null($content_type), "Created a content type when the term is incorret.");

    # Name is currently created automatically.
    # $bad = $good;
    # unset($bad['name']);
    # $is_valid = $content_type_service->validate($bad);
    # $this->assertFalse($is_valid, "A content type definition missing the 'name' should fail the validation check but it passed.");
    # $content_type = $content_type_service->createContentType($bad);
    # $this->assertTrue(is_null($content_type), "Created a content type when the name is incorret.");

    $bad = $good;
    unset($bad['label']);
    $is_valid = $content_type_service->validate($bad);
    $this->assertFalse($is_valid, "A content type definition missing the 'label' should fail the validation check but it passed.");
    $content_type = $content_type_service->createContentType($bad);
    $this->assertTrue(is_null($content_type), "Created a content type when the label is incorret.");

    $bad = $good;
    unset($bad['category']);
    $is_valid = $content_type_service->validate($bad);
    $this->assertFalse($is_valid, "A content type definition missing the 'category' should fail the validation check but it passed.");
    $content_type = $content_type_service->createContentType($bad);
    $this->assertTrue(is_null($content_type), "Created a content type when the category is incorret.");


    $bad = $good;
    unset($bad['help_text']);
    $is_valid = $content_type_service->validate($bad);
    $this->assertFalse($is_valid, "A content type definition missing the 'help_text' should fail the validation check but it passed.");
    $content_type = $content_type_service->createContentType($bad);
    $this->assertTrue(is_null($content_type), "Created a content type when the help_text is incorret.");


    # Synonyms are not yet supported but will be in a later PR.
    # $bad = $good;
    # $bad['synonyms'] = 'xyz';
    # $is_valid = $content_type_service->validate($bad);
    # $this->assertFalse($is_valid, "A content type definition with a malformed synonyms list should fail the validation check but it passed.");
    # $content_type = $content_type_service->createContentType($bad);
    # $this->assertTrue(is_null($content_type), "Created a content type when the synonyms are incorret.");

    */
  }
}

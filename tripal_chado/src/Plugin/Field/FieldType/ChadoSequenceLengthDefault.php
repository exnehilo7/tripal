<?php

namespace Drupal\tripal_chado\Plugin\Field\FieldType;

use Drupal\tripal_chado\TripalField\ChadoFieldItemBase;
use Drupal\tripal_chado\TripalStorage\ChadoIntStoragePropertyType;
use Drupal\tripal_chado\TripalStorage\ChadoTextStoragePropertyType;
use Drupal\core\Form\FormStateInterface;
use Drupal\core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of Default Tripal field for sequence data.
 *
 * @FieldType(
 *   id = "chado_sequence_length_default",
 *   label = @Translation("Chado Feature Sequence Length"),
 *   description = @Translation("A chado feature sequence length"),
 *   default_widget = "chado_sequence_length_widget_default",
 *   default_formatter = "chado_sequence_length_formatter_default"
 * )
 */
 

class ChadoSequenceLengthDefault extends ChadoFieldItemBase {

  public static $id = "chado_sequence_length_default";

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'seqlen';
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = parent::defaultFieldSettings();
    $settings['termIdSpace'] = 'data';
    $settings['termAccession'] = '1249';
    $settings['fixed_value'] = TRUE;
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    $settings = parent::defaultStorageSettings();
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function tripalTypes($field_definition) {
    $entity_type_id = $field_definition->getTargetEntityTypeId();

    // Get the base table columns needed for this field.
    $settings = $field_definition->getSetting('storage_plugin_settings');

    // Get the property terms by using the Chado table columns they map to.
    $storage = \Drupal::entityTypeManager()->getStorage('chado_term_mapping');
    $mapping = $storage->load('core_mapping');
    $record_id_term = 'SIO:000729';
    $seqlen_term = $mapping->getColumnTermId('feature', 'seqlen');

    // Return the properties for this field.
    $properties = [];
    $properties[] = new ChadoIntStoragePropertyType($entity_type_id, self::$id, 'record_id', $record_id_term, [
        'action' => 'store_id',
        'drupal_store' => TRUE,
        'chado_table' => 'feature',
        'chado_column' => 'feature_id'
    ]);
    $properties[] =  new ChadoIntStoragePropertyType($entity_type_id, self::$id, 'seqlen', $seqlen_term, [
      'action' => 'store',
      'chado_column' => 'seqlen',
      'chado_table' => 'feature'
    ]);
    return $properties;
  }
}

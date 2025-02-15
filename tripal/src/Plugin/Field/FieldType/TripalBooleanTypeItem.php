<?php

namespace Drupal\tripal\Plugin\Field\FieldType;

use Drupal\tripal\TripalField\TripalFieldItemBase;
use Drupal\tripal\TripalStorage\BoolStoragePropertyType;
use Drupal\tripal\TripalStorage\StoragePropertyValue;
use Drupal\core\Form\FormStateInterface;
use Drupal\core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'boolean' field type.
 *
 * @FieldType(
 *   id = "tripal_boolean_type",
 *   label = @Translation("Tripal Boolean Field Type"),
 *   description = @Translation("A boolean field."),
 *   default_widget = "default_tripal_boolean_type_widget",
 *   default_formatter = "default_tripal_boolean_type_formatter"
 * )
 */
class TripalBooleanTypeItem extends TripalFieldItemBase {

  public static $id = "tripal_boolean_type";

  /**
   * {@inheritdoc}
   */
  public static function tripalTypes($field_definition) {
    $entity_type_id = $field_definition->getTargetEntityTypeId();
    $storage_settings = $field_definition->getSettings();
    $termIdSpace = $storage_settings['termIdSpace'];
    $termAccession = $storage_settings['termAccession'];

    return [
      new BoolStoragePropertyType($entity_type_id, self::$id, "value", $termIdSpace . ':' . $termAccession),
    ];
  }
}

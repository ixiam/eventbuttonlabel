<?php

require_once 'eventbuttonlabel.civix.php';
// phpcs:disable
use CRM_Eventbuttonlabel_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function eventbuttonlabel_civicrm_config(&$config) {
  _eventbuttonlabel_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function eventbuttonlabel_civicrm_install() {
  _eventbuttonlabel_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function eventbuttonlabel_civicrm_enable() {
  _eventbuttonlabel_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 */
function eventbuttonlabel_civicrm_buildForm($formName, &$form) {

  if (in_array($formName, ['CRM_Event_Form_Registration_AdditionalParticipant', 'CRM_Event_Form_Registration_Register', 'CRM_Event_Form_Registration_Confirm'])) {
    $hideMessages = _event_eventbuttonlabel_getButtonLabel($form->getVar('_eventId'), 'remove_status_msg');
    if (is_bool($hideMessages) && !empty($hideMessages)) {
      CRM_Core_Session::singleton()->getStatus(TRUE);
    }
    $buttons = &$form->getElement('buttons');
    $isConfirmEnabled = TRUE;
    $_name = $form->getVar('_name');
    $participantNo = substr($_name, 12);
    $values = [
      'CRM_Event_Form_Registration_Confirm' => ['_qf_Confirm_next', 'confirm', $isConfirmEnabled],
      'CRM_Event_Form_Registration_AdditionalParticipant' => ['_qf_Participant_' . $participantNo . '_next_skip', 'additional_skip_button', !$isConfirmEnabled],
      'CRM_Event_Form_Registration_Register' => ['_qf_Register_upload', 'register', !$isConfirmEnabled],
    ];

    foreach ($buttons->_elements as $index => &$elements) {
      if ($elements->_attributes['name'] == $values[$formName][0]) {
        $newButtonLabel = _event_eventbuttonlabel_getButtonLabel($form->getVar('_eventId'), $values[$formName][1]);
        if (!is_bool($newButtonLabel) && !empty($newButtonLabel)) {
          _event_eventbuttonlabel_updateButtonLabel($elements, $newButtonLabel, $values[$formName][2]);
        }
        if (is_bool($newButtonLabel) && !empty($newButtonLabel) && $values[$formName][1] == 'additional_skip_button') {
          unset($buttons->_elements[$index]);
        }
        if ($values[$formName][2]) {
          $form->assign('confirmButtonName', $newButtonLabel);
        }
        break;
      }
    }
    if ($formName == 'CRM_Event_Form_Registration_Register' && !empty($form->_values['event']['is_multiple_registrations'])) {
      $buttonLabel = _event_eventbuttonlabel_getButtonLabel($form->getVar('_eventId'), 'additional');
      if (!empty($buttonLabel)) {
        $form->assign('additional_buttonLabel', $buttonLabel);
        $regularButtonLabel = _event_eventbuttonlabel_getButtonLabel($form->getVar('_eventId'), 'register');
        $form->assign('regular_buttonLabel', $regularButtonLabel);
        CRM_Core_Region::instance('page-body')->add([
          'template' => 'CRM/Eventbuttonlabel/Additional.tpl',
        ]);
      }
    }
    elseif ($formName == 'CRM_Event_Form_Registration_Confirm') {
      CRM_Core_Region::instance('page-body')->add([
        'template' => 'CRM/Eventbuttonlabel/ButtonFile.tpl',
      ]);
    }
  }
}

/**
 * Function to get button label.
 *
 * @param $eventID
 * @param $pageName
 * @return bool|mixed|string|void
 */
function _event_eventbuttonlabel_getButtonLabel($eventID, $pageName) {
  try {
    $labelName = civicrm_api4('Event', 'get', [
        'select' => [
          "eventbuttonlabel_cg_buttonlabel.eventbuttonlabel_cf_{$pageName}",
        ],
        'where' => [
          ['id', '=', $eventID],
        ],
        'checkPermissions' => FALSE,
      ], 0)["eventbuttonlabel_cg_buttonlabel.eventbuttonlabel_cf_{$pageName}"] ?? '';

    if (is_bool($labelName) && !empty($labelName)) {
      return TRUE;
    }

    return $labelName;
  }
  catch (Exception $e) {
  }
}

/**
 * Function to update button label.
 *
 * @param $element
 * @param $labelName
 * @param false $isConfirmEnabled
 */
function _event_eventbuttonlabel_updateButtonLabel(&$element, $labelName,
                                                   $isConfirmEnabled = FALSE) {
  if (!property_exists($element, '_content')) {
    $element->_attributes['value'] = $labelName;
  }
  else {
    $element->_content = substr_replace($element->_content, $labelName, (strpos($element->_content, '/i> ') + 4));
  }
  if ($isConfirmEnabled) {
    $element->_attributes['onclick'] = "return submitOnceLabel(this,'" . $pageName . "','" . ts('Processing') . "');";
  }
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function eventbuttonlabel_civicrm_managed(&$entities) {
  $entities[] = [
    'module' => 'eventbuttonlabel',
    'name' => 'eventbuttonlabel_cg_buttonlabel',
    'entity' => 'CustomGroup',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'eventbuttonlabel_cg_buttonlabel',
      'title' => ts('Event Button Labels'),
      'extends' => 'Event',
      'style' => 'Inline',
      'is_active' => TRUE,
      'is_public' => FALSE,
      'is_reserved' => 1,
      'options' => ['match' => ['name']],
    ],
  ];

  $entities[] = [
    'module' => 'eventbuttonlabel',
    'name' => 'eventbuttonlabel_cf_register',
    'entity' => 'CustomField',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'eventbuttonlabel_cf_register',
      'label' => ts('Register Page button Label'),
      'help_post' => ts('Used when no additonal participant configured.'),
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_active' => TRUE,
      'text_length' => 255,
      'weight' => 1,
      'option_type' => 0,
      'custom_group_id' => 'eventbuttonlabel_cg_buttonlabel',
      'options' => ['match' => ['name', 'custom_group_id']],
    ],
  ];

  $entities[] = [
    'module' => 'eventbuttonlabel',
    'name' => 'eventbuttonlabel_cf_additional',
    'entity' => 'CustomField',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'eventbuttonlabel_cf_additional',
      'label' => ts('Register Page button Label when additional participant is selected'),
      'help_post' => ts('When a user selects additional participants from the drop down then this label gets used.'),
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_active' => TRUE,
      'text_length' => 255,
      'weight' => 2,
      'option_type' => 0,
      'custom_group_id' => 'eventbuttonlabel_cg_buttonlabel',
      'options' => ['match' => ['name', 'custom_group_id']],
    ],
  ];

  $entities[] = [
    'module' => 'eventbuttonlabel',
    'name' => 'eventbuttonlabel_cf_confirm',
    'entity' => 'CustomField',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'eventbuttonlabel_cf_confirm',
      'label' => ts('Confirm Page Button Label'),
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_active' => TRUE,
      'text_length' => 255,
      'weight' => 3,
      'option_type' => 0,
      'custom_group_id' => 'eventbuttonlabel_cg_buttonlabel',
      'options' => ['match' => ['name', 'custom_group_id']],
    ],
  ];

  $entities[] = [
    'module' => 'eventbuttonlabel',
    'name' => 'eventbuttonlabel_cf_additional_skip_button',
    'entity' => 'CustomField',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'eventbuttonlabel_cf_additional_skip_button',
      'label' => ts('Additional Participant Remove Skip Participant button'),
      'data_type' => 'Boolean',
      'html_type' => 'Radio',
      'is_active' => TRUE,
      'weight' => 4,
      'custom_group_id' => 'eventbuttonlabel_cg_buttonlabel',
      'options' => ['match' => ['name', 'custom_group_id']],
    ],
  ];

  $entities[] = [
    'module' => 'eventbuttonlabel',
    'name' => 'eventbuttonlabel_cf_remove_status_msg',
    'entity' => 'CustomField',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'eventbuttonlabel_cf_remove_status_msg',
      'label' => ts('Remove Status Messages appear during the registration process.'),
      'data_type' => 'Boolean',
      'html_type' => 'Radio',
      'is_active' => TRUE,
      'weight' => 5,
      'custom_group_id' => 'eventbuttonlabel_cg_buttonlabel',
      'options' => ['match' => ['name', 'custom_group_id']],
    ],
  ];
}

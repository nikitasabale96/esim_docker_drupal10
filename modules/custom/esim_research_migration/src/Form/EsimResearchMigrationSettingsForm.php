<?php

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class EsimResearchMigrationSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esim_research_migration_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('esim_research_migration.settings');

    $form['emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('(Bcc) Notification emails'),
      '#description' => $this->t('Specify emails id for Bcc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('research_migration_emails'),
    ];

    $form['cc_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('(Cc) Notification emails'),
      '#description' => $this->t('Specify emails id for Cc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('research_migration_cc_emails'),
    ];

    $form['from_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Outgoing from email address'),
      '#description' => $this->t('Email address to be display in the from field of all outgoing messages'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('research_migration_from_email'),
    ];

    $form['resource_upload'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions for uploading synopsis in the proposal form'),
      '#description' => $this->t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('resource_upload_extensions'),
    ];

    $form['extensions']['abstract_upload'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions for abstract'),
      '#description' => $this->t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('research_migration_abstract_upload_extensions'),
    ];

    $form['extensions']['research_migration_upload'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed extensions for project files'),
      '#description' => $this->t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('research_migration_project_files_extensions'),
    ];

    $form['extensions']['list_of_available_projects_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions for file uploaded for available projects list'),
      '#description' => $this->t('A comma separated list WITHOUT SPACE of file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('list_of_available_projects_file'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Optional: add validation here if needed
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('esim_research_migration.settings');

    $config->set('research_migration_emails', $form_state->getValue('emails'));
    $config->set('research_migration_cc_emails', $form_state->getValue('cc_emails'));
    $config->set('research_migration_from_email', $form_state->getValue('from_email'));
    $config->set('resource_upload_extensions', $form_state->getValue(['resource_upload']));
    $config->set('research_migration_abstract_upload_extensions', $form_state->getValue(['abstract_upload']));
    $config->set('research_migration_project_files_extensions', $form_state->getValue(['research_migration_upload']));
    $config->set('list_of_available_projects_file', $form_state->getValue(['list_of_available_projects_file']));

    $config->save();

    $this->messenger()->addStatus($this->t('Settings updated.'));
  }

}

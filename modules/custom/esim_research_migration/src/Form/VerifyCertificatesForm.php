<?php

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

final class VerifyCertificatesForm extends FormBase {

  public function getFormId(): string {
    return 'esim_research_migration_verify_certificates_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['qr_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter QR Code'),
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $qr_code = trim((string) $form_state->getValue('qr_code'));
    $form_state->setRedirect('esim_research_migration.verify_certificates_verify_certificates', ['qr_code' => $qr_code]);
  }

}


<?php

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

final class EsimResearchMigrationDeleteSolutionForm extends FormBase {

  public function getFormId(): string {
    return 'esim_research_migration_delete_solution_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $account = $this->currentUser();
    if (!$account->isAuthenticated()) {
      $this->messenger()->addError($this->t('Please log in to continue.'));
      $form_state->setRedirect('user.login');
      return [];
    }

    $options = ['' => $this->t('- Select -')];
    $result = \Drupal::database()
      ->select('research_migration_proposal', 'p')
      ->fields('p', ['id', 'project_title'])
      ->condition('p.uid', $account->id())
      ->condition('p.approval_status', 3, '<>')
      ->orderBy('p.id', 'DESC')
      ->execute();

    foreach ($result as $row) {
      $options[(string) $row->id] = $row->project_title . ' (ID: ' . $row->id . ')';
    }

    $form['proposal_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Proposal'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand this will permanently delete the selected submission and its files.'),
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#button_type' => 'danger',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $proposal_id = (int) $form_state->getValue('proposal_id');
    if ($proposal_id <= 0) {
      $this->messenger()->addError($this->t('Invalid proposal selected.'));
      return;
    }

    $proposal = \Drupal::database()->select('research_migration_proposal', 'p')
      ->fields('p', ['id', 'uid'])
      ->condition('p.id', $proposal_id)
      ->execute()
      ->fetchObject();

    if (!$proposal || (int) $proposal->uid !== (int) $this->currentUser()->id()) {
      $this->messenger()->addError($this->t('You do not have access to delete this proposal.'));
      return;
    }

    if (research_migration_abstract_delete_project($proposal_id)) {
      $this->messenger()->addStatus($this->t('The submission has been deleted.'));
    }
    else {
      $this->messenger()->addError($this->t('Unable to delete the submission.'));
    }

    $form_state->setRedirect('esim_research_migration.abstract');
  }

}


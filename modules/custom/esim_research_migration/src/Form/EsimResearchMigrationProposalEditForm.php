<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Form\EsimResearchMigrationProposalEditForm.
 */

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;

class EsimResearchMigrationProposalEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esim_research_migration_proposal_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $proposal_id = (int) \Drupal::routeMatch()->getParameter('proposal_id');
    if ($proposal_id <= 0) {
      $this->messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      return [];
    }

    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_data = $query->execute()->fetchObject();
    if (!$proposal_data) {
      $this->messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      return [];
    }
    /*if ($proposal_q) {
        if ($proposal_data = $proposal_q->fetchObject()) {
            /* everything ok 
        } //$proposal_data = $proposal_q->fetchObject()
        else {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('research-migration-project/manage-proposal');
            return;
        }
    } //$proposal_q
    else {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('research-migration-project/manage-proposal');
        return;
    }*/
    $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
    $form['name_title'] = [
      '#type' => 'select',
      '#title' => t('Title'),
      '#options' => [
        'Dr' => 'Dr',
        'Prof' => 'Prof',
        'Mr' => 'Mr',
        'Ms' => 'Ms',
      ],
      '#required' => TRUE,
      '#default_value' => $proposal_data->name_title,
    ];
    $form['contributor_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Proposer'),
      '#size' => 30,
      '#maxlength' => 50,
      '#required' => TRUE,
      '#default_value' => $proposal_data->contributor_name,
    ];
    $form['student_email_id'] = [
      '#type' => 'item',
      '#title' => t('Email'),
    '#markup' => $user_data ? $user_data->getEmail() : '',
    ];
    $form['university'] = [
      '#type' => 'textfield',
      '#title' => t('University'),
      '#size' => 200,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#default_value' => $proposal_data->university,
    ];
    $form['institute'] = [
      '#type' => 'textfield',
      '#title' => t('Institute'),
      '#size' => 80,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#default_value' => $proposal_data->institute,
    ];
    $form['how_did_you_know_about_project'] = [
      '#type' => 'textfield',
      '#title' => t('How did you come to know about the Research Migration Project?'),
      '#default_value' => $proposal_data->how_did_you_know_about_project,
      '#required' => TRUE,
    ];
    $form['faculty_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Faculty'),
      '#size' => 50,
      '#maxlength' => 50,
      '#validated' => TRUE,
      '#default_value' => $proposal_data->faculty_name,
    ];
    $form['faculty_department'] = [
      '#type' => 'textfield',
      '#title' => t('Department of the Faculty'),
      '#size' => 50,
      '#maxlength' => 50,
      '#validated' => TRUE,
      '#default_value' => $proposal_data->faculty_department,
    ];
    $form['faculty_email'] = [
      '#type' => 'textfield',
      '#title' => t('Email id of the Faculty'),
      '#size' => 255,
      '#maxlength' => 255,
      '#validated' => TRUE,
      '#default_value' => $proposal_data->faculty_email,
    ];
    $form['project_title'] = [
      '#type' => 'textfield',
      '#title' => t('Title of the Research Migration Project'),
      '#size' => 300,
      '#maxlength' => 350,
      '#required' => TRUE,
      '#default_value' => $proposal_data->project_title,
    ];
    $form['source_of_the_project'] = [
      '#type' => 'textarea',
      '#title' => t('Source of the Project'),
      '#default_value' => $proposal_data->source_of_the_project,
      // '#disabled' => TRUE,

    ];
    /* $form['solver_used'] = array(
        '#type' => 'textfield',
        '#title' => t('Solver to be used'),
        '#size' => 50,
        '#maxlength' => 50,
        '#required' => true,
        '#default_value' => $proposal_data->solver_used,
    );*/
    $form['date_of_proposal'] = [
      '#type' => 'textfield',
      '#title' => t('Date of Proposal'),
      '#default_value' => date('d/m/Y', $proposal_data->creation_date),
      '#disabled' => TRUE,
    ];
    $form['delete_proposal'] = [
      '#type' => 'checkbox',
      '#title' => t('Delete Proposal'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl($this->t('Cancel'), Url::fromRoute('esim_research_migration.proposal_pending'))->toString(),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = $this->currentUser();
    $proposal_id = (int) \Drupal::routeMatch()->getParameter('proposal_id');
    if (!$proposal_id) {
      $this->messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      return;
    }

    $connection = \Drupal::database();
    $query = $connection->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_data = $query->execute()->fetchObject();

    if (!$proposal_data) {
      $this->messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      return;
    }

    if ((int) $form_state->getValue('delete_proposal') === 1) {
      $user_storage = \Drupal::entityTypeManager()->getStorage('user');
      /** @var \Drupal\user\UserInterface|null $user_data */
      $user_data = $user_storage->load($proposal_data->uid);
      $email_to = $user_data ? (string) $user_data->getEmail() : '';

      $config = \Drupal::config('esim_research_migration.settings');
      $from = (string) $config->get('research_migration_from_email');
      // ?: \Drupal::config('system.site')->get('mail'))
      $bcc = (string) $config->get('research_migration_emails');
      $cc = (string) $config->get('research_migration_cc_emails');

      $params['research_migration_proposal_deleted']['proposal_id'] = $proposal_id;
      $params['research_migration_proposal_deleted']['user_id'] = $proposal_data->uid;
      $params['research_migration_proposal_deleted']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];

      $mail_manager = \Drupal::service('plugin.manager.mail');
      $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
      if ($email_to !== '') {
        $mail_result = $mail_manager->mail('esim_research_migration', 'research_migration_proposal_deleted', $email_to, $langcode, $params, $from, TRUE);
        if (empty($mail_result['result'])) {
          $this->messenger()->addError($this->t('Error sending email message.'));
        }
      }

      if (_rm_rrmdir_project($proposal_id) !== TRUE) {
        $this->messenger()->addWarning($this->t('Project directory could not be deleted from disk. Continuing with proposal record deletion.'));
      }

      $delete_query = $connection->delete('research_migration_proposal');
      $delete_query->condition('id', $proposal_id);
      $num_deleted = (int) $delete_query->execute();

      if ($num_deleted > 0) {
        $this->messenger()->addStatus($this->t('The Research Migration proposal has been deleted.'));
      }
      else {
        $this->messenger()->addError($this->t('Unable to delete the proposal record.'));
      }

      $form_state->setRedirect('esim_research_migration.proposal_pending');
      Cache::invalidateTags(['research_migration_proposal_list', 'research_migration_proposal:' . $proposal_id]);
      return;
    }

    $values = $form_state->getValues();
    $project_title = $values['project_title'];
    $proposar_name = $values['name_title'] . ' ' . $values['contributor_name'];
    $directory_names = _rm_dir_name($project_title, $proposar_name);
    if (_rm_RenameDir($proposal_id, $directory_names)) {
      $directory_name = $directory_names;
    }
    else {
      return;
    }
    $str = substr($proposal_data->samplefilepath, strrpos($proposal_data->samplefilepath, '/'));
    $resource_file = ltrim($str, '/');
    $samplefilepath = $directory_name . '/' . $resource_file;

    $query = "UPDATE research_migration_proposal SET
				name_title=:name_title,
				contributor_name=:contributor_name,
				university=:university,
				institute=:institute,
				how_did_you_know_about_project = :how_did_you_know_about_project,
				faculty_name = :faculty_name,
				faculty_department = :faculty_department,
				faculty_email = :faculty_email,
				project_title=:project_title,
                source_of_the_project =:source_of_the_project,
                directory_name=:directory_name,
                samplefilepath=:samplefilepath
				WHERE id=:proposal_id";
    $args = [
      ':name_title' => $values['name_title'],
      ':contributor_name' => $values['contributor_name'],
      ':university' => $values['university'],
      ":institute" => $values['institute'],
      ":how_did_you_know_about_project" => $values['how_did_you_know_about_project'],
      ":faculty_name" => $values['faculty_name'],
      ":faculty_department" => $values['faculty_department'],
      ":faculty_email" => $values['faculty_email'],
      ':project_title' => $project_title,
      ':source_of_the_project' => $values['source_of_the_project'],
      ':directory_name' => $directory_name,
      ':samplefilepath' => $samplefilepath,
      ':proposal_id' => $proposal_id,
    ];
    $connection->query($query, $args);
    $this->messenger()->addStatus($this->t('Proposal Updated'));
    Cache::invalidateTags(['research_migration_proposal_list', 'research_migration_proposal:' . $proposal_id]);
  }

}

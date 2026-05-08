<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Form\EsimResearchMigrationProposalStatusForm.
 */

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Cache\Cache;

class EsimResearchMigrationProposalStatusForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esim_research_migration_proposal_status_form';
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
    // $query_abstract = db_select('research_migration_submitted_abstracts_file');
    // $query_abstract->fields('research_migration_submitted_abstracts_file');
    // $query_abstract->condition('proposal_id', $proposal_id);
    // $query_abstract->condition('filetype', 'A');
    // $query_abstract_pdf = $query_abstract->execute()->fetchObject();
    if (!$proposal_data) {
      $this->messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      return [];
    }
    if ($proposal_data->faculty_name == '') {
      $faculty_name = 'NA';
    }
    else {
      $faculty_name = $proposal_data->faculty_name;
    }
    if ($proposal_data->faculty_department == '') {
      $faculty_department = 'NA';
    }
    else {
      $faculty_department = $proposal_data->faculty_department;
    }
    if ($proposal_data->faculty_email == '') {
      $faculty_email = 'NA';
    }
    else {
      $faculty_email = $proposal_data->faculty_email;
    }
    // $query = db_select('research_migration_software_version');
    // $query->fields('research_migration_software_version');
    // $query->condition('id', $proposal_data->version_id);
    // $version_data = $query->execute()->fetchObject();
    // if(!$version_data){
    //     $version = 'NA';
    // }
    // else{
    // $version = $version_data->research_migration_version;
    // }
    // $query = db_select('research_migration_simulation_type');
    // $query->fields('research_migration_simulation_type');
    // $query->condition('id', $proposal_data->simulation_type_id);
    // $simulation_type_data = $query->execute()->fetchObject();
    // if(!$simulation_type_data){
    //     $simulation_type = 'NA';
    // }
    // else{
    // $simulation_type = $simulation_type_data->simulation_type;
    // }
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $form['contributor_name'] = array(
    //         '#type' => 'item',
    //         '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->contributor_name, 'user/' . $proposal_data->uid),
    //         '#title' => t('Student name'),
    //     );

    $student_user = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
    $student_email = $student_user ? (string) $student_user->getEmail() : '';
    $form['student_email_id'] = [
      '#type' => 'item',
      '#markup' => $student_email !== '' ? $student_email : $this->t('Not available'),
      '#title' => $this->t('Email'),
    ];
    $form['university'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->university,
      '#title' => t('University/Institute'),
    ];
    $form['how_did_you_know_about_project'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->how_did_you_know_about_project,
      '#title' => t('How did you know about the project'),
    ];
    $form['faculty_name'] = [
      '#type' => 'item',
      '#markup' => $faculty_name,
      '#title' => t('Name of the faculty'),
    ];
    $form['faculty_department'] = [
      '#type' => 'item',
      '#markup' => $faculty_department,
      '#title' => t('Department of the faculty'),
    ];
    $form['faculty_email'] = [
      '#type' => 'item',
      '#markup' => $faculty_email,
      '#title' => t('Email of the faculty'),
    ];
    $form['country'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->country,
      '#title' => t('Country'),
    ];
    $form['all_state'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->state,
      '#title' => t('State'),
    ];
    $form['city'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->city,
      '#title' => t('City'),
    ];
    $form['pincode'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->pincode,
      '#title' => t('Pincode/Postal code'),
    ];
    $form['project_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->project_title,
      '#title' => t('Title of the Research Migration Project'),
    ];
    $form['source_of_the_project'] = [
      '#type' => 'item',
      '#title' => t('Source of the Project'),
      '#markup' => $proposal_data->source_of_the_project,
    ];
    /************************** reference link filter *******************/
    $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
    $reference = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $proposal_data->reference);
    /******************************/
    /*$form['reference'] = array(
    '#type' => 'item',
    '#markup' => $reference,
    '#title' => t('References')
    );*/
    // if (($query_abstract_pdf->filename != "") && ($query_abstract_pdf->filename != 'NULL')) {
    //     $str = substr($query_abstract_pdf->filename, strrpos($query_abstract_pdf->filename, '/'));
    //     $resource_file = ltrim($str, '/');

    //     $form['samplefilepath'] = array(
    //         '#type' => 'item',
    //         '#title' => t('Synopsis file '),
    //         '#markup' => l($resource_file, 'research-migration-project/download/project-file/' . $proposal_id) . "",
    //     );
    // } //$proposal_data->user_defined_compound_filepath != ""
    // else {
    //     $form['samplefilepath'] = array(
    //         '#type' => 'item',
    //         '#title' => t('Synopsis file '),
    //         '#markup' => "Not uploaded<br><br>",
    //     );
    // }
    $proposal_status = '';
    switch ($proposal_data->approval_status) {
      case 0:
        $proposal_status = t('Pending');
        break;
      case 1:
        $proposal_status = t('Approved');
        break;
      case 2:
        $proposal_status = t('Dis-approved');
        break;
      case 3:
        $proposal_status = t('Completed');
        break;
      case 5:
        $proposal_status = t('On Hold');
        break;
      default:
        $proposal_status = t('Unkown');
        break;
    }
    $form['proposal_status'] = [
      '#type' => 'item',
      '#markup' => $proposal_status,
      '#title' => t('Proposal Status'),
    ];
    if ($proposal_data->approval_status == 0) {
      // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $form['approve'] = array(
//             '#type' => 'item',
//             '#markup' => l('Click here', 'research-migration-project/manage-proposal/approve/' . $proposal_id),
//             '#title' => t('Approve'),
//         );

    } //$proposal_data->approval_status == 0
    if ($proposal_data->approval_status == 1) {
      $form['completed'] = [
        '#type' => 'checkbox',
        '#title' => t('Completed'),
        '#description' => t('Check if user has provided all the required files and pdfs.'),
      ];
    } //$proposal_data->approval_status == 1
    if ($proposal_data->approval_status == 2) {
      $form['message'] = [
        '#type' => 'item',
        '#markup' => $proposal_data->message,
        '#title' => t('Reason for disapproval'),
      ];
    } //$proposal_data->approval_status == 2
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $form['cancel'] = array(
    //         '#type' => 'markup',
    //         '#markup' => l(t('Cancel'), 'research-migration-project/manage-proposal/all'),
    //     );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $account = $this->currentUser();
    $proposal_id = (int) \Drupal::routeMatch()->getParameter('proposal_id');
    if ($proposal_id <= 0) {
      $this->messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      return;
    }
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_data = $query->execute()->fetchObject();
    if (!$proposal_data) {
      $this->messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      return;
    }
    /* set the book status to completed */
    if ($form_state->getValue(['completed']) == 1) {
      $up_query = "UPDATE research_migration_proposal SET approval_status = :approval_status , actual_completion_date = :expected_completion_date WHERE id = :proposal_id";
      $args = [
        ":approval_status" => '3',
        ":proposal_id" => $proposal_id,
        ":expected_completion_date" => time(),
      ];
      $result = \Drupal::database()->query($up_query, $args);
      CreateReadmeFileResearchMigrationProject($proposal_id);
      if (!$result) {
        \Drupal::messenger()->addError('Error in update status');
        return;
      } //!$result
      $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
      $email_to = $user_data ? $user_data->getEmail() : '';

      $config = \Drupal::config('esim_research_migration.settings');
      $from = (string) ($config->get('research_migration_from_email') ?: \Drupal::config('system.site')->get('mail'));
      $bcc = (string) $config->get('research_migration_emails');
      $cc = (string) $config->get('research_migration_cc_emails');

      $params['research_migration_proposal_completed']['proposal_id'] = $proposal_id;
      $params['research_migration_proposal_completed']['user_id'] = $proposal_data->uid;
      $params['research_migration_proposal_completed']['headers'] = [
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
      $mail_result = $mail_manager->mail('esim_research_migration', 'research_migration_proposal_completed', $email_to, $langcode, $params, $from, TRUE);
      if (empty($mail_result['result'])) {
        $this->messenger()->addError($this->t('Error sending email message.'));
      }

      \Drupal::messenger()->addStatus('eSim Research Migration proposal has been marked completed. The contributor is notified of the Completion.');
    }
    $form_state->setRedirect('esim_research_migration.proposal_pending');
    Cache::invalidateTags(['research_migration_proposal_list', 'research_migration_proposal:' . $proposal_id]);
  }
}

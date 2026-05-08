<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Form\EsimResearchMigrationProposalApprovalForm.
 */

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;


class EsimResearchMigrationProposalApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esim_research_migration_proposal_approval_form';
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
    // $version = $version_data->research_migration_version;
    // $query = db_select('research_migration_simulation_type');
    // $query->fields('research_migration_simulation_type');
    // $query->condition('id', $proposal_data->simulation_type_id);
    // $simulation_type_data = $query->execute()->fetchObject();
    // $simulation_type = $simulation_type_data->simulation_type;
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $form['contributor_name'] = array(
    //         '#type' => 'item',
    //         '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->contributor_name, 'user/' . $proposal_data->uid),
    //         '#title' => t('Student name'),
    //     );

$user_uid = Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid]);
$contributor_link = Link::fromTextAndUrl($proposal_data->name_title . ' ' . $proposal_data->contributor_name, $user_uid)->toString();

$form['contributor_name'] = [
  '#type' => 'item',
  '#title' => t('Student name'),
  '#markup' => $contributor_link,
];

   $student_account = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
$email = $student_account ? $student_account->getEmail() : '';

$form['student_email_id'] = [
  '#type'    => 'item',
  '#title'   => t('Student Email'),
  '#plain_text' => $email !== '' ? $email : (string) t('Not available'),
];
    $form['contributor_contact_no'] = [
      '#title' => t('Contact No.'),
      '#type' => 'item',
      '#markup' => $proposal_data->contact_no,
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
      '#default_value' => $proposal_data->source_of_the_project,
      '#disabled' => TRUE,
    ];
    $form['date_of_proposal'] = [
      '#type' => 'textfield',
      '#title' => t('Date of Proposal'),
      '#default_value' => date('d/m/Y', $proposal_data->creation_date),
      '#disabled' => TRUE,
    ];
    $form['expected_completion_date'] = [
      '#type' => 'textfield',
      '#title' => t('Expected Date of Completion'),
      '#default_value' => date('d/m/Y', $proposal_data->expected_date_of_completion),
      '#disabled' => TRUE,
    ];
    if (($proposal_data->samplefilepath != "") && ($proposal_data->samplefilepath != 'NULL')) {
      $str = substr($proposal_data->samplefilepath, strrpos($proposal_data->samplefilepath, '/'));
      $resource_file = ltrim($str, '/');
      // @FIXME
      // l() expects a Url object, created from a route name or external URI.
      // $form['samplefilepath'] = array(
      //             '#type' => 'item',
      //             '#title' => t('Synopsis file '),
      //             '#markup' => l($resource_file, 'research-migration-project/download/project-file/' . $proposal_id) . "",
      //         );
$url = Url::fromRoute('esim_research_migration.project_files', ['proposal_id' => $proposal_id]);
    $link = Link::fromTextAndUrl($resource_file, $url)->toString();
    $form['abstractfilepath'] = [
      '#type' => 'item',
      '#title' => t('Abstract file '),
      '#markup' => $link,
    ];
  
    } //$proposal_data->user_defined_compound_filepath != ""
    else {
      $form['samplefilepath'] = [
        '#type' => 'item',
        '#title' => t('Synopsis file '),
        '#markup' => "Not uploaded<br><br>",
      ];
    }
    $form['approval'] = [
      '#type' => 'radios',
      '#title' => t('esim research migration proposal'),
      '#options' => [
        '1' => 'Approve',
        '2' => 'Disapprove',
      ],
      '#required' => TRUE,
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('Reason for disapproval'),
      '#attributes' => [
        'placeholder' => t('Enter reason for disapproval in minimum 30 characters '),
        'cols' => 50,
        'rows' => 4,
      ],
      '#states' => [
        'visible' => [
          ':input[name="approval"]' => [
            'value' => '2'
            ]
          ]
        ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $form['cancel'] = array(
    //         '#type' => 'item',
    //         '#markup' => l(t('Cancel'), 'research-migration-project/manage-proposal'),
    //     );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue(['approval']) == 2) {
      if ($form_state->getValue(['message']) == '') {
        $form_state->setErrorByName('message', t('Reason for disapproval could not be empty'));
      } //$form_state['values']['message'] == ''
    } //$form_state['values']['approval'] == 2
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

    $config = \Drupal::config('esim_research_migration.settings');
    $from = (string) ($config->get('research_migration_from_email') ?: \Drupal::config('system.site')->get('mail'));
    $bcc = (string) $config->get('research_migration_emails');
    $cc = (string) $config->get('research_migration_cc_emails');
    $mail_manager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();

    if ($form_state->getValue(['approval']) == 1) {
      $query = "UPDATE {research_migration_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 1 WHERE id = :proposal_id";
      $args = [
        ":uid" => $account->id(),
        ":date" => time(),
        ":proposal_id" => $proposal_id,
      ];
      \Drupal::database()->query($query, $args);
      /* sending email */
      $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
      $email_to = $user_data ? (string) $user_data->getEmail() : '';

      $params['research_migration_proposal_approved']['proposal_id'] = $proposal_id;
      $params['research_migration_proposal_approved']['user_id'] = $proposal_data->uid;
      $params['research_migration_proposal_approved']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];
      if ($email_to !== '') {
        $mail_result = $mail_manager->mail('esim_research_migration', 'research_migration_proposal_approved', $email_to, $langcode, $params, $from, TRUE);
        if (empty($mail_result['result'])) {
          $this->messenger()->addError($this->t('Error sending email message.'));
        }
      }

      \Drupal::messenger()->addStatus('eSim Research Migration with proposal No. ' . $proposal_id . ' has been approved. The contributor has been notified of the approval');
      $form_state->setRedirect('esim_research_migration.proposal_pending');
      Cache::invalidateTags(['research_migration_proposal_list', 'research_migration_proposal:' . $proposal_id]);
      return;
    } //$form_state['values']['approval'] == 1
    else {
      if ($form_state->getValue(['approval']) == 2) {
        $query = "UPDATE {research_migration_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 2, dissapproval_reason = :dissapproval_reason WHERE id = :proposal_id";
        $args = [
          ":uid" => $account->id(),
          ":date" => time(),
          ":dissapproval_reason" => $form_state->getValue(['message']),
          ":proposal_id" => $proposal_id,
        ];
        $result = \Drupal::database()->query($query, $args);
        /* sending email */
        $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
        $email_to = $user_data ? (string) $user_data->getEmail() : '';

        $params['research_migration_proposal_disapproved']['proposal_id'] = $proposal_id;
        $params['research_migration_proposal_disapproved']['user_id'] = $proposal_data->uid;
        $params['research_migration_proposal_disapproved']['headers'] = [
          'From' => $from,
          'MIME-Version' => '1.0',
          'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
          'Content-Transfer-Encoding' => '8Bit',
          'X-Mailer' => 'Drupal',
          'Cc' => $cc,
          'Bcc' => $bcc,
        ];
        if ($email_to !== '') {
          $mail_result = $mail_manager->mail('esim_research_migration', 'research_migration_proposal_disapproved', $email_to, $langcode, $params, $from, TRUE);
          if (empty($mail_result['result'])) {
            $this->messenger()->addError($this->t('Error sending email message.'));
          }
        }

        \Drupal::messenger()->addError('eSim Research Migration with Proposal No. ' . $proposal_id . ' has been disapproved. The contributor has been notified of the disapproval.');
        $form_state->setRedirect('esim_research_migration.proposal_pending');
        Cache::invalidateTags(['research_migration_proposal_list', 'research_migration_proposal:' . $proposal_id]);
        return;
      }
    } //$form_state['values']['approval'] == 2
  }

}

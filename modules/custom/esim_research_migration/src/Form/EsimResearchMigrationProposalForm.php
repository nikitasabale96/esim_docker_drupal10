<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Form\EsimResearchMigrationProposalForm.
 */

namespace Drupal\esim_research_migration\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Database\Database;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\user\Entity\User;
use Drupal\Core\Cache\Cache;
use Drupal\Core\File\FileSystemInterface;


class EsimResearchMigrationProposalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esim_research_migration_proposal_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $no_js_use = NULL) {
    $account = $this->currentUser();
    if (!$account->isAuthenticated()) {
      $login_link = Link::fromTextAndUrl($this->t('login'), Url::fromRoute('user.login'))->toString();
      $this->messenger()->addError($this->t('It is mandatory to @login_link on this website to access the Research Migration proposal form. If you are a new user please create a new account first.', [
        '@login_link' => $login_link,
      ]));
      $form_state->setRedirect('user.login', [], ['query' => \Drupal::destination()->getAsArray()]);
      return [];
    }

    /** @var \Drupal\user\UserInterface|null $user */
    $user = User::load($account->id());

    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('uid', $account->id());
    $query->orderBy('id', 'DESC');
    $query->range(0, 1);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if ($proposal_data) {
      if ($proposal_data->approval_status == 0 || $proposal_data->approval_status == 1) {
        $this->messenger()->addStatus($this->t('We have already received your proposal.'));
        $form_state->setRedirect('<front>');
        return [];
      }
    } //$proposal_data
    // var_dump($proposal_q);die;
    $form['#attributes'] = [
      'enctype' => "multipart/form-data"
      ];

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
    ];
    $form['contributor_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the contributor'),
      // '#size' => 250,
      '#attributes' => [
        'placeholder' => t('Enter your full name.....')
        ],
      '#maxlength' => 250,
      '#required' => TRUE,
    ];
    $form['contributor_email_id'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
      // '#size' => 30,
      '#value' => $user->getEmail(),
      '#disabled' => TRUE,
    ];
    $form['contributor_contact_no'] = [
      '#type' => 'textfield',
      '#title' => t('Contact No.'),
      // '#size' => 10,
      '#attributes' => [
        'placeholder' => t('Enter your contact number')
        ],
      '#maxlength' => 250,
    ];
    $form['university'] = [
      '#type' => 'textfield',
      '#title' => t('University'),
      // '#size' => 80,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Insert full name of your university.... '
        ],
    ];
    $form['institute'] = [
      '#type' => 'textfield',
      '#title' => t('Institute'),
      // '#size' => 80,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Insert full name of your institute.... '
        ],
    ];
    $form['how_did_you_know_about_project'] = [
      '#type' => 'select',
      '#title' => t('How did you come to know about the Research Migration Project?'),
      '#options' => [
        'Poster' => 'Poster',
        'Website' => 'Website',
        'Email' => 'Email',
        'Others' => 'Others',
      ],
      '#required' => TRUE,
    ];
    $form['others_how_did_you_know_about_project'] = [
      '#type' => 'textfield',
      '#title' => t('If ‘Other’, please specify'),
      '#maxlength' => 50,
      '#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
      '#states' => [
        'visible' => [
          ':input[name="how_did_you_know_about_project"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['faculty_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Faculty Member of your Institution, if any, who helped you with this Research Migration Project'),
      // '#size' => 50,
      '#maxlength' => 50,
      '#validated' => TRUE,
      '#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
    ];
    $form['faculty_department'] = [
      '#type' => 'textfield',
      '#title' => t('Department of the Faculty Member of your Institution, if any, who helped you with this Research Migration Project'),
      // '#size' => 50,
      '#maxlength' => 50,
      '#validated' => TRUE,
      '#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
    ];
    $form['faculty_email'] = [
      '#type' => 'textfield',
      '#title' => t('Email id of the Faculty Member of your Institution, if any, who helped you with this Research Migration Project'),
      // '#size' => 255,
      '#maxlength' => 255,
      '#validated' => TRUE,
      '#description' => t('<span style="color:red">Maximum character limit is 255</span>'),
    ];
    $form['country'] = [
      '#type' => 'select',
      '#title' => t('Country'),
      '#options' => [
        'India' => 'India',
        'Others' => 'Others',
      ],
      '#required' => TRUE,
      '#tree' => TRUE,
      '#validated' => TRUE,
    ];
    $form['other_country'] = [
      '#type' => 'textfield',
      '#title' => t('Other than India'),
      // '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your country name')
        ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['other_state'] = [
      '#type' => 'textfield',
      '#title' => t('State other than India'),
      // '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your state/region name')
        ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['other_city'] = [
      '#type' => 'textfield',
      '#title' => t('City other than India'),
      // '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your city name')
        ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['all_state'] = [
      '#type' => 'select',
      '#title' => t('State'),
      '#options' => _rm_df_list_of_states(),
      '#validated' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'India'
            ]
          ]
        ],
    ];
    $form['city'] = [
      '#type' => 'select',
      '#title' => t('City'),
      '#options' => _rm_df_list_of_cities(),
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'India'
            ]
          ]
        ],
    ];
    $form['pincode'] = [
      '#type' => 'textfield',
      '#title' => t('Pincode'),
      // '#size' => 6,
    ];
    /***************************************************************************/
    $form['hr'] = [
      '#type' => 'item',
      '#markup' => '<hr>',
    ];
    $form['project_title'] = [
      '#type' => 'textfield',
      '#title' => t('Title of the Research Migration Project'),
      // '#size' => 80,
      '#maxlength' => 250,
      '#description' => t('Maximum character limit is 250'),
      '#required' => TRUE,
      '#validated' => TRUE,
    ];

    $form['source_of_the_project'] = [
      '#type' => 'textarea',
      '#title' => t('Source of the Project'),
      // '#size' => 80,
      // '#maxlength' => 200,
		'#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Insert the Journal name, title of proceedings (for conference papers) '
        ],
    ];

    $form['samplefile'] = [
      '#type' => 'fieldset',
      '#title' => t('<span style="color:black;">Synopsis Submission</span> <span style="color:#f00;">*</span>'),
      '#required' => TRUE,
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
   

    $form['samplefile']['samplefile_path'] = [
        '#type' => 'file',
        // '#size' => 48,
        '#description' => $this->t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') 
            . '<br />' . $this->t('<span style="color:red;">Allowed file extensions: ') 
            . \Drupal::config('esim_research_migration.settings')->get('resource_upload_extensions') . '</span>',
    ];
    
    $form['date_of_proposal'] = [
      '#type' => 'date',
      '#title' => t('Date of Proposal'),
      '#default_value' => date('Y-m-d'),
      '#disabled' => TRUE,
    ];
    $form['expected_date_of_completion'] = [
      '#type' => 'date',
      '#title' => t('Expected Date of Completion'),
      '#date_label_position' => '',
      '#description' => '',
      '#default_value' => '',
      '#date_format' => 'd-M-Y',
      //'#date_increment' => 0,
      //'#minDate' => '+0',
      //'#date_year_range' => '+0 : +1',
		'#required' => TRUE,
      '#datepicker_options' => [
        'maxDate' => '+45D',
        // not more than current date
           'minDate' => '+1',
        // not more than given date
      ],
    ];
    $form['term_condition'] = [
      '#type' => 'checkboxes',
      '#title' => t('Terms And Conditions'),
      '#options' => [
        'status' => t('<a href="/research-migration-project/term-and-conditions" target="_blank">I agree to the Terms and Conditions</a>')
        ],
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    //var_dump($form_state['values']['solver_used']);die;

    $terms = $form_state->getValue('term_condition');
    if (empty($terms['status'])) {
      $form_state->setErrorByName('term_condition', $this->t('Please check the terms and conditions'));
    }
    if ($form_state->getValue([
      'country'
      ]) == 'Others') {
      if ($form_state->getValue(['other_country']) == '') {
        $form_state->setErrorByName('other_country', t('Enter country name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_country'] == ''
      else {
        $form_state->setValue(['country'], $form_state->getValue([
          'other_country'
          ]));
      }
      if ($form_state->getValue(['other_state']) == '') {
        $form_state->setErrorByName('other_state', t('Enter state name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_state'] == ''
      else {
        $form_state->setValue(['all_state'], $form_state->getValue([
          'other_state'
          ]));
      }
      if ($form_state->getValue(['other_city']) == '') {
        $form_state->setErrorByName('other_city', t('Enter city name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_city'] == ''
      else {
        $form_state->setValue(['city'], $form_state->getValue(['other_city']));
      }
    } //$form_state['values']['country'] == 'Others'
    else {
      if ($form_state->getValue(['country']) == '') {
        $form_state->setErrorByName('country', t('Select country name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['country'] == ''
      if ($form_state->getValue([
        'all_state'
        ]) == '') {
        $form_state->setErrorByName('all_state', t('Select state name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['all_state'] == ''
      if ($form_state->getValue([
        'city'
        ]) == '') {
        $form_state->setErrorByName('city', t('Select city name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['city'] == ''
    }
    //Validation for project title
    $form_state->setValue(['project_title'], trim($form_state->getValue([
      'project_title'
      ])));
    if ($form_state->getValue(['project_title']) != '') {
      if (strlen($form_state->getValue(['project_title'])) > 250) {
        $form_state->setErrorByName('project_title', t('Maximum charater limit is 250 charaters only, please check the length of the project title'));
      } //strlen($form_state['values']['project_title']) > 250
      else {
        if (strlen($form_state->getValue(['project_title'])) < 10) {
          $form_state->setErrorByName('project_title', t('Minimum charater limit is 10 charaters, please check the length of the project title'));
        }
      } //strlen($form_state['values']['project_title']) < 10
    } //$form_state['values']['project_title'] != ''


    if ($form_state->getValue(['how_did_you_know_about_project']) == 'Others') {
      if ($form_state->getValue(['others_how_did_you_know_about_project']) == '') {
        $form_state->setErrorByName('others_how_did_you_know_about_project', t('Please enter how did you know about the project'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_country'] == ''
      else {
        $form_state->setValue(['how_did_you_know_about_project'], $form_state->getValue([
          'others_how_did_you_know_about_project'
          ]));
      }
    }


    if (isset($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
      /* check if atleast one source or result file is uploaded */
      if (empty($_FILES['files']['name']['samplefile_path'])) {
        $form_state->setErrorByName('samplefilepath', t('Please upload the Synopsis file'));
      }
      /* check for valid filename extensions */
      foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
        if ($file_name) {
          /* checking file type */
          // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
          $allowed_extensions_str = (string) \Drupal::config('esim_research_migration.settings')->get('resource_upload_extensions');
          $allowed_extensions = array_filter(array_map('trim', explode(',', $allowed_extensions_str)));
          $fnames = explode('.', strtolower((string) $_FILES['files']['name'][$file_form_name]));
          $temp_extension = end($fnames);
          if ($allowed_extensions && !in_array($temp_extension, $allowed_extensions, TRUE)) {
            $form_state->setErrorByName($file_form_name, t('Only file with ' . $allowed_extensions_str . ' extensions can be uploaded.'));
          }
          if (!empty($_FILES['files']['size'][$file_form_name]) && $_FILES['files']['size'][$file_form_name] <= 0) {
            $form_state->setErrorByName($file_form_name, t('File size cannot be zero.'));
          }
          /* check if valid file name */
          if (!esim_research_migration_check_valid_filename((string) $_FILES['files']['name'][$file_form_name])) {
            $form_state->setErrorByName($file_form_name, t('Invalid file name specified. Only alphabets and numbers are allowed as a valid filename.'));
          }
        } //$file_name
      } //$_FILES['files']['name'] as $file_form_name => $file_name
    }
    
    return $form_state;
  }
  function research_migration_path() {
    return $_SERVER['DOCUMENT_ROOT'] . base_path() . 'esim_uploads/research_migration_uploads/';
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
  $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $root_path = $this->research_migration_path();
    if (!$user->id()) {
      \Drupal::messenger()->addError('It is mandatory to login on this website to access the proposal form');
      return;
    }


    $project_title = $form_state->getValue(['project_title']);

    if ($form_state->getValue(['how_did_you_know_about_project']) == 'Others') {
      $how_did_you_know_about_project = $form_state->getValue(['others_how_did_you_know_about_project']);
    }
    else {
      $how_did_you_know_about_project = $form_state->getValue(['how_did_you_know_about_project']);
    }
    /* inserting the user proposal */
    $v = $form_state->getValues();
    $project_title = trim($project_title);
    $proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
    $university = $v['university'];
    $directory_name = _rm_dir_name($project_title, $proposar_name);
    $expected_date = (string) $v['expected_date_of_completion'];
    $expected_timestamp = $expected_date !== '' ? strtotime($expected_date) : 0;
    $connection = Database::getConnection();
    $result1 = $connection->insert('research_migration_proposal')->fields([
      "uid" => $user->id(),
      "approver_uid" => 0,
      "name_title" => $v['name_title'],
      "contributor_name" => $this->_df_sentence_case(trim($v['contributor_name'])),
      "contact_no" => $v['contributor_contact_no'],
      "university" => $v['university'],
      "institute" => $this->_df_sentence_case($v['institute']),
      "how_did_you_know_about_project" => trim($how_did_you_know_about_project),
      "faculty_name" => $v['faculty_name'],
      "faculty_department" => $v['faculty_department'],
      "faculty_email" => $v['faculty_email'],
      "city" => $v['city'],
      "pincode" => $v['pincode'],
      "state" => $v['all_state'],
      "country" => $v['country'],
      "project_title" => $project_title,
      "source_of_the_project" => trim($v['source_of_the_project']),
      "directory_name" => $directory_name,
      "approval_status" => 0,
      "is_completed" => 0,
      "dissapproval_reason" => NULL,
      "creation_date" => time(),
      "expected_date_of_completion" => $expected_timestamp ?: 0,
      "approval_date" => 0,
      "samplefilepath" => "",
    ])->execute();
    // $result1 = \Drupal::database()->query($result, $args)->execute();
    //var_dump($result1->id);die;
    // $query_pro = db_select('research_migration_proposal');
    // $query_pro->fields('research_migration_proposal');
    //	$query_pro->condition('id', $proposal_data->id);
    // $abstracts_pro = $query_pro->execute()->fetchObject();
    //	$proposal_id = $abstracts_pro->id;
    $dest_path = $directory_name . '/';
    $dest_path1 = $root_path . $dest_path;
    $file_system = \Drupal::service('file_system');
  $file_system->prepareDirectory(
  $dest_path1,
  FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
);
    /* uploading files */
    if (isset($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
      if ($file_name) {
        /* checking file type */
        //$file_type = 'S';
        if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
          \Drupal::messenger()->addError(t("Error uploading file. File !filename already exists.", [
            '!filename' => $_FILES['files']['name'][$file_form_name]
            ]));
          //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
        } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
			/* uploading file */
        if (is_uploaded_file($_FILES['files']['tmp_name'][$file_form_name]) && move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . 'abstract_' . $_FILES['files']['name'][$file_form_name])) {
          $query = "UPDATE {research_migration_proposal} SET samplefilepath = :samplefilepath WHERE id = :id";
          $args = [
            ":samplefilepath" => $dest_path . 'abstract_' . $_FILES['files']['name'][$file_form_name],
            ":id" => $result1,
          ];

          $updateresult = \Drupal::database()->query($query, $args);
          //var_dump($args);die;

          \Drupal::messenger()->addStatus($file_name . ' uploaded successfully.');
        } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
        else {
          \Drupal::messenger()->addError('Error uploading file : ' . $dest_path . '/' . $file_name);
        }
      } //$file_name
    } //$_FILES['files']['name'] as $file_form_name => $file_name
    }
    if (!$result1) {
      \Drupal::messenger()->addError(t('Error receiving your proposal. Please try again.'));
      return;
    } //!$proposal_id
    /* sending email */
    $email_to = $user->getEmail();
    $config = \Drupal::config('esim_research_migration.settings');
    $from = (string) ($config->get('research_migration_from_email') ?: \Drupal::config('system.site')->get('mail'));
    $bcc = (string) $config->get('research_migration_emails');
    $cc = (string) $config->get('research_migration_cc_emails');

    $params['research_migration_proposal_received']['result1'] = $result1;
    $params['research_migration_proposal_received']['user_id'] = $user->id();
    $params['research_migration_proposal_received']['headers'] = [
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
    $mail_result = $mail_manager->mail('esim_research_migration', 'research_migration_proposal_received', $email_to, $langcode, $params, $from, TRUE);
    if (empty($mail_result['result'])) {
      $this->messenger()->addError($this->t('Error sending email message.'));
    }

    $this->messenger()->addStatus($this->t('We have received your Research Migration proposal. We will get back to you soon.'));
    $form_state->setRedirect('<front>');
    Cache::invalidateTags(['research_migration_proposal_list']);
 
  }

  public function _df_sentence_case($string)
  {
    $string = ucwords(strtolower($string));
    foreach (array(
      '-',
      '\''
    ) as $delimiter)
    {
      if (strpos($string, $delimiter) !== false)
      {
        $string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
      } //strpos($string, $delimiter) !== false
    } //array( '-', '\'' ) as $delimiter
    return $string;
  }
}

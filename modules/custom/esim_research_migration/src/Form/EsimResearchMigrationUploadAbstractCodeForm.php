<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Form\EsimResearchMigrationUploadAbstractCodeForm.
 */

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

class EsimResearchMigrationUploadAbstractCodeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esim_research_migration_upload_abstract_code_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $form['#attributes'] = ['enctype' => "multipart/form-data"];
    /* get current proposal */
    //$proposal_id = (int) arg(3);
    $uid = $user->id();
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('uid', $uid);
    $query->condition('approval_status', '1');
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        $form_state->setRedirect('esim_research_migration.abstract');
        return [];
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirect('esim_research_migration.abstract');
      return [];
    }
    $query = \Drupal::database()->select('research_migration_submitted_abstracts');
    $query->fields('research_migration_submitted_abstracts');
    $query->condition('proposal_id', $proposal_data->id);
    $abstracts_q = $query->execute()->fetchObject();
    if ($abstracts_q) {
      if ($abstracts_q->is_submitted == 1) {
        \Drupal::messenger()->addError(t('You have already submited your Case Directory, hence you can not upload any more, for any query please write to us.'));
        $form_state->setRedirect('esim_research_migration.abstract');
        return [];
      } //$abstracts_q->is_submitted == 1
    } //$abstracts_q->is_submitted == 1
    $form['project_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->project_title,
      '#title' => t('Title of the Research Migration Project'),
    ];
    $form['contributor_name'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->contributor_name,
      '#title' => t('Contributor Name'),
    ];
    $existing_uploaded_A_file = $this->getUploadedFile('A', (int) $proposal_data->id);
    if (!$existing_uploaded_A_file) {
      $existing_uploaded_A_file = new \stdClass();
      $existing_uploaded_A_file->filename = 'No file uploaded';
    } //!$existing_uploaded_S_file
    $config = \Drupal::config('esim_research_migration.settings');
    $abstract_extensions = (string) $config->get('research_migration_abstract_upload_extensions');
    $project_extensions = (string) $config->get('research_migration_project_files_extensions');

    $form['upload_an_abstract'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload an abstract of the project.'),
      '#description' => $this->t('Current File: @file', ['@file' => $existing_uploaded_A_file->filename]) . '<br />' . $this->t('Allowed file extensions: @ext', ['@ext' => $abstract_extensions]),
    ];

    $existing_uploaded_S_file = $this->getUploadedFile('S', (int) $proposal_data->id);
    if (!$existing_uploaded_S_file) {
      $existing_uploaded_S_file = new \stdClass();
      $existing_uploaded_S_file->filename = 'No file uploaded';
    }
    $form['upload_research_migration_developed_process'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload the Case Directory'),
      '#description' => $this->t('Current File: @file', ['@file' => $existing_uploaded_S_file->filename]) . '<br />' . $this->t('Allowed file extensions: @ext', ['@ext' => $project_extensions]),
    ];

    $form['prop_id'] = [
      '#type' => 'hidden',
      '#value' => $proposal_data->id,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];

    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $form['cancel'] = array(
    //         '#type' => 'item',
    //         '#markup' => l(t('Cancel'), 'research-migration-project/abstract-code'),
    //     );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (isset($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
      /* check if file is uploaded */
      $proposal_id = (int) $form_state->getValue('prop_id');
      $existing_uploaded_A_file = $this->getUploadedFile('A', $proposal_id);
      $existing_uploaded_S_file = $this->getUploadedFile('S', $proposal_id);
      if (!$existing_uploaded_S_file) {
        if (empty($_FILES['files']['name']['upload_research_migration_developed_process'])) {
          $form_state->setErrorByName('upload_research_migration_developed_process', t('Please upload the file.'));
        }
      } //!$existing_uploaded_S_file
      if (!$existing_uploaded_A_file) {
        if (empty($_FILES['files']['name']['upload_an_abstract'])) {
          $form_state->setErrorByName('upload_an_abstract', t('Please upload the file.'));
        }
      } //!$existing_uploaded_A_file
		/* check for valid filename extensions */
      if (!empty($_FILES['files']['name']['upload_an_abstract']) || !empty($_FILES['files']['name']['upload_research_migration_developed_process'])) {
        foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
          if ($file_name) {
            /* checking file type */
            if (strstr($file_form_name, 'upload_research_migration_developed_process')) {
              $file_type = 'S';
            }
            else {
              if (strstr($file_form_name, 'upload_an_abstract')) {
                $file_type = 'A';
              }
              else {
                $file_type = 'U';
              }
            }
            $allowed_extensions_str = '';
            switch ($file_type) {
              case 'S':
                $allowed_extensions_str = (string) \Drupal::config('esim_research_migration.settings')->get('research_migration_project_files_extensions');

                break;
              case 'A':
                $allowed_extensions_str = (string) \Drupal::config('esim_research_migration.settings')->get('research_migration_abstract_upload_extensions');

                break;
            } //$file_type
            $allowed_extensions = array_filter(array_map('trim', explode(',', $allowed_extensions_str)));
            $tmp_ext = explode('.', strtolower((string) $_FILES['files']['name'][$file_form_name]));
            $temp_extension = end($tmp_ext);
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
      } //$_FILES['files']['name'] as $file_form_name => $file_name
    } //isset($_FILES['files'])
    // drupal_add_js('jQuery(document).ready(function () { alert("Hello!"); });', 'inline');
    // drupal_static_reset('drupal_add_js') ;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $account = $this->currentUser();
    /** @var \Drupal\user\UserInterface|null $user */
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($account->id());
    $v = $form_state->getValues();
    $root_path = esim_research_migration_path();
    $proposal_data = esim_research_migration_get_proposal();
    $proposal_id = $proposal_data->id;
    if (!$proposal_data) {
      $form_state->setRedirect('<front>');
      return;
    } //!$proposal_data
    $proposal_id = $proposal_data->id;
    $proposal_directory = $proposal_data->directory_name;
    /* create proposal folder if not present */
    //$dest_path = $proposal_directory . '/';
    $dest_path_project_files = $proposal_directory . '/';
    $proposal_id = $proposal_data->id;
    $query_s = "SELECT * FROM {research_migration_submitted_abstracts} WHERE proposal_id = :proposal_id";
    $args_s = [":proposal_id" => $proposal_id];
    $query_s_result = \Drupal::database()->query($query_s, $args_s)->fetchObject();
    if (!$query_s_result) {
      /* creating solution database entry */
      $query = "INSERT INTO {research_migration_submitted_abstracts} (
	proposal_id,
	approver_uid,
	abstract_approval_status,
	abstract_upload_date,
	abstract_approval_date,
	is_submitted) VALUES (:proposal_id, :approver_uid, :abstract_approval_status,:abstract_upload_date, :abstract_approval_date, :is_submitted)";
      $args = [
        ":proposal_id" => $proposal_id,
        ":approver_uid" => 0,
        ":abstract_approval_status" => 0,
        ":abstract_upload_date" => time(),
        ":abstract_approval_date" => 0,
        ":is_submitted" => 1,
      ];
      $submitted_abstract_id = \Drupal::database()->query($query, $args, $query);
      $query1 = "UPDATE {research_migration_proposal} SET is_submitted = :is_submitted WHERE id = :id";
      $args1 = [
        ":is_submitted" => 1,
        ":id" => $proposal_id,
      ];
      \Drupal::database()->query($query1, $args1);
      \Drupal::messenger()->addStatus('Synopsis Submission uploaded successfully.');
    } //!$query_s_result
    else {
      $query = "UPDATE {research_migration_submitted_abstracts} SET


	abstract_upload_date =:abstract_upload_date,
	is_submitted= :is_submitted
	WHERE proposal_id = :proposal_id
	";
      $args = [
        ":abstract_upload_date" => time(),
        ":is_submitted" => 1,
        ":proposal_id" => $proposal_id,
      ];
      $submitted_abstract_id = \Drupal::database()->query($query, $args, $query);
      $query1 = "UPDATE {research_migration_proposal} SET is_submitted = :is_submitted WHERE id = :id";
      $args1 = [
        ":is_submitted" => 1,
        ":id" => $proposal_id,
      ];
      \Drupal::database()->query($query1, $args1);
      \Drupal::messenger()->addStatus('Synopsis Submission updated successfully.');
    }
    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
      if ($file_name) {
        /* checking file type */
        if (strstr($file_form_name, 'upload_research_migration_developed_process')) {
          $file_type = 'S';
        } //strstr($file_form_name, 'upload_research_migration_developed_process')
        else {
          if (strstr($file_form_name, 'upload_an_abstract')) {
            $file_type = 'A';
          }
          else {
            $file_type = 'U';
          }
        }


        if (file_exists($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name])) {
          //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
                        // move upload and update table file type and timestamp
          if (is_uploaded_file($_FILES['files']['tmp_name'][$file_form_name])) {
            move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]);
          }
          $query = "UPDATE {research_migration_submitted_abstracts_file} SET filesize=:filesize, timestamp=:timestamp WHERE proposal_id = :proposal_id AND filetype = :filetype";
          $args = [
            ":filesize" => $_FILES['files']['size'][$file_form_name],
            ":timestamp" => time(),
            ":proposal_id" => $proposal_id,
            ":filetype" => $file_type,
          ];
          \Drupal::database()->query($query, $args);

          \Drupal::messenger()->addStatus(t("File !filename already exists hence overwirtten the exisitng file ", [
            '!filename' => $_FILES['files']['name'][$file_form_name]
            ]));
        } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
                    /* uploading file */
        else {
          if (is_uploaded_file($_FILES['files']['tmp_name'][$file_form_name]) && move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name])) {
            /* for uploaded files making an entry in the database */
            $query_abstracts = "SELECT * FROM research_migration_submitted_abstracts WHERE proposal_id = :proposal_id";
            $query_abstracts_args = [":proposal_id" => $proposal_id];
            $query_abstracts_result = \Drupal::database()->query($query_abstracts, $query_abstracts_args)->fetchObject();
            $submitted_abstract_id = $query_abstracts_result->id;
            $query_ab_f = "SELECT * FROM research_migration_submitted_abstracts_file WHERE proposal_id = :proposal_id AND filetype =
				:filetype";
            $args_ab_f = [
              ":proposal_id" => $proposal_id,
              ":filetype" => $file_type,
            ];
            $query_ab_f_result = \Drupal::database()->query($query_ab_f, $args_ab_f)->fetchObject();
            if (!$query_ab_f_result) {
              $query = "INSERT INTO {research_migration_submitted_abstracts_file} (submitted_abstract_id, proposal_id, uid, approvar_uid, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:submitted_abstract_id, :proposal_id, :uid, :approvar_uid, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
              $args = [
                ":submitted_abstract_id" => $submitted_abstract_id,
                ":proposal_id" => $proposal_id,
                ":uid" => $account->id(),
                ":approvar_uid" => 0,
                ":filename" => $_FILES['files']['name'][$file_form_name],
                ":filepath" => $_FILES['files']['name'][$file_form_name],
                ":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
                ":filesize" => $_FILES['files']['size'][$file_form_name],
                ":filetype" => $file_type,
                ":timestamp" => time(),
              ];
              \Drupal::database()->query($query, $args, $query);
              \Drupal::messenger()->addStatus($file_name . ' uploaded successfully.');
            } //!$query_ab_f_result
            else {
              unlink($root_path . $dest_path_project_files . $query_ab_f_result->filename);
              $query = "UPDATE {research_migration_submitted_abstracts_file} SET filename = :filename, filepath=:filepath, filemime=:filemime, filesize=:filesize, timestamp=:timestamp WHERE proposal_id = :proposal_id AND filetype = :filetype";
              $args = [
                ":filename" => $_FILES['files']['name'][$file_form_name],
                ":filepath" => $_FILES['files']['name'][$file_form_name],
                ":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
                ":filesize" => $_FILES['files']['size'][$file_form_name],
                ":timestamp" => time(),
                ":proposal_id" => $proposal_id,
                ":filetype" => $file_type,
              ];
              \Drupal::database()->query($query, $args, $query);

              \Drupal::messenger()->addStatus($file_name . ' file updated successfully.');
            }
          } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
          else {
            \Drupal::messenger()->addError('Error uploading file : ' . $dest_path_project_files . $file_name);
          }
        }
        //$file_type
      } //$file_name
    } //$_FILES['files']['name'] as $file_form_name => $file_name
    /* sending email */
    $email_to = $user ? (string) $user->getEmail() : '';
    $config = \Drupal::config('esim_research_migration.settings');
    $from = (string) ($config->get('research_migration_from_email') ?: \Drupal::config('system.site')->get('mail'));
    $bcc = (string) $config->get('research_migration_emails');
    $cc = (string) $config->get('research_migration_cc_emails');

    $params['abstract_uploaded']['proposal_id'] = $proposal_id;
    $params['abstract_uploaded']['submitted_abstract_id'] = $submitted_abstract_id;
    $params['abstract_uploaded']['user_id'] = $account->id();
    $params['abstract_uploaded']['headers'] = [
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
      $mail_result = $mail_manager->mail('esim_research_migration', 'abstract_uploaded', $email_to, $langcode, $params, $from, TRUE);
      if (empty($mail_result['result'])) {
        $this->messenger()->addError($this->t('Error sending email message.'));
      }
    }

    $form_state->setRedirect('esim_research_migration.abstract');
    Cache::invalidateTags([
      'research_migration_proposal_list',
      'research_migration_proposal:' . $proposal_id,
      'research_migration_submitted_abstracts_list',
      'research_migration_submitted_abstracts_file_list',
    ]);
  }

  private function getUploadedFile(string $filetype, int $proposal_id): ?object {
    return \Drupal::database()
      ->select('research_migration_submitted_abstracts_file', 'f')
      ->fields('f')
      ->condition('proposal_id', $proposal_id)
      ->condition('filetype', $filetype)
      ->range(0, 1)
      ->execute()
      ->fetchObject() ?: NULL;
  }

}

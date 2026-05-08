<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Form\AddProjectTitleForm.
 */

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AddProjectTitleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_project_title_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $account = $this->currentUser();
    if (!$account->isAuthenticated()) {
      $login_link = \Drupal\Core\Link::fromTextAndUrl($this->t('login'), \Drupal\Core\Url::fromRoute('user.login'))->toString();
      $this->messenger()->addError($this->t('It is mandatory to @login_link on this website to access this form. If you are a new user please create a new account first.', [
        '@login_link' => $login_link,
      ]));
      $form_state->setRedirect('user.login');
      return [];
    }
    $form['#attributes'] = [
      'enctype' => "multipart/form-data"
      ];
    $form['new_project_title_name'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the name of the project title'),
      '#size' => 250,
      '#attributes' => [
        'placeholder' => t('Enter the name of the project title displayed to the contributor')
        ],
      '#maxlength' => 250,
      '#required' => TRUE,
    ];
    $form['project_link'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the Link of the project'),
      '#size' => 250,
      '#attributes' => [
        'placeholder' => t('Enter the Link of the project displayed to the contributor')
        ],
      '#maxlength' => 250,
      '#required' => TRUE,
    ];
    /*$form['upload_project_title_resource_file'] = array(
		'#type' => 'fieldset',
		'#title' => t('Browse and upload the file to display with the project title <span style="color:#f00;">*</span>'),
		'#collapsible' => FALSE,
		'#collapsed' => FALSE
	);
	$form['upload_project_title_resource_file']['project_title_resource_file_path'] = array(
		'#type' => 'file',
		'#size' => 48,
		'#description' => t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . variable_get('list_of_available_projects_file', '') . '</span>'
	);*/
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!isset($_FILES['files']['name']) || !is_array($_FILES['files']['name'])) {
      return;
    }

    $allowed_extensions_str = (string) \Drupal::config('esim_research_migration.settings')->get('list_of_available_projects_file');
    $allowed_extensions = array_filter(array_map('trim', explode(',', $allowed_extensions_str)));

    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
      if (!$file_name) {
        continue;
      }

      if ($allowed_extensions && !empty($allowed_extensions_str)) {
        $fnames = explode('.', strtolower((string) $file_name));
        $temp_extension = end($fnames);
        if (!in_array($temp_extension, $allowed_extensions, TRUE)) {
          $form_state->setErrorByName($file_form_name, t('Only file with @ext extensions can be uploaded.', ['@ext' => $allowed_extensions_str]));
        }
      }

      if (!empty($_FILES['files']['size'][$file_form_name]) && $_FILES['files']['size'][$file_form_name] <= 0) {
        $form_state->setErrorByName($file_form_name, t('File size cannot be zero.'));
      }

      if (!esim_research_migration_check_valid_filename((string) $file_name)) {
        $form_state->setErrorByName($file_form_name, t('Invalid file name specified. Only alphabets and numbers are allowed as a valid filename.'));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $v = $form_state->getValues();
    $result = "INSERT INTO {rm_list_of_project_titles}
	(
	rm_project_title_name,
	rm_project_link
	)VALUES
	(
	:rm_project_title_name,
	:rm_project_link
	)";
    $args = [
      ":rm_project_title_name" => $v['new_project_title_name'],
      ":rm_project_link" => $v['project_link'],
    ];
    $result1 = \Drupal::database()->query($result, $args, $result);
    $dest_path = esim_research_migration_project_titles_resource_file_path();
    //var_dump($dest_path);die;
    if (!isset($_FILES['files']['name']) || !is_array($_FILES['files']['name'])) {
      \Drupal::messenger()->addStatus(t('Project title added successfully.'));
      return;
    }

    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
      if ($file_name) {
        /* checking file type */
        //$file_type = 'S';
			//var_dump($dest_path . $result1 .'_' . $_FILES['files']['name'][$file_form_name]);die;
        if (file_exists($dest_path . $result1 . '_' . $_FILES['files']['name'][$file_form_name])) {
          \Drupal::messenger()->addError(t("Error uploading file. File !filename already exists.", [
            '!filename' => $_FILES['files']['name'][$file_form_name]
            ]));
          //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
        } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
			/* uploading file */
        if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $dest_path . $result1 . '_' . $_FILES['files']['name'][$file_form_name])) {
          $query = "UPDATE {rm_list_of_project_titles} SET filepath = :filepath WHERE id = :id";
          $args = [
            ":filepath" => $result1 . '_' . $_FILES['files']['name'][$file_form_name],
            ":id" => $result1,
          ];

          $updateresult = \Drupal::database()->query($query, $args);
          //var_dump($args);die;
          \Drupal::messenger()->addStatus($file_name . ' uploaded successfully.');
        } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
        else {
          \Drupal::messenger()->addError('Error uploading file: ' . $dest_path . $result1 . '_' . $file_name);
        }
      } //$file_name
    } //$_FILES['files']['name'] as $file_form_name => $file_name
    \Drupal::messenger()->addStatus(t('Project title added successfully'));
  }

}

<?php
function add_project_title_form($form, &$form_state)
{
	$user = \Drupal::currentUser();
	/************************ start approve book details ************************/
	if ($user->uid == 0)
	{
		$msg = \Drupal::messenger()->addError(t('It is mandatory to ' . \Drupal\Core\Link::fromTextAndUrl('login', \Drupal\Core\Url::fromRoute('user.page')) . ' on this website to access the research migration proposal form. If you are new user please create a new account first.'));
		drupal_goto('user');
		return $msg;
	} //$user->uid == 0
	$form['#attributes'] = array(
		'enctype' => "multipart/form-data"
	);
	$form['new_project_title_name'] = array(
		'#type' => 'textfield',
		'#title' => t('Enter the name of the project title'),
		'#size' => 250,
		'#attributes' => array(
			'placeholder' => t('Enter the name of the project title displayed to the contributor')
		),
		'#maxlength' => 250,
		'#required' => TRUE
	);
	$form['project_link'] = array(
		'#type' => 'textfield',
		'#title' => t('Enter the Link of the project'),
		'#size' => 250,
		'#attributes' => array(
			'placeholder' => t('Enter the Link of the project displayed to the contributor')
		),
		'#maxlength' => 250,
		'#required' => TRUE
	);
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
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit')
	);
	return $form;
}
function add_project_title_form_validate($form, &$form_state) {
	if (isset($_FILES['files']))
	{
		/* check if atleast one source or result file is uploaded */
		if (!($_FILES['files']['name']['project_title_resource_file_path']))
			form_set_error('project_title_resource_file_path', t('Please upload the file'));
		/* check for valid filename extensions */
		foreach ($_FILES['files']['name'] as $file_form_name => $file_name)
		{
			if ($file_name)
			{
				/* checking file type */
				// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $allowed_extensions_str = variable_get('list_of_available_projects_file', '');

				$allowed_extensions = explode(',', $allowed_extensions_str);
				$fnames = explode('.', strtolower($_FILES['files']['name'][$file_form_name]));
				$temp_extension = end($fnames);
				if (!in_array($temp_extension, $allowed_extensions))
					form_set_error($file_form_name, t('Only file with ' . $allowed_extensions_str . ' extensions can be uploaded.'));
				if ($_FILES['files']['size'][$file_form_name] <= 0)
					form_set_error($file_form_name, t('File size cannot be zero.'));
				/* check if valid file name */
				if (!esim_research_migration_check_valid_filename($_FILES['files']['name'][$file_form_name]))
					form_set_error($file_form_name, t('Invalid file name specified. Only alphabets and numbers are allowed as a valid filename.'));
			} //$file_name
		} //$_FILES['files']['name'] as $file_form_name => $file_name
	} 
	return $form_state;
}

function add_project_title_form_submit($form, &$form_state) { 
	$user = \Drupal::currentUser();
	$v = $form_state["values"];
	$result = "INSERT INTO {rm_list_of_project_titles}
	(
	rm_project_title_name,
	rm_project_link
	)VALUES
	(
	:rm_project_title_name,
	:rm_project_link
	)";
	$args = array(
		":rm_project_title_name" => $v['new_project_title_name'],
		":rm_project_link" => $v['project_link']
	);
	$result1 = \Drupal::database()->query($result, $args, $result);
	$dest_path = esim_research_migration_project_titles_resource_file_path();
	//var_dump($dest_path);die;
	foreach ($_FILES['files']['name'] as $file_form_name => $file_name)
	{
		if ($file_name)
		{
			/* checking file type */
			//$file_type = 'S';
			//var_dump($dest_path . $result1 .'_' . $_FILES['files']['name'][$file_form_name]);die;
			if (file_exists($dest_path . $result1 . '_' . $_FILES['files']['name'][$file_form_name]))
			{
				 \Drupal::messenger()->addError(t("Error uploading file. File !filename already exists.", array('!filename' => $_FILES['files']['name'][$file_form_name])));
				//unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
			} //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
			/* uploading file */
			if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $dest_path . $result1 . '_' . $_FILES['files']['name'][$file_form_name]))
			{
				$query = "UPDATE {rm_list_of_project_titles} SET filepath = :filepath WHERE id = :id";
				$args = array(
					":filepath" => $result1 . '_' . $_FILES['files']['name'][$file_form_name],
					":id" => $result1
				);
				
				$updateresult = \Drupal::database()->query($query, $args);
				//var_dump($args);die;
				\Drupal::messenger()->addStatus($file_name . ' uploaded successfully.');
			} //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
			else
			{
				\Drupal::messenger()->addError('Error uploading file: ' . $dest_path . $result1 . '_' . $file_name);
			}
		} //$file_name
	} //$_FILES['files']['name'] as $file_form_name => $file_name
	\Drupal::messenger()->addStatus(t('Project title added successfully'));
}
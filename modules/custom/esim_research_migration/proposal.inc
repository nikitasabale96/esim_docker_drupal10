<?php
/*
Approval Status :
0 - Pending
1 - Approved
2 - Dis-Approved
3 - Completed
Solution Status :
0 - Pending
1 - Approved
2 - Dis-Approved
Solution Display :
0 - No
1 - Yes
is_completed
0 - in progress
1 - Completed
Tables :
research_migration_solution : approval_status
0 - Pending
1 - Approved
2 - Disapproved (delete it)
*/
function esim_research_migration_proposal_form($form, &$form_state, $no_js_use = FALSE)
{
	$user = \Drupal::currentUser();
	/************************ start approve book details ************************/
	if ($user->uid == 0)
	{
		$msg = \Drupal::messenger()->addError(t('It is mandatory to ' . \Drupal\Core\Link::fromTextAndUrl('login', \Drupal\Core\Url::fromRoute('user.page')) . ' on this website to access the Research Migration proposal form. If you are new user please create a new account first.'));
		drupal_goto('user/login', array('query' => drupal_get_destination()));
		return $msg;
	} //$user->uid == 0
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('uid', $user->uid);
	$query->orderBy('id', 'DESC');
	$query->range(0, 1);
	$proposal_q = $query->execute();
	$proposal_data = $proposal_q->fetchObject();
	if ($proposal_data)
	{
		if ($proposal_data->approval_status == 0 || $proposal_data->approval_status == 1)
		{
			\Drupal::messenger()->addStatus(t('We have already received your proposal.'));
			drupal_goto('');
			return;
		} //$proposal_data->approval_status == 0 || $proposal_data->approval_status == 1
	} //$proposal_data
	$form['#attributes'] = array(
		'enctype' => "multipart/form-data"
	);

	$form['name_title'] = array(
		'#type' => 'select',
		'#title' => t('Title'),
		'#options' => array(
			'Dr' => 'Dr',
			'Prof' => 'Prof',
			'Mr' => 'Mr',
			'Ms' => 'Ms'
		),
		'#required' => TRUE
	);
	$form['contributor_name'] = array(
		'#type' => 'textfield',
		'#title' => t('Name of the contributor'),
		'#size' => 250,
		'#attributes' => array(
			'placeholder' => t('Enter your full name.....')
		),
		'#maxlength' => 250,
		'#required' => TRUE
	);
	$form['contributor_email_id'] = array(
		'#type' => 'textfield',
		'#title' => t('Email'),
		'#size' => 30,
		'#value' => $user->mail,
		'#disabled' => TRUE
	);
	$form['contributor_contact_no'] = array(
		'#type' => 'textfield',
		'#title' => t('Contact No.'),
		'#size' => 10,
		'#attributes' => array(
			'placeholder' => t('Enter your contact number')
		),
		'#maxlength' => 250
	);
	$form['university'] = array(
		'#type' => 'textfield',
		'#title' => t('University'),
		'#size' => 80,
		'#maxlength' => 200,
		'#required' => TRUE,
		'#attributes' => array(
			'placeholder' => 'Insert full name of your university.... '
		)
	);
	$form['institute'] = array(
		'#type' => 'textfield',
		'#title' => t('Institute'),
		'#size' => 80,
		'#maxlength' => 200,
		'#required' => TRUE,
		'#attributes' => array(
			'placeholder' => 'Insert full name of your institute.... '
		)
	);
	$form['how_did_you_know_about_project'] = array(
		'#type' => 'select',
		'#title' => t('How did you come to know about the Research Migration Project?'),
		'#options' =>  array(
			'Poster' => 'Poster',
			'Website' => 'Website',
			'Email' => 'Email',
			'Others' => 'Others'
		),
		'#required' => TRUE
	);
	$form['others_how_did_you_know_about_project'] = array(
		'#type' => 'textfield',
		'#title' => t('If ‘Other’, please specify'),
		'#maxlength' => 50,
		'#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
		'#states' => array(
			'visible' => array(
				':input[name="how_did_you_know_about_project"]' => array(
					'value' => 'Others'
				)
			)
		)		
	);
	$form['faculty_name'] = array(
		'#type' => 'textfield',
		'#title' => t('Name of the Faculty Member of your Institution, if any, who helped you with this Research Migration Project'),
		'#size' => 50,
		'#maxlength' => 50,
		'#validated' => TRUE,
		'#description' => t('<span style="color:red">Maximum character limit is 50</span>')
	);
	$form['faculty_department'] = array(
		'#type' => 'textfield',
		'#title' => t('Department of the Faculty Member of your Institution, if any, who helped you with this Research Migration Project'),
		'#size' => 50,
		'#maxlength' => 50,
		'#validated' => TRUE,
		'#description' => t('<span style="color:red">Maximum character limit is 50</span>')
	);
	$form['faculty_email'] = array(
		'#type' => 'textfield',
		'#title' => t('Email id of the Faculty Member of your Institution, if any, who helped you with this Research Migration Project'),
		'#size' => 255,
		'#maxlength' => 255,
		'#validated' => TRUE,
		'#description' => t('<span style="color:red">Maximum character limit is 255</span>')
	);
	$form['country'] = array(
		'#type' => 'select',
		'#title' => t('Country'),
		'#options' => array(
			'India' => 'India',
			'Others' => 'Others'
		),
		'#required' => TRUE,
		'#tree' => TRUE,
		'#validated' => TRUE
	);
	$form['other_country'] = array(
		'#type' => 'textfield',
		'#title' => t('Other than India'),
		'#size' => 100,
		'#attributes' => array(
			'placeholder' => t('Enter your country name')
		),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'Others'
				)
			)
		)
	);
	$form['other_state'] = array(
		'#type' => 'textfield',
		'#title' => t('State other than India'),
		'#size' => 100,
		'#attributes' => array(
			'placeholder' => t('Enter your state/region name')
		),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'Others'
				)
			)
		)
	);
	$form['other_city'] = array(
		'#type' => 'textfield',
		'#title' => t('City other than India'),
		'#size' => 100,
		'#attributes' => array(
			'placeholder' => t('Enter your city name')
		),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'Others'
				)
			)
		)
	);
	$form['all_state'] = array(
		'#type' => 'select',
		'#title' => t('State'),
		'#options' => _rm_df_list_of_states(),
		'#validated' => TRUE,
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'India'
				)
			)
		)
	);
	$form['city'] = array(
		'#type' => 'select',
		'#title' => t('City'),
		'#options' => _rm_df_list_of_cities(),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'India'
				)
			)
		)
	);
	$form['pincode'] = array(
		'#type' => 'textfield',
		'#title' => t('Pincode'),
		'#size' => 6
	);
	/***************************************************************************/
	$form['hr'] = array(
		'#type' => 'item',
		'#markup' => '<hr>'
	);	
		$form['project_title'] = array(
		'#type' => 'textfield',
		'#title' => t('Title of the Research Migration Project'),
		'#size' => 80,		
		'#maxlength' => 250,
		'#description' => t('Maximum character limit is 250'),
		'#required' => TRUE,
		'#validated' => TRUE,
		);
	
	$form['source_of_the_project'] = array(
		'#type' => 'textarea',
		'#title' => t('Source of the Project'),
		// '#size' => 80,
		// '#maxlength' => 200,
		'#required' => TRUE,
		'#attributes' => array(
			'placeholder' => 'Insert the Journal name, title of proceedings (for conference papers) '
		)
	);
	
	$form['samplefile'] = array(
		'#type' => 'fieldset',
		'#title' => t('<span style="color:black;">Synopsis Submission</span> <span style="color:#f00;">*</span>'),
		'#required' => TRUE,
		'#collapsible' => FALSE,
		'#collapsed' => FALSE
	);
	// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $form['samplefile']['samplefile_path'] = array(
// 		'#type' => 'file',
// 		'#size' => 48,
// 		'#description' => t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') . '<br />' . t('<span style="color:red;">Allowed file extensions : ') . variable_get('resource_upload_extensions', '') . '</span>'
// 	);

	$form['date_of_proposal'] = array(
		'#type' => 'date_popup',
		'#title' => t('Date of Proposal'),
		'#default_value' => date("Y-m-d H:i:s"),
		'#date_format' => 'd M Y',
		'#disabled' => TRUE,
		'#date_label_position' => '',
	);
	$form['expected_date_of_completion'] = array(
		'#type' => 'date_popup',
		'#title' => t('Expected Date of Completion'),
		'#date_label_position' => '',
		'#description' => '',
		'#default_value' => '',
		'#date_format' => 'd-M-Y',
		//'#date_increment' => 0,
		//'#minDate' => '+0',
		//'#date_year_range' => '+0 : +1',
		'#required' => TRUE,
		 '#datepicker_options' => array(
           'maxDate' => '+45D', // not more than current date
           'minDate' => '+1' // not more than given date
       ),
	);
	$form['term_condition'] = array(
		'#type' => 'checkboxes',
		'#title' => t('Terms And Conditions'),
		'#options' => array(
			'status' => t('<a href="/research-migration-project/term-and-conditions" target="_blank">I agree to the Terms and Conditions</a>')
		),
		'#required' => TRUE
	);
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit')
	);
	return $form;
}



function esim_research_migration_proposal_form_validate($form, &$form_state)
{
	//var_dump($form_state['values']['solver_used']);die;
	
	if ($form_state['values']['term_condition'] == '1')
	{
		form_set_error('term_condition', t('Please check the terms and conditions'));
		// $form_state['values']['country'] = $form_state['values']['other_country'];
	} //$form_state['values']['term_condition'] == '1'
	if ($form_state['values']['country'] == 'Others')
	{
		if ($form_state['values']['other_country'] == '')
		{
			form_set_error('other_country', t('Enter country name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['other_country'] == ''
		else
		{
			$form_state['values']['country'] = $form_state['values']['other_country'];
		}
		if ($form_state['values']['other_state'] == '')
		{
			form_set_error('other_state', t('Enter state name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['other_state'] == ''
		else
		{
			$form_state['values']['all_state'] = $form_state['values']['other_state'];
		}
		if ($form_state['values']['other_city'] == '')
		{
			form_set_error('other_city', t('Enter city name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['other_city'] == ''
		else
		{
			$form_state['values']['city'] = $form_state['values']['other_city'];
		}
	} //$form_state['values']['country'] == 'Others'
	else
	{
		if ($form_state['values']['country'] == '')
		{
			form_set_error('country', t('Select country name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['country'] == ''
		if ($form_state['values']['all_state'] == '')
		{
			form_set_error('all_state', t('Select state name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['all_state'] == ''
		if ($form_state['values']['city'] == '')
		{
			form_set_error('city', t('Select city name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['city'] == ''
	}
	//Validation for project title
	$form_state['values']['project_title'] = trim($form_state['values']['project_title']);
	if ($form_state['values']['project_title'] != '')
	{
		if (strlen($form_state['values']['project_title']) > 250)
		{
			form_set_error('project_title', t('Maximum charater limit is 250 charaters only, please check the length of the project title'));
		} //strlen($form_state['values']['project_title']) > 250
		else if (strlen($form_state['values']['project_title']) < 10)
		{
			form_set_error('project_title', t('Minimum charater limit is 10 charaters, please check the length of the project title'));
		} //strlen($form_state['values']['project_title']) < 10
	} //$form_state['values']['project_title'] != ''
	
	
	if ($form_state['values']['how_did_you_know_about_project'] == 'Others')
	{
		if ($form_state['values']['others_how_did_you_know_about_project'] == '')
		{
			form_set_error('others_how_did_you_know_about_project', t('Please enter how did you know about the project'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['other_country'] == ''
		else
		{
			$form_state['values']['how_did_you_know_about_project'] = $form_state['values']['others_how_did_you_know_about_project'];
		}
	}
	
	
if (isset($_FILES['files']))
	{
		/* check if atleast one source or result file is uploaded */
		if (!($_FILES['files']['name']['samplefile_path']))
			form_set_error('samplefilepath', t('Please upload the Synopsis file'));
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
// $allowed_extensions_str = variable_get('resource_upload_extensions', '');

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
function esim_research_migration_proposal_form_submit($form, &$form_state)
{
	$user = \Drupal::currentUser();
	$root_path = research_migration_path();
	if (!$user->uid)
	{
		\Drupal::messenger()->addError('It is mandatory to login on this website to access the proposal form');
		return;
	}
	

		$project_title = $form_state['values']['project_title'];
	
	if ($form_state['values']['how_did_you_know_about_project'] == 'Others')
	{
			$how_did_you_know_about_project = $form_state['values']['others_how_did_you_know_about_project'];
	}
	else {
		$how_did_you_know_about_project = $form_state['values']['how_did_you_know_about_project'];
	}
	/* inserting the user proposal */
	$v = $form_state["values"];
	$project_title = trim($project_title);
	$proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
	$university = $v['university'];
	$directory_name = _rm_dir_name($project_title, $proposar_name);
	$result = "INSERT INTO {research_migration_proposal} 
    (
    uid, 
    approver_uid,
    name_title, 
    contributor_name,
    contact_no,
    university,
    institute,
    how_did_you_know_about_project,
    faculty_name,
    faculty_department,
    faculty_email,
    city, 
    pincode, 
    state, 
    country,
    project_title, 
	source_of_the_project,
    directory_name,
    approval_status,
    is_completed, 
    dissapproval_reason,
    creation_date, 
    expected_date_of_completion,
    approval_date,
	samplefilepath
    ) VALUES
    (
    :uid, 
    :approver_uid, 
    :name_title, 
    :contributor_name, 
    :contact_no,
    :university, 
    :institute,
    :how_did_you_know_about_project,
    :faculty_name,
    :faculty_department,
    :faculty_email,
    :city, 
    :pincode, 
    :state,  
    :country,
    :project_title,
	:source_of_the_project, 
    :directory_name,
    :approval_status,
    :is_completed, 
    :dissapproval_reason,
    :creation_date, 
    :expected_date_of_completion,
    :approval_date,
	:samplefilepath
    )";
	$args = array(
		":uid" => $user->uid,
		":approver_uid" => 0,
		":name_title" => $v['name_title'],
		":contributor_name" => _df_sentence_case(trim($v['contributor_name'])),
		":contact_no" => $v['contributor_contact_no'],
		":university" => $v['university'],
		":institute" => _df_sentence_case($v['institute']),
		":how_did_you_know_about_project" => trim($how_did_you_know_about_project),
		":faculty_name" => $v['faculty_name'],
		":faculty_department" => $v['faculty_department'],
		":faculty_email" => $v['faculty_email'],
		":city" => $v['city'],
		":pincode" => $v['pincode'],
		":state" => $v['all_state'],
		":country" => $v['country'],
		":project_title" => $project_title,
		":source_of_the_project" => trim($v['source_of_the_project']),
		":directory_name" => $directory_name,
		":approval_status" => 0,
		":is_completed" => 0,
		":dissapproval_reason" => "NULL",
		":creation_date" => time(),
		":expected_date_of_completion" => strtotime(date($v['expected_date_of_completion'])),
		":approval_date" => 0,
		":samplefilepath" => "",
		);
	$result1 = \Drupal::database()->query($result, $args, $result);
	//var_dump($result1->id);die;
	// $query_pro = db_select('research_migration_proposal');
	// $query_pro->fields('research_migration_proposal');
//	$query_pro->condition('id', $proposal_data->id);
	// $abstracts_pro = $query_pro->execute()->fetchObject();
//	$proposal_id = $abstracts_pro->id;
	$dest_path = $directory_name . '/';
	$dest_path1 = $root_path . $dest_path;
	if (!is_dir($root_path . $dest_path))
		mkdir($root_path . $dest_path);
	/* uploading files */
	foreach ($_FILES['files']['name'] as $file_form_name => $file_name)
	{
		if ($file_name)
		{
			/* checking file type */
			//$file_type = 'S';
			if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]))
			{
				 \Drupal::messenger()->addError(t("Error uploading file. File !filename already exists.", array('!filename' => $_FILES['files']['name'][$file_form_name])));
				//unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
			} //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
			/* uploading file */
			if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . 'abstract_' . $_FILES['files']['name'][$file_form_name]))
			{
				$query = "UPDATE {research_migration_proposal} SET samplefilepath = :samplefilepath WHERE id = :id";
				$args = array(
					":samplefilepath" => $dest_path . 'abstract_' . $_FILES['files']['name'][$file_form_name],
					":id" => $result1
				);
				
				$updateresult = \Drupal::database()->query($query, $args);
				//var_dump($args);die;

				\Drupal::messenger()->addStatus($file_name . ' uploaded successfully.');
			} //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
			else
			{
				\Drupal::messenger()->addError('Error uploading file : ' . $dest_path . '/' . $file_name);
			}
		} //$file_name
	} //$_FILES['files']['name'] as $file_form_name => $file_name
	if (!$result1)
	{
		\Drupal::messenger()->addError(t('Error receiving your proposal. Please try again.'));
		return;
	} //!$proposal_id
	/* sending email */
	$email_to = $user->mail;
	// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $form = variable_get('research_migration_from_email', '');

	// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $bcc = variable_get('research_migration_emails', '');

	// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $cc = variable_get('research_migration_cc_emails', '');

	$params['research_migration_proposal_received']['result1'] = $result1;
	$params['research_migration_proposal_received']['user_id'] = $user->uid;
	$params['research_migration_proposal_received']['headers'] = array(
		'From' => $form,
		'MIME-Version' => '1.0',
		'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
		'Content-Transfer-Encoding' => '8Bit',
		'X-Mailer' => 'Drupal',
		'Cc' => $cc,
		'Bcc' => $bcc
	);
	if (!drupal_mail('research_migration', 'research_migration_proposal_received', $email_to, user_preferred_language($user), $params, $form, TRUE))
		\Drupal::messenger()->addError('Error sending email message.');
	\Drupal::messenger()->addStatus(t('We have received your Research Migration proposal. We will get back to you soon.'));
	drupal_goto('');
}

function research_migration_path() {
  return $_SERVER['DOCUMENT_ROOT'] . base_path() . 'esim_uploads/research_migration_uploads/';
}

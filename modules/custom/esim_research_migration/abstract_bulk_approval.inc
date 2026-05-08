<?php

function esim_research_migration_abstract_bulk_approval_form($form, &$form_state)
{
	$options_first = _bulk_list_of_research_migration_project();
	$selected = isset($form_state['values']['research_migration_project']) ? $form_state['values']['research_migration_project'] : key($options_first);
	$form = array();
	$form['research_migration_project'] = array(
		'#type' => 'select',
		'#title' => t('Title of the Research Migration project'),
		'#options' => _bulk_list_of_research_migration_project(),
		'#default_value' => $selected,
		'#ajax' => array(
			
			'callback' => 'ajax_bulk_research_migration_abstract_details_callback'
		),
		'#suffix' => '<div id="ajax_selected_research_migration"></div><div id="ajax_selected_research_migration_pdf"></div>'
	);
	$form['research_migration_actions'] = array(
		'#type' => 'select',
		'#title' => t('Please select action for Research Migration project'),
		'#options' => _bulk_list_research_migration_actions(),
		'#default_value' => 0,
		'#prefix' => '<div id="ajax_selected_research_migration_action" style="color:red;">',
		'#suffix' => '</div>',
		'#states' => array(
			'invisible' => array(
				':input[name="research_migration_project"]' => array(
					'value' => 0
				)
			)
		)
	);
	$form['message'] = array(
		'#type' => 'textarea',
		'#title' => t('If Dis-Approved please specify reason for Dis-Approval'),
		'#prefix' => '<div id= "message_submit">',
		'#states' => array(
			'visible' => array(
				array(
					':input[name="research_migration_actions"]' => array(
						'value' => 2
					)
				),
				'or',
				array(
					':input[name="research_migration_actions"]' => array(
						'value' => 3
					)
				)
			)
		)
	);
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit'),
		'#states' => array(
			'invisible' => array(
				':input[name="lab"]' => array(
					'value' => 0
				)
			)
		)
	);
	return $form;
}
function ajax_bulk_research_migration_abstract_details_callback($form, $form_state)
{
	$commands = array();
	$research_migration_project_default_value = $form_state['values']['research_migration_project'];
	if ($research_migration_project_default_value != 0)
	{
		$commands[] = ajax_command_html('#ajax_selected_research_migration', _research_migration_details($research_migration_project_default_value));
		$form['research_migration_actions']['#options'] = _bulk_list_research_migration_actions();
		$commands[] = ajax_command_replace('#ajax_selected_research_migration_action', \Drupal::service("renderer")->render($form['research_migration_actions']));
	} //$research_migration_project_default_value != 0
	else
	{
		$commands[] = ajax_command_html('#ajax_selected_research_migration', '');
		$commands[] = ajax_command_data('#ajax_selected_research_migration', 'form_state_value_select', $form_state['values']['research_migration_project']);
	}
	return array(
		'#type' => 'ajax',
		'#commands' => $commands
	);
}
/************************************************************/
function esim_research_migration_abstract_bulk_approval_form_submit($form, &$form_state)
{
	$user = \Drupal::currentUser();
	$msg = '';
	$root_path = esim_research_migration_path();
	//var_dump($root_path);die;
	if ($form_state['clicked_button']['#value'] == 'Submit')
	{
		if ($form_state['values']['research_migration_project'])
			//var_dump($form_state['values']['research_migration_actions']);die;
		// research_migration_abstract_del_lab_pdf($form_state['values']['research_migration_project']);
			if (\Drupal::currentUser()->hasPermission('Research Migration bulk manage abstract'))
			{
				$query = \Drupal::database()->select('research_migration_proposal');
				$query->fields('research_migration_proposal');
				$query->condition('id', $form_state['values']['research_migration_project']);
				$user_query = $query->execute();
				$user_info = $user_query->fetchObject();
				//var_dump($user_info);die;
				$user_data = \Drupal::entityTypeManager()->getStorage('user')->load($user_info->uid);
				if ($form_state['values']['research_migration_actions'] == 1)
				{
					// approving entire project //
					$query = \Drupal::database()->select('research_migration_submitted_abstracts');
					$query->fields('research_migration_submitted_abstracts');
					$query->condition('proposal_id', $form_state['values']['research_migration_project']);
					$abstracts_q = $query->execute();
					//var_dump($abstracts_q);die;
					$experiment_list = '';
					while ($abstract_data = $abstracts_q->fetchObject())
					{
						\Drupal::database()->query("UPDATE {research_migration_submitted_abstracts} SET abstract_approval_status = 1, is_submitted = 1, approver_uid = :approver_uid WHERE id = :id", array(
							':approver_uid' => $user->uid,
							':id' => $abstract_data->id
						));
						\Drupal::database()->query("UPDATE {research_migration_submitted_abstracts_file} SET file_approval_status = 1, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", array(
							':approver_uid' => $user->uid,
							':submitted_abstract_id' => $abstract_data->id
						));
					} //$abstract_data = $abstracts_q->fetchObject()
					\Drupal::messenger()->addStatus(t('Approved Research Migration Project. Use the checkbox below to publish this Research Migration on the completed Research Migration page.'));
					drupal_goto('research-migration-project/manage-proposal/status/' . $form_state['values']['research_migration_project']);
					// email 
					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $email_subject = t('[!site_name][Research Migration Project] Your uploaded Research Migration project have been approved', array(
// 						'!site_name' => variable_get('site_name', '')
// 					));

					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $email_body = array(
// 						0 => t('
// 
// Dear ' . $user_info->contributor_name . ',
// 
// Your uploaded project files for the Research Migration project has been approved.
// 
// Title of Research Migration project  : ' . $user_info->project_title . '
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE,IIT Bombay', array(
// 							'!site_name' => variable_get('site_name', ''),
// 							'!user_name' => $user_data->name
// 						))
// 					);

					/** sending email when everything done **/
					$email_to = $user_data->mail;
					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $from = variable_get('research_migration_from_email', '');

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

					$params['standard']['subject'] = $email_subject;
					$params['standard']['body'] = $email_body;
					$params['standard']['headers'] = array(
						'From' => $from,
						'MIME-Version' => '1.0',
						'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
						'Content-Transfer-Encoding' => '8Bit',
						'X-Mailer' => 'Drupal',
						'Cc' => $cc,
						'Bcc' => $bcc
					);
					if (!drupal_mail('research_migration', 'standard', $email_to, language_default(), $params, $from, TRUE))
					{
						$msg = \Drupal::messenger()->addError('Error sending email message.');
					} //!drupal_mail('research_migration', 'standard', $email_to, language_default(), $params, $from, TRUE)
				} //$form_state['values']['research_migration_actions'] == 1
				elseif ($form_state['values']['research_migration_actions'] == 2)
				{
					//pending review entire project 
					$query = \Drupal::database()->select('research_migration_submitted_abstracts');
					$query->fields('research_migration_submitted_abstracts');
					$query->condition('proposal_id', $form_state['values']['research_migration_project']);
					$abstracts_q = $query->execute();
					$experiment_list = '';
					while ($abstract_data = $abstracts_q->fetchObject())
					{
						\Drupal::database()->query("UPDATE {research_migration_submitted_abstracts} SET abstract_approval_status = 0, is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", array(
							':approver_uid' => $user->uid,
							':id' => $abstract_data->id
						));
						\Drupal::database()->query("UPDATE {research_migration_proposal} SET is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", array(
							':approver_uid' => $user->uid,
							':id' => $abstract_data->proposal_id
						));
						\Drupal::database()->query("UPDATE {research_migration_submitted_abstracts_file} SET file_approval_status = 0, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", array(
							':approver_uid' => $user->uid,
							':submitted_abstract_id' => $abstract_data->id
						));
					} //$abstract_data = $abstracts_q->fetchObject()
					\Drupal::messenger()->addStatus(t('The proposal has been marked for resubmission'));
					// email 
					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $email_subject = t('[!site_name][Research Migration Project] Your uploaded Research Migration project have been marked as pending', array(
// 						'!site_name' => variable_get('site_name', '')
// 					));

					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $email_body = array(
// 						0 => t('
// 
// Dear ' . $user_info->contributor_name . ',
// 
// Kindly resubmit the project files for the project : ' . $user_info->project_title . '.
// 
// Reason for resubmission: ' . $form_state['values']['message'] . '
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE,IIT Bombay', array(
// 							'!site_name' => variable_get('site_name', ''),
// 							'!user_name' => $user_data->name
// 						))
// 					);

					/** sending email when everything done **/
					$email_to = $user_data->mail;
					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $from = variable_get('research_migration_from_email', '');

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

					$params['standard']['subject'] = $email_subject;
					$params['standard']['body'] = $email_body;
					$params['standard']['headers'] = array(
						'From' => $from,
						'MIME-Version' => '1.0',
						'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
						'Content-Transfer-Encoding' => '8Bit',
						'X-Mailer' => 'Drupal',
						'Cc' => $cc,
						'Bcc' => $bcc
					);
					if (!drupal_mail('research_migration', 'standard', $email_to, language_default(), $params, $from, TRUE))
					{
						\Drupal::messenger()->addError('Error sending email message.');
					} //!drupal_mail('research_migration', 'standard', $email_to, language_default(), $params, $from, TRUE)
				} //$form_state['values']['research_migration_actions'] == 2
				elseif ($form_state['values']['research_migration_actions'] == 3) //disapprove and delete entire Research Migration project
				{
					if (strlen(trim($form_state['values']['message'])) <= 30)
					{
						form_set_error('message', t(''));
						$msg = \Drupal::messenger()->addError("Please mention the reason for disapproval. Minimum 30 character required");
						return $msg;
					} //strlen(trim($form_state['values']['message'])) <= 30
					if (!\Drupal::currentUser()->hasPermission('Research Migration bulk delete abstract'))
					{
						$msg = \Drupal::messenger()->addError(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Lab.'));
						return $msg;
					} //!user_access('research_migration bulk delete code')
					if (research_migration_abstract_delete_project($form_state['values']['research_migration_project'])) //////
					{
						\Drupal::messenger()->addStatus(t('Dis-Approved and Deleted Entire Research Migration project.'));
						// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $email_subject = t('[!site_name][Research Migration Project] Your uploaded Research Migration project have been marked as dis-approved', array(
// 						'!site_name' => variable_get('site_name', '')
// 					));

					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $email_body = array(
// 						0 => t('
// Dear ' . $user_info->contributor_name . ',
// 
// We regret to inform you that the project files submitted for the Research Migration project title: ' . $user_info->project_title . ' are disapproved by the reviewer.
// 
// Reason for dis-approval: ' . $form_state['values']['message'] . '
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE,IIT Bombay', array(
// 						'!site_name' => variable_get('site_name', ''),
// 						'!user_name' => $user_data->name
// 											))
// 					);

					$email_to = $user_data->mail;
					// @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $from = variable_get('research_migration_from_email', '');

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

					$params['standard']['subject'] = $email_subject;
					$params['standard']['body'] = $email_body;
					$params['standard']['headers'] = array(
						'From' => $from,
						'MIME-Version' => '1.0',
						'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
						'Content-Transfer-Encoding' => '8Bit',
						'X-Mailer' => 'Drupal',
						'Cc' => $cc,
						'Bcc' => $bcc
					);
					if (!drupal_mail('research_migration', 'standard', $email_to, language_default(), $params, $from, TRUE))
					{
						\Drupal::messenger()->addError('Error sending email message.');
					}
					} //research_migration_abstract_delete_project($form_state['values']['research_migration_project'])
					else
					{
						\Drupal::messenger()->addError(t('Error Dis-Approving and Deleting Entire Research Migration project.'));
					}
					// email 
					
				} //$form_state['values']['research_migration_actions'] == 3
				
			} //user_access('research_migration project bulk manage code')
		return $msg;
	} //$form_state['clicked_button']['#value'] == 'Submit'
}
/**********************************************************/
function _bulk_list_of_research_migration_project()
{
	$project_titles = array(
		'0' => 'Please select...'
	);
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('is_submitted', 1);
	$query->condition('approval_status', 1);
	$query->orderBy('project_title', 'ASC');
	$project_titles_q = $query->execute();
	while ($project_titles_data = $project_titles_q->fetchObject())
	{
		$project_titles[$project_titles_data->id] = $project_titles_data->project_title . ' (Proposed by ' . $project_titles_data->contributor_name . ')';
	} //$project_titles_data = $project_titles_q->fetchObject()
	return $project_titles;
}
function _bulk_list_research_migration_actions()
{
	$research_migration_actions = array(
		0 => 'Please select...'
	);
	$research_migration_actions[1] = 'Approve Entire Research Migration Project';
	$research_migration_actions[2] = 'Resubmit Project files';
	$research_migration_actions[3] = 'Dis-Approve Entire Research Migration Project (This will delete Research Migration Project)';
	//$research_migration_actions[4] = 'Delete Entire Research Migration Project Including Proposal';
	return $research_migration_actions;
}
function _research_migration_details($research_migration_proposal_id)
{
	$return_html = "";
	$query_pro = \Drupal::database()->select('research_migration_proposal');
	$query_pro->fields('research_migration_proposal');
	$query_pro->condition('id', $research_migration_proposal_id);
	$abstracts_pro = $query_pro->execute()->fetchObject();
	//var_dump($abstracts_pro);die;
	$query_pdf = \Drupal::database()->select('research_migration_submitted_abstracts_file');
	$query_pdf->fields('research_migration_submitted_abstracts_file');
	$query_pdf->condition('proposal_id', $research_migration_proposal_id);
	$query_pdf->condition('filetype', 'A');
	$abstracts_pdf = $query_pdf->execute()->fetchObject();
	if ($abstracts_pdf == TRUE)
	{
		if ($abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != "")
		{
			$abstract_filename = $abstracts_pdf->filename;
		} //$abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != ""
		else
		{
			$abstract_filename = "File not uploaded";
		}
	} //$abstracts_pdf == TRUE
	else
	{
		$abstract_filename = "File not uploaded";
	}
	$query_process = \Drupal::database()->select('research_migration_submitted_abstracts_file');
	$query_process->fields('research_migration_submitted_abstracts_file');
	$query_process->condition('proposal_id', $research_migration_proposal_id);
	$query_process->condition('filetype', 'S');
	$abstracts_query_process = $query_process->execute()->fetchObject();
	$query = \Drupal::database()->select('research_migration_submitted_abstracts');
	$query->fields('research_migration_submitted_abstracts');
	$query->condition('proposal_id', $research_migration_proposal_id);
	$abstracts_q = $query->execute()->fetchObject();
	if ($abstracts_q)
	{
		if ($abstracts_q->is_submitted == 0)
		{
			//drupal_set_message(t('Abstract is not submmited yet.'), 'error', $repeat = FALSE);
			//return;
		} //$abstracts_q->is_submitted == 0
	} //$abstracts_q
	//var_dump($abstracts_query_process);die;
	if ($abstracts_query_process == TRUE)
	{
		if ($abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != "")
		{
			$abstracts_query_process_filename = $abstracts_query_process->filename;
		} //$abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != ""
		else
		{
			$abstracts_query_process_filename = "File not uploaded";
		}
	} //$abstracts_query_process == TRUE
	else
	{
		// @FIXME
// l() expects a Url object, created from a route name or external URI.
// $url = l('Upload abstract', 'research-migration-project/abstract-code/upload');

		$abstracts_query_process_filename = "File not uploaded";
	}
	// @FIXME
// l() expects a Url object, created from a route name or external URI.
// $download_research_migration = l('Download Research Migration project','research-migration-project/full-download/project/'.$research_migration_proposal_id);

	$return_html .= '<strong>Proposer Name:</strong><br />' . $abstracts_pro->name_title . ' ' . $abstracts_pro->contributor_name . '<br /><br />';
	$return_html .= '<strong>Title of the Research Migration Project:</strong><br />' . $abstracts_pro->project_title . '<br /><br />';
	$return_html .= '<strong>Uploaded an abstract (brief outline) of the project:</strong><br />' . $abstract_filename . '<br /><br />';
	$return_html .= '<strong>Uploaded Case Directory Folder:</strong><br />' . $abstracts_query_process_filename . '<br /><br />';
	$return_html .= $download_research_migration;
	return $return_html;
}

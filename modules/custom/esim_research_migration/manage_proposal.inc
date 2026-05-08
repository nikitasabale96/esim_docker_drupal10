<?php
// $Id$
function esim_research_migration_proposal_pending()
{
    /* get pending proposals to be approved */
    $pending_rows = array();
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('approval_status', 0);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject()) {
        // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $pending_rows[$pending_data->id] = array(
//             date('d-m-Y', $pending_data->creation_date),
//             l($pending_data->name_title . ' ' . $pending_data->contributor_name, 'user/' . $pending_data->uid),
//             $pending_data->project_title,
//             l('Approve', 'research-migration-project/manage-proposal/approve/' . $pending_data->id) . ' | ' . l('Edit', 'research-migration-project/manage-proposal/edit/' . $pending_data->id),
//         );

    } //$pending_data = $pending_q->fetchObject()
    /* check if there are any pending proposals */
    if (!$pending_rows) {
        \Drupal::messenger()->addStatus(t('There are no pending proposals.'));
        return '';
    } //!$pending_rows
    $pending_header = array(
        'Date of Submission',
        'Student Name',
        'Title of the Research Migration Project',
        'Action',
    );
    //$output = theme_table($pending_header, $pending_rows);
    // @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
// 
// 
// @see https://www.drupal.org/node/2195739
// $output = theme('table', array(
//         'header' => $pending_header,
//         'rows' => $pending_rows,
//     ));

    return $output;
}
function esim_research_migration_proposal_all()
{
    /* get pending proposals to be approved */
    $proposal_rows = array();
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->orderBy('id', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject()) {
        $approval_status = '';
        switch ($proposal_data->approval_status) {
            case 0:
                $approval_status = 'Pending';
                break;
            case 1:
                $approval_status = 'Approved';
                break;
            case 2:
                $approval_status = 'Dis-approved';
                break;
            case 3:
                $approval_status = 'Completed';
                break;
            case 5:
                $approval_status = 'On Hold';
                break;
            default:
                $approval_status = 'Unknown';
                break;
        } //$proposal_data->approval_status
        if ($proposal_data->actual_completion_date == 0) {
            $actual_completion_date = "Not Completed";
        } //$proposal_data->actual_completion_date == 0
        else {
            $actual_completion_date = date('d-m-Y', $proposal_data->actual_completion_date);
        }
        if ($proposal_data->approval_date == 0) {
            $approval_date = "Not Approved";
        } //$proposal_data->actual_completion_date == 0
        else {
            $approval_date = date('d-m-Y', $proposal_data->approval_date);
        }
        // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $proposal_rows[] = array(
//             date('d-m-Y', $proposal_data->creation_date),
//             l($proposal_data->contributor_name, 'user/' . $proposal_data->uid),
//             $proposal_data->project_title,
//             $approval_date,
//             $actual_completion_date,
//             $approval_status,
//             l('Status', 'research-migration-project/manage-proposal/status/' . $proposal_data->id) . ' | ' . l('Edit', 'research-migration-project/manage-proposal/edit/' . $proposal_data->id),
//         );

    } //$proposal_data = $proposal_q->fetchObject()
    /* check if there are any pending proposals */
    if (!$proposal_rows) {
        \Drupal::messenger()->addStatus(t('There are no proposals.'));
        return '';
    } //!$proposal_rows
    $proposal_header = array(
        'Date of Submission',
        'Student Name',
        'Title of the Research Migration project',
        'Date of Approval',
        'Date of Project Completion',
        'Status',
        'Action',
    );
    // @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
// 
// 
// @see https://www.drupal.org/node/2195739
// $output = theme('table', array(
//         'header' => $proposal_header,
//         'rows' => $proposal_rows,
//     ));

    return $output;
}
function esim_research_migration_proposal_edit_file_all()
{
    /* get pending proposals to be approved */
    $proposal_rows = array();
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->orderBy('id', 'DESC');
    $query->condition('approval_status', '0', '<>');
    $query->condition('approval_status', '1', '<>');
    $query->condition('approval_status', '2', '<>');
    $query->orderBy('approval_status', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject()) {
        $approval_status = '';
        switch ($proposal_data->approval_status) {
            case 0:
                $approval_status = 'Pending';
                break;
            case 1:
                $approval_status = 'Approved';
                break;
            case 2:
                $approval_status = 'Dis-approved';
                break;
            case 3:
                $approval_status = 'Completed';
                break;
            case 5:
                $approval_status = 'On Hold';
                break;
            default:
                $approval_status = 'Unknown';
                break;
        } //$proposal_data->approval_status
        if ($proposal_data->actual_completion_date == 0) {
            $actual_completion_date = "Not Completed";
        } //$proposal_data->actual_completion_date == 0
        else {
            $actual_completion_date = date('d-m-Y', $proposal_data->actual_completion_date);
        }
        if ($proposal_data->approval_date == 0) {
            $approval_date = "Not Approved";
        } //$proposal_data->actual_completion_date == 0
        else {
            $approval_date = date('d-m-Y', $proposal_data->approval_date);
        }
        // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $proposal_rows[] = array(
//             date('d-m-Y', $proposal_data->creation_date),
//             l($proposal_data->contributor_name, 'user/' . $proposal_data->uid),
//             $proposal_data->project_title,
//             $approval_date,
//             $actual_completion_date,
//             $approval_status,
//             l('Edit', 'research-migration-project/abstract-code/edit-upload-files/' . $proposal_data->id),
//         );

    } //$proposal_data = $proposal_q->fetchObject()
    /* check if there are any pending proposals */
    if (!$proposal_rows) {
        \Drupal::messenger()->addStatus(t('There are no proposals.'));
        return '';
    } //!$proposal_rows
    $proposal_header = array(
        'Date of Submission',
        'Student Name',
        'Title of the Research Migration project',
        'Date of Approval',
        'Date of Project Completion',
        'Status',
        'Action',
    );
    // @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
// 
// 
// @see https://www.drupal.org/node/2195739
// $output = theme('table', array(
//         'header' => $proposal_header,
//         'rows' => $proposal_rows,
//     ));

    return $output;
}
/******************************************************************************/
/************************** PROPOSAL APPROVAL FORM ****************************/
/******************************************************************************/
function esim_research_migration_proposal_approval_form($form, &$form_state)
{
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    // $query_abstract = db_select('research_migration_submitted_abstracts_file');
    // $query_abstract->fields('research_migration_submitted_abstracts_file');
    // $query_abstract->condition('proposal_id', $proposal_id);
    // $query_abstract->condition('filetype', 'A');
    // $query_abstract_pdf = $query_abstract->execute()->fetchObject();
    if ($proposal_q) {
        if ($proposal_data = $proposal_q->fetchObject()) {
            /* everything ok */
        } //$proposal_data = $proposal_q->fetchObject()
        else {
            \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
            drupal_goto('research-migration-project/manage-proposal');
            return;
        }
    } //$proposal_q
    else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('research-migration-project/manage-proposal');
        return;
    }
    if ($proposal_data->faculty_name == '') {
        $faculty_name = 'NA';
    } else {
        $faculty_name = $proposal_data->faculty_name;
    }
    if ($proposal_data->faculty_department == '') {
        $faculty_department = 'NA';
    } else {
        $faculty_department = $proposal_data->faculty_department;
    }
    if ($proposal_data->faculty_email == '') {
        $faculty_email = 'NA';
    } else {
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

    $form['student_email_id'] = array(
        '#title' => t('Student Email'),
        '#type' => 'item',
        '#markup' => \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid)->mail,
        '#title' => t('Email'),
    );
    $form['contributor_contact_no'] = array(
        '#title' => t('Contact No.'),
        '#type' => 'item',
        '#markup' => $proposal_data->contact_no,
    );
    $form['university'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->university,
        '#title' => t('University/Institute'),
    );
    $form['how_did_you_know_about_project'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->how_did_you_know_about_project,
        '#title' => t('How did you know about the project'),
    );
    $form['faculty_name'] = array(
        '#type' => 'item',
        '#markup' => $faculty_name,
        '#title' => t('Name of the faculty'),
    );
    $form['faculty_department'] = array(
        '#type' => 'item',
        '#markup' => $faculty_department,
        '#title' => t('Department of the faculty'),
    );
    $form['faculty_email'] = array(
        '#type' => 'item',
        '#markup' => $faculty_email,
        '#title' => t('Email of the faculty'),
    );
    $form['country'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->country,
        '#title' => t('Country'),
    );
    $form['all_state'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->state,
        '#title' => t('State'),
    );
    $form['city'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->city,
        '#title' => t('City'),
    );
    $form['pincode'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->pincode,
        '#title' => t('Pincode/Postal code'),
    );
    $form['project_title'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->project_title,
        '#title' => t('Title of the Research Migration Project'),
    );
   
    $form['source_of_the_project'] = array(
		'#type' => 'item',
		'#title' => t('Source of the Project'),
        '#default_value' => $proposal_data->source_of_the_project,
		'#disabled' => TRUE,
	);
    $form['date_of_proposal'] = array(
        '#type' => 'textfield',
        '#title' => t('Date of Proposal'),
        '#default_value' => date('d/m/Y', $proposal_data->creation_date),
        '#disabled' => true,
    );
    $form['expected_completion_date'] = array(
        '#type' => 'textfield',
        '#title' => t('Expected Date of Completion'),
        '#default_value' => date('d/m/Y', $proposal_data->expected_date_of_completion),
        '#disabled' => true,
    );
        if (($proposal_data->samplefilepath != "") && ($proposal_data->samplefilepath != 'NULL'))
        {
            $str = substr($proposal_data->samplefilepath,strrpos($proposal_data->samplefilepath, '/'));
            $resource_file =ltrim($str, '/');
        // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $form['samplefilepath'] = array(
//             '#type' => 'item',
//             '#title' => t('Synopsis file '),
//             '#markup' => l($resource_file, 'research-migration-project/download/project-file/' . $proposal_id) . "",
//         );

    } //$proposal_data->user_defined_compound_filepath != ""
    else {
        $form['samplefilepath'] = array(
            '#type' => 'item',
            '#title' => t('Synopsis file '),
            '#markup' => "Not uploaded<br><br>",
        );
    }
    $form['approval'] = array(
        '#type' => 'radios',
        '#title' => t('esim research migration proposal'),
        '#options' => array(
            '1' => 'Approve',
            '2' => 'Disapprove',
        ),
        '#required' => true,
    );
    $form['message'] = array(
        '#type' => 'textarea',
        '#title' => t('Reason for disapproval'),
        '#attributes' => array(
            'placeholder' => t('Enter reason for disapproval in minimum 30 characters '),
            'cols' => 50,
            'rows' => 4,
        ),
        '#states' => array(
            'visible' => array(
                ':input[name="approval"]' => array(
                    'value' => '2',
                ),
            ),
        ),
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit'),
    );
    // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $form['cancel'] = array(
//         '#type' => 'item',
//         '#markup' => l(t('Cancel'), 'research-migration-project/manage-proposal'),
//     );

    return $form;
}
function esim_research_migration_proposal_approval_form_validate($form, &$form_state)
{
    if ($form_state['values']['approval'] == 2) {
        if ($form_state['values']['message'] == '') {
            form_set_error('message', t('Reason for disapproval could not be empty'));
        } //$form_state['values']['message'] == ''
    } //$form_state['values']['approval'] == 2
}
function esim_research_migration_proposal_approval_form_submit($form, &$form_state)
{
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
        if ($proposal_data = $proposal_q->fetchObject()) {
            /* everything ok */
        } //$proposal_data = $proposal_q->fetchObject()
        else {
            \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
            drupal_goto('research-migration-project/manage-proposal');
            return;
        }
    } //$proposal_q
    else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('research-migration-project/manage-proposal');
        return;
    }
    if ($form_state['values']['approval'] == 1) {
        $query = "UPDATE {research_migration_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 1 WHERE id = :proposal_id";
        $args = array(
            ":uid" => $user->uid,
            ":date" => time(),
            ":proposal_id" => $proposal_id,
        );
        \Drupal::database()->query($query, $args);
        /* sending email */
        $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
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
// $bcc = $user->mail . ', ' . variable_get('research_migration_emails', '');

        // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $cc = variable_get('research_migration_cc_emails', '');

        $params['research_migration_proposal_approved']['proposal_id'] = $proposal_id;
        $params['research_migration_proposal_approved']['user_id'] = $proposal_data->uid;
        $params['research_migration_proposal_approved']['headers'] = array(
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc,
        );
        if (!drupal_mail('research_migration', 'research_migration_proposal_approved', $email_to, language_default(), $params, $from, true)) {
            \Drupal::messenger()->addError('Error sending email message.');
        }

        \Drupal::messenger()->addStatus('eSim Research Migration with proposal No. ' . $proposal_id . ' has been approved. The contributor has been notified of the approval');
        drupal_goto('research-migration-project/manage-proposal');
        return;
    } //$form_state['values']['approval'] == 1
    else if ($form_state['values']['approval'] == 2) {
        $query = "UPDATE {research_migration_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 2, dissapproval_reason = :dissapproval_reason WHERE id = :proposal_id";
        $args = array(
            ":uid" => $user->uid,
            ":date" => time(),
            ":dissapproval_reason" => $form_state['values']['message'],
            ":proposal_id" => $proposal_id,
        );
        $result = \Drupal::database()->query($query, $args);
        /* sending email */
        $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
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
// $bcc = $user->mail . ', ' . variable_get('research_migration_emails', '');

        // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $cc = variable_get('research_migration_cc_emails', '');

        $params['research_migration_proposal_disapproved']['proposal_id'] = $proposal_id;
        $params['research_migration_proposal_disapproved']['user_id'] = $proposal_data->uid;
        $params['research_migration_proposal_disapproved']['headers'] = array(
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc,
        );
        if (!drupal_mail('research_migration', 'research_migration_proposal_disapproved', $email_to, language_default(), $params, $from, true)) {
            \Drupal::messenger()->addError('Error sending email message.');
        }

        \Drupal::messenger()->addError('eSim Research Migration with Proposal No. ' . $proposal_id . ' has been disapproved. The contributor has been notified of the disapproval.');
        drupal_goto('research-migration-project/manage-proposal');
        return;
    } //$form_state['values']['approval'] == 2
}
/******************************************************************************/
/*************************** PROPOSAL STATUS FORM *****************************/
/******************************************************************************/
function esim_research_migration_proposal_status_form($form, &$form_state)
{
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    // $query_abstract = db_select('research_migration_submitted_abstracts_file');
    // $query_abstract->fields('research_migration_submitted_abstracts_file');
    // $query_abstract->condition('proposal_id', $proposal_id);
    // $query_abstract->condition('filetype', 'A');
    // $query_abstract_pdf = $query_abstract->execute()->fetchObject();
    if ($proposal_q) {
        if ($proposal_data = $proposal_q->fetchObject()) {
            /* everything ok */
        } //$proposal_data = $proposal_q->fetchObject()
        else {
            \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
            drupal_goto('research-migration-project/manage-proposal');
            return;
        }
    } //$proposal_q
    else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('research-migration-project/manage-proposal');
        return;
    }
    if ($proposal_data->faculty_name == '') {
        $faculty_name = 'NA';
    } else {
        $faculty_name = $proposal_data->faculty_name;
    }
    if ($proposal_data->faculty_department == '') {
        $faculty_department = 'NA';
    } else {
        $faculty_department = $proposal_data->faculty_department;
    }
    if ($proposal_data->faculty_email == '') {
        $faculty_email = 'NA';
    } else {
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

    $form['student_email_id'] = array(
        '#title' => t('Student Email'),
        '#type' => 'item',
        '#markup' => \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid)->mail,
        '#title' => t('Email'),
    );
    $form['university'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->university,
        '#title' => t('University/Institute'),
    );
    $form['how_did_you_know_about_project'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->how_did_you_know_about_project,
        '#title' => t('How did you know about the project'),
    );
    $form['faculty_name'] = array(
        '#type' => 'item',
        '#markup' => $faculty_name,
        '#title' => t('Name of the faculty'),
    );
    $form['faculty_department'] = array(
        '#type' => 'item',
        '#markup' => $faculty_department,
        '#title' => t('Department of the faculty'),
    );
    $form['faculty_email'] = array(
        '#type' => 'item',
        '#markup' => $faculty_email,
        '#title' => t('Email of the faculty'),
    );
    $form['country'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->country,
        '#title' => t('Country'),
    );
    $form['all_state'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->state,
        '#title' => t('State'),
    );
    $form['city'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->city,
        '#title' => t('City'),
    );
    $form['pincode'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->pincode,
        '#title' => t('Pincode/Postal code'),
    );
    $form['project_title'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->project_title,
        '#title' => t('Title of the Research Migration Project'),
    );
    $form['source_of_the_project'] = array(
		'#type' => 'item',
		'#title' => t('Source of the Project'),
        '#markup' => $proposal_data->source_of_the_project,
       
		
	);
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
                $approval_status = t('On Hold');
                break;
        default:
            $proposal_status = t('Unkown');
            break;
    }
    $form['proposal_status'] = array(
        '#type' => 'item',
        '#markup' => $proposal_status,
        '#title' => t('Proposal Status'),
    );
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
        $form['completed'] = array(
            '#type' => 'checkbox',
            '#title' => t('Completed'),
            '#description' => t('Check if user has provided all the required files and pdfs.'),
        );
    } //$proposal_data->approval_status == 1
    if ($proposal_data->approval_status == 2) {
        $form['message'] = array(
            '#type' => 'item',
            '#markup' => $proposal_data->message,
            '#title' => t('Reason for disapproval'),
        );
    } //$proposal_data->approval_status == 2
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit'),
    );
    // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $form['cancel'] = array(
//         '#type' => 'markup',
//         '#markup' => l(t('Cancel'), 'research-migration-project/manage-proposal/all'),
//     );

    return $form;
}
function esim_research_migration_proposal_status_form_submit($form, &$form_state)
{
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    //$proposal_q = db_query("SELECT * FROM {research_migration_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
        if ($proposal_data = $proposal_q->fetchObject()) {
            /* everything ok */
        } //$proposal_data = $proposal_q->fetchObject()
        else {
            \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
            drupal_goto('research-migration-project/manage-proposal');
            return;
        }
    } //$proposal_q
    else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('research-migration-project/manage-proposal');
        return;
    }
    /* set the book status to completed */
    if ($form_state['values']['completed'] == 1) {
        $up_query = "UPDATE research_migration_proposal SET approval_status = :approval_status , actual_completion_date = :expected_completion_date WHERE id = :proposal_id";
        $args = array(
            ":approval_status" => '3',
            ":proposal_id" => $proposal_id,
            ":expected_completion_date" => time(),
        );
        $result = \Drupal::database()->query($up_query, $args);
        CreateReadmeFileResearchMigrationProject($proposal_id);
        if (!$result) {
            \Drupal::messenger()->addError('Error in update status');
            return;
        } //!$result
        /* sending email */
        $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
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
// $bcc = $user->mail . ', ' . variable_get('research_migration_emails', '');

        // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $cc = variable_get('research_migration_cc_emails', '');

        $params['research_migration_proposal_completed']['proposal_id'] = $proposal_id;
        $params['research_migration_proposal_completed']['user_id'] = $proposal_data->uid;
        $params['research_migration_proposal_completed']['headers'] = array(
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc,
        );
        if (!drupal_mail('research_migration', 'research_migration_proposal_completed', $email_to, language_default(), $params, $from, true)) {
            \Drupal::messenger()->addError('Error sending email message.');
        }

        \Drupal::messenger()->addStatus('eSim Research Migration proposal has been marked completed. The contributor is notified of the Completion.');
    }
    drupal_goto('research-migration-project/manage-proposal');
    return;

}
/******************************************************************************/
/**************************** PROPOSAL EDIT FORM ******************************/
/******************************************************************************/
function esim_research_migration_proposal_edit_form($form, &$form_state)
{
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    //$proposal_q = db_query("SELECT * FROM {research_migration_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
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
    $form['name_title'] = array(
        '#type' => 'select',
        '#title' => t('Title'),
        '#options' => array(
            'Dr' => 'Dr',
            'Prof' => 'Prof',
            'Mr' => 'Mr',
            'Ms' => 'Ms',
        ),
        '#required' => true,
        '#default_value' => $proposal_data->name_title,
    );
    $form['contributor_name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name of the Proposer'),
        '#size' => 30,
        '#maxlength' => 50,
        '#required' => true,
        '#default_value' => $proposal_data->contributor_name,
    );
    $form['student_email_id'] = array(
        '#type' => 'item',
        '#title' => t('Email'),
        '#markup' => $user_data->mail,
    );
    $form['university'] = array(
        '#type' => 'textfield',
        '#title' => t('University'),
        '#size' => 200,
        '#maxlength' => 200,
        '#required' => true,
        '#default_value' => $proposal_data->university,
    );
    $form['institute'] = array(
        '#type' => 'textfield',
        '#title' => t('Institute'),
        '#size' => 80,
        '#maxlength' => 200,
        '#required' => true,
        '#default_value' => $proposal_data->institute,
    );
    $form['how_did_you_know_about_project'] = array(
        '#type' => 'textfield',
        '#title' => t('How did you come to know about the Research Migration Project?'),
        '#default_value' => $proposal_data->how_did_you_know_about_project,
        '#required' => true,
    );
    $form['faculty_name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name of the Faculty'),
        '#size' => 50,
        '#maxlength' => 50,
        '#validated' => true,
        '#default_value' => $proposal_data->faculty_name,
    );
    $form['faculty_department'] = array(
        '#type' => 'textfield',
        '#title' => t('Department of the Faculty'),
        '#size' => 50,
        '#maxlength' => 50,
        '#validated' => true,
        '#default_value' => $proposal_data->faculty_department,
    );
    $form['faculty_email'] = array(
        '#type' => 'textfield',
        '#title' => t('Email id of the Faculty'),
        '#size' => 255,
        '#maxlength' => 255,
        '#validated' => true,
        '#default_value' => $proposal_data->faculty_email,
    );
    $form['project_title'] = array(
        '#type' => 'textfield',
        '#title' => t('Title of the Research Migration Project'),
        '#size' => 300,
        '#maxlength' => 350,
        '#required' => true,
        '#default_value' => $proposal_data->project_title,
    );
    $form['source_of_the_project'] = array(
		'#type' => 'textarea',
		'#title' => t('Source of the Project'),
        '#default_value' => $proposal_data->source_of_the_project,
        // '#disabled' => TRUE,
		
	);
   /* $form['solver_used'] = array(
        '#type' => 'textfield',
        '#title' => t('Solver to be used'),
        '#size' => 50,
        '#maxlength' => 50,
        '#required' => true,
        '#default_value' => $proposal_data->solver_used,
    );*/
    $form['date_of_proposal'] = array(
        '#type' => 'textfield',
        '#title' => t('Date of Proposal'),
        '#default_value' => date('d/m/Y', $proposal_data->creation_date),
        '#disabled' => true,
    );
    $form['delete_proposal'] = array(
        '#type' => 'checkbox',
        '#title' => t('Delete Proposal'),
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit'),
    );
    // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $form['cancel'] = array(
//         '#type' => 'item',
//         '#markup' => l(t('Cancel'), 'research-migration-project/manage-proposal'),
//     );

    return $form;
}

// function ajax_solver_used_callback($form, $form_state){
//     $simulation_id = isset($form_state['values']['simulation_type']) ? $form_state['values']['simulation_type'] : key($simulation_type_options);
//     if($simulation_id < 19){
//         $form['solver_used']['#options'] = _cs_list_of_solvers($version_id, $simulation_id);
//         $form['solver_used']['#required'] = TRUE;
//         $form['solver_used']['#validated'] = TRUE;
//         $commands[] = ajax_command_replace('#ajax-solver-replace', drupal_render($form['solver_used']));
//         $commands[] = ajax_command_html('#ajax-solver-text-replace', '');
//     }
//     else{
//         $commands[] = ajax_command_html('#ajax-solver-replace', '');
//         $form['solver_used_text']['#required'] = TRUE;
//         $form['solver_used_text']['#validated'] = TRUE;
//         $commands[] = ajax_command_replace('#ajax-solver-text-replace', drupal_render($form['solver_used_text']));
//     }
//     return array(
//         '#type' => 'ajax',
//         '#commands' => $commands
//     );
// }
// function esim_research_migration_proposal_edit_form_validate($form, $form_state){
//     if ($form_state['values']['simulation_type'] < 19) {
//         if($form_state['values']['solver_used'] == '0'){
//             form_set_error('solver_used', t('Please select an option'));
//         }
//     }
//     else if ($form_state['values']['simulation_type'] == 19) {
//     if ($form_state['values']['solver_used_text'] != '')
//     {
//         if (strlen($form_state['values']['solver_used_text']) > 100)
//         {
//             form_set_error('solver_used_text', t('Maximum charater limit is 100 charaters only, please check the length of the solver used'));
//         } //strlen($form_state['values']['project_title']) > 250
//         else if (strlen($form_state['values']['solver_used_text']) < 7)
//         {
//             form_set_error('solver_used_text', t('Minimum charater limit is 7 charaters, please check the length of the solver used'));
//         } //strlen($form_state['values']['project_title']) < 10
//     } 
//     else
//     {
//         form_set_error('solver_used_text', t('Solver used cannot be empty'));
//     }
//     }
// }
function esim_research_migration_proposal_edit_form_submit($form, &$form_state)
{
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    $query = \Drupal::database()->select('research_migration_proposal');
    $query->fields('research_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
        if ($proposal_data = $proposal_q->fetchObject()) {
            /* everything ok */
        } //$proposal_data = $proposal_q->fetchObject()
        else {
            \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
            drupal_goto('research-migration-project/manage-proposal');
            return;
        }
    } //$proposal_q
    else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('research-migration-project/manage-proposal');
        return;
    }
    /* delete proposal */
    if ($form_state['values']['delete_proposal'] == 1) {
        /* sending email */
        $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
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

        $params['research_migration_proposal_deleted']['proposal_id'] = $proposal_id;
        $params['research_migration_proposal_deleted']['user_id'] = $proposal_data->uid;
        $params['research_migration_proposal_deleted']['headers'] = array(
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc,
        );
        if (!drupal_mail('research_migration', 'research_migration_proposal_deleted', $email_to, user_preferred_language($user), $params, $from, true)) {
            \Drupal::messenger()->addError('Error sending email message.');
        }

        \Drupal::messenger()->addStatus(t('The Research Migration proposal has been deleted.'));
        if (_rm_rrmdir_project($proposal_id) == true) {
            $query = \Drupal::database()->delete('research_migration_proposal');
            $query->condition('id', $proposal_id);
            $num_deleted = $query->execute();
            \Drupal::messenger()->addStatus(t('Proposal Deleted'));
            drupal_goto('research-migration-project/manage-proposal');
            return;
        } //rrmdir_project($proposal_id) == TRUE
    } //$form_state['values']['delete_proposal'] == 1
    /* update proposal */
    $v = $form_state['values'];
    $project_title = $v['project_title'];
    $proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
    $university = $v['university'];
    $directory_names = _rm_dir_name($project_title, $proposar_name);
	if (_rm_RenameDir($proposal_id, $directory_names)) {
        $directory_name = $directory_names;
    } //LM_RenameDir($proposal_id, $directory_names)
    else {
        return;
    }
    $str = substr($proposal_data->samplefilepath,strrpos($proposal_data->samplefilepath, '/'));
	$resource_file =ltrim($str, '/');
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
    $args = array(
        ':name_title' => $v['name_title'],
        ':contributor_name' => $v['contributor_name'],
        ':university' => $v['university'],
        ":institute" => $v['institute'],
        ":how_did_you_know_about_project" => $v['how_did_you_know_about_project'],
        ":faculty_name" => $v['faculty_name'],
        ":faculty_department" => $v['faculty_department'],
        ":faculty_email" => $v['faculty_email'],
        ':project_title' => $project_title,
        ':source_of_the_project' => $v['source_of_the_project'],
        ':directory_name' => $directory_name,
        ':samplefilepath' => $samplefilepath,
        ':proposal_id' => $proposal_id,
    );
    $result = \Drupal::database()->query($query, $args);
    \Drupal::messenger()->addStatus(t('Proposal Updated'));
}

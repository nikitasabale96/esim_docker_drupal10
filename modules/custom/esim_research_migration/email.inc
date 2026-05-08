<?php
/**
 * Implementation of hook_mail().
 */
function old_research_migration_mail($key, &$message, $params)
{
    $user = \Drupal::currentUser();
    $language = $message['language'];
    //$language = user_preferred_language($user);
    switch ($key) {
        case 'research_migration_proposal_received':
            /* initializing data */
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_received']['result1']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_received']['user_id']);
            $message['headers'] = $params['research_migration_proposal_received']['headers'];
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been received', array(
//                 '!site_name' => variable_get('site_name', ''),
//             ), array(
//                 'language' => $language->language,
//             ));

            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['body'] = array(
//                 'body' => t('
// Dear ' . $proposal_data->contributor_name . ',
// 
// We have received your Research Migration Project proposal with the following details:
// 
// Full Name: ' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '
// Email: ' . $user->mail . '
// University/Institute: ' . $proposal_data->university . '
// City: ' . $proposal_data->city . '
// State: ' . $proposal_data->state . '
// Country: ' . $proposal_data->country . '
// Project Title: ' . $proposal_data->project_title . '
// Date of Proposal: ' . date('d/m/Y', $proposal_data->creation_date) . '
// Expected Date of Completion: ' . date('d/m/Y', $proposal_data->expected_date_of_completion) . '
// 
// Your proposal is under review. You will soon receive an email when same has been approved/disapproved.
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE, IIT Bombay', array(
//                     '!site_name' => variable_get('site_name', ''),
//                     '!user_name' => $user->name,
//                 ), array(
//                     'language' => $language->language,
//                 )),
//             );

            break;
        case 'research_migration_proposal_disapproved':
            /* initializing data */
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_disapproved']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_disapproved']['user_id']);
            //$user = user_load($params['research_migration_proposal']['name']);
            $message['headers'] = $params['research_migration_proposal_disapproved']['headers'];
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been disapproved', array(
//                 '!site_name' => variable_get('site_name', ''),
//             ), array(
//                 'language' => $language->language,
//             ));

            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['body'] = array(
//                 'body' => t('
// Dear ' . $proposal_data->contributor_name . ',
// 
// We regret to inform you that your Research Migration proposal with following details have been disapproved:
// 
// Full Name: ' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '
// Email: ' . $user_data->mail . '
// University/Institute: ' . $proposal_data->university . '
// City: ' . $proposal_data->city . '
// State: ' . $proposal_data->state . '
// Country: ' . $proposal_data->country . '
// Project Title: ' . $proposal_data->project_title . '
// Date of Proposal: ' . date('d/m/Y', $proposal_data->creation_date) . '
// Expected Date of Completion: ' . date('d/m/Y', $proposal_data->expected_date_of_completion) . '
// 
// Reason for rejection: ' . $proposal_data->dissapproval_reason . '
// 
// 
// You are welcome to submit a new proposal.
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE, IIT Bombay', array(
//                     '!site_name' => variable_get('site_name', ''),
//                     '!user_name' => $user_data->name,
//                 ), array(
//                     'language' => $language->language,
//                 )),
//             );

            break;
        case 'research_migration_proposal_approved':
            /* initializing data */
            $url = 'http://esim.fossee.in/research-migration-project/abstract-code';
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_approved']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_approved']['user_id']);
            $message['headers'] = $params['research_migration_proposal_approved']['headers'];
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been approved', array(
//                 '!site_name' => variable_get('site_name', ''),
//             ), array(
//                 'language' => $language->language,
//             ));

            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['body'] = array(
//                 'body' => t('
// Dear ' . $proposal_data->contributor_name . ',
// 
// Your Research Migration Project proposal with the following details has been approved:
// 
// Full Name: ' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '
// Email: ' . $user_data->mail . '
// University/Institute: ' . $proposal_data->university . '
// City: ' . $proposal_data->city . '
// State: ' . $proposal_data->state . '
// Country: ' . $proposal_data->country . '
// Project Title: ' . $proposal_data->project_title . '
// Date of Proposal: ' . date('d/m/Y', $proposal_data->creation_date) . '
// Expected Date of Completion: ' . date('d/m/Y', $proposal_data->expected_date_of_completion) . '
// 
// 
// You can upload your project files at: ' . $url . '
// Best Wishes,
// 
// !site_name Team,
// FOSSEE, IIT Bombay', array(
//                     '!site_name' => variable_get('site_name', ''),
//                     '!user_name' => $user_data->name,
//                 ), array(
//                     'language' => $language->language,
//                 )),
//             );

            break;
        case 'research_migration_proposal_completed':
            /* initializing data */
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_completed']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_completed']['user_id']);
            $message['headers'] = $params['research_migration_proposal_completed']['headers'];
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been completed', array(
//                 '!site_name' => variable_get('site_name', ''),
//             ), array(
//                 'language' => $language->language,
//             ));

            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['body'] = array(
//                 'body' => t('
// Dear ' . $proposal_data->contributor_name . ',
// 
// Your Research Migration Project and Synopsis on the following process have been completed successfully.
// 
// Full Name: ' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '
// Email: ' . $user_data->mail . '
// University/Institute: ' . $proposal_data->university . '
// City: ' . $proposal_data->city . '
// State: ' . $proposal_data->state . '
// Country: ' . $proposal_data->country . '
// Project Title: ' . $proposal_data->project_title . '
// Best Wishes,
// 
// !site_name Team,
// FOSSEE, IIT Bombay', array(
//                     '!site_name' => variable_get('site_name', ''),
//                     '!user_name' => $user_data->name,
//                 ), array(
//                     'language' => $language->language,
//                 )),
//             );

            break;
        case 'research_migration_proposal_deleted':
            /* initializing data */
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_deleted']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_deleted']['user_id']);
            $message['headers'] = $params['research_migration_proposal_deleted']['headers'];
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been deleted', array(
//                 '!site_name' => variable_get('site_name', ''),
//             ), array(
//                 'language' => $language->language,
//             ));

            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['body'] = array(
//                 'body' => t('
// Dear ' . $proposal_data->contributor_name . ',
// 
// Your Research Migration Project proposal with the following details has been deleted :
// 
// Full Name: ' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '
// Email: ' . $user_data->mail . '
// University/Institute: ' . $proposal_data->university . '
// City: ' . $proposal_data->city . '
// State: ' . $proposal_data->state . '
// Country: ' . $proposal_data->country . '
// Project Title: ' . $proposal_data->project_title . '
// 
// You can propose a new Research Migration Project.
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE, IIT Bombay', array(
//                     '!site_name' => variable_get('site_name', ''),
//                     '!user_name' => $user_data->name,
//                 ), array(
//                     'language' => $language->language,
//                 )),
//             );

            break;
        /**************************************************************/
        case 'abstract_uploaded':
            // $solution_q = db_query("SELECT * FROM {research_migration_solution} WHERE id = %d LIMIT 1", $params['abstract_uploaded']['solution_id']);
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['abstract_uploaded']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();

            $user = \Drupal::entityTypeManager()->getStorage('user')->load($params['abstract_uploaded']['user_id']);
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['subject'] = t('[!site_name][Research Migration Project] You have uploaded Research Migration Project files', array(
//                 '!site_name' => variable_get('site_name', ''),
//             ), array(
//                 'language' => $language->language,
//             ));

            $message['headers'] = $params['abstract_uploaded']['headers'];
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['body'] = array(
//                 'body' => t('
// Dear ' . $proposal_data->contributor_name . ',
// 
// You have uploaded the following Synopsis and Project files:
// 
// Project Title: ' . $proposal_data->project_title . '
// 
// 
// The synopsis and project files are under review. You will be notified when it has been approved.
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE, IIT Bombay', array(
//                     '!site_name' => variable_get('site_name', ''),
//                     '!user_name' => $user->name,
//                 ), array(
//                     'language' => $language->language,
//                 )),
//             );

            break;
/**************************************************************/
        case 'abstract_edit_file_uploaded':
            // $solution_q = db_query("SELECT * FROM {research_migration_solution} WHERE id = %d LIMIT 1", $params['abstract_uploaded']['solution_id']);
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['abstract_edit_file_uploaded']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();

            $user = \Drupal::entityTypeManager()->getStorage('user')->load($params['abstract_edit_file_uploaded']['user_id']);
            $abs_file_name = $params['abstract_edit_file_uploaded']['abs_file'];
            $proj_file_name = $params['abstract_edit_file_uploaded']['proj_file'];

            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['subject'] = t('[!site_name][Research Migration Project] You have updated project files/synopsis of a research migration', array(
//                 '!site_name' => variable_get('site_name', ''),
//             ), array(
//                 'language' => $language->language,
//             ));

            $message['headers'] = $params['abstract_edit_file_uploaded']['headers'];
            // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $message['body'] = array(
//                 'body' => t('
// Dear ' . $proposal_data->contributor_name . ',
// 
// You have updated the synopsis/ project files for the following research migration:
// 
// Project Title: ' . $proposal_data->project_title . '
// 
// Best Wishes,
// 
// !site_name Team,
// FOSSEE, IIT Bombay', array(
//                     '!site_name' => variable_get('site_name', ''),
//                     '!user_name' => $user->name,
//                 ), array(
//                     'language' => $language->language,
//                 )),
//             );

            break;
        case 'standard':
            $message['subject'] = $params['standard']['subject'];
            $message['body'] = $params['standard']['body'];
            $message['headers'] = $params['standard']['headers'];
            break;
    } //$key
}

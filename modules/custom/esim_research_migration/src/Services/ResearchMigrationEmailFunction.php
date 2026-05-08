<?php

namespace Drupal\esim_research_migration\Services;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Plugin\Discovery\DiscoveryInterface;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\LabMigration\PluginMail;


/**
 * Provides email functions for the Research Migration module.
 */
class ResearchMigrationEmailFunction extends PluginMail {

  /**
   * {@inheritdoc}
   */
 
function research_migration_mail($key, &$message, $params) {
    $user = \Drupal::currentUser();
    $language = $message['language'];
    // Get language properly for the current user.
    $language = $user->getPreferredLangcode();
    
    switch ($key) {
        case 'research_migration_proposal_received':
            // Initialize data
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_received']['result1']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_received']['user_id']);
            $message['headers'] = $params['research_migration_proposal_received']['headers'];
            
            // Fetch site name from configuration instead of variable_get
            $site_name = \Drupal::config('system.site')->get('name');
            $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been received', ['!site_name' => $site_name]);
            
            $message['body'] = [
                'body' => t('
                    Dear @contributor_name,

                    We have received your Research Migration Project proposal with the following details:

                    Full Name: @full_name
                    Email: @email
                    University/Institute: @university
                    City: @city
                    State: @state
                    Country: @country
                    Project Title: @project_title
                    Date of Proposal: @date_of_proposal
                    Expected Date of Completion: @expected_date

                    Your proposal is under review. You will soon receive an email when it has been approved or disapproved.

                    Best Wishes,

                    @site_name Team,
                    FOSSEE, IIT Bombay',
                [
                    '@contributor_name' => $proposal_data->contributor_name,
                    '@full_name' => $proposal_data->name_title . ' ' . $proposal_data->contributor_name,
                    '@email' => $user->getEmail(),
                    '@university' => $proposal_data->university,
                    '@city' => $proposal_data->city,
                    '@state' => $proposal_data->state,
                    '@country' => $proposal_data->country,
                    '@project_title' => $proposal_data->project_title,
                    '@date_of_proposal' => date('d/m/Y', $proposal_data->creation_date),
                    '@expected_date' => date('d/m/Y', $proposal_data->expected_date_of_completion),
                    '@site_name' => $site_name,
                ], ['language' => $language]),
            ];
            break;

        case 'research_migration_proposal_disapproved':
            // Initialize data
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_disapproved']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_disapproved']['user_id']);
            $message['headers'] = $params['research_migration_proposal_disapproved']['headers'];

            // Fetch site name from configuration instead of variable_get
            $site_name = \Drupal::config('system.site')->get('name');
            $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been disapproved', ['!site_name' => $site_name]);
            
            $message['body'] = [
                'body' => t('
                    Dear @contributor_name,

                    We regret to inform you that your Research Migration proposal with the following details has been disapproved:

                    Full Name: @full_name
                    Email: @email
                    University/Institute: @university
                    City: @city
                    State: @state
                    Country: @country
                    Project Title: @project_title
                    Date of Proposal: @date_of_proposal
                    Expected Date of Completion: @expected_date

                    Reason for rejection: @reason

                    You are welcome to submit a new proposal.

                    Best Wishes,

                    @site_name Team,
                    FOSSEE, IIT Bombay',
                [
                    '@contributor_name' => $proposal_data->contributor_name,
                    '@full_name' => $proposal_data->name_title . ' ' . $proposal_data->contributor_name,
                    '@email' => $user_data->getEmail(),
                    '@university' => $proposal_data->university,
                    '@city' => $proposal_data->city,
                    '@state' => $proposal_data->state,
                    '@country' => $proposal_data->country,
                    '@project_title' => $proposal_data->project_title,
                    '@date_of_proposal' => date('d/m/Y', $proposal_data->creation_date),
                    '@expected_date' => date('d/m/Y', $proposal_data->expected_date_of_completion),
                    '@reason' => $proposal_data->dissapproval_reason,
                    '@site_name' => $site_name,
                ], ['language' => $language]),
            ];
            break;

        case 'research_migration_proposal_approved':
            // Initialize data
            $url = 'http://esim.fossee.in/research-migration-project/abstract-code';
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_approved']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_approved']['user_id']);
            $message['headers'] = $params['research_migration_proposal_approved']['headers'];

            // Fetch site name from configuration instead of variable_get
            $site_name = \Drupal::config('system.site')->get('name');
            $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been approved', ['!site_name' => $site_name]);
            
            $message['body'] = [
                'body' => t('
                    Dear @contributor_name,

                    Your Research Migration Project proposal with the following details has been approved:

                    Full Name: @full_name
                    Email: @email
                    University/Institute: @university
                    City: @city
                    State: @state
                    Country: @country
                    Project Title: @project_title
                    Date of Proposal: @date_of_proposal
                    Expected Date of Completion: @expected_date

                    You can upload your project files at: @url

                    Best Wishes,

                    @site_name Team,
                    FOSSEE, IIT Bombay',
                [
                    '@contributor_name' => $proposal_data->contributor_name,
                    '@full_name' => $proposal_data->name_title . ' ' . $proposal_data->contributor_name,
                    '@email' => $user_data->getEmail(),
                    '@university' => $proposal_data->university,
                    '@city' => $proposal_data->city,
                    '@state' => $proposal_data->state,
                    '@country' => $proposal_data->country,
                    '@project_title' => $proposal_data->project_title,
                    '@date_of_proposal' => date('d/m/Y', $proposal_data->creation_date),
                    '@expected_date' => date('d/m/Y', $proposal_data->expected_date_of_completion),
                    '@url' => $url,
                    '@site_name' => $site_name,
                ], ['language' => $language]),
            ];
            break;

        case 'research_migration_proposal_completed':
            // Initialize data
            $query = \Drupal::database()->select('research_migration_proposal');
            $query->fields('research_migration_proposal');
            $query->condition('id', $params['research_migration_proposal_completed']['proposal_id']);
            $query->range(0, 1);
            $proposal_data = $query->execute()->fetchObject();
            $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($params['research_migration_proposal_completed']['user_id']);
            $message['headers'] = $params['research_migration_proposal_completed']['headers'];

            // Fetch site name from configuration instead of variable_get
            $site_name = \Drupal::config('system.site')->get('name');
            $message['subject'] = t('[!site_name][Research Migration Project] Your Research Migration Project proposal has been completed', ['!site_name' => $site_name]);

            $message['body'] = [
                'body' => t('
                    Dear @contributor_name,

                    Your Research Migration Project and Synopsis on the following process have been completed successfully:

                    Full Name: @full_name
                    Email: @email
                    University/Institute: @university
                    City: @city
                    State: @state
                    Country: @country
                    Project Title: @project_title

                    Best Wishes,

                    @site_name Team,
                    FOSSEE, IIT Bombay',
                [
                    '@contributor_name' => $proposal_data->contributor_name,
                    '@full_name' => $proposal_data->name_title . ' ' . $proposal_data->contributor_name,
                    '@email' => $user_data->getEmail(),
                    '@university' => $proposal_data->university,
                    '@city' => $proposal_data->city,
                    '@state' => $proposal_data->state,
                    '@country' => $proposal_data->country,
                    '@project_title' => $proposal_data->project_title,
                    '@site_name' => $site_name,
                ], ['language' => $language]),
            ];
            break;
    }
}

  }


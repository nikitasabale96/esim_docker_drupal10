<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Form\EsimResearchMigrationRunForm.
 */

namespace Drupal\esim_research_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

class EsimResearchMigrationRunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esim_research_migration_run_form';
  }

public function buildForm(array $form, FormStateInterface $form_state) {
  $options_first = _list_of_research_migration();
  $route_match = \Drupal::routeMatch();
  $url_research_migration_id = (int) $route_match->getParameter('proposal_id');

  $research_migration_data = _research_migration_information($url_research_migration_id);
  if ($research_migration_data === 'Not found') {
    $url_research_migration_id = '';
  }

  if (!$url_research_migration_id) {
    $selected = $form_state->getValue('research_migration') ?? key($options_first);
  }
  elseif ($url_research_migration_id === '') {
    $selected = 0;
  }
  else {
    $selected = $url_research_migration_id;
  }

  $form['research_migration'] = [
    '#type' => 'select',
    '#title' => $this->t('Title of the research migration'),
    '#options' => $options_first,
    '#default_value' => $selected,
    '#ajax' => [
      'callback' => '::researchMigrationProjectDetailsCallback',
      'wrapper' => 'ajax_research_migration_details',
    ],
  ];

  if (!$url_research_migration_id) {
    $form['research_migration_details'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax_research_migration_details'],
    ];
    $form['selected_research_migration'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax_selected_research_migration'],
    ];
  }
  else {
    $form['research_migration_details'] = [
      '#type' => 'markup',
      '#markup' => '<div id="ajax_research_migration_details">' . $this->_research_migration_details($url_research_migration_id) . '</div>',
    ];

    $link1 = Link::fromTextAndUrl('Download Synopsis', Url::fromUri('internal:/research-migration-project/download/project-file/' . $url_research_migration_id))->toString();
    $link2 = Link::fromTextAndUrl('Download Research Migration', Url::fromUri('internal:/research-migration-project/full-download/project/' . $url_research_migration_id))->toString();

    $form['selected_research_migration'] = [
      '#type' => 'markup',
      '#markup' => '<div id="ajax_selected_research_migration">' . $link1 . '<br>' . $link2 . '</div>',
    ];
  }

  return $form;
}

public function _research_migration_details($id) {
  $details = _research_migration_information($id);
  if ($details === 'Not found') {
    return '';
  }

  $markup = '<span style="color: rgb(128, 0, 0);"><strong>About the research migration</strong></span><br />';
  $markup .= '<ul>';
  $markup .= '<li><strong>Proposer Name:</strong> ' . htmlspecialchars($details->name_title . ' ' . $details->contributor_name) . '</li>';
  $markup .= '<li><strong>Title of the research migration:</strong> ' . htmlspecialchars($details->project_title) . '</li>';
  $markup .= '<li><strong>Source of the Project:</strong> ' . htmlspecialchars($details->source_of_the_project) . '</li>';
  $markup .= '<li><strong>University:</strong> ' . htmlspecialchars($details->university) . '</li>';

  if (!empty($details->faculty_name)) {
    $markup .= '<li><strong>Name of the faculty:</strong> ' . htmlspecialchars($details->faculty_name) . '</li>';
  }

  $markup .= '</ul>';

  return $markup;
}
public function researchMigrationProjectDetailsCallback(array &$form, FormStateInterface $form_state) {
  $response = new AjaxResponse();
  $selected_id = $form_state->getValue('research_migration');

  if (!empty($selected_id) && $selected_id != 0) {
    // Generate details
    $details_markup = $this->_research_migration_details($selected_id);
    $response->addCommand(new HtmlCommand('#ajax_research_migration_details', $details_markup));

    // Get migration info
    $research_migration_info = _research_migration_information($selected_id);

    if (!empty($research_migration_info) && isset($research_migration_info->uid) && $research_migration_info->uid > 0) {
      $synopsis_link = Link::fromTextAndUrl('Download Synopsis',
        Url::fromUri('internal:/research-migration-project/download/project-file/' . $selected_id))->toString();

      $full_link = Link::fromTextAndUrl('Download Research Migration',
        Url::fromUri('internal:/research-migration-project/full-download/project/' . $selected_id))->toString();

      $links_markup = $synopsis_link . '<br>' . $full_link;
      $response->addCommand(new HtmlCommand('#ajax_selected_research_migration', $links_markup));
    }
    else {
      $response->addCommand(new HtmlCommand('#ajax_selected_research_migration', ''));
    }
  }
  else {
    // Empty case
    $response->addCommand(new HtmlCommand('#ajax_research_migration_details', ''));
    $response->addCommand(new HtmlCommand('#ajax_selected_research_migration', ''));
  }

  return $response;
}

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
   
  }
  
  }
  function bootstrap_table_format($headers, $rows) {
  $thead = '';
  $tbody = '';

  foreach ($headers as $header) {
    $thead .= '<th>' . htmlspecialchars($header) . '</th>';
  }

  foreach ($rows as $row) {
    $tbody .= '<tr>';
    foreach ($row as $data) {
      $tbody .= '<td>' . htmlspecialchars($data) . '</td>';
    }
    $tbody .= '</tr>';
  }

  return "
    <table class='table table-bordered table-hover' style='margin-left:-140px'>
      <thead>{$thead}</thead>
      <tbody>{$tbody}</tbody>
    </table>";
}
function _list_of_research_migration() {
  $research_migration_titles = ['0' => 'Please select...'];

  $connection = \Drupal::database();
  $query = $connection->select('research_migration_proposal', 'rmp')
    ->fields('rmp')
    ->condition('approval_status', 3)
    ->orderBy('project_title', 'ASC');
  $result = $query->execute();

  foreach ($result as $record) {
    $research_migration_titles[$record->id] = $record->project_title .
      ' (Proposed by ' . $record->name_title . ' ' . $record->contributor_name . ')';
  }

  return $research_migration_titles;
}
function _research_migration_information($proposal_id) {
  $connection = \Drupal::database();
  $query = $connection->select('research_migration_proposal', 'rmp')
    ->fields('rmp')
    ->condition('id', $proposal_id)
    ->condition('approval_status', 3);
  $result = $query->execute()->fetchObject();

  return $result ?: 'Not found';
}



?>

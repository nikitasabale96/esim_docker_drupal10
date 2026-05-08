<?php

/**
 * @file
 * Contains \Drupal\esim_research_migration\Controller\DefaultController.
 */

namespace Drupal\esim_research_migration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Utility\UrlHelper;

/**
 * Default controller for the esim_research_migration module.
 */
class DefaultController extends ControllerBase {

  public function manageProposalRedirect() {
    return $this->redirect('esim_research_migration.proposal_pending');
  }

  public function esim_research_migration_proposal_pending() {
    $pending_rows = [];

    $pending_q = \Drupal::database()->select('research_migration_proposal', 'r')
      ->fields('r')
      ->condition('r.approval_status', 0)
      ->orderBy('r.id', 'DESC')
      ->execute()
      ->fetchAll();

    foreach ($pending_q as $pending_data) {
      $submission_date = date('d-m-Y', $pending_data->creation_date);

      $pending_rows[] = [
        ['data' => ['#plain_text' => $submission_date]],
        [
          'data' => Link::fromTextAndUrl(
            $pending_data->name_title . ' ' . $pending_data->contributor_name,
            Url::fromRoute('entity.user.canonical', ['user' => $pending_data->uid])
          )->toRenderable(),
        ],
        ['data' => ['#plain_text' => $pending_data->project_title]],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ approve }} | {{ edit }}',
            '#context' => [
              'approve' => Link::fromTextAndUrl(
                $this->t('Approve'),
                Url::fromRoute('esim_research_migration.proposal_approval_form', ['proposal_id' => $pending_data->id])
              )->toRenderable(),
              'edit' => Link::fromTextAndUrl(
                $this->t('Edit'),
                Url::fromRoute('esim_research_migration.proposal_edit_form', ['proposal_id' => $pending_data->id])
              )->toRenderable(),
            ],
          ],
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Date of Submission'),
        $this->t('Student Name'),
        $this->t('Title of the Research Migration Project'),
        $this->t('Action'),
      ],
      '#rows' => $pending_rows,
      '#attributes' => ['class' => ['research-migration-pending-table']],
      '#empty' => $this->t('There are no pending proposals.'),
      '#cache' => [
        'max-age' => 0,
        'tags' => ['research_migration_proposal_list'],
        'contexts' => ['user.permissions'],
      ],
    ];
  }

  public function esim_research_migration_proposal_all() {
    $proposal_rows = [];

    $proposal_q = \Drupal::database()->select('research_migration_proposal', 'p')
      ->fields('p')
      ->orderBy('id', 'DESC')
      ->execute()
      ->fetchAll();

    foreach ($proposal_q as $proposal_data) {
      switch ((int) $proposal_data->approval_status) {
        case 0:
          $approval_status = $this->t('Pending');
          break;

        case 1:
          $approval_status = $this->t('Approved');
          break;

        case 2:
          $approval_status = $this->t('Dis-approved');
          break;

        case 3:
          $approval_status = $this->t('Completed');
          break;

        case 5:
          $approval_status = $this->t('On Hold');
          break;

        default:
          $approval_status = $this->t('Unknown');
          break;
      }

      $actual_completion_date = $proposal_data->actual_completion_date
        ? date('d-m-Y', $proposal_data->actual_completion_date)
        : $this->t('Not Completed');
      $approval_date = $proposal_data->approval_date
        ? date('d-m-Y', $proposal_data->approval_date)
        : $this->t('Not Approved');

      $proposal_rows[] = [
        ['data' => ['#plain_text' => date('d-m-Y', $proposal_data->creation_date)]],
        [
          'data' => Link::fromTextAndUrl(
            $proposal_data->contributor_name,
            Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid])
          )->toRenderable(),
        ],
        ['data' => ['#plain_text' => $proposal_data->project_title]],
        ['data' => ['#plain_text' => $approval_date]],
        ['data' => ['#plain_text' => $actual_completion_date]],
        ['data' => ['#plain_text' => $approval_status]],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ status }} | {{ edit }}',
            '#context' => [
              'status' => Link::fromTextAndUrl(
                $this->t('Status'),
                Url::fromRoute('esim_research_migration.proposal_status_form', ['proposal_id' => $proposal_data->id])
              )->toRenderable(),
              'edit' => Link::fromTextAndUrl(
                $this->t('Edit'),
                Url::fromRoute('esim_research_migration.proposal_edit_form', ['proposal_id' => $proposal_data->id])
              )->toRenderable(),
            ],
          ],
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Date of Submission'),
        $this->t('Student Name'),
        $this->t('Title of the Research Migration project'),
        $this->t('Date of Approval'),
        $this->t('Date of Project Completion'),
        $this->t('Status'),
        $this->t('Action'),
      ],
      '#rows' => $proposal_rows,
      '#attributes' => ['class' => ['proposal-table']],
      '#empty' => $this->t('No proposals found.'),
      '#cache' => [
        'max-age' => 0,
        'tags' => ['research_migration_proposal_list'],
        'contexts' => ['user.permissions'],
      ],
    ];
  }

public function esim_research_migration_proposal_edit_file_all() {
  $proposal_rows = [];
  $query = \Drupal::database()->select('research_migration_proposal', 'r');
  $query->fields('r');
  $query->condition('approval_status', [0, 1, 2], 'NOT IN');
  $query->orderBy('approval_status', 'DESC');
  $query->orderBy('id', 'DESC');
  $results = $query->execute();

  foreach ($results as $proposal_data) {
    // Determine status
    switch ((int) $proposal_data->approval_status) {
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
    }

    $actual_completion_date = $proposal_data->actual_completion_date == 0
      ? 'Not Completed'
      : date('d-m-Y', $proposal_data->actual_completion_date);

    $approval_date = $proposal_data->approval_date == 0
      ? 'Not Approved'
      : date('d-m-Y', $proposal_data->approval_date);

    $submission_date = date('d-m-Y', $proposal_data->creation_date);

    $proposal_rows[] = [
      ['data' => ['#plain_text' => $submission_date]],
      [
        'data' => Link::fromTextAndUrl(
          $proposal_data->contributor_name,
          Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid])
        )->toRenderable(),
      ],
      ['data' => ['#plain_text' => $proposal_data->project_title]],
      ['data' => ['#plain_text' => $approval_date]],
      ['data' => ['#plain_text' => $actual_completion_date]],
      ['data' => ['#plain_text' => $approval_status]],
      [
        'data' => Link::fromTextAndUrl(
          $this->t('Edit'),
          Url::fromRoute('esim_research_migration.edit_upload_abstract_code_form', [], ['query' => ['proposal_id' => $proposal_data->id]])
        )->toRenderable(),
      ],
    ];
  }

  if (empty($proposal_rows)) {
    \Drupal::messenger()->addStatus(t('There are no proposals.'));
    return [
      '#markup' => t('There are no proposals.'),
    ];
  }

  $proposal_header = [
    'Date of Submission',
    'Student Name',
    'Title of the Research Migration project',
    'Date of Approval',
    'Date of Project Completion',
    'Status',
    'Action',
  ];

  return [
    '#type' => 'table',
    '#header' => $proposal_header,
    '#rows' => $proposal_rows,
    '#empty' => t('No proposals found.'),
    '#cache' => [
      'max-age' => 0,
      'tags' => ['research_migration_proposal_list'],
      'contexts' => ['user.permissions'],
    ],
  ];
}


 public function esim_research_migration_abstract() {
  $user = \Drupal::currentUser();
  $proposal_data = esim_research_migration_get_proposal();

  if (!$proposal_data) {
    return new RedirectResponse(Url::fromRoute('<front>')->toString());
  }

  // Fetch abstract submission
  $abstracts_q = \Drupal::database()->select('research_migration_submitted_abstracts')
    ->fields('research_migration_submitted_abstracts')
    ->condition('proposal_id', $proposal_data->id)
    ->execute()
    ->fetchObject();

  // Proposal details
  $abstracts_pro = \Drupal::database()->select('research_migration_proposal')
    ->fields('research_migration_proposal')
    ->condition('id', $proposal_data->id)
    ->execute()
    ->fetchObject();

  // Abstract PDF file (type A)
  $abstracts_pdf = \Drupal::database()->select('research_migration_submitted_abstracts_file')
    ->fields('research_migration_submitted_abstracts_file')
    ->condition('proposal_id', $proposal_data->id)
    ->condition('filetype', 'A')
    ->execute()
    ->fetchObject();

  $abstract_filename = 'File not uploaded';
  if ($abstracts_pdf && !empty($abstracts_pdf->filename) && $abstracts_pdf->filename !== 'NULL') {
    $abstract_filename = $abstracts_pdf->filename;
  }

  // Case directory (type S)
  $abstracts_query_process = \Drupal::database()->select('research_migration_submitted_abstracts_file')
    ->fields('research_migration_submitted_abstracts_file')
    ->condition('proposal_id', $proposal_data->id)
    ->condition('filetype', 'S')
    ->execute()
    ->fetchObject();

  $abstracts_query_process_filename = 'File not uploaded';
  $action_link_render = NULL;

  if ($abstracts_query_process && !empty($abstracts_query_process->filename) && $abstracts_query_process->filename !== 'NULL') {
    $abstracts_query_process_filename = $abstracts_query_process->filename;

    if (!empty($abstracts_q)) {
      if ($abstracts_q->is_submitted == 0) {
        $action_link_render = Link::fromTextAndUrl(
          $this->t('Edit'),
          Url::fromRoute('esim_research_migration.upload_abstract_code_form')
        )->toRenderable();
      }
    }
  } else {
    $action_link_render = Link::fromTextAndUrl(
      $this->t('Upload Case Directory'),
      Url::fromRoute('esim_research_migration.upload_abstract_code_form')
    )->toRenderable();
  }

  $rows = [
    [
      ['data' => ['#plain_text' => $this->t('Contributor Name')]],
      ['data' => ['#plain_text' => $proposal_data->name_title . ' ' . $proposal_data->contributor_name]],
    ],
    [
      ['data' => ['#plain_text' => $this->t('Title of the Research Migration Project')]],
      ['data' => ['#plain_text' => $proposal_data->project_title]],
    ],
    [
      ['data' => ['#plain_text' => $this->t('Uploaded Synopsis Submission')]],
      ['data' => ['#plain_text' => $abstract_filename]],
    ],
    [
      ['data' => ['#plain_text' => $this->t('Uploaded Case Directory')]],
      ['data' => ['#plain_text' => $abstracts_query_process_filename]],
    ],
  ];

  if (!empty($action_link_render)) {
    $rows[] = [
      ['data' => ['#plain_text' => $this->t('Action')]],
      ['data' => $action_link_render],
    ];
  }

  return [
    '#type' => 'table',
    '#header' => [$this->t('Field'), $this->t('Value')],
    '#rows' => $rows,
    '#cache' => [
      'tags' => ['research_migration_proposal:' . $proposal_data->id],
      'contexts' => ['user'],
    ],
  ];
}


  public function esim_research_migration_download_full_project() {
    $proposal_id = $this->resolveIdentifier();
    if (!$proposal_id) {
      $this->messenger()->addError($this->t('Missing proposal identifier.'));
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $database = \Drupal::database();
    $proposal = $database->select('research_migration_proposal', 'r')
      ->fields('r')
      ->condition('id', $proposal_id)
      ->execute()
      ->fetchObject();

    if (!$proposal) {
      $this->messenger()->addError($this->t('Unable to find the requested proposal.'));
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $file_system = \Drupal::service('file_system');
    $root_path = rtrim(esim_research_migration_path(), '/') . '/';
    $directory_name = trim($proposal->directory_name, '/');
    if ($directory_name === '') {
      $this->messenger()->addError($this->t('Invalid proposal directory.'));
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $zip_filename = $file_system->tempnam($file_system->getTempDirectory(), 'rm_zip_') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
      $this->messenger()->addError($this->t('Could not create archive for download.'));
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $files = $database->select('research_migration_submitted_abstracts_file', 'f')
      ->fields('f')
      ->condition('proposal_id', $proposal_id)
      ->execute();

    $added_files = 0;
    foreach ($files as $file) {
      $source = $root_path . $directory_name . '/' . ltrim($file->filepath, '/');
      if (is_file($source)) {
        $destination = $directory_name . '/' . str_replace(' ', '_', basename($file->filename));
        $zip->addFile($source, $destination);
        $added_files++;
      }
    }
    $zip->close();

    if ($added_files === 0) {
      $this->messenger()->addError($this->t('There are no project files available for download.'));
      @unlink($zip_filename);
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $download_name = str_replace(' ', '_', $proposal->project_title) . '.zip';
    $response = new BinaryFileResponse($zip_filename);
    $response->setContentDisposition('attachment', $download_name);
    $response->deleteFileAfterSend(TRUE);
    $response->setPrivate();
    $response->headers->addCacheControlDirective('max-age', 0);

    return $response;
  }

  public function esim_research_migration_completed_proposals_all() {
    $query = \Drupal::database()->select('research_migration_proposal', 'r');
    $query->fields('r');
    $query->condition('approval_status', 3);
    $query->orderBy('actual_completion_date', 'DESC');
    $records = $query->execute()->fetchAll();

    $rows = [];
    $counter = count($records);
    foreach ($records as $row) {
      $year = $row->actual_completion_date ? date('Y', $row->actual_completion_date) : $this->t('NA');
      $rows[] = [
        ['data' => ['#plain_text' => $counter]],
        [
          'data' => Link::fromTextAndUrl(
            $row->project_title,
            Url::fromRoute('esim_research_migration.run_form_with_id', ['proposal_id' => $row->id])
          )->toRenderable(),
        ],
        ['data' => ['#plain_text' => $row->contributor_name]],
        ['data' => ['#plain_text' => $row->institute]],
        ['data' => ['#plain_text' => $year]],
      ];
      $counter--;
    }

    return [
      '#theme' => 'table',
      '#caption' => $this->t('Work has been completed for the following research migrations. We welcome your contributions.'),
      '#header' => [
        $this->t('No'),
        $this->t('Research Migration Project'),
        $this->t('Contributor Name'),
        $this->t('University/ Institute'),
        $this->t('Year of Completion'),
      ],
      '#rows' => $rows,
      '#empty' => [
        '#type' => 'inline_template',
        '#template' => 'Currently, there are no submissions in this section. Click {{ link }} to propose a Research Migration Project.',
        '#context' => [
          'link' => Link::fromTextAndUrl(
            $this->t('here'),
            Url::fromRoute('esim_research_migration.proposal_form')
          )->toRenderable(),
        ],
      ],
      '#attributes' => ['class' => ['research-migration-completed-table']],
      '#cache' => [
        'max-age' => 0,
        'tags' => ['research_migration_proposal_list'],
        'contexts' => ['user.permissions'],
      ],
    ];
  }

  public function esim_research_migration_progress_all() {
    $query = \Drupal::database()->select('research_migration_proposal', 'r');
    $query->fields('r');
    $query->condition('approval_status', 1);
    $query->condition('is_completed', 0);
    $query->orderBy('approval_date', 'DESC');
    $results = $query->execute()->fetchAll();
    $rows = [];
    $counter = count($results);
    foreach ($results as $row) {
      $approval_year = $row->approval_date ? date('Y', $row->approval_date) : $this->t('NA');
      $rows[] = [
        ['data' => ['#plain_text' => $counter]],
        ['data' => ['#plain_text' => $row->project_title]],
        ['data' => ['#plain_text' => $row->contributor_name]],
        ['data' => ['#plain_text' => $row->institute]],
        ['data' => ['#plain_text' => $approval_year]],
      ];
      $counter--;
    }

    $proposal_link = Link::fromTextAndUrl(
      $this->t('here'),
      Url::fromRoute('esim_research_migration.proposal_form')
    )->toString();

    return [
      '#theme' => 'table',
      '#caption' => $this->t('Work is in progress for the following submissions under the Research Migration Project.'),
      '#header' => [
        $this->t('No'),
        $this->t('Research Migration Project'),
        $this->t('Contributor Name'),
        $this->t('Institute'),
        $this->t('Year'),
      ],
      '#rows' => $rows,
      // '#empty' => Markup::create($this->t('Currently, there are no submissions in progress. Click @link to propose a Research Migration Project.', [
      //   '@link' => $proposal_link,
      // ])),
      '#attributes' => ['class' => ['research-migration-progress-table']],
      '#cache' => [
        'max-age' => 0,
        'tags' => ['research_migration_proposal_list'],
        'contexts' => ['user.permissions'],
      ],
    ];
  }

  public function list_of_available_project_titles() {
    $preference_rows = [];
    $i = 1;
    $query = \Drupal::database()->select('rm_list_of_project_titles', 't');
    $query->fields('t');
    $query->leftJoin('research_migration_proposal', 'p', 'p.project_title = t.rm_project_title_name AND p.approval_status IN (0, 1, 3)');
    $query->isNull('p.id');
    $results = $query->execute();

    foreach ($results as $result) {
      $link_render = ['#plain_text' => ''];
      $raw_link = (string) ($result->rm_project_link ?? '');
      $safe_link = UrlHelper::stripDangerousProtocols($raw_link);
      if ($safe_link !== '' && UrlHelper::isValid($safe_link, TRUE)) {
        $link_render = Link::fromTextAndUrl(
          $this->t('Click Here'),
          Url::fromUri($safe_link, ['attributes' => ['target' => '_blank', 'rel' => 'noopener noreferrer']])
        )->toRenderable();
      }
      $download_link = Link::fromTextAndUrl(
        $this->t('Download'),
        Url::fromRoute('esim_research_migration.download_research_migration_project_title_files', ['project_title_id' => $result->id])
      )->toRenderable();

      $preference_rows[] = [
        ['data' => ['#plain_text' => $i]],
        ['data' => ['#plain_text' => $result->rm_project_title_name]],
        ['data' => $link_render],
        ['data' => $download_link],
      ];
      $i++;
    }
    return [
      '#theme' => 'table',
      '#header' => [
        $this->t('No'),
        $this->t('List of available projects'),
        $this->t('Link to the paper'),
        $this->t('Download'),
      ],
      '#rows' => $preference_rows,
      '#empty' => $this->t('No project titles available at the moment.'),
      '#attributes' => ['class' => ['research-migration-project-titles-table']],
      '#cache' => [
        'max-age' => 0,
        'tags' => ['rm_list_of_project_titles_list', 'research_migration_proposal_list'],
        'contexts' => ['user.permissions'],
      ],
    ];
  }

  public function download_research_migration_project_title_files() {
    $file_id = $this->resolveIdentifier('project_title_id') ?? $this->resolveIdentifier('id');
    if (!$file_id) {
      $this->messenger()->addError($this->t('Missing project title identifier.'));
      return $this->redirect('esim_research_migration.list_of_available_project_titles');
    }

    $record = \Drupal::database()->select('rm_list_of_project_titles', 't')
      ->fields('t')
      ->condition('id', $file_id)
      ->execute()
      ->fetchObject();

    if (!$record || empty($record->filepath)) {
      $this->messenger()->addError($this->t('Unable to find the requested resource.'));
      return $this->redirect('esim_research_migration.list_of_available_project_titles');
    }

    $root_path = rtrim(esim_research_migration_project_titles_resource_file_path(), '/');
    $file_path = $root_path . '/' . ltrim($record->filepath, '/');

    if (!is_file($file_path)) {
      $this->messenger()->addError($this->t('The requested file is not available.'));
      return $this->redirect('esim_research_migration.list_of_available_project_titles');
    }

    $response = new BinaryFileResponse($file_path);
    $response->setContentDisposition('attachment', basename($file_path));
    $response->setPrivate();
    $response->headers->addCacheControlDirective('max-age', 0);
    return $response;
  }

  public function esim_research_migration_project_files() {
    $proposal_id = $this->resolveIdentifier();
    if (!$proposal_id) {
      $this->messenger()->addError($this->t('Missing proposal identifier.'));
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $proposal = \Drupal::database()->select('research_migration_proposal', 'p')
      ->fields('p')
      ->condition('id', $proposal_id)
      ->execute()
      ->fetchObject();

    if (!$proposal || empty($proposal->samplefilepath)) {
      $this->messenger()->addError($this->t('No synopsis file available for this proposal.'));
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $root_path = rtrim(esim_research_migration_path(), '/') . '/';
    $file_path = $root_path . ltrim($proposal->samplefilepath, '/');

    if (!is_file($file_path)) {
      $this->messenger()->addError($this->t('The requested file is not available.'));
      return $this->redirect('esim_research_migration.proposal_all');
    }

    $response = new BinaryFileResponse($file_path);
    $response->setContentDisposition('attachment', basename($file_path));
    $response->setPrivate();
    $response->headers->addCacheControlDirective('max-age', 0);
    return $response;
  }

  public function _list_research_migration_certificates() {
    $user = $this->currentUser();
    $proposals = \Drupal::database()->query("SELECT id, project_title, contributor_name FROM research_migration_proposal WHERE approval_status = 3 AND uid = :uid", [
      ':uid' => $user->id(),
    ]);

    $rows = [];
    foreach ($proposals as $proposal) {
      $rows[] = [
        ['data' => ['#plain_text' => $proposal->project_title]],
        ['data' => ['#plain_text' => $proposal->contributor_name]],
        [
          'data' => Link::fromTextAndUrl(
            $this->t('Download Certificate'),
            Url::fromRoute('esim_research_migration.generate_pdf', ['proposal_id' => $proposal->id])
          )->toRenderable(),
        ],
      ];
    }

    if (empty($rows)) {
      $this->messenger()->addStatus($this->t('You need to propose a Research Migration Proposal or your Research Migration is under review.'));

      return [
        '#type' => 'markup',
        '#markup' => '<span style="color:red;">' . $this->t('No certificate available.') . '</span>',
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => [
        $this->t('Project Title'),
        $this->t('Contributor Name'),
        $this->t('Download Certificates'),
      ],
      '#rows' => $rows,
      '#attributes' => ['class' => ['research-migration-certificates-table']],
      '#cache' => [
        'max-age' => 0,
        'tags' => ['research_migration_proposal_list'],
        'contexts' => ['user'],
      ],
    ];
  }

  public function generatePdf($proposal_id) {
    $proposal_id = (int) $proposal_id;
    if ($proposal_id <= 0) {
      $this->messenger()->addError($this->t('Missing proposal identifier.'));
      return $this->redirect('esim_research_migration._list_research_migration_certificates');
    }

    $account = $this->currentUser();
    $proposal = \Drupal::database()->select('research_migration_proposal', 'p')
      ->fields('p')
      ->condition('p.id', $proposal_id)
      ->condition('p.uid', $account->id())
      ->condition('p.approval_status', 3)
      ->execute()
      ->fetchObject();

    if (!$proposal) {
      $this->messenger()->addError($this->t('Certificate is not available for this proposal.'));
      return $this->redirect('esim_research_migration._list_research_migration_certificates');
    }

    $module_path = drupal_get_path('module', 'esim_research_migration');
    require_once $module_path . '/pdf/fpdf/fpdf.php';
    require_once $module_path . '/pdf/phpqrcode/qrlib.php';

    $site_url = rtrim(\Drupal::request()->getSchemeAndHttpHost(), '/');
    $verify_base_url = $site_url . '/research-migration-project/certificates/verify/';

    $qr_code_record = \Drupal::database()->select('research_migration_qr_code', 'q')
      ->fields('q')
      ->condition('q.proposal_id', $proposal_id)
      ->execute()
      ->fetchObject();

    $qr_string = '';
    if ($qr_code_record && !empty($qr_code_record->qr_code) && strtolower((string) $qr_code_record->qr_code) !== 'null') {
      $qr_string = (string) $qr_code_record->qr_code;
    }
    else {
      $qr_string = substr(bin2hex(random_bytes(8)), 0, 10);
      \Drupal::database()->merge('research_migration_qr_code')
        ->key(['proposal_id' => $proposal_id])
        ->fields(['qr_code' => $qr_string])
        ->execute();
    }

    $file_system = \Drupal::service('file_system');
    $temp_dir = $file_system->getTempDirectory();
    $qr_png = $file_system->tempnam($temp_dir, 'rm_qr_') . '.png';
    $pdf_file = $file_system->tempnam($temp_dir, 'rm_cert_');

    $code_contents = $verify_base_url . $qr_string;
    \QRcode::png($code_contents, $qr_png);

    $pdf = new \FPDF('L', 'mm', 'Letter');
    $pdf->AddPage();

    $image_bg = $module_path . '/pdf/images/bg_cert.png';
    if (is_file($image_bg)) {
      $pdf->Image($image_bg, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    }

    $pdf->SetMargins(18, 1, 18);
    $pdf->Ln(41);

    $pdf->SetFont('Times', 'I', 18);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'This is to certify that', 0, 1, 'C');

    $pdf->SetFont('Times', 'I', 18);
    $pdf->SetTextColor(37, 22, 247);
    $pdf->Cell(0, 10, $proposal->name_title . '. ' . $proposal->contributor_name, 0, 1, 'C');

    $pdf->SetFont('Times', 'I', 18);
    $pdf->SetTextColor(0, 0, 0);
    $institute_line = 'from ' . $proposal->institute;
    $pdf->MultiCell(0, 10, $institute_line, 0, 'C');
    $pdf->Cell(0, 10, 'has successfully completed the Research Migration of', 0, 1, 'C');

    $title = wordwrap((string) $proposal->project_title, 60, "\n", TRUE);
    $pdf->SetTextColor(37, 22, 247);
    $pdf->SetFont('Times', 'I', 20);
    $pdf->MultiCell(0, 10, $title, 0, 'C');

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Times', 'I', 18);
    $pdf->Cell(0, 8, 'under eSim Research Migration project.', 0, 1, 'C');

    $pdf->SetY(100);
    $pdf->SetX(25);
    if (is_file($qr_png)) {
      $pdf->Image($qr_png, $pdf->GetX() + 15, $pdf->GetY() + 55, 30, 0);
    }

    $sign = $module_path . '/pdf/images/sign1.png';
    if (is_file($sign)) {
      $pdf->Image($sign, $pdf->GetX() + 85, $pdf->GetY() + 30, 75, 0);
    }

    $logo_esim = $module_path . '/pdf/images/esim-logo.png';
    $logo_fossee = $module_path . '/pdf/images/fossee.png';
    if (is_file($logo_esim)) {
      $pdf->Image($logo_esim, $pdf->GetX() + 100, $pdf->GetY() + 62, 50, 0);
    }
    if (is_file($logo_fossee)) {
      $pdf->Image($logo_fossee, $pdf->GetX() + 180, $pdf->GetY() + 62, 40, 0);
    }

    $pdf->SetFont('Times', 'I', 14);
    $pdf->SetLeftMargin(40);
    $pdf->Ln(78);
    $pdf->Cell(0, 0, $qr_string, 0, 1, 'L');
    $pdf->Ln(24);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Times', 'I', 12);
    $pdf->Cell(0, 8, 'This is computer generated certificate and requires no signature. To verify, scan the QR code or visit:', 0, 1, 'L');
    $pdf->SetX(85);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->Write(0, $verify_base_url, $verify_base_url);

    $pdf->Output($pdf_file, 'F');
    @unlink($qr_png);

    $download_filename = str_replace(' ', '-', (string) $proposal->contributor_name) . '-esim-Research-Migration-Certificate.pdf';
    $response = new BinaryFileResponse($pdf_file);
    $response->setContentDisposition('attachment', $download_filename);
    $response->deleteFileAfterSend(TRUE);
    $response->setPrivate();
    $response->headers->addCacheControlDirective('max-age', 0);
    return $response;
  }

  public function verify_certificates($qr_code = '') {
    $request = \Drupal::request();
    $resolved_code = $request->query->get('qr_code');
    $qr_code = $qr_code ?: ($resolved_code ?? '');

    if (!empty($qr_code)) {
      $qr_code = (string) $qr_code;
      $proposal_id = \Drupal::database()->select('research_migration_qr_code', 'q')
        ->fields('q', ['proposal_id'])
        ->condition('q.qr_code', $qr_code)
        ->execute()
        ->fetchField();

      if (!$proposal_id) {
        return [
          '#type' => 'markup',
          '#markup' => '<b>' . $this->t('Sorry! The QR code you entered seems to be invalid. Please try again.') . '</b>',
        ];
      }

      $proposal = \Drupal::database()->select('research_migration_proposal', 'p')
        ->fields('p', ['contributor_name', 'project_title', 'project_guide_name'])
        ->condition('p.id', (int) $proposal_id)
        ->condition('p.approval_status', 3)
        ->execute()
        ->fetchObject();

      if (!$proposal) {
        return [
          '#type' => 'markup',
          '#markup' => '<b>' . $this->t('Certificate details not available.') . '</b>',
        ];
      }

      $rows = [
        [$this->t('Name'), $proposal->contributor_name],
        [$this->t('Project'), $this->t('Research Migration Project')],
        [$this->t('Research Migration completed'), $proposal->project_title],
      ];
      if (!empty($proposal->project_guide_name)) {
        $rows[] = [$this->t('Project Guide'), $proposal->project_guide_name];
      }

      return [
        '#type' => 'table',
        '#header' => [$this->t('Field'), $this->t('Value')],
        '#rows' => $rows,
        '#caption' => $this->t('Participation Details'),
        '#cache' => [
          'tags' => ['research_migration_proposal:' . (int) $proposal_id],
          'contexts' => ['user.permissions'],
        ],
      ];
    }

    return \Drupal::formBuilder()->getForm(\Drupal\esim_research_migration\Form\VerifyCertificatesForm::class);
  }

  /**
   * Resolve an identifier from the current route or request query.
   */
  protected function resolveIdentifier(string $parameter = 'proposal_id'): ?int {
    $route_match = \Drupal::routeMatch();
    $value = $route_match->getParameter($parameter);
    if ($value === NULL && $parameter !== 'id') {
      $value = $route_match->getParameter('id');
    }

    if (is_object($value) && method_exists($value, 'id')) {
      $value = $value->id();
    }

    if ($value === NULL) {
      $request = \Drupal::request();
      $value = $request->query->get($parameter);
      if ($value === NULL && $parameter !== 'id') {
        $value = $request->query->get('id');
      }
    }

    if ($value === NULL || $value === '') {
      return NULL;
    }

    return (int) $value;
  }

  public function downloadUploadFile() {
    $proposal_id = $this->resolveIdentifier('proposal_id');
    if (!$proposal_id) {
      $this->messenger()->addError($this->t('Missing proposal identifier.'));
      return $this->redirect('esim_research_migration.run_form');
    }

    $proposal = \Drupal::database()->select('research_migration_proposal', 'p')
      ->fields('p')
      ->condition('id', $proposal_id)
      ->execute()
      ->fetchObject();

    if (!$proposal) {
      $this->messenger()->addError($this->t('Unable to find the requested proposal.'));
      return $this->redirect('esim_research_migration.run_form');
    }

    $relative_path = '';
    if (!empty($proposal->user_defined_compound_filepath)) {
      $relative_path = (string) $proposal->user_defined_compound_filepath;
    }
    elseif (!empty($proposal->samplefilepath)) {
      $relative_path = (string) $proposal->samplefilepath;
    }

    if ($relative_path === '') {
      $this->messenger()->addError($this->t('No file available for this proposal.'));
      return $this->redirect('esim_research_migration.run_form');
    }

    $root_path = rtrim(esim_research_migration_path(), '/') . '/';
    $file_path = $root_path . ltrim($relative_path, '/');

    if (!is_file($file_path)) {
      $this->messenger()->addError($this->t('The requested file is not available.'));
      return $this->redirect('esim_research_migration.run_form');
    }

    $response = new BinaryFileResponse($file_path);
    $response->setContentDisposition('attachment', basename($file_path));
    $response->setPrivate();
    $response->headers->addCacheControlDirective('max-age', 0);
    return $response;
  }

}

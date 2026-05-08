<?php

 function esim_research_migration_proposal_all() {
    /* get pending proposals to be approved */
    $proposal_rows = [];
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
    $proposal_header = [
      'Date of Submission',
      'Student Name',
      'Title of the Research Migration project',
      'Date of Approval',
      'Date of Project Completion',
      'Status',
      'Action',
    ];
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


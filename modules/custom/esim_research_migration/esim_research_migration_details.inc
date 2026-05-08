<?php
// research migration display completed proposals
function esim_research_migration_completed_proposals_all()
{
	$output = "";
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('approval_status', 3);
	$query->orderBy('actual_completion_date', 'DESC');
	//$query->condition('is_completed', 1);
	$result = $query->execute();
	
	//var_dump($research_migration_abstract);die;
	if ($result->rowCount() == 0)
	{
		$output .= "Currently, there are no submissions in this section. Click <a href='proposal'>here</a> to propose a Research Migration Project." ."<hr>";

	} //$result->rowCount() == 0
	else
	{
		$output .= "Work has been completed for the following research migrations. We welcome your contributions." . "<hr>";
		$preference_rows = array();
		$i = $result->rowCount();
		while ($row = $result->fetchObject())
		{
			$proposal_id = $row->id;
			$query1 = \Drupal::database()->select('research_migration_submitted_abstracts_file');
			$query1->fields('research_migration_submitted_abstracts_file');
			$query1->condition('file_approval_status', 1);
			$query1->condition('proposal_id', $proposal_id);
			$research_migration_files = $query1->execute();
			$research_migration_abstract = $research_migration_files->fetchObject();
			
			// @FIXME
// l() expects a Url object, created from a route name or external URI.
// $project_title = l($row->project_title, "research-migration-project/research-migration-run/" . $row->id);

			$year = date("Y", $row->actual_completion_date);
			$preference_rows[] = array(
				$i,
				$project_title,
				$row->contributor_name,
				$row->institute,
				$year
			);
			$i--;
		} //$row = $result->fetchObject()
		$preference_header = array(
			'No',
			'Research Migration Project',
			'Contributor Name',
			'University/ Institute',
			'Year of Completion'
		);
		// @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
// 
// 
// @see https://www.drupal.org/node/2195739
// $output .= theme('table', array(
// 			'header' => $preference_header,
// 			'rows' => $preference_rows
// 		));

	}
	return $output;
}
// research migration display in progress proposals
function esim_research_migration_progress_all()
{
	$page_content = "";
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('approval_status', 1);
	$query->condition('is_completed', 0);
	$query->orderBy('approval_date', 'DESC');
	$result = $query->execute();
	if ($result->rowCount() == 0)
	{
		$page_content .= "Currently, there are no submissions in progress. Click <a href='proposal'>here</a> to propose a Research Migration Project.<hr>";
	} //$result->rowCount() == 0
	else
	{
		$page_content .= "Work is in progress for the following submissions under the Research Migration Project<hr>";
		$preference_rows = array();
		$i = $result->rowCount();
		while ($row = $result->fetchObject())
		{
			$approval_date = date("Y", $row->approval_date);
			$preference_rows[] = array(
				$i,
				$row->project_title,
				$row->contributor_name,
				$row->institute,
				$approval_date
			);
			$i--;
		} //$row = $result->fetchObject()
		$preference_header = array(
			'No',
			'Research Migration Project',
			'Contributor Name',
			'Institute',
			'Year'
		);
		// @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
// 
// 
// @see https://www.drupal.org/node/2195739
// $page_content .= theme('table', array(
// 			'header' => $preference_header,
// 			'rows' => $preference_rows
// 		));

	}
	return $page_content;
}

<?php
/******************************************************************************/
/****************************** DELETION FUNCTIONS ****************************/
/******************************************************************************/
function research_migration_abstract_delete_project($proposal_id)
{
	$status = TRUE;
	$root_path = esim_research_migration_path();
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $proposal_id);
	$proposal_q = $query->execute();
	$proposal_data = $proposal_q->fetchObject();
	if (!$proposal_data)
	{
		\Drupal::messenger()->addError('Invalid Research Migration Project.');
		return FALSE;
	} //!$proposal_data
	$query = \Drupal::database()->select('research_migration_submitted_abstracts_file');
	$query->fields('research_migration_submitted_abstracts_file');
	$query->condition('proposal_id', $proposal_id);
	$abstract_q = $query->execute();
	$dir_project_files = $root_path . $proposal_data->directory_name;
	while ($abstract_data = $abstract_q->fetchObject())
	{
		if (is_dir($dir_project_files)){

		unlink($root_path . $proposal_data->directory_name . '/' . $abstract_data->filepath);
		}
		else
		{
			\Drupal::messenger()->addError('Invalid Research Migration Project Abstract.');
		}
		\Drupal::database()->delete('research_migration_submitted_abstracts_file')->condition('proposal_id', $proposal_id)->execute();
		//!dwsim_flowsheet_delete_abstract_file($abstract_data->id)
	}
	\Drupal::database()->delete('research_migration_submitted_abstracts')->condition('proposal_id', $proposal_id)->execute();
	unlink($root_path . $proposal_data->samplefilepath);
	$res = rmdir($root_path . $proposal_data->directory_name);
	\Drupal::database()->delete('research_migration_proposal')->condition('id', $proposal_data->id)->execute();
	
	return $status;
}

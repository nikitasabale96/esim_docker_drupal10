<?php
// $Id$
function esim_research_migration_download_full_project()
{
	$user = \Drupal::currentUser();
	$id = arg(3);
	$root_path = esim_research_migration_path();
	//var_dump($root_path);die;
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $id);
	$research_migration_q = $query->execute();
	$research_migration_data = $research_migration_q->fetchObject();
	$research_migration_PATH = $research_migration_data->directory_name . '/';
	/* zip filename */
	$zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
	/* creating zip archive on the server */
	$zip = new ZipArchive();
	$zip->open($zip_filename, ZipArchive::CREATE);
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $id);
	$circuit_simulation_udc_q = $query->execute();
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $id);
	$query = \Drupal::database()->select('research_migration_submitted_abstracts_file');
	$query->fields('research_migration_submitted_abstracts_file');
	$query->condition('proposal_id', $id);
	$project_files = $query->execute();
	while ($esim_project_files = $project_files->fetchObject()) {
		$zip->addFile($root_path . $research_migration_PATH . $esim_project_files->filepath, $research_migration_PATH . str_replace(' ', '_', basename($esim_project_files->filename)));
	}
	$zip_file_count = $zip->numFiles;
	$zip->close();
	if ($zip_file_count > 0) {
		if ($user->uid) {
			/* download zip file */
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $research_migration_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			ob_end_flush();
			ob_clean();
			flush();
			readfile($zip_filename);
			unlink($zip_filename);
		} //$user->uid
		else {
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $research_migration_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			ob_end_flush();
			ob_clean();
			flush();
			readfile($zip_filename);
			unlink($zip_filename);
		}
	} //$zip_file_count > 0
	else {
		\Drupal::messenger()->addError("There are no research migration project in this proposal to download");
		drupal_goto('research-migration-project/full-download/project');
	}
}
/*
function circuit_simulation_download_completed_project()
{
		$user = \Drupal::currentUser();
	$id = arg(3);
	$root_path = esim_research_migration_path();
	$query = db_select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $id);
	$research_migration_q = $query->execute();
	$research_migration_data = $research_migration_q->fetchObject();
	$research_migration_PATH = $research_migration_data->directory_name . '/';
	/* zip filename 
	$zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
	/* creating zip archive on the server 
	$zip = new ZipArchive();
	$zip->open($zip_filename, ZipArchive::CREATE);
	$query = db_select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $id);
	$circuit_simulation_udc_q = $query->execute();
	$query = db_select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $id);
	$query = db_select('research_migration_submitted_abstracts_file');
	$query->fields('research_migration_submitted_abstracts_file');
	$query->condition('proposal_id', $id);
	$project_files = $query->execute();
	//var_dump($root_path . $research_migration_PATH . 'project_files/');die;
	while ($esim_project_files = $project_files->fetchObject()) {
		$zip->addFile($root_path . $research_migration_PATH . 'project_files/' . $esim_project_files->filepath, $research_migration_PATH . str_replace(' ', '_', basename($esim_project_files->filename)));
	}
	$zip_file_count = $zip->numFiles;
	$zip->close();
	if ($zip_file_count > 0) {
		if ($user->uid) {
			/* download zip file 
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $research_migration_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			ob_end_flush();
			ob_clean();
			flush();
			readfile($zip_filename);
			unlink($zip_filename);
		} //$user->uid
		else {
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $research_migration_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			ob_end_flush();
			ob_clean();
			flush();
			readfile($zip_filename);
			unlink($zip_filename);
		}
	} //$zip_file_count > 0
	else {
		drupal_set_message("There are circuit simulation project in this proposal to download", 'error');
		drupal_goto('research-migration-project/full-download/project');
	}
}*/

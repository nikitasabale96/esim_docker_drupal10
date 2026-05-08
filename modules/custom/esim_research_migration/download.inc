<?php
function esim_research_migration_project_files() {
	$proposal_id = arg(3);
	$root_path = esim_research_migration_path();
	// $query = db_select('research_migration_submitted_abstracts_file');
	// $query->fields('research_migration_submitted_abstracts_file');
	// $query->condition('proposal_id', $proposal_id);
	// $query->condition('filetype', 'A');
	// $result = $query->execute();
	// $esim_research_migration_project_files = $result->fetchObject();
	$query1 = \Drupal::database()->select('research_migration_proposal');
	$query1->fields('research_migration_proposal');
	$query1->condition('id', $proposal_id);
	$result1 = $query1->execute();
	$research_migration = $result1->fetchObject();
	$directory_name = $research_migration->directory_name . '/';
	$str = substr($research_migration->samplefilepath,strrpos($research_migration->samplefilepath, '/'));
            $resource_file =ltrim($str, '/');
	$abstract_file = $research_migration->samplefilepath;
	//var_dump('abstract_file(filepath):' . $abstract_file . '    resource_file(filename):' . $resource_file);die;
	ob_clean();
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: application/pdf");
	header('Content-disposition: attachment; filename="' . $resource_file . '"');
	header("Content-Length: " . filesize($root_path . $abstract_file));
	header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Pragma: no-cache");
	readfile($root_path . $abstract_file);
	ob_end_flush();
	ob_clean();
}

// function esim_circuit_simulation_project_files() {
// 	$proposal_id = arg(3);
// 	$root_path = circuit_simulation_document_path();
// 	$query = db_select('esim_circuit_simulation_submitted_abstracts_file');
// 	$query->fields('esim_circuit_simulation_submitted_abstracts_file');
// 	$query->condition('proposal_id', $proposal_id);
// 	$result = $query->execute();
// 	$esim_circuit_simulation_project_files = $result->fetchObject();
// 	//var_dump($esim_circuit_simulation_project_files);die;
// 	$query1 = db_select('esim_circuit_simulation_proposal');
// 	$query1->fields('esim_circuit_simulation_proposal');
// 	$query1->condition('id', $proposal_id);
// 	$result1 = $query1->execute();
// 	$circuit_simulation = $result1->fetchObject();
// 	$directory_name = $circuit_simulation->directory_name . '/project_files/';
// 	$samplecodename = $esim_circuit_simulation_project_files->filename;
// 	ob_clean();
// 	header("Pragma: public");
// 	header("Expires: 0");
// 	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
// 	header("Cache-Control: public");
// 	header("Content-Description: File Transfer");
// 	header("Content-Type: application/pdf");
// 	header('Content-disposition: attachment; filename="' . $samplecodename . '"');
// 	header("Content-Length: " . filesize($root_path . $directory_name . $samplecodename));
// 	header("Content-Transfer-Encoding: binary");
// 	header("Expires: 0");
// 	header("Pragma: no-cache");
// 	ob_clean();
// 	readfile($root_path . $directory_name . $samplecodename);
// 	//ob_end_flush();
// 	//ob_clean();
// }



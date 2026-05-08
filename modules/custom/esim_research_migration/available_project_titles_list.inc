<?php
// research migration display completed proposals
function list_of_available_project_titles()
{
	$output = "";
	//$static_url = "https://static.fossee.in/esim/project-titles/";
	$preference_rows = array();
		$i = 1;
	$query = \Drupal::database()->query("SELECT * from rm_list_of_project_titles WHERE {rm_project_title_name} NOT IN( SELECT  project_title from research_migration_proposal WHERE approval_status = 0 OR approval_status = 1 OR approval_status = 3)");
	while($result = $query->fetchObject()) {
		// @FIXME
// l() expects a Url object, created from a route name or external URI.
// $preference_rows[] = array(
// 				$i,
// 				//print_r(array_keys($case_studies_list))
// 				$result->rm_project_title_name,
// 				l('Click Here', $result->rm_project_link , array('attributes' => array('target'=>'_blank')))
// 				//l(Download, 'research-migration-project/download/project-title-file/' .$result->id)
// 				);

			$i++;
	}
		$preference_header = array(
			'No',
			'List of available projects',
			'Link to the paper'
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

	
	return $output;
}
function download_research_migration_project_title_files() {
	$id = arg(3);
	$root_path = esim_research_migration_project_titles_resource_file_path();
	$query = \Drupal::database()->select('rm_list_of_project_titles');
	$query->fields('rm_list_of_project_titles');
	$query->condition('id', $id);
	$result = $query->execute();
	$rm_project_files_list = $result->fetchObject();
	//$directory_name = $case_study_project_files_list->filepath;
	$abstract_file = $rm_project_files_list->filepath;
	ob_clean();
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: application/pdf");
	header('Content-disposition: attachment; filename="' . $abstract_file . '"');
	header("Content-Length: " . filesize($root_path . $abstract_file));
	header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Pragma: no-cache");
	readfile($root_path . $abstract_file);
	ob_end_flush();
	ob_clean();
}
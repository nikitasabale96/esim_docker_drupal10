<?php
function esim_research_migration_run_form($form, &$form_state)
{
	$options_first = _list_of_research_migration();
	$url_research_migration_id = (int) arg(2);
	$research_migration_data = _research_migration_information($url_research_migration_id);
	if ($research_migration_data == 'Not found') {
		$url_research_migration_id = '';
	} //$research_migration_data == 'Not found'
	if (!$url_research_migration_id) {
		$selected = isset($form_state['values']['research_migration']) ? $form_state['values']['research_migration'] : key($options_first);
	} //!$url_research_migration_id
	elseif ($url_research_migration_id == '') {
		$selected = 0;
	} //$url_research_migration_id == ''
	else {
		$selected = $url_research_migration_id;
	}
	$form = array();
	$form['research_migration'] = array(
		'#type' => 'select',
		'#title' => t('Title of the research migration'),
		'#options' => _list_of_research_migration(),
		'#default_value' => $selected,
		'#ajax' => array(
			'callback' => 'research_migration_project_details_callback'
		)
	);
	if (!$url_research_migration_id) {
		$form['research_migration_details'] = array(
			'#type' => 'item',
			'#markup' => '<div id="ajax_research_migration_details"></div>'
		);
		$form['selected_research_migration'] = array(
			'#type' => 'item',
			'#markup' => '<div id="ajax_selected_research_migration"></div>'
		);
	} //!$url_research_migration_id
	else {
		$research_migration_default_value = $url_research_migration_id;
		$form['research_migration_details'] = array(
			'#type' => 'item',
			'#markup' => '<div id="ajax_research_migration_details">' . _research_migration_details($research_migration_default_value) . '</div>'
		);
		// @FIXME
// l() expects a Url object, created from a route name or external URI.
// $form['selected_research_migration'] = array(
// 			'#type' => 'item',
// 			'#markup' => '<div id="ajax_selected_research_migration">' . l('Download Synopsis', "research-migration-project/download/project-file/" . $research_migration_default_value) . '<br>' . l('Download research migration', 'research-migration-project/full-download/project/' . $research_migration_default_value) . '</div>'
// 		);

	}
	return $form;
}
function research_migration_project_details_callback($form, $form_state)
{
	$commands = array();
	$research_migration_default_value = $form_state['values']['research_migration'];
	if ($research_migration_default_value != 0) {
		$form['research_migration_details']['#markup'] = _research_migration_details($research_migration_default_value);
		$research_migration_details = _research_migration_information($research_migration_default_value);
		$provider = \Drupal::entityTypeManager()->getStorage('user')->load($research_migration_details->uid);
		if ($research_migration_details->uid > 0) {
			// @FIXME
// l() expects a Url object, created from a route name or external URI.
// $commands[] = ajax_command_html('#ajax_selected_research_migration', l('Download Synopsis', "research-migration-project/download/project-file/" . $research_migration_default_value) . '<br>' . l('Download research migration', 'research-migration-project/full-download/project/' . $research_migration_default_value));

		} //$research_migration_details->uid > 0
		else {
			$commands[] = ajax_command_html('#ajax_selected_research_migration', '');
			$commands[] = ajax_command_html('#ajax_selected_research_migration', '');
		}
		$commands[] = ajax_command_html('#ajax_research_migration_details', _research_migration_details($research_migration_default_value));
	} //$research_migration_default_value != 0
	else {
		// $form['lab_experiment_list']['#options'] = _ajax_get_experiment_list();
		//  $commands[] = ajax_command_replace('#ajax_selected_experiment', drupal_render($form['lab_experiment_list']));
		$commands[] = ajax_command_html('#ajax_research_migration_details', '');
		$commands[] = ajax_command_html('#ajax_selected_research_migration', '');
		$commands[] = ajax_command_html('#ajax_selected_research_migration', '');
		$commands[] = ajax_command_data('#ajax_selected_research_migration', 'form_state_value_select', $form_state['values']['research_migration']);
	}
	return array(
		'#type' => 'ajax',
		'#commands' => $commands
	);
}
function bootstrap_table_format($headers, $rows)
{
	$thead = "";
	$tbody = "";
	foreach ($headers as $header) {
		$thead .= "<th>{$header}</th>";
	} //$headers as $header
	foreach ($rows as $row) {
		$tbody .= "<tr>";
		foreach ($row as $data) {
			$tbody .= "<td>{$data}</td>";
		} //$row as $data
		$tbody .= "</tr>";
	} //$rows as $row
	$table = "
            <table class='table table-bordered table-hover' style='margin-left:-140px'>
                <thead>{$thead}</thead>
                <tbody>{$tbody}</tbody>
            </table>
        ";
	return $table;
}
/*****************************************************/
function _list_of_research_migration()
{
	$research_migration_titles = array(
		'0' => 'Please select...'
	);
	//$lab_titles_q = db_query("SELECT * FROM {research_migration_proposal} WHERE solution_display = 1 ORDER BY lab_title ASC");
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('approval_status', 3);
	$query->orderBy('project_title', 'ASC');
	$research_migration_titles_q = $query->execute();
	while ($research_migration_titles_data = $research_migration_titles_q->fetchObject()) {
		$research_migration_titles[$research_migration_titles_data->id] = $research_migration_titles_data->project_title . ' (Proposed by ' . $research_migration_titles_data->name_title . ' ' . $research_migration_titles_data->contributor_name . ')';
	} //$research_migration_titles_data = $research_migration_titles_q->fetchObject()
	return $research_migration_titles;
}
function _research_migration_information($proposal_id)
{
	$query = \Drupal::database()->select('research_migration_proposal');
	$query->fields('research_migration_proposal');
	$query->condition('id', $proposal_id);
	$query->condition('approval_status', 3);
	$research_migration_q = $query->execute();
	$research_migration_data = $research_migration_q->fetchObject();
	if ($research_migration_data) {
		return $research_migration_data;
	} //$research_migration_data
	else {
		return 'Not found';
	}
}

function _research_migration_details($research_migration_default_value)
{
	
	$research_migration_details = _research_migration_information($research_migration_default_value);
	$faculty_name = $research_migration_details->faculty_name; 
	// var_dump($research_migration_details);die;
	if ($research_migration_default_value != 0 && !empty($faculty_name)) {
		$form['research_migration_details']['#markup'] = '<span style="color: rgb(128, 0, 0);"><strong>About the research migration</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Proposer Name:</strong> ' . $research_migration_details->name_title . ' ' . $research_migration_details->contributor_name . '</li>' . '<li><strong>Title of the research migration:</strong> ' . $research_migration_details->project_title . '</li>' . '<li><strong>Source of the Project:</strong> ' . $research_migration_details->source_of_the_project . '</li>' . '<li><strong>University:</strong> ' . $research_migration_details->university . '</li>' . '<li><strong>Name of the faculty:</strong> ' . $research_migration_details->faculty_name . '</li>' . '</ul>';
		$details = $form['research_migration_details']['#markup'];
		return $details;
	} //$research_migration_default_value != 0
	else{
		$form['research_migration_details']['#markup'] = '<span style="color: rgb(128, 0, 0);"><strong>About the research migration</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Proposer Name:</strong> ' . $research_migration_details->name_title . ' ' . $research_migration_details->contributor_name . '</li>' . '<li><strong>Title of the research migration:</strong> ' . $research_migration_details->project_title . '</li>' . '<li><strong>Source of the Project:</strong> ' . $research_migration_details->source_of_the_project . '</li>' . '<li><strong>University:</strong> ' . $research_migration_details->university . '</li>' . '</ul>';
		$details = $form['research_migration_details']['#markup'];
		return $details;
	}
}

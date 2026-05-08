<?php
function generate_pdf()
{
	$mpath = drupal_get_path('module', 'esim_research_migration');
	require($mpath.'/pdf/fpdf/fpdf.php');
	require($mpath.'/pdf/phpqrcode/qrlib.php');
	$user = \Drupal::currentUser();
	$x                 = $user->uid;
	$proposal_id       = arg(3);
	$query3 = \Drupal::database()->query("SELECT * FROM research_migration_proposal WHERE approval_status=3 AND uid= :uid AND id=:proposal_id", array(
		':uid' => $user->uid,
		':proposal_id'=>$proposal_id
	));
	$data3             = $query3->fetchObject();
	if($data3){
	if($data3->uid != $x){
		\Drupal::messenger()->addError('Certificate is not available');
		return;
	}
	}
	$gender            = array(
		'salutation' => 'Mr. /Ms.',
		'gender' => 'He/She'
	);
	if ($data3->gender) {
		if ($data3->gender == 'M') {
			$gender = array(
				'salutation' => 'Mr.',
				'gender' => 'He'
			);
		} //$data3->gender == 'M'
		else {
			$gender = array(
				'salutation' => 'Ms.',
				'gender' => 'She'
			);
		}
	} //$data3->gender
	$pdf = new FPDF('L', 'mm', 'Letter');
	if (!$pdf) {
		echo "Error!";
	} //!$pdf
	$pdf->AddPage();
	$image_bg = $mpath . "/pdf/images/bg_cert.png";
	// $pdf->Image($image_bg, 0, 0, $pdf->w, $pdf->h);
	$pdf->Image($image_bg, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
	//$pdf->Rect(5, 5, 267, 207, 'D');
	$pdf->SetMargins(18, 1, 18);
	//$pdf->Line(7.0, 7.0, 270.0, 7.0);
	//$pdf->Line(7.0, 7.0, 7.0, 210.0);
	//$pdf->Line(270.0, 210.0, 270.0, 7.0);
	//$pdf->Line(7.0, 210.0, 270.0, 210.0);
	$path   = drupal_get_path('module', 'esim_research_migration');
	//$image1 = $mpath . "/pdf/images/dwsim_logo.png";
	$pdf->Ln(15);
	$pdf->Ln(26);
	//$pdf->Cell(200, 8, $pdf->Image($image1, 105, 15, 0, 28), 0, 1, 'C');
	//$pdf->Ln(20);
	
	//$pdf->SetTextColor(139, 69, 19);
	//$pdf->Cell(240, 8, 'Certificate of Participation', '0', 1, 'C');
	//$pdf->Ln(26);
	$pdf->SetFont('Times', 'I', 18);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(0, 10, 'This is to certify that', '0', '1', 'C');
	$pdf->Ln(0);
	$pdf->SetFont('Times', 'I', 18);
	//$pdf->SetFont('Arial', 'BI', 25);
	$pdf->SetTextColor(37, 22, 247);
	$pdf->Cell(0, 10, $data3->name_title . '. ' . $data3->contributor_name, '0', '1', 'C');
	$pdf->Ln(0);
	$pdf->SetFont('Times', 'I', 18);

	// $university=SetTextColor(37, 22, 247);
	if (strtolower($data3->branch) != "others") {
		$title = WordWrap($data3->project_title,60);
		$institute = 'from ' . $data3->institute;
		$pdf->SetTextColor(0, 0, 0);
		//$pdf->Cell(240, 8, 'from ' . $data3->institute . ' has successfully', '0', '1', 'C');
		$pdf->MultiCell(0, 10, $institute, '0','C');
		$pdf->Ln(0);
		$pdf->Cell(0, 10, 'has successfully completed the Research Migration of', '0', '1', 'C');		
		$pdf->Ln(0);
		$pdf->SetTextColor(37, 22, 247);
		$pdf->SetFont('Times','I',20);
		$pdf->MultiCell(0, 10, $title, '0', 'C');		
		$pdf->Ln(0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Times','I',18);
		$pdf->Cell(0, 8, 'under eSim Research Migration project.', '0', '1', 'C');		
		$pdf->Ln(0);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Ln(4);
		// $pdf->Cell(0, 8, 'The work done is available at', '0', '1', 'C');
		$pdf->Cell(0, 8, '', '0', '1', 'C');
		$pdf->SetX(115);
		$pdf->SetFont('Times', 'I', 'U');
		$pdf->SetTextColor(37, 22, 247);
		// $pdf->write(0, 'https://esim.fossee.in/research-migration-project', 'https://esim.fossee.in/research-migration-project');
		$pdf->Ln(0);
		//$pdf->SetFont('Times', 'I', 16);
		//$pdf->Cell(0, 8, 'under Research Migration Project', '0', '1', 'C');
		//$pdf->Ln(0);
		//$pdf->SetFont('Helvetica', '', 18);
		//$pdf->Cell(0, 0, ' Research Migration Project', '0', '0', 'C');
		//$pdf->Cell(240, 4, '', '0', '1', 'C');
		//$pdf->SetX(120);
		//$pdf->SetFont('', 'U');
		//$pdf->SetTextColor(139, 69, 19);
		//$pdf->write(0, 'http://esim.fossee.in/', 'http://esim.fossee.in/');
		//$pdf->Ln(0);
		//$pdf->Cell(240, 8, 'Book: ' . $data2->book . ', Author: ' . $data2->author . '.', '0', '1', 'C');
		//$pdf->MultiCell(240, 8, 'Book: ' . $data2->book . ', Author: ' . $data2->author . '.', '0','C');
		$pdf->Ln(0);
	} //strtolower($data3->branch) != "others"
	else {
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Cell(240, 8, 'from ' . $data3->institute . ' college', '0', '1', 'C');
		$pdf->Ln(0);
		$pdf->Cell(240, 8, 'has successfully completed the Research Migration of', '0', '1', 'C');
		$pdf->Ln(0);
		$pdf->SetTextColor(139, 69, 19);
		$pdf->Cell(0, 12, $data3->project_title, '0', '1', 'C');
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Ln(0);
		$pdf->SetFont('Times', '', 16);
		$pdf->Cell(0, 8, ' under eSim Research Migration Project', '0', '1', 'C');
		//$pdf->Cell(240, 8, 'He/she has coded ' . $number_of_example . ' solved examples using DWSIM from the', '0', '1', 'C');
		//$pdf->Ln(0);
		//$pdf->Cell(240, 8, 'Book: ' . $data2->book . ', Author: ' . $data2->author . '.', '0', '1', 'C');
		//$pdf->Ln(0);
	}
	$proposal_get_id = 0;
	$UniqueString    = "";
	$tempDir         = $path . "/pdf/temp_prcode/";
	$query           = \Drupal::database()->select('research_migration_qr_code');
	$query->fields('research_migration_qr_code');
	$query->condition('proposal_id', $proposal_id);
	$result          = $query->execute();
	$data            = $result->fetchObject();
	$DBString        = $data->qr_code;
	$proposal_get_id = $data->proposal_id;
	if ($DBString == "" || $DBString == "null") {
		$UniqueString = generateRandomString();
		$query        = "
				INSERT INTO research_migration_qr_code
				(proposal_id,qr_code)
				VALUES
				(:proposal_id,:qr_code)
				";
		$args         = array(
			":proposal_id" => $proposal_id,
			":qr_code" => $UniqueString
		);
		$result       = \Drupal::database()->query($query, $args, $query);
	} //$DBString == "" || $DBString == "null"
	else {
		$UniqueString = $DBString;
	}
	$codeContents        = "https://esim.fossee.in/research-migration-project/certificates/verify/" . $UniqueString;
	$fileName            = 'generated_qrcode.png';
	$pngAbsoluteFilePath = $tempDir . $fileName;
	$urlRelativeFilePath = $path . "/pdf/temp_prcode/" . $fileName;
	QRcode::png($codeContents, $pngAbsoluteFilePath);
	$pdf->SetY(85);
	$pdf->SetX(350);
	$pdf->Ln(15);
	$sign1 = $path . "/pdf/images/sign1.png";
	// $sign2 = $path . "/pdf/images/sign2.png";
	$pdf->Image($sign1, $pdf->GetX()+85, $pdf->GetY() + 35, 75, 0);
	// $pdf->Image($sign2, $pdf->GetX()+160, $pdf->GetY() + 45, 75, 0);
	$pdf->Image($pngAbsoluteFilePath, $pdf->GetX()+15, $pdf->GetY()+60, 30, 0);
	$fossee = $path . "/pdf/images/esim-logo.png";
	$moe = $path . "/pdf/images/fossee.png"; 
	/*$nia_logo = $path . "/pdf/images/nia.png";
	
	$start_date = 1606761000;
	$end_date = 1640975364;
	if($data3->approval_date > $start_date && $data3->approval_date < $end_date){
		$pdf->Image($fossee, $pdf->GetX()+55, $pdf->GetY()+80, 50, 0);
		$pdf->Image($nia_logo, $pdf->GetX()+135, $pdf->GetY()+80, 50, 0);
		$pdf->Image($moe, $pdf->GetX()+200, $pdf->GetY()+80, 40, 0);
		$pdf->Ln(2);
	$ftr_line = $path . "/pdf/images/ftr_line.png"; 
	$pdf->Image($ftr_line, $pdf->GetX()+15, $pdf->GetY()+105, 220, 0);
	$pdf->Ln(3);
	$ftr_line_2 = $path . "/pdf/images/ftr_line_2.png"; 
	$pdf->Image($ftr_line_2, $pdf->GetX()+25, $pdf->GetY()+106, 190, 0);
}
else{
*/	$pdf->Image($fossee, $pdf->GetX()+100, $pdf->GetY()+67, 50, 0);
	$pdf->Image($moe, $pdf->GetX()+180, $pdf->GetY()+67, 40, 0);
	$pdf->Ln(4);
	// $ftr_line = $path . "/pdf/images/ftr_line.png"; 
	// $pdf->Image($ftr_line, $pdf->GetX()+15, $pdf->GetY()+90, 220, 0);
/*}*/
	//$pdf->SetX(29);
	//$pdf->SetY(-50);
	
	$pdf->SetFont('Times', 'I', 14);
	$pdf->SetLeftMargin(40);
	$pdf->GetY()+10;
	$pdf->Ln(52);
	$pdf->Cell(0, 0, $UniqueString, '0', '1', 'L');
	$pdf->Ln(31);

	$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Times','I',12);
		$pdf->Cell(0, 8, 'This is computer generated certificate and requires no signature. To verify, scan the QR code or visit:', '0', '1', 'L');	
	$pdf->Ln(0);
	$pdf->SetX(85);
	// $pdf->SetTextColor(139, 69, 19);
	$pdf->SetTextColor(0, 0, 255);
	$pdf->write(0, 'https://esim.fossee.in/research-migration-project/certificates/verify', 'https://esim.fossee.in/research-migration-project/certificates/verify');
		$pdf->Ln(0);
	$pdf->SetFont('Arial', 'I', 8);
	$pdf->SetTextColor(0, 0, 0);
	$filename = str_replace(' ', '-', $data3->contributor_name) . '-esim-Research-Migration-Certificate.pdf';
	$file     = $path . '/pdf/temp_certificate/' . $proposal_id . '_' . $filename;
	$pdf->Output($file, 'F');
	ob_clean();
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=" . $filename);
	header("Content-Length: " . filesize($file));
	header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Pragma: no-cache");
	flush();
	$fp = fopen($file, "r");
	while (!feof($fp)) {
		echo fread($fp, filesize($file));
		flush();
	} //!feof($fp)
	ob_end_flush();
	ob_clean();
	fclose($fp);
	unlink($file);
	//drupal_goto('flowsheeting-project/certificate');
	return;
}
function generateRandomString($length = 5)
{
	$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString     = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	} //$i = 0; $i < $length; $i++
	return $randomString;
}

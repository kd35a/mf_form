<?php

if(!function_exists('check_var'))
{
	function check_var($in, $type = '', $v2 = true, $default = '', $return_empty = false, $force_req_type = '')
	{
		$out = $temp = "";

		if($v2 == true)
		{
			$type2 = substr($in, 0, 3);

			if(isset($_SESSION[$in]) && ($force_req_type == "" || $force_req_type == "session"))
			{
				$temp = $_SESSION[$in] != '' ? $_SESSION[$in] : "";
			}

			else if(isset($_POST[$in]) && substr($in, 0, 3) != "ses" && ($force_req_type == "" || $force_req_type == "post"))
			{
				$temp = $_POST[$in] != '' ? $_POST[$in] : "";
			}

			else if(isset($_GET[$in]) && substr($in, 0, 3) != "ses" && ($force_req_type == "" || $force_req_type == "get"))
			{
				$temp = $_GET[$in] != '' ? $_GET[$in] : "";
			}
		}

		else
		{
			$type2 = "";
			$temp = $in;
		}

		if($type == 'telno' || $type2 == 'tel')
		{
			$temp = trim($temp);

			if($temp == '' || preg_match('/^([-+\d()\s]+)$/', $temp))
			{
				$out = $temp;
			}

			else
			{
				if($return_empty == false){$out = $temp;}
			}
		}

		else if($type == 'email' || $type2 == 'e-m')
		{
			$temp = trim($temp);

			if($temp == '' || preg_match('/^[-A-Za-z\d_.]+[@][A-Za-z\d_-]+([.][A-Za-z\d_-]+)*[.][A-Za-z]{2,8}$/', $temp))
			{
				$out = $temp;
			}

			else
			{
				if($return_empty == false){$out = $temp;}
			}
		}

		else if($type == 'url' || $type2 == 'url')
		{
			$temp = trim($temp);

			if($temp == '' || preg_match('/([-a-zA-Z\d_]+\.)*[-a-zA-Z\d_]+\.[-a-zA-Z\d_]{2,6}/', $temp))
			{
				$out = $temp;
			}

			else
			{
				if($return_empty == false){$out = $temp;}
			}
		}

		else if($type == 'date' || $type == 'shortDate' || $type == 'shortDate2' || $type2 == 'dte')
		{
			if($type == 'shortDate')
			{
				if($temp == '' || (preg_match('/^\d{4}-\d{2}$/', $temp) && substr($temp, 0, 4) > 1970 && substr($temp, 0, 4) < 2038))
				{
					$out = $temp;
				}

				else
				{
					if($temp == "0000-00")
					{
						$out = "";
					}

					else
					{
						if($return_empty == false){$out = trim($temp);}
					}
				}
			}

			else if($type == 'shortDate2') //Används av formulär för Securitas
			{
				if($temp == '' || preg_match('/^\d{6}$/', $temp))
				{
					$out = $temp;
				}

				else
				{
					if($temp == "000000")
					{
						$out = "";
					}

					else
					{
						if($return_empty == false){$out = trim($temp);}
					}
				}
			}

			else
			{
				if($temp == '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $temp) && substr($temp, 0, 4) > 1970 && substr($temp, 0, 4) < 2038))
				{
					$out = $temp;
				}

				else
				{
					if($temp == "0000-00-00")
					{
						$out = "";
					}

					else
					{
						if($return_empty == false){$out = trim($temp);}
					}
				}
			}
		}

		else if(is_array($temp) || $type == 'array' || $type2 == 'arr')
		{
			if(is_array($temp) || $temp == '')
			{
				$out = $temp; //Får aldrig köras addslashes() på detta
			}
		}

		else if($type == 'char' || $type2 == 'str')
		{
			$out = trim(addslashes($temp));

			$out_temp = htmlspecialchars($out);

			if($out != '' && $out_temp != $out)
			{
				$out = $out_temp;
			}
		}

		else if($type == 'float' || $type2 == 'dbl') //is_numeric()
		{
			if($temp == strval(floatval($temp)) || $temp == '')
			{
				$out = $temp;
			}

			else
			{
				if($return_empty == false){$out = trim(trim($temp), "&nbsp;");}
			}
		}

		else if($type == 'int' || $type2 == 'int')
		{
			$temp = str_replace(" ", "", $temp);

			if($temp == strval(intval($temp)) || $temp == '')
			{
				$out = $temp;
			}

			else
			{
				if($return_empty == false){$out = trim($temp);}
			}
		}

		if($out == '')
		{
			$out = $default;
		}

		return $out;
	}
}

######################
function show_textfield($var, $text, $id, $max_length = '', $field_length = 0, $required = false, $xtra = '', $default = '', $xtra_class = '', $type = 'text')
{
	$label = $after = $color = "";

	if($id == "0000-00-00"){$id = "";}

	//$xtra_class = $xtra_class != '' && substr($xtra_class, 0, 1) != " " ? " ".$xtra_class : $xtra_class;

	if($required == true)
	{
		$after .= " *";
		$xtra .= " required";
	}

	$size = $field_length > 0 ? " size='".$field_length."'" : "";
	$max_length = $max_length > 0 ? " maxlength='".$max_length."'" : "";

	if($default != '')
	{
		$default .= "...";

		$xtra .= " placeholder='".$default."'";
	}

	if($text != '')
	{
		$label = "<label for='".$var."'>".$text.$after."</label>";
	}

	$out = "<div class='form_textfield".($xtra_class != '' ? " ".$xtra_class : "")."'>"
		.$label
		."<input type='".$type."'".$size."".$max_length." name='".$var."' value='".stripslashes($id)."'".$xtra."/>
	</div>";

	return $out;
}
#################

######################################
function show_textarea($data)
{
	if(!isset($data['text'])){			$data['text'] = "";}
	if(!isset($data['value'])){			$data['value'] = "";}
	if(!isset($data['xtra'])){			$data['xtra'] = "";}
	if(!isset($data['class'])){			$data['class'] = "";}
	if(!isset($data['required'])){		$data['required'] = 0;}

	if($data['required'] == 1){	$data['xtra'] .= " required";}

	$out = "<div class='form_textarea".($data['class'] != '' ? " ".$data['class'] : "")."'>";

		if($data['text'] != '')
		{
			$out .= "<label for='".$data['name']."'>".$data['text'].($data['required'] == 1 ? " *" : "")."</label>";
		}

		$out .= "<textarea class='".(isset($data['size']) ? "textarea_".$data['size'] : "")."' name='".$data['name']."' id='".$data['name']."'".($data['xtra'] != '' ? " ".$data['xtra'] : "").">".stripslashes($data['value'])."</textarea>
	</div>";

	return $out;
}
#################

############################
function show_select($data)
{
	if(!isset($data['compare'])){	$data['compare'] = "";}
	if(!isset($data['xtra'])){		$data['xtra'] = "";}
	if(!isset($data['text'])){		$data['text'] = "";}
	if(!isset($data['maxsize'])){	$data['maxsize'] = 10;}
	if(!isset($data['required'])){	$data['required'] = 0;}
	if(!isset($data['class'])){		$data['class'] = "";}

	if(isset($data['data']) && $data['data'] != '')
	{
		$label = "";

		$count_temp = count($data['data']);

		if(preg_match('/(\[\])/', $data['name']))
		{
			$data['class'] .= ($data['class'] != '' ? " " : "")."top";
			$data['xtra'] .= " multiple='multiple' size='".($count_temp > $data['maxsize'] ? $data['maxsize'] : $count_temp)."'";
		}

		if($data['text'] != '')
		{
			$label = "<label for='".$data['name']."'>".$data['text']."</label>";
		}

		if($count_temp == 1 && $data['required'] == 1 && $data['text'] != '')
		{
			$out = input_hidden($data['name'], $data['data'][0][0]);
		}

		else
		{
			$out = "<div class='form_select".($data['class'] != '' ? " ".$data['class'] : "")."'>"
				.$label
				."<select id='".str_replace("[]", "", $data['name'])."' name='".$data['name']."'".$data['xtra'].">";

					$one_has_been_selected = $allready_selected = false;

					for($i = 0; $i < $count_temp; $i++)
					{
						if($data['data'][$i][0] > 0 && $data['data'][$i][0] == "opt_start")
						{
							$out .= "<optgroup label='".$data['data'][$i][1]."'>";
						}

						else if($data['data'][$i][0] > 0 && $data['data'][$i][0] == "opt_end")
						{
							$out .= "</optgroup>";
						}

						else
						{
							$out .= "<option value='".$data['data'][$i][0]."'";

								$intTotalRows = 0;

								if(($data['compare'] == $data['data'][$i][0] || $intTotalRows > 0) && $allready_selected == false)
								{
									$out .= " selected";

									if($intTotalRows == 0)
									{
										$allready_selected = true;
									}

									$one_has_been_selected = true;
								}

							$out .= ">".$data['data'][$i][1]."</option>";
						}
					}

				$out .= "</select>".($data['required'] == 1 ? " *" : "")
			."</div>";
		}

		return $out;
	}
}
############################

######################################
function show_checkbox($data)
{
	if(!isset($data['required'])){		$data['required'] = 0;}
	if(!isset($data['compare'])){		$data['compare'] = 0;}
	if(!isset($data['xtra'])){			$data['xtra'] = "";}
	if(!isset($data['xtra_class'])){	$data['xtra_class'] = "";}

	$label = "";

	$checked = $data['value'] == $data['compare'] ? " checked" : "";

	if($data['text'] != '')
	{
		$label = "<label for='".$data['name']."'>".$data['text'];

			if($data['required'] == 1)
			{
				$label .= " *";
			}
			
		$label .= "</label>";
	}

	$out = "<div class='form_checkbox".($data['xtra_class'] != '' ? " ".$data['xtra_class'] : "")."'>
		<input type='checkbox' id='".$data['name']."' name='".$data['name']."' value='".$data['value']."'".$checked.$data['xtra']."/>".$label
	."</div>";

	return $out;
}
#################

################################
function show_radio_input($data)
{
	if(!isset($data['label'])){			$data['label'] = "";}
	if(!isset($data['text'])){			$data['text'] = "";}
	if(!isset($data['compare'])){		$data['compare'] = "";}
	if(!isset($data['xtra'])){			$data['xtra'] = "";}
	if(!isset($data['xtra_class'])){	$data['xtra_class'] = "";}
	
	$checked = "";

	if($data['compare'] != '' && $data['compare'] == $data['value'])
	{
		$checked = " checked";
	}

	$out = "<div class='form_radio".($data['xtra_class'] != '' ? " ".$data['xtra_class'] : "")."'>
		<input type='radio' name='".$data['name']."' value='".$data['value']."'".$checked.$data['xtra']."/>";

		if($data['label'] != '')
		{
			$out .= "<label for='".$data['name']."'>".$data['label']."</label>";
		}

	$out .= "</div>";

	return $out;
}
#################

#################
function show_submit($var, $text, $xtra = '', $type = 'submit', $class = '')
{
	$out = "<button type='".$type."'".($var != '' ? " name='".$var."'" : "").($class != '' ? " class='".$class."'" : "").$xtra."><span>".$text."</span></button>";

	return $out;
}
#################

#####################
function input_hidden($name, $value, $allow_empty = false, $xtra = "")
{
	if($value != '' || $value == 0 || $allow_empty == true)
	{
		return "<input type='hidden' name='".$name."' value='".$value."'".$xtra."/>";
	}
}
#####################

########################################
function show_table_header($arr_header)
{
	$out = "<tr>";

		$count_temp = count($arr_header);

		for($i = 0; $i < $count_temp; $i++)
		{
			$out .= "<th>".$arr_header[$i]."</th>";
		}

	$out .= "</tr>";

	return $out;
}
########################################

function on_post_query_form()
{
	global $wpdb;

	if(isset($_POST['btnQuerySubmit']))
	{
		$intQueryID = check_var('intQueryID');

		$strAnswerIP = $_SERVER['REMOTE_ADDR'];

		$send_text = "";

		$result = $wpdb->get_results("SELECT queryName, queryEmail, queryEmailName FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."'");
		$r = $result[0];
		$strQueryName = $r->queryName;
		$strQueryEmail = $r->queryEmail;
		$strQueryEmailName = $r->queryEmailName;

		$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText, checkCode, queryTypeForced FROM ".$wpdb->prefix."query_check RIGHT JOIN ".$wpdb->prefix."query2type USING (checkID) INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");

		foreach($result as $r)
		{
			$intQuery2TypeID2 = $r->query2TypeID;
			$intQueryTypeID2 = $r->queryTypeID;
			$strQueryTypeText = $r->queryTypeText;
			$strCheckCode = $r->checkCode != '' ? $r->checkCode : "char";
			$intQueryTypeRequired = $r->queryTypeForced;

			$var = check_var($intQuery2TypeID2, $strCheckCode, true, '', false, 'post');

			$send_text .= $strQueryTypeText;

			//Hidden
			/*if($intQueryTypeID2 == 13)
			{
				$regexp1 = "/\[var=(.*?)]/";
				$regexp2 = "/\[(x*?)]/";

				if(preg_match($regexp1, $strQueryTypeText))
				{
					$query_var_name = get_match($regexp1, $strQueryTypeText, false);

					$var = check_var($query_var_name);
				}

				else if(preg_match($regexp2, $strQueryTypeText))
				{
					$query_counter_value = get_match($regexp2, $strQueryTypeText, false);
					$query_counter_label = str_replace("[".$query_counter_value."]", "", $strQueryTypeText);

					$strQueryCounter = $wpdb->get_var("SELECT answerText FROM ".$wpdb->prefix."query2answer INNER JOIN ".$wpdb->prefix."query_answer USING (answerID) INNER JOIN ".$wpdb->prefix."query2type USING (query2TypeID) WHERE ".$wpdb->prefix."query2answer.queryID = '".$intQueryID."' AND queryTypeID = '".$intQueryTypeID2."' AND answerText LIKE '%".$query_counter_label."%' ORDER BY answerCreated DESC");

					$query_counter_value_old = str_replace($query_counter_label, "", $strQueryCounter);

					if($query_counter_value_old == "")
					{
						$query_counter_value_old = 0;
					}

					$query_counter_value_old++;

					$var = $query_counter_label.zeroise($query_counter_value_old, strlen($query_counter_value));
				}

				else
				{
					$var = $strQueryTypeText;
				}

				$arr_query[] = "INSERT INTO ".$wpdb->prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$intQuery2TypeID2."', '".$var."')";

				$send_text .= " ".$var."\n";
			}*/

			//Connected
			###################################
			/*else if($intQueryTypeID2 == 14)
			{
				list($strQueryTypeText, $arr_content1) = explode(":", $strQueryTypeText);
				list($intQueryID_temp, $intQuery2TypeID2_temp) = explode("|", $arr_content1);

				$result = $wpdb->get_results("SELECT answerID FROM ".$wpdb->prefix."query2answer INNER JOIN ".$wpdb->prefix."query_answer USING (answerID) WHERE queryID = '".$intQueryID_temp."' AND query2TypeID = '".$intQuery2TypeID2_temp."' AND answerText = '".$var."'");
				$rows = count($result);

				if($rows == 0)
				{
					$var = "";

					echo "Error (".$strQueryTypeText.")";
					exit;
				}
			}*/
			###################################

			if($intQueryTypeID2 == 11)
			{
				$var = "";

				if(is_array($_POST[$intQuery2TypeID2]))
				{
					foreach($_POST[$intQuery2TypeID2] as $value)
					{
						$var .= ($var != '' ? "," : "").check_var($value, $strCheckCode, false);
					}
				}
			}

			if($var != '')
			{
				$arr_query[] = "INSERT INTO ".$wpdb->prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$intQuery2TypeID2."', '".$var."')";

				$send_text .= " ".$var."\n";
			}

			else if($intQueryTypeID2 == 8)
			{
				$var_radio = isset($_POST['radio_'.$intQuery2TypeID2]) ? check_var($_POST['radio_'.$intQuery2TypeID2], 'int', false) : '';

				if($var_radio != '')
				{
					$arr_query[] = "INSERT INTO ".$wpdb->prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$var_radio."', '')";
				}

				$strQueryTypeText_temp = $wpdb->get_var("SELECT queryTypeText FROM ".$wpdb->prefix."query2type WHERE query2TypeID = '".$var_radio."'");

				$send_text .= ($strQueryTypeText_temp == $strQueryTypeText ? " x" : "")."\n";
			}

			else
			{
				if($intQueryTypeRequired == true && $globals['error_text'] == '')
				{
					echo "You have to enter all mandatory fields (".$strQueryTypeText.")";
					exit;
				}

				$send_text .= "\n";
			}
		}

		if(isset($arr_query))
		{
			$updated = true;

			$wpdb->get_results("INSERT INTO ".$wpdb->prefix."query2answer (queryID, answerIP, answerCreated) VALUES ('".$intQueryID."', '".$strAnswerIP."', NOW())");

			$intAnswerID = mysql_insert_id();

			if($intAnswerID > 0)
			{
				foreach($arr_query as $query)
				{
					$wpdb->get_results(str_replace("[answer_id]", $intAnswerID, $query));

					if(mysql_affected_rows() == 0)
					{
						$updated = false;
					}
				}
			}

			else
			{
				$updated = false;
			}

			if($updated == true)
			{
				if($strQueryEmail != '' && isset($send_text) && $send_text != '')
				{
					/*require("include/phpmailer/class.phpmailer.php");

					$mail = new PHPMailer();*/

					/*$mail->IsSMTP();
					$mail->Host = "mail.yourdomain.com";
					//$mail->SMTPDebug  = 2;
					
					$mail->SMTPAuth = true;
					$mail->Host = "mail.yourdomain.com";
					$mail->Port = 26;
					$mail->Username = "yourname@yourdomain";
					$mail->Password = "yourpassword";*/

					/*$mail->From = get_bloginfo('admin_email');
					$mail->FromName = get_bloginfo('name');
					$mail->AddAddress($strQueryEmail);
					$mail->IsHTML(true);
					$mail->Subject = $strQueryEmailName;
					$mail->Body = $send_text;

					if(!$mail->Send())
					{
						echo "Mailer Error: ".$mail->ErrorInfo;
					}*/
					
					$headers = "From: ".get_bloginfo('name')." <".get_bloginfo('admin_email').">\r\n";
					wp_mail($strQueryEmail, $strQueryEmailName, $send_text, $headers);
				}

				$this_url = $_SERVER['HTTP_REFERER'];

				echo "<script>location.href = '".$this_url.(preg_match("/\?/", $this_url) ? "&" : "?")."sent';</script>";
			}

			else
			{
				echo "There was an error...";
			}

			exit;
		}

		else
		{
			echo "You have to enter all mandatory fields";
			exit;
		}
	}
}

################################
function show_query_form($data)
{
	global $wpdb, $intAnswerID;

	if(!isset($data['edit'])){			$data['edit'] = false;}
	if(!isset($data['sent'])){			$data['sent'] = false;}

	$out = "";

	$result = $wpdb->get_results("SELECT queryDeadline, queryAnswerName, queryAnswer, queryButtonText FROM ".$wpdb->prefix."query WHERE queryID = '".$data['query_id']."'");
	$r = $result[0];
	$dteQueryDeadline = $r->queryDeadline;
	$strQueryAnswerName = $r->queryAnswerName;
	$strQueryAnswer = $r->queryAnswer;
	$strQueryButtonText = $r->queryButtonText;

	if($data['sent'] == true)
	{
		$out .= "<h2>".$strQueryAnswerName."</h2>
		<p>".$strQueryAnswer."</p>";
	}

	else
	{
		if($dteQueryDeadline != '' && $dteQueryDeadline > '0000-00-00' && $dteQueryDeadline < date("Y-m-d") && $data['edit'] == false)
		{
			echo "Wrong deadline";
			exit;
		}

		else
		{
			$cols = $data['edit'] == true ? 5 : 2;

			$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText, queryTypeForced, queryTypeClass, query2TypeOrder FROM ".$wpdb->prefix."query2type INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$data['query_id']."' GROUP BY ".$wpdb->prefix."query2type.query2TypeID ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");
			$intTotalRows = count($result);

			if($intTotalRows > 0)
			{
				$out .= "<form method='post' action='' id='form_".$data['query_id']."' class='mf_form'>";

					$i = 1;

					$intQueryTypeID2_temp = $intQuery2TypeID2_temp = "";

					foreach($result as $r)
					{
						$intQuery2TypeID2 = $r->query2TypeID;
						$intQueryTypeID2 = $r->queryTypeID;
						$strQueryTypeText2 = $r->queryTypeText;
						$intQueryTypeRequired = $r->queryTypeForced;
						$strQueryTypeClass = $r->queryTypeClass;
						$intQuery2TypeOrder = $r->query2TypeOrder;

						$strAnswerText = "";

						if($intAnswerID > 0)
						{
							$resultInfo = $wpdb->get_results("SELECT answerText FROM ".$wpdb->prefix."query_answer WHERE query2TypeID = '".$intQuery2TypeID2."' AND answerID = '".$intAnswerID."' LIMIT 0, 1");
							$rowsInfo = count($resultInfo);

							if($rowsInfo > 0)
							{
								$r = $resultInfo[0];
								$strAnswerText = $r->answerText;
							}
						}

						//
						if($strAnswerText == '')
						{
							$strAnswerText = check_var($intQuery2TypeID2, 'char');
						}

						if($data['edit'] == true)
						{
							$out .= "<div id='type_".$intQuery2TypeID2."' class='form_row'>";
						}

							switch($intQueryTypeID2)
							{
								//Checkbox
								case 1:
									$out .= show_checkbox(array('name' => $intQuery2TypeID2, 'text' => $strQueryTypeText2, 'required' => $intQueryTypeRequired, 'value' => 1, 'compare' => $strAnswerText, 'xtra_class' => $strQueryTypeClass));
								break;

								//Input range
								case 2:
									$arr_content = explode("|", $strQueryTypeText2);

									$out .= show_textfield($intQuery2TypeID2, $arr_content[0]." (<span>".$strAnswerText."</span>)", $strAnswerText, 200, 0, $intQueryTypeRequired, " min='".$arr_content[1]."' max='".$arr_content[2]."'", "", $strQueryTypeClass, "range");
								break;

								//Input date
								case 7:
									$out .= show_textfield($intQuery2TypeID2, $strQueryTypeText2, $strAnswerText, 200, 0, $intQueryTypeRequired, "", "", $strQueryTypeClass, "date");
								break;

								//Radio button
								case 8:
									if($intQueryTypeID2 != $intQueryTypeID2_temp)
									{
										$intQuery2TypeID2_temp = $intQuery2TypeID2;
									}

									if($strAnswerText == '' && $intQueryTypeRequired == 1)
									{
										$strAnswerText = $intQuery2TypeID2;
									}

									$out .= show_radio_input(array('name' => 'radio_'.$intQuery2TypeID2_temp, 'label' => $strQueryTypeText2, 'value' => $intQuery2TypeID2, 'compare' => $strAnswerText, 'xtra_class' => $strQueryTypeClass));
								break;

								//Select
								case 10:
									$arr_content1 = explode(":", $strQueryTypeText2);
									$arr_content2 = explode(",", $arr_content1[1]);

									$arr_data = array();

									foreach($arr_content2 as $str_content)
									{
										$arr_content3 = explode("|", $str_content);

										$arr_data[] = array($arr_content3[0], $arr_content3[1]);
									}

									$out .= show_select(array('data' => $arr_data, 'name' => $intQuery2TypeID2, 'text' => $arr_content1[0], 'compare' => $strAnswerText, 'required' => $intQueryTypeRequired, 'class' => $strQueryTypeClass));
								break;

								//Select (multiple)
								case 11:
									$arr_content1 = explode(":", $strQueryTypeText2);
									$arr_content2 = explode(",", $arr_content1[1]);

									$arr_data = array();

									foreach($arr_content2 as $str_content)
									{
										$arr_content3 = explode("|", $str_content);

										$arr_data[] = array($arr_content3[0], $arr_content3[1]);
									}

									$out .= show_select(array('data' => $arr_data, 'name' => $intQuery2TypeID2."[]", 'text' => $arr_content1[0], 'compare' => $strAnswerText, 'required' => $intQueryTypeRequired, 'class' => $strQueryTypeClass));
								break;

								//Textfield
								case 3:
								//case 14:
									/*if($intQueryTypeID2 == 14)
									{
										list($strQueryTypeText2, $rest_value) = explode(":", $strQueryTypeText2);
									}*/

									$out .= show_textfield($intQuery2TypeID2, $strQueryTypeText2, $strAnswerText, 200, 0, ($intQueryTypeRequired == 1 ? true : false), '', '', $strQueryTypeClass);
								break;

								//Textarea
								case 4:
									$out .= show_textarea(array('name' => $intQuery2TypeID2, 'text' => $strQueryTypeText2, 'value' => $strAnswerText, 'size' => 'small', 'required' => $intQueryTypeRequired, 'class' => $strQueryTypeClass));
								break;

								//Text
								case 5:
									$out .= "<p".($strQueryTypeClass != '' ? " class='".$strQueryTypeClass."'" : "").">".$strQueryTypeText2."</p>";
								break;

								//Space
								case 6:
									$out .= $data['edit'] == true ? "<p class='grey".($strQueryTypeClass != '' ? " ".$strQueryTypeClass : "")."'>(space)</p>" : "<p".($strQueryTypeClass != '' ? " class='".$strQueryTypeClass."'" : "").">&nbsp;</p>";
								break;

								//Hidden info
								/*case 13:
									if($data['edit'] == true)
									{
										$out .= "<p class='grey".($strQueryTypeClass != '' ? " ".$strQueryTypeClass : "")."'>(Hidden: '".$strQueryTypeText2."')</p>";
									}
								break;*/
							}

						if($data['edit'] == true)
						{
							$out .= "<div class='form_buttons'>"
									.show_checkbox(array('name' => $intQuery2TypeID2, 'text' => "Required", 'value' => 1, 'compare' => $intQueryTypeRequired, 'xtra' => " class='ajax_checkbox' rel='require/type/".$intQuery2TypeID2."'"))
									."<a href='?page=mf_form/create/index.php&intQueryID=".$data['query_id']."&intQuery2TypeID=".$intQuery2TypeID2."#content' class='icon-edit'></a>
									<a href='#delete/type/".$intQuery2TypeID2."' class='ajax_link confirm_link icon-trash'></a>
								</div>
							</div>";
						}

						$i++;

						//Set temp id to check on next row if it is connected radio buttons
						$intQueryTypeID2_temp = $intQueryTypeID2;
					}

					if($intAnswerID > 0)
					{
						$out .= show_submit('btnQueryUpdate', "Update")
						.input_hidden('intQueryID', $data['query_id'])
						.input_hidden('intAnswerID', $intAnswerID);
					}

					else if($data['edit'] == false)
					{
						$out .= show_submit('btnQuerySubmit', ($strQueryButtonText != '' ? $strQueryButtonText : "Send"))
						.input_hidden('intQueryID', $data['query_id']);
					}

				$out .= "</form>";
			}
		}
	}

	return $out;
}
################################

//Tar fram ett resultat av en regexp i en text
#######################
function get_match($regexp, $in, $all = true)
{
	preg_match($regexp, $in, $out);

	if(count($out) > 0)
	{
		if($all == true)
		{
			return $out;
		}

		else if(count($out) <= 1)
		{
			return $out[0];
		}

		else
		{
			return $out[1];
		}
	}
}
#######################

//
##################
function set_file_content($data)
{
	$success = false;

	if(isset($data['realpath']) && $data['realpath'] == true)
	{
		$data['file'] = realpath($data['file']);
	}

	if($data['file'] != '')
	{
		if($fh = fopen($data['file'], $data['mode']))
		{
			if(fwrite($fh, $data['content']))
			{
				fclose($fh);

				$success = true;
			}
		}
	}

	return $success;
}
##################
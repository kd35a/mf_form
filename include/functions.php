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

		else if($type == 'array' || $type2 == 'arr')
		{
			if(is_array($temp) || $temp == '')
			{
				$out = $temp; //Får aldrig köras addslashes() på detta
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

	$xtra_class = $xtra_class != '' && substr($xtra_class, 0, 1) != " " ? " ".$xtra_class : $xtra_class;

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
		$label = "<label for='".$var."'>".$text.":".$after."</label>";
	}

	$out = "<div class='form_textfield".$xtra_class."'>"
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
			$out .= "<label for='".$data['name']."'>".$data['text'].":".($data['required'] == 1 ? " *" : "")."</label>";
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
	if(!isset($data['required'])){	$data['required'] = 0;}
	if(!isset($data['compare'])){	$data['compare'] = 0;}
	if(!isset($data['xtra'])){		$data['xtra'] = "";}

	$label = "";

	$checked = $data['value'] == $data['compare'] ? " checked" : "";

	if($data['text'] != '')
	{
		$label = "<label for='".$data['name']."'> ".$data['text'];

			if($data['required'] == 1)
			{
				$label .= " *";
			}
			
		$label .= "</label>";
	}

	$out = "<div class='form_checkbox'>
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
	
	$checked = "";

	if($data['compare'] != '' && $data['compare'] == $data['value'])
	{
		$checked = " checked";
	}

	return "<div class='form_radio'>
		<input type='radio' name='".$data['name']."' value='".$data['value']."'".$checked.$data['xtra']."/>".$data['label']
	."</div>";
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

################################
function show_query_form($data)
{
	global $wpdb, $intAnswerID;

	if(!isset($data['edit'])){			$data['edit'] = false;}
	if(!isset($data['sent'])){			$data['sent'] = false;}

	$out = "";

	$result = $wpdb->get_results("SELECT queryDeadline, queryAnswerName, queryAnswer FROM ".$wpdb->prefix."query WHERE queryID = '".$data['query_id']."'");
	$r = $result[0];
	$dteQueryDeadline = $r->queryDeadline;
	$strQueryAnswerName = $r->queryAnswerName;
	$strQueryAnswer = $r->queryAnswer;

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

			$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText, queryTypeForced, query2TypeOrder FROM ".$wpdb->prefix."query2type INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$data['query_id']."' GROUP BY ".$wpdb->prefix."query2type.query2TypeID ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");
			$intTotalRows = count($result);

			if($intTotalRows > 0)
			{
				$out .= "<form method='post' action='' id='form_".$data['query_id']."' class='sortable_form'>";

					$i = 1;

					$intQueryTypeID2_temp = $intQuery2TypeID2_temp = "";

					foreach($result as $r)
					{
						$intQuery2TypeID2 = $r->query2TypeID;
						$intQueryTypeID2 = $r->queryTypeID;
						$strQueryTypeText2 = $r->queryTypeText;
						$intQueryTypeRequired = $r->queryTypeForced;
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

						//Tar fram medskickad variabel. Till för att fylla på med info från svar som man gjort på publik sidan men som av någon anledning inte skickats iväg
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
								//Kryssruta
								case 1:
									$out .= show_checkbox(array('name' => $intQuery2TypeID2, 'text' => $strQueryTypeText2, 'required' => $intQueryTypeRequired, 'value' => 1, 'compare' => $strAnswerText));
								break;

								//Flervalsruta v2
								case 8:
									if($intQueryTypeID2 != $intQueryTypeID2_temp)
									{
										$intQuery2TypeID2_temp = $intQuery2TypeID2;
									}

									if($strAnswerText == '' && $intQueryTypeRequired == 1)
									{
										$strAnswerText = $intQuery2TypeID2;
									}

									$out .= show_radio_input(array('name' => 'radio_'.$intQuery2TypeID2_temp, 'label' => $strQueryTypeText2, 'value' => $intQuery2TypeID2, 'compare' => $strAnswerText));
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

									$out .= show_select(array('data' => $arr_data, 'name' => $intQuery2TypeID2, 'text' => $arr_content1[0], 'compare' => $strAnswerText, 'required' => $intQueryTypeRequired));
								break;

								//Textrad
								case 3:
								case 14:
									if($intQueryTypeID2 == 14)
									{
										list($strQueryTypeText2, $rest_value) = explode(":", $strQueryTypeText2);
									}

									$out .= show_textfield($intQuery2TypeID2, $strQueryTypeText2, $strAnswerText, 200, 0, ($intQueryTypeRequired == 1 ? true : false));
								break;

								//Textruta
								case 4:
									$out .= show_textarea(array('name' => $intQuery2TypeID2, 'text' => $strQueryTypeText2, 'value' => $strAnswerText, 'size' => 'small', 'required' => $intQueryTypeRequired));
								break;

								//Text
								case 5:
									$out .= "<p>".$strQueryTypeText2."</p>";
								break;

								//Mellanrum
								case 6:
									$out .= $data['edit'] == true ? "<p class='grey'>(mellanrum)</p>" : "<p>&nbsp;</p>";
								break;

								//Dold info
								case 13:
									if($data['edit'] == true)
									{
										$out .= "<p class='grey'>(Dolt: '".$strQueryTypeText2."')</p>";
									}
								break;
							}

						if($data['edit'] == true)
						{
							$out .= "<div class='form_buttons'>"
									.show_checkbox(array('name' => $intQuery2TypeID2, 'text' => "Tvinga", 'value' => 1, 'compare' => $intQueryTypeRequired, 'xtra' => " class='ajax_checkbox' rel='require/type/".$intQuery2TypeID2."'"))
									."<a href='?page=mf_form/create/index.php&intQueryID=".$data['query_id']."&intQuery2TypeID=".$intQuery2TypeID2."' class='foundicon-edit'></a>
									<a href='#delete/type/".$intQuery2TypeID2."' class='ajax_link confirm_link foundicon-trash'></a>
								</div>
							</div>";

							//<span class='moveable'>Flytta</span>
						}

						$i++;

						//Sätter temporärt så att det går att jämföra med föregående objekt i formuläret och avgöra om det är sammanhängande radiobuttons
						$intQueryTypeID2_temp = $intQueryTypeID2;
					}

					if($intAnswerID > 0) //$data['edit'] == true && 
					{
						$out .= show_submit('btnQueryUpdate', "Uppdatera")
						.input_hidden('intQueryID', $data['query_id'])
						.input_hidden('intAnswerID', $intAnswerID);
					}

					else if($data['edit'] == false)
					{
						$out .= show_submit('btnQuerySubmit', "Skicka")
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
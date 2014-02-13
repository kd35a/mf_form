<?php

//Kryptera
######################
class encryption 
{
	function encryption($type)
	{
		$this->set_key($type);
		$this->iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
	}

	function set_key($type)
	{
		$this->key = "mf_crypt".$type;
	}

	function encrypt($text, $key = "")
	{
		if($key != '')
		{
			$this->set_key($key);
		}

		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->key, $text, MCRYPT_MODE_ECB, $this->iv));
	}

	function decrypt($text, $key = "")
	{
		if($key != '')
		{
			$this->set_key($key);
		}

		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->key, base64_decode($text), MCRYPT_MODE_ECB, $this->iv));
	}
}
######################

function wp_date_format($date, $full_datetime = false)
{
	global $wpdb;

	$date_format = $wpdb->get_var("SELECT option_value FROM ".$wpdb->options." WHERE option_name = '".($full_datetime == true ? "links_updated_date_format" : "date_format")."'");

	return date($date_format, strtotime($date));
}

if(!function_exists('check_var'))
{
	function check_var($in, $type = '', $v2 = true, $default = '', $return_empty = false, $force_req_type = '')
	{
		global $arrErrorField;

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
				$out = str_replace(" ", "", $temp);
			}

			else
			{
				if($return_empty == false){$out = $temp;}
				$arrErrorField[] = $in;
			}
		}

		else if($type == 'soc' || $type2 == 'soc')
		{
			$temp = trim($temp);
			$temp = str_replace(array("-", " "), "", $temp);

			if(strlen($temp) == 12)
			{
				$temp = substr($temp, 2);
			}

			if($temp == '' || strlen($temp) == 10 && preg_match('/^([-\d\s]+)$/', $temp))
			{
				$out = $temp;
			}

			else
			{
				if($return_empty == false){$out = $temp;}
				$arrErrorField[] = $in;
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
				$arrErrorField[] = $in;
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
				$arrErrorField[] = $in;
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
						$arrErrorField[] = $in;
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
						$arrErrorField[] = $in;
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
						$arrErrorField[] = $in;
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
				$arrErrorField[] = $in;
			}
		}

		else if($type == 'int' || $type2 == 'int' || $type == 'zip' || $type2 == 'zip')
		{
			$temp = str_replace(" ", "", $temp);

			if($temp == strval(intval($temp)) || $temp == '')
			{
				$out = $temp;
			}

			else
			{
				if($return_empty == false){$out = trim($temp);}
				$arrErrorField[] = $in;
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
	global $arrErrorField;

	$label = $after = $color = "";

	if($id == "0000-00-00"){$id = "";}

	if($required == true)
	{
		$after .= " *";
		$xtra .= " required";
	}

	$size = $field_length > 0 ? " size='".$field_length."'" : "";
	$max_length = $max_length > 0 ? " maxlength='".$max_length."'" : "";

	if(count($arrErrorField) > 0 && preg_match('/('. implode('|', $arrErrorField) .')/', $var))
	{
		$xtra_class .= " red_border";
	}

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

			$container_class = "form_select_multiple";
		}

		else
		{
			$container_class = "form_select";
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
			$out = "<div class='".$container_class.($data['class'] != '' ? " ".$data['class'] : "")."'>"
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

######################
function show_file_field($data)
{
	if(!isset($data['text'])){		$data['text'] = "";}
	if(!isset($data['class'])){		$data['class'] = "";}
	//if(!isset($data['size'])){		$data['size'] = 0;}
	if(!isset($data['multiple'])){	$data['multiple'] = false;}

	$label = "";

	if($data['text'] != '')
	{
		$label = "<label for='".$data['name']."'>".$data['text']."</label>";
	}

	$out .= "<div class='form_file_input".($data['class'] != '' ? " ".$data['class'] : "")."'>"
		.$label
		."<input type='file'".($data['multiple'] == true ? " multiple='true'" : "")." name='".$data['name'].($data['multiple'] == true ? "[]" : "")."' value=''/>
	</div>";

	return $out;
}
######################

#################
function show_submit($var, $text, $xtra = '', $type = 'submit', $class = '')
{
	return "<button type='".$type."'".($var != '' ? " name='".$var."'" : "").($class != '' ? " class='".$class."'" : "").$xtra."><span>".$text."</span></button>";
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

function get_file_content($data)
{
	global $globals, $arr_lang;

	$content = "";

	if(filesize($data['file']) > 0)
	{
		if($fh = fopen(realpath($data['file']), 'r'))
		{
			$content = fread($fh, filesize($data['file']));
			fclose($fh);
		}

		else
		{
			insert_error($arr_lang['file_not_opened']." (".$data['file'].")");
		}
	}

	return $content;
}

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

	$strAnswerIP = $_SERVER['REMOTE_ADDR'];
	$dup_ip = false;

	if(isset($_POST['btnQuerySubmit']))
	{
		$intQueryID = check_var('intQueryID');

		$send_text = $error_text = $send_from = "";

		$result = $wpdb->get_results("SELECT queryDenyDups, queryEncrypted, queryName, queryEmail, queryEmailName, queryMandatoryText FROM ".$wpdb->base_prefix."query WHERE queryID = '".$intQueryID."'");
		$r = $result[0];
		$intQueryDenyDups = $r->queryDenyDups;
		$intQueryEncrypted = $r->queryEncrypted;
		$strQueryName = $r->queryName;
		$strQueryEmail = $r->queryEmail;
		$strQueryEmailName = $r->queryEmailName;
		$strQueryMandatoryText = $r->queryMandatoryText;
		
		if($intQueryEncrypted == 1)
		{
			$encryption = new encryption("query");
		}

		//
		#######################
		if($intQueryDenyDups == 1)
		{
			$rowsIP = $wpdb->get_var("SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2answer WHERE queryID = '".$intQueryID."' AND answerIP = '".$strAnswerIP."' LIMIT 0, 1");

			if($rowsIP > 0)
			{
				$dup_ip = true;
			}
		}
		#######################

		if($dup_ip == true)
		{
			$error_text = "You have already voted"; // (".$strAnswerIP.")
		}

		else
		{
			$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText, checkCode, queryTypeForced FROM ".$wpdb->base_prefix."query_check RIGHT JOIN ".$wpdb->base_prefix."query2type USING (checkID) INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");

			foreach($result as $r)
			{
				$intQuery2TypeID2 = $r->query2TypeID;
				$intQueryTypeID2 = $r->queryTypeID;
				$strQueryTypeText = $r->queryTypeText;
				$strCheckCode = $r->checkCode != '' ? $r->checkCode : "char";
				$intQueryTypeRequired = $r->queryTypeForced;

				$var = $var_send = check_var($intQuery2TypeID2, $strCheckCode, true, '', true, 'post'); //Changed to true on return empty 131226

				if($var != '' && $intQueryTypeID2 == 3 && $strCheckCode == 'email')
				{
					$send_from = $var;
				}

				if($intQueryTypeID2 == 2)
				{
					list($strQueryTypeText, $rest) = explode("|", $strQueryTypeText);
				}

				else if($intQueryTypeID2 == 7)
				{
					$var_send = wp_date_format($var);
				}

				else if($intQueryTypeID2 == 10)
				{
					$arr_content1 = explode(":", $strQueryTypeText);
					$arr_content2 = explode(",", $arr_content1[1]);

					foreach($arr_content2 as $str_content)
					{
						$arr_content3 = explode("|", $str_content);

						if($var == $arr_content3[0])
						{
							$var_send = $arr_content3[1];
						}
					}

					$strQueryTypeText = $arr_content1[0];
				}

				else if($intQueryTypeID2 == 11)
				{
					$var = "";

					if(is_array($_POST[$intQuery2TypeID2]))
					{
						foreach($_POST[$intQuery2TypeID2] as $value)
						{
							$var .= ($var != '' ? "," : "").check_var($value, $strCheckCode, false);
						}
					}

					$arr_content1 = explode(":", $strQueryTypeText);
					$arr_content2 = explode(",", $arr_content1[1]);

					$arr_answer_text = explode(",", $var);

					$var_send = "";

					foreach($arr_content2 as $str_content)
					{
						$arr_content3 = explode("|", $str_content);

						if(in_array($arr_content3[0], $arr_answer_text))
						{
							$var_send .= ($var_send != '' ? ", " : "").$arr_content3[1];
						}
					}

					$strQueryTypeText = $arr_content1[0];
				}

				$send_text .= "\n".$strQueryTypeText."\n";

				if($var != '')
				{
					if($intQueryEncrypted == 1)
					{
						$var = $encryption->encrypt($var, $intQuery2TypeID2);
					}

					$arr_query[] = "INSERT INTO ".$wpdb->base_prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$intQuery2TypeID2."', '".$var."')";

					$send_text .= " ".$var_send."\n";
				}

				else if($intQueryTypeID2 == 8)
				{
					$var_radio = isset($_POST['radio_'.$intQuery2TypeID2]) ? check_var($_POST['radio_'.$intQuery2TypeID2], 'int', false) : '';

					if($var_radio != '')
					{
						if($intQueryEncrypted == 1)
						{
							$var_radio = $encryption->encrypt($var_radio, $intQuery2TypeID2);
						}

						$arr_query[] = "INSERT INTO ".$wpdb->base_prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$var_radio."', '')";
					}

					$strQueryTypeText_temp = $wpdb->get_var("SELECT queryTypeText FROM ".$wpdb->base_prefix."query2type WHERE query2TypeID = '".$var_radio."'");

					$send_text .= ($strQueryTypeText_temp == $strQueryTypeText ? " x" : "")."\n";
				}

				else
				{
					if($intQueryTypeRequired == true && $globals['error_text'] == '')
					{
						$error_text = ($strQueryMandatoryText != '' ? $strQueryMandatoryText : "You have to enter all mandatory fields"); // (".$strQueryTypeText.")
					}
				}
			}
		}

		if($error_text != '')
		{
			echo "<p class='noti_error'>".$error_text."</p>";
		}

		else if(isset($arr_query))
		{
			$updated = true;

			$wpdb->get_results("INSERT INTO ".$wpdb->base_prefix."query2answer (queryID, answerIP, answerCreated) VALUES ('".$intQueryID."', '".$strAnswerIP."', NOW())");

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
					if($send_from != '')
					{
						$headers = "From: ".$send_from." <".$send_from.">\r\n";
					}

					else
					{
						$headers = "From: ".get_bloginfo('name')." <".get_bloginfo('admin_email').">\r\n";
					}

					wp_mail($strQueryEmail, $strQueryEmailName, strip_tags($send_text), $headers);
				}

				$this_url = $_SERVER['HTTP_REFERER'];

				$data['sent'] = true;
			}

			else
			{
				echo "There was an error...";
				exit;
			}
		}

		/*else
		{
			echo "Something went wrong...";
			exit;
		}*/
	}

	if(!isset($data['edit'])){			$data['edit'] = false;}
	if(!isset($data['sent'])){			$data['sent'] = false;}
	if(!isset($data['query2type_id'])){	$data['query2type_id'] = 0;}

	$out = "";

	$result = $wpdb->get_results("SELECT queryDenyDups, queryShowAnswers, queryAnswerName, queryAnswer, queryButtonText FROM ".$wpdb->base_prefix."query WHERE queryID = '".$data['query_id']."'"); //, queryDeadline
	$r = $result[0];
	$intQueryDenyDups = $r->queryDenyDups;
	$intQueryShowAnswers = $r->queryShowAnswers;
	//$dteQueryDeadline = $r->queryDeadline;
	$strQueryAnswerName = $r->queryAnswerName;
	$strQueryAnswer = $r->queryAnswer;
	$strQueryButtonText = $r->queryButtonText;

	//
	#######################
	if($intQueryDenyDups == 1)
	{
		$rowsIP = $wpdb->get_var("SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2answer WHERE queryID = '".$data['query_id']."' AND answerIP = '".$strAnswerIP."' LIMIT 0, 1");

		/*if($strAnswerIP == "46.195.158.105")
		{
			$out .= $rowsIP." (SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2answer WHERE queryID = '".$data['query_id']."' AND answerIP = '".$strAnswerIP."' LIMIT 0, 1)";
		}*/

		if($rowsIP > 0)
		{
			$dup_ip = true;
		}
	}
	#######################

	if($data['sent'] == true || $dup_ip == true)
	{
		$out .= "<div class='mf_form mf_form_results'>";

			if($intQueryShowAnswers == 1)
			{
				$intTotalAnswers = $wpdb->get_var("SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_answer USING (query2TypeID) WHERE queryID = '".$data['query_id']."' AND queryTypeID = '8'");

				$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText FROM ".$wpdb->base_prefix."query2type WHERE queryID = '".$data['query_id']."' AND (queryTypeID = '5' OR queryTypeID = '8') ORDER BY query2TypeOrder ASC, query2TypeCreated ASC"); // OR queryTypeID = '6'
				$intTotalRows = count($result);

				if($intTotalRows > 0)
				{
					foreach($result as $r)
					{
						$intQuery2TypeID2 = $r->query2TypeID;
						$intQueryTypeID2 = $r->queryTypeID;
						$strQueryTypeText2 = $r->queryTypeText;

						$out .= "<div".($intQueryTypeID2 == 8 ? " class='form_radio'" : "").">";

							if($intQueryTypeID2 == 8)
							{
								$intAnswerCount = $wpdb->get_var("SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_answer USING (query2TypeID) WHERE queryID = '".$data['query_id']."' AND queryTypeID = '8' AND query2TypeID = '".$intQuery2TypeID2."'");

								$intAnswerPercent = round($intAnswerCount / $intTotalAnswers * 100);

								$out .= "<div style='width: ".$intAnswerPercent."%'>&nbsp;</div>";
							}

							$out .= "<p>"
								.$strQueryTypeText2;

								if($intQueryTypeID2 == 8)
								{
									$out .= "<span>".$intAnswerPercent."%</span>";
								}

							$out .= "</p>
						</div>";
					}
				}
			}

			else
			{
				$out .= "<h2>".$strQueryAnswerName."</h2>
				<div>".$strQueryAnswer."</div>";
			}

		$out .= "</div>";
	}

	else
	{
		/*if($dteQueryDeadline != '' && $dteQueryDeadline > '0000-00-00' && $dteQueryDeadline < date("Y-m-d") && $data['edit'] == false)
		{
			echo "Wrong deadline";
			exit;
		}

		else
		{*/
			$cols = $data['edit'] == true ? 5 : 2;

			$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, checkCode, queryTypeText, queryTypeForced, queryTypeClass, query2TypeOrder FROM ".$wpdb->base_prefix."query_check RIGHT JOIN ".$wpdb->base_prefix."query2type USING (checkID) INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '".$data['query_id']."' GROUP BY ".$wpdb->base_prefix."query2type.query2TypeID ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");
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
						$strCheckCode = $r->checkCode;
						$strQueryTypeText2 = $r->queryTypeText;
						$intQueryTypeRequired = $r->queryTypeForced;
						$strQueryTypeClass = $r->queryTypeClass;
						$intQuery2TypeOrder = $r->query2TypeOrder;

						$strAnswerText = "";

						if($intAnswerID > 0)
						{
							$resultInfo = $wpdb->get_results("SELECT answerText FROM ".$wpdb->base_prefix."query_answer WHERE query2TypeID = '".$intQuery2TypeID2."' AND answerID = '".$intAnswerID."' LIMIT 0, 1");
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
							$out .= "<div id='type_".$intQuery2TypeID2."' class='form_row".($data['query2type_id'] == $intQuery2TypeID2 ? " active" : "")."'>";
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

									if($strAnswerText == '' && isset($arr_content[3]))
									{
										$strAnswerText = $arr_content[3];
									}

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
									$out .= show_textfield($intQuery2TypeID2, $strQueryTypeText2, $strAnswerText, 200, 0, ($intQueryTypeRequired == 1 ? true : false), '', '', $strQueryTypeClass.($strCheckCode == "zip" ? " form_zipcode" : ""));
								break;

								//Textarea
								case 4:
									$out .= show_textarea(array('name' => $intQuery2TypeID2, 'text' => $strQueryTypeText2, 'value' => $strAnswerText, 'size' => 'small', 'required' => $intQueryTypeRequired, 'class' => $strQueryTypeClass));
								break;

								//Text
								case 5:
									$out .= "<div".($strQueryTypeClass != '' ? " class='".$strQueryTypeClass."'" : "").">".$strQueryTypeText2."</div>";
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
		//}
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
<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );

jimport( 'joomla.application.component.model' );
jimport( 'joomla.database.table.user' );



class jgiveModelmasspayment extends JModelLegacy
{
	function performmasspay()
	{
		$params=JComponentHelper::getParams('com_jgive');
		$log = &JLog::getInstance('paypal.log');
		$msg	= "<table border=\"0\" width=\"100%\">
					<tr>
						<th align=\"center\">".JText::_( 'COM_JGIVE_OWNER' )."</th>
						<th align=\"center\">".JText::_( 'COM_JGIVE_PAYOUT_AMT' )."</th>
						<th align=\"center\">".JText::_( 'COM_JGIVE_PAYOUT_STATUS' )."</th>
					</tr>";

		$db= &JFactory::getDBO();
		$event_owners = $this->getAllCampaignCretor();
		$nvpStr ="";
		$k=0;
		$log->addEntry(array('comment' => '------------------New Masspayment Data-----------'));
		$arrayuniqid=$eventnamearr=$confirm=$arrayuserid =$reason=$payvaluestr=$arraypayval="";
		for ($i=0, $n=count( $event_owners); $i < $n; $i++)
			{
					$payvalue=0;
					$log->addEntry(array('comment' => 'Owner Id Being Processed'.$event_owners[$i]->creator_id));
					$rows = $this->getCampaignData($event_owners[$i]->creator_id);
					$paytotal=$rows->nprice-$rows->nfee;
					$pusers = JFactory::getuser($event_owners[$i]->creator_id);
					//Get Total Payout
					$sumresult = $this->gettotalpayout($event_owners[$i]->creator_id);
					$payvalue=$paytotal-$sumresult;
					$log->addEntry(array('comment' => 'Amount earned: '.$paytotal.' Amount paid: '.$sumresult.' Balance: '.$payvalue));
					///added by sagar to check minimum value for masspayment
					if($payvalue<$params->get('min_val_masspay'))
					{
						$log->addEntry(array('comment' => JText::sprintf('MIN_AMT_MASSPAY_ERROR',$params->get('min_val_masspay'))));
						continue;
					}

					//event owner emailid
					$payee_email=$this->getpaypalemail_campaignowner($event_owners[$i]->creator_id,$event_owners[$i]->id);
					if($payvalue <= 0)
					{
						$log->addEntry(array('comment' => 'Amount is less than zero-'.$payvalue.' so skip payment'));
						continue;
					}
					$payvaluestr.=$payvalue."&";
					$arraypayval[$k] = $payvalue;
					$log->addEntry(array('comment' => 'Net Amount Paid-'.$payvalue));
					$paydata['creator']=$event_owners[$i]->creator_id;
					$paydata['amount']=$payvalue;
					$paydata['status']='0';
					$paydata['payee_name']=$pusers->name;
					$paydata['email_id']=$payee_email;
					$paydata['type']='campaign';

					//Insert Payout Data
					$insertid=$this->insertPayoutData($paydata);
					$arrayuserid[$k] = $event_owners[$i]->creator_id;
					$eventnamearr[$k] = $event_owners[$i]->id;
					$confirm[$k] = $event_owners[$i]->confirmedcount;
					$receiverEmail = urlencode($payee_email);//email
					$amount = urlencode($payvalue);
					$uniqid = urlencode($insertid);
					$arrayuniqid[$k]=$uniqid;
					$app 		=JFactory::getApplication();
					$sitename	= $app->getCfg('sitename');
					$note=JText::sprintf('COM_JGIVE_MASSPAY_NOTE',date('Y-m-d H:i:s'),$sitename);
					$note = urlencode($note);
					$nvpStr.="&L_EMAIL$k=$receiverEmail&L_Amt$k=$amount&L_NOTE$k=$note&L_UNIQUEID$k=$uniqid";
					$k++;
			}
			$log->addEntry(array('comment' => 'Paypal Request string-'.$nvpStr));
			//print_r($nvpStr);die;
			$httpParsedResponseAr = $this->PPHttpPost('MassPay', $nvpStr);
			$new_array = array_map(create_function('$key, $value', 'return $key."=".$value." & ";'), array_keys($httpParsedResponseAr), array_values($httpParsedResponseAr));
			$Responsestr=implode($new_array);
			$log->addEntry(array('comment' => 'Paypal Response string-'.$Responsestr));
					if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
					{
						for($l=0; $l<count($arrayuniqid); $l++)
						{
							$obj = new stdClass;
							$obj ->id					= $arrayuniqid[$l];
							$obj ->transaction_id  	= $httpParsedResponseAr["CORRELATIONID"];
							$obj ->status     = '1';
							$resp=$this->updatePayoutData($obj);
							if ($httpParsedResponseAr["ACK"] == "FAILURE")
								$reason.=urldecode ($httpParsedResponseAr['L_SHORTMESSAGE'.$l])."&";
							else $reason.= '--';
						}
					}
			if(!empty($arrayuserid))
			{
				for($j=0; $j<count($arrayuserid); $j++)
				{
						$msg	.= "<tr>
									<td align=\"center\">{$arrayuserid[$j]}</td>
									<td align=\"center\">{$arraypayval[$j]}</td>
									<td align=\"center\">".strtoupper($httpParsedResponseAr["ACK"])."</td>

									</tr>";
				}
				$msg .= "</table>";
		  }
		  else
		  {
				$msg="<table><tr><td>".JText::_( 'COM_JGIVE_NO_USERS_PROCESS' )."</td></tr></table>";
		  }
      return $msg;
    }

    //payouts data
	function PPHttpPost($methodName_, $nvpStr_) {

		$api	= $this->getApiDetails();
		if(!$api)
		{
			echo JTEXT::_('COM_JGIVE_MASS_PAY_ERR');
		}
		$API_UserName	= $api['apiuser'];
		$API_Password	= $api['apipass'];
		$API_Signature	= $api['apisign'];
		$API_Endpoint 	= $api['apiend'];
		$version 		= $api['apiv'];

		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		// Get response from the server.
		$httpResponse = curl_exec($ch);

		if(!$httpResponse) {
			exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}

		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);

		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
			exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}

		return $httpParsedResponseAr;
	}
		function getApiDetails()
		{

			$params=JComponentHelper::getParams('com_jgive');
			$apiend='https://api-3t.paypal.com/nvp';
			if($params->get('sandbox')==1)
			{
				$apiend='https://api-3t.sandbox.paypal.com/nvp';
			}
			$masspay_config=array(
			'apiuser'=>		$params->get('apiuser'),
			'apipass'=>		$params->get('apipass'),
			'apisign'=>		$params->get('apisign'),
			'apiend'=>		$apiend,
			'apiv'=>			$params->get('apiv'),
			);
			$var	= $masspay_config;
			return $var;
		}

	function getAllCampaignCretor()
	{
		$db= &JFactory::getDBO();
		$query="SELECT camp.id ,camp.creator_id,count(o.campaign_id) as confirmedcount FROM `#__jg_campaigns`as camp
				INNER JOIN `#__jg_orders` as o ON
				o.campaign_id=camp.id
				WHERE o.status='C'
				GROUP BY camp.paypal_email";

		$db->setQuery($query);
		$createds = $db->loadObjectList();
		return $createds;

	}

	function gettotalpayout($creator)
	{
		$db= &JFactory::getDBO();
		$query = "SELECT sum(amount) AS nsum
							FROM #__jg_payouts
							WHERE  status='1'
							AND user_id ='".$creator."'
							GROUP BY user_id";
		$db->setQuery($query);
		$sumresult = $db->loadResult();
		return $sumresult;

	}

	function insertPayoutData($paydata)
	{
			$db= &JFactory::getDBO();
			$res = new stdClass();
			$res->id 					= '';
			$res->user_id   	= $paydata['creator'];
			$res->date	  		= date("Y-m-d H:i:s");
			$res->amount	  	= $paydata['amount'];
			$res->status   		= $paydata['status'];
			$res->payee_name	= $paydata['payee_name'];
			$res->email_id		= $paydata['email_id'];
			$res->ip_address	= $_SERVER["REMOTE_ADDR"];
			$res->type          =$paydata['type'];

			if (!$db->insertObject( '#__jg_payouts', $res, 'id' ))
			{
				echo $db->stderr();
				return false;
			}
	return $db->insertid();
	}

	function updatePayoutData($obj){
			$db= &JFactory::getDBO();
			if(!$db->updateObject( '#__jg_payouts', $obj, 'id' ))
			{
				echo $db->stderr();
				return false;
			}
			return true;
	}

	function getpaypalemail_campaignowner($cusid,$cid)
	{
		$db= &JFactory::getDBO();
		$sql="SELECT paypal_email
		      FROM #__jg_campaigns
              WHERE creator_id=".$cusid."
              AND id=".$cid."
              GROUP BY paypal_email";
		$db->setQuery($sql);
		$result = $db->LoadResult();
		return $result;
	}

	function getCampaignData($creator)
	{
		$db= &JFactory::getDBO();
		$query="SELECT camp.id, camp.creator_id, SUM( o.original_amount ) AS nprice, SUM( o.fee ) AS nfee
				FROM  `#__jg_campaigns` AS camp
				INNER JOIN  `#__jg_orders` AS o ON o.campaign_id = camp.id
				WHERE o.status =  'C'
				AND camp.creator_id =$creator
				GROUP BY camp.paypal_email";
		$db->setQuery($query);
		$rows = $db->loadObject();
		return $rows;
	}

}//class

<?php
// 
// Developed by CoCo
// Copyright (C) 2016 CoCoSoFt
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

  
//	syslog(LOG_WARNING, "channel reader running");

// memory jogger ToDo
  require_once $_SERVER["DOCUMENT_ROOT"] . "../php/srkAmiHelperClass";
  require_once $_SERVER["DOCUMENT_ROOT"] . "../php/srkHelperClass";

  $amiHelper = new amiHelper;
  $channels = build_channel_array($amiHelper->get_coreShowChannels());

/*
	$result = `sudo /usr/sbin/asterisk -rx 'core show channels concise'`;
  $data = explode("\n", $result);
*/

  $stream = null;
 
    $stream .= '<table class="w3-table w3-card-4 w3-text-gray w3-striped w3-hoverable" id="chantable">';
		$stream .= '<thead>';	
		$stream .= '<tr>';	
		$stream .= '</tr>';
		$stream .= '</thead>';
		$stream .= '<tbody>';     


  foreach($channels as $key=>$chan) {

    if ($chan['Application'] == 'AppDial') {
        continue;
    }

    // TOC and CLID are common to all
    $stream .= "<tr>";  
    // time on call 
    $stream .= "<td>" . $chan['Duration'] . "</td>";
    // CLID
    $stream .= "<td>" . $chan['CallerIDNum'] . "</td>";
// nice little arrow
    $stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';

    switch ($chan['Application']) {

      case "Dial":
          $stream .= "<td>" . $chan['Exten'] . "</td>";
          $stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          $target =  $chan['ChannelStateDesc'];
          $linked = findLinked($channels,$chan['Channel'],$chan['BridgeId']);
          if ($linked) {
            $target = $linked;
          }
          $stream .= "<td>" . $target . "</td>";
          break;
      case "Queue":
          $stream .= "<td>In Queue</td>";
          $stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          $queueName = explode(',',$chan['ApplicationData']);
          $stream .= "<td>" . $queueName[0] . "</td>";
          break;
      case "ConfBridge":
          $stream .= "<td>Conference Room</td>";
          $stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          $confName = explode(',',$chan['ApplicationData']);
          $stream .= "<td>" . $confName[0] . "</td>";
          break;
      case "VoiceMail":
          $stream .= "<td>Leaving Voicemail</td>";
          $stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          $vName = explode(',',$chan['ApplicationData']);
          $stream .= "<td>" . $vName[0] . "</td>";
          break;
      case "VoiceMailMain":
          $stream .= "<td>Retrieving Voicemail</td>";
          $stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
//          $confName = explode(',',$chan['ApplicationData']);
          $stream .= "<td>" . $chan['ApplicationData'] . "</td>";
          break;          
    }


    $stream .= "</td>"; 
    $stream .= '</tbody>';
    $stream .= "</table>";  

  }  
  echo $stream;


/*
		foreach($data as $line) {
			if (preg_match("/Up/", $line) && (preg_match("/!Dial!/", $line) 
				||  preg_match("/!ConfBridge!/i", $line) 
				||  preg_match("/!VoiceMail/i", $line) 
				|| preg_match("/!Queue!/i", $line)) ) {
          		$pieces = explode("!", $line);
          	// TOC and CLID are common to all
          		$stream .= "<tr>";	
          	// time on call	
          		$stream .= "<td>" . gmdate("H:i:s", $pieces[11]) . "</td>";
          	// CLID
          		$cn = preg_replace(' /^00/ ', '+', $pieces[7]);
          		$stream .= "<td>" . $cn . "</td>";
// nice little arrow
          		$stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          		
// regular Dial
          		if (preg_match("/!Dial!/i", $line)) {          	
          		// number connected
          			$dn = preg_replace(' /^00/ ', '+', $pieces[2]);
          			$stream .= "<td>" . $dn . "</td>";
          			$stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          			$stream .= "<td>" . $pieces[12] . "</td>";
          			continue;
          		}
// Confbridge          
          		if (preg_match("/!ConfBridge!/i", $line)) {
          			$dn = preg_replace(' /^00/ ', '+', $pieces[2]);
          			$stream .= "<td>" . $dn . "</td>";
          			$stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          			$stream .= "<td>ConfBridge</td>";
          			continue;
          		}
// Queue
          		if (preg_match("/!Queue!/i", $line)) {
          			$splitqueue = explode(',',$pieces[6]); 
          			$stream .= "<td>" . $splitqueue[0] . "</td>";
          			$stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          			if ($pieces[12] == '(None)') {
          				$stream .= "<td>In Queue</td>";
          			}
          			else {
          				$stream .= "<td>" . $pieces[12] . "</td>";;
          			}	
          		} 
// Voicemail
			     if (preg_match("/!Voicemail/i", $line)) {
          			$splitqueue = explode(',',$pieces[6]); 
          			$stream .= "<td>" . $splitqueue[0] . "</td>";
          			$stream .= '<td class="icons"><img src="/sark-common/icons/arrowright.png" border=0 title = "Direction of call"></td>';
          			$stream .= "<td>Voicemail";
          			if (preg_match("/!VoicemailMain/i", $line)) {
          				$stream .= " listen";
          			}
          			else {
          				$stream .= " record";
          			}	
          			$stream .= "</td>";	
          		}   			
          		
          	}
          	$stream .= "</tr>";
		}
		$stream .= '</tbody>';
		$stream .= "</table>";	
//    syslog(LOG_WARNING, "$stream");
*/
    

function build_channel_array($amirets) {
/*
 * build an array of active channels by cleaning up the AMI output
 * (which contains stuff we don't want).
 */ 
  $channel_array=array();
  $lines = explode("\r\n",$amirets);  
  $channel = 0;
  foreach ($lines as $line) {
    // ignore lines that aren't couplets
    if (!preg_match(' /:/ ',$line)) { 
        continue;
    }
    
    // parse the couplet  
    $couplet = explode(': ', $line);
    
    // ignore events and ListItems
    if ($couplet[0] == 'Event' || $couplet[0] == 'EventList' || $couplet[0] == 'ListItems' || $couplet[0] == 'Response' || $couplet[0] == 'Message' ) {
      continue;
    }
    
    //check for a new channel and set a new key if we have one
    if ($couplet[0] == 'Channel') {
      $channel = $couplet[1];
    }
    else {
      if (!$channel) {
        continue;
      }
      else {
        $channel_array [$channel][$couplet[0]] = $couplet[1];
      }
    }
  }
  return $channel_array; 
}

function findLinked($channels,$channelID,$bridge) {

  foreach ($channels as $candidate) {
    if (candidate['Channel'] != $channelID && candidate['BridgeID'] == $bridge) {
      return candidate['CallerIDNum'];
    }
  }
  return null;
}
   	
?>


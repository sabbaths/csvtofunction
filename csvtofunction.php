<?php
   $comment = '
      /*  
       Function Name      : <br>
       int contactID      : Contacts - contactID <br>
       int formResponseID :  - Response ID <br>
       Created by         : N/A <br>
       Version            : 01.00.00 <br>
       Date               : <br>
       Description        : <br>
       Reference #        : <br>
      */
   ';

   $ls_code = '// LS Code //

   ';
   
   $contant_values = '
      client = zoho.crm.getRecordById("Contacts",clientID); <br>
      company = client.get("Filing_Key"); <br>
      ClaimantsFirstName = client.get("First_Name"); <br>
      ClaimantsLastName = client.get("Last_Name"); <br>
      email = client.get("Email"); <br>
      ssn = client.get("SSN1"); <br>';
   $ls_values = '
      //START OF VALUES <br>';
   $post_values = '
      //START OF POST//
      response = invokeurl
      [ <br>';

   $textarea = $_POST['textarea'];
   $textarea_array = explode("\n", $textarea);

   foreach($textarea_array as $textarea_line) {

      #$api_name_value = explode(" ", $textarea_line);
      $api_name_value = preg_split('/\s+/', $textarea_line);
      
      $api_name = $api_name_value[0];
      $api_value = $api_name_value[1];

      if(empty($api_name)) {
         continue;
      }   

      if($api_name == "API") {
         $contant_values = 
            "formResponse = zoho.crm.getRecordById(\"" . $api_value . "\",formResponseID); <br>" . 
            $contant_values;

      } else if ($api_name == "URL") {
         $url = "url : \"{$api_value}\" <br>";
         $post_values = $post_values . $url .
            "type : POST <br>
            parameters: <br>
            { <br>
            \"ClaimantsFirstName\":ClaimantsFirstName, <br> 
            \"ClaimantsLastName\":ClaimantsLastName, <br>
            \"email\":email, <br>
            \"ssn\":ssn, <br>"; 
      } else {
         $ls_values = $ls_values .
                     "{$api_name} = formResponse.get(\"{$api_value}\"); <br>";

         $post_values = $post_values .
                     "\"{$api_name}\":{$api_name}, <br>";
      }
   }
   echo $comment;
   echo "<br>";
   echo $contant_values;
   echo "<br>";
   echo $ls_values;
   echo "<br>";
   echo $post_values . "}];";
   echo "<br>";
   echo $ls_code;
   echo <<<END
      // LS Code // <br>
      currentDay = zoho.currenttime.toString("yyyy-MM-dd'T'HH:mm:ss"); <br>
      claims = zoho.crm.getRelatedRecords("Claims","Contacts",clientID); <br>
      for each  claim in claims <br>
      { <br>
         if(claim.get("LS_Questionnaire") == "GWP - Skin Condition" && (claim.get("Claim_Status") == "Waiting on LS Responses" || claim.get("Claim_Status") == "Needs Non-Conforming LS" || <br>claim.get("Claim_Status") == "Sent Non-Conforming LS Email" || claim.get("Claim_Status") == "NC LS Email Follow Up 1 Complete" || claim.get("Claim_Status") == "NC LS Email Follow Up 2 Complete" || <br> claim.get("Claim_Status") == "NC LS Email Follow Up 3 Complete" || claim.get("Claim_Status") == "Unresponsive - NC LS")) <br>
         { <br>
            customInfo = {"MAP_LS_Response_Received":true}; <br>
            response = zoho.crm.updateRecord("Claims",claim.get("id"),customInfo,{"trigger":{'workflow','blueprint'}}); <br>
            info response; <br>
         } <br>
         else if(claim.get("LS_Questionnaire") == "GWP - Skin Condition" && claim.get("Claim_Status") == "Needs Peer Review") <br>
         { <br>
            customInfo = {"MAP_LS_Response_Received":true,"Date_MAP_LS_Response_Received":currentDay}; <br>
            response = zoho.crm.updateRecord("Claims",claim.get("id"),customInfo); <br>
         } <br>
         /** HVAC **/ <br>
         else if((claim.get("Claim_SubType") == "HVAC IBS – GWI Presumptive" || claim.get("Claim_SubType") == "HVAC Headache – GWI Presumptive" || claim.get("Claim_SubType") == "HVAC Sinusitis – GWI <br>Presumptive" || claim.get("Claim_SubType") == "HVAC Allergic Rhinitis – GWI Presumptive" || claim.get("Claim_SubType") == "HVAC Skin Condition – GWI Presumptive") && (claim.get("Claim_Status") == <br>"Awaiting Responses" || claim.get("Claim_Status") == "Questionnaire Follow Up" || claim.get("Claim_Status") == "Unresponsive Questionnaire")) <br>
         { <br>
            customInfo = {"Responses_Received":true}; <br>
            response = zoho.crm.updateRecord("Claims",claim.get("id"),customInfo,{"trigger":{'workflow','blueprint'}}); <br>
            info response; <br>
         } <br>
      } <br>
      END;
?>
<?
function PF_htmlbody($conn,$edit){
	$sSQLCmd="select epostbody from epost where epostid=".PF_ReSqlCmd($edit,"S");

	$row=PF_Get_rs($conn,$sSQLCmd);
	
	if (mysql_num_rows($row) > 0) {
	
		$rs =mysql_fetch_array($row);
		return $rs[0];
	}	
}
function PF_Limit($conn,$UserStatus,$LimitStatus,$LimitNumber){
		global $DBString;
		global $Config;
		$Config['ModifyStatus']=false;
		@session_start(); 	
		
	
    	//管理者介面
	if (substr_count(REQUEST_URI,"/admin/")>0 ){				
		//如果session不見改用cookie
		if (($_SESSION['status']=='' || $_SESSION['userid']=="") && $_COOKIE["sessionid"]!=""){
			PF_print(1);
				if($conn==null){
					$conn=PF_ConnString($DBString);
				}		
				$sSQLCmd="select * from adminuser where sessionid=".PF_ReSqlCmd($_COOKIE["sessionid"],"S")." limit 0,1";
				
				$row=PF_Get_rs($conn,$sSQLCmd);
				if (mysql_num_rows($row) > 0) {
					$rs=mysql_fetch_array($row);
				
					$_SESSION['userid']=$rs["userid"]; 
					$_SESSION['useraccount']=$rs["Account"]; 
					$_SESSION['status']=$rs["status"]; 
					$_SESSION['userlimit']=$rs["userlimit"]; 
				}
		}
		if  ($_SESSION['status']=="999"){
                     $Config['ModifyStatus']=true;                     
                     return null;
        	}
        }	

	switch ($LimitStatus){
     		   case "1":	//'只限某些會員才可以使用    
     		   
						  if ($UserStatus!="ALL"){			
						 	if ($UserStatus=="9"){
						 		
						 			if (substr($_SESSION['status'], 0, 2)!="99"){
						 				die(PF_LG("抱歉您的角色無法使用此功能!"));
						 			}						 		
						 	}else{
						 		
							 	if (PF_SplitCompare($UserStatus,$_SESSION['status'])==false){							 								 	
							 			die(PF_LG("抱歉您的角色無法使用此功能!"));
							 	}
							}	
						 }
				          	if ($LimitNumber==""){return null;}
				          
						 if (isset($_SESSION['userlimit'])){					 							 	
						 	$LimitNumbers=explode(";",$LimitNumber);
							foreach ($LimitNumbers as $k => $v){			
								if (PF_SplitCompare($_SESSION['userlimit'],$v)){							 								 														
									$Config['ModifyStatus']=true;                     
									return null;
								}
							};		
						 }		  
				       if ($Config['ModifyStatus']==false){					 		  
		                         	die(PF_LG("抱歉您的角色無法使用此功能!"));
		                       }
                    break;  
                   case "0":	      //一定要會員才可以使用	                 
        			    if (substr_count(REQUEST_URI,"/admin/")>0 ){
        			    	if (substr($_SESSION['status'],0,1)!='9'){
        			    		PF_Script("alert('".PF_LG("抱歉，請先登入")."!');location.href='adminlogin.php'");
        			    		exit();
        				}
        			    }else{
        			    	
		        			    	if (PF_SplitCompare("0,1,2,3,4",substr($_SESSION['status'],0,1))==false){
		      			    		   PJ_HTMLHead();
							          PF_Script("alert('".PF_LG("抱歉，請先登入")."!')");
							     
					         	if ($Redirect==''){$Redirect=$_SERVER['REQUEST_URI'];}

?>
<form name="oForm"  method="post" language="javascript" action="<?=FC_WebUrl?>member_login.php">
<?
$cc="";
$keyarray="";
foreach($_POST as $_key=>$_value){
	
	if (PF_SplitCompare($keyarray,$_key)==false && $_key!='email'){
	if (is_array($_value)){
?>
<input type="hidden" name="<?=$_key?>" value="<?=implode (",", $_value)?>">              
<?}else{?>
	<input type="hidden" name="<?=$_key?>" value="<?=$_value?>">              
<?			
	}
	$keyarray=$cc.$_key;
	$cc=",";	
	}
}
//exit();
?>
	<input name="Redirect"  type="hidden" value="<?=htmlspecialchars($Redirect)?>">
		<input type="submit" style="display:none"> 	
</form>

<SCRIPT language=JavaScript>
document.forms["oForm"].submit();
</SCRIPT>

<?					
								exit();
							}
		                  }
		                  
		                  		//檢查是否有重覆登入
		    				if ($_SESSION['memberid']!=''){		   
				                  	$filename= FC_VirtualFolder."images/temp/".$_SESSION['memberid'];		                  	
							if (file_exists($filename)){						
								$handle = fopen ($filename, "rb");
								$contents = "";
								while (!feof($handle)) {
								   $contents .= fread($handle, 8192);
								}
								fclose($handle);               
				               	       if ($_SESSION['sessionid']!=$contents){
									$_SESSION['memberid']="";
									PF_Script("alert('".PF_LG("此帳號已經有其他人在使用,請重新登入")."');location.href='".FC_WebUrl."'");
									exit();
								}
							}	
						  }	        
					 	if ($UserStatus!="ALL"){
							 	if (PF_SplitCompare($UserStatus,$_SESSION['status'])==false){
							 			die(PF_LG("抱歉您的角色無法使用此功能!"));
							 	}
						 }
						 
						 			
                          break;              
               default:    //'不是會員也可以使用   
		
        		             break;                        
        }   
       
}
Function PF_orderemail($conn,$ordergroupid){
			$XmlDoc=PF_LoadXmlDoc("Setup.xml");

			$sSQLCmd="select *  from ordergroup where ordergroupid=".PF_ReSqlCmd($ordergroupid,"N");
		
			$row=PF_Get_rs($conn,$sSQLCmd);
			if (mysql_num_rows($row) == 0) {
				return false;
			}
			$rsodergroup =mysql_fetch_array($row);			
			
             $ordergroupnumber=$rsodergroup["ordergroupnumber"];
             $id=PF_SearchXML($XmlDoc,"//參數設定檔/訂單處理情況/KIND/傳回值","上稿編號", $rsodergroup["orderstatus"]);
            if (isset($id)==false){
             return null;
            };
			//$ebody=PF_htmlbody($conn,$id);
				$ebody= PF_ReadFile("email/orderemail.htm");
		
			$mailtitle=PF_SearchXML($XmlDoc,"//參數設定檔/訂單處理情況/KIND/傳回值","資料", $rsodergroup["orderstatus"]); 
			$paystatus=PF_SearchXML($XmlDoc,"//參數設定檔/付款方式/KIND/傳回值","資料", $rsodergroup["paystatus"]);                   
			$ebody=str_replace("[+paystatus+]", $paystatus ,$ebody);
              foreach($rsodergroup as $_key=>$_value){
		    		$ebody=str_replace("[+".$_key."+]", $_value ,$ebody);
			}		
	$orderbody="<table border=0 cellpadding=1 cellspacing=1 width=\"100%\" align=\"center\" bordercolorlight=\"#666666\" bordercolordark=\"#FFFFFF\">";				
	
	
     $orderbody.="<tr class=\"TitleBgcolor\">";
	$orderbody.="<td align=\"center\">商品分類</td>";
	$orderbody.="<td align=\"center\">產品名稱</td>";
	$orderbody.="<td align=\"center\">數量</td>";
	$orderbody.="<td align=\"center\">規格</td>";
	$orderbody.="<td align=\"center\">單價</td>";
	$orderbody.="<td align=\"center\">小計</td>";
	$orderbody.="<td align=\"center\">獲瓦數</td>";
	$orderbody.="</tr>";
     
		$sSQLCmd="select kindheadtitle,product.producttitle,productquality,productitemtitle,productprice,productbouns from orderdetail left join product on (orderdetail.productid=product.productid) left join kindhead on (product.kindheadid=kindhead.kindheadid)  where ordergroupnumber=".PF_ReSqlCmd($ordergroupnumber,"S");
		$row=PF_Get_rs($conn,$sSQLCmd);

		if (mysql_num_rows($row) > 0) {                     
			while ($rs=mysql_fetch_array($row)) {
				$j=($j+1);
				$bgclass= ($j % 2 == 0 ? "DataBgcolor1" : "DataBgcolor2");  
	                            $orderbody.="<tr class=\".$bgclass.\">";
	                            $orderbody.="<td align=right>".$rs["kindheadtitle"]."&nbsp;</td>";
						      $orderbody.="<td align=right>".$rs["producttitle"]."&nbsp;</td>";
						      $orderbody.="<td align=right>".$rs["productquality"]."&nbsp;</td>";
						      $orderbody.="<td align=right>".$rs["productitemtitle"]."&nbsp;</td>";
						      $orderbody.="<td align=right>".$rs["productprice"]."&nbsp;</td>"; 	         
						      $orderbody.="<td align=right>".PF_FN($rs["productquality"]*$rs["productprice"],0)."&nbsp;</td>";
						      $orderbody.="<td align=right>".$rs["productbouns"]."&nbsp;</td>"; 	         
						      $orderbody.="</tr>";
			}
	   }  
		$orderbody.="</table>";
		
		$ebody=str_replace("[+paytxt+]", PF_ReadFile("email/paystatus".$rsodergroup["paystatus"].".htm") ,$ebody);
		$ebody=str_replace("[+orderbody+]", $orderbody ,$ebody);
		$ebody=str_replace("[+FC_Section+]", FC_Section ,$ebody);
		$ebody=str_replace("[+FC_Portage+]", FC_Portage ,$ebody);
		$ebody=str_replace("[+FC_BankName+]", FC_BankName ,$ebody);
		$ebody=str_replace("[+FC_BankAccount+]", FC_BankAccount ,$ebody);
		$ebody=str_replace("[+FC_BankAccountName+]", FC_BankAccountName ,$ebody);

		$sSQLCmd="select email from member where memberid=".PF_ReSqlCmd($rsodergroup["memberid"],"N");
		//echo $sSQLCmd;
		$row=PF_Get_rs($conn,$sSQLCmd);
		
	if (mysql_num_rows($row) > 0) {
		$rs =mysql_fetch_array($row);
		
		PF_SendEmail ($rs["email"],FC_Email,FC_Company."-".$mailtitle."[".$ordergroupnumber."]",FC_Company ,FC_Email, $ebody,"");		
	}		
		
					
  	            
}
function PF_amount($conn,$XmlDoc,$memberid,$logistickind,$amount,$memo){
				   $DB =new DBclass;
                     $DB->conn=$conn;
                     $DB->Table="amount";
                     $DB->FieldAdd("memberid",$memberid,"N");                    //店名
                     $DB->FieldAdd("logistickind",$logistickind,"N");                    //點數狀態
                     $DB->FieldAdd("amount",$amount,"N");                    //數量
                     $DB->FieldAdd("userid",$_SESSION['userid'],"N");                    //服務人員
                     $DB->FieldAdd("memo",$memo,"S");                    //備註
                     $DB->FieldAdd("adddate","DATE","D");                    //建立時間;
                    $DB->Action="insert";	        
                    //$DB->Debug="1";
                     $DB->Execute();	  
                     
                     $sSQLCmd="update member set memberbouns=memberbouns".PF_SearchXML($XmlDoc,"//參數設定檔/點數狀態/KIND/傳回值","加減項",$logistickind).$amount." where memberid=".$memberid;
                     
                     PF_Get_rs($conn,$sSQLCmd);     
                     $amountid=PF_GETField($conn,"select max(amountid) from amount"); 	         
				   $sSQLCmd="update amount set lastamount=(select memberbouns from member where memberid=".PF_ReSqlCmd($memberid,"N").") where amountid=".PF_ReSqlCmd($amountid,"N");
				   PF_Get_rs($conn,$sSQLCmd);
}				   
function  PF_LG($Lang){
	return $Lang;
}
function PF_board($conn,$kind,$id){
}

function PF_calculateclass($classid){

	$sSQLCmd="update class set";
	$sSQLCmd.=" graduationcount=(select count(*) from classmember where isgraduation=1 and classmember.classid=class.classid ), ";
	$sSQLCmd.=" certificatecount=(select count(*) from classmember where naharegistrationkind is not null and classmember.classid=class.classid ) ";
	$sSQLCmd.="where classid=".PF_ReSqlCmd($classid,"N");
	PF_Get_rs($conn,$sSQLCmd);
	
}

function PF_SMS($conn,$mobile,$smsbody,$sendTime){

	if ($mobile==''){
		return ;
	}
	if (mb_strwidth($mobile)<10){		
		$msg= "長度不足";
	}
	if (is_numeric($mobile)==false){
		 $msg= "格式錯誤";
	}
	
	//$messageid=date("YmdHis").floor(microtime()*1000);
	if ($msg==""){
				/*
				username   會員帳號 
				password  會員密碼 
				dstaddr  接收簡訊之手機號碼，一次發送多筆號碼可用逗號隔開(不可超過 50 筆)。 
				國內號碼 09 開頭，十碼的數字； 
				國際號碼請在開頭多個%2b 
				例如傳大陸:  %2b8613681912700  其中 86 是大陸國碼，
				後面(以 13、15、18 開頭) 11 位數字是
				大陸手機號碼。 encoding    BIG5/ASCII/UCS2/PBIG5/PASCII/LBIG5/LASCII/LUCS2/PUSH 
				預設值為 BIG5 (註: 此 encoding 可提供簡訊購作訊息處理，
				以及手機接收訊息後該用何 種編碼讀取等之用。
				P 表 POPUP 簡訊、L 表長簡訊、PUSH 表 wap push)
				 smbody   簡訊內容，中英文長度為 70 個字元，純英文為 160 個字元  
				 若 encoding 為 LBIG5/LASCII/LUCS2，則大小為 330個中英文字 若 encoding 為 PUSH，
				 則此欄為 wap push title dlvtime  預約時間，
				 格式為 YYYY/MM/DD hh24:mm:ss wapurl PUSH 當 encoding 設為 PUSH 才可以使用這個 tag replyurl  receiver 若有回覆簡訊時，vender 用來接收該回覆訊息的網址。(需另計點) 當 encoding 設為 BIG5/ASCII/UCS2 才可以使用這個 tag replydays  收取用戶回覆的天數，預設為 3(天)最大值不能超過 30(天)， 當 encoding 設為 BIG5/ASCII/UCS2 才可以使用這個 tag response  狀態回報網址，預設為空字串(不回報) 
				 */
					
			$postdata="username=".FC_SMSAccount;
			$postdata.="&password=".FC_SMSPassword;
			$postdata.="&dstaddr=".$mobile;
			$postdata.="&smbody=".$smsbody;
			
			$body=PF_geturlpost("http://www.smsgo.com.tw/sms_gw/sendsms.aspx",$postdata);
			//$body="msgid=1512100111447143\r\nstatuscode=0\r\nstatusstr=OK\r\npoint=1";			
			$array=explode("\r\n", $body);
			//PF_print(count($array));
			if (count($array)>=5){
			
					$msgid=$array[0];
					$statuscode=str_replace("statuscode=","",$array[1]);
					$statusstr=str_replace("statusstr=","",$array[2]);
					$point=str_replace("point=","",$array[3]);

					$DB =new DBclass;
					$DB->conn=$conn;
					//$DB->Debug="1";
					$DB->Table="smslog";
					$DB->NFieldAdd("id",$msgid,"S","自動編號","N");
					$DB->NFieldAdd("smsbody",$smsbody,"S","簡訊內容","Y");
					$DB->NFieldAdd("mobile",$mobile,"S","手機","Y");
					$DB->NFieldAdd("status",$statuscode,"S","狀態","N");
					$DB->NFieldAdd("msg",$statusstr,"S","狀態訊息","N");
					//$DB->NFieldAdd("lastpoint",$point,"N","最後點數","N");
					$DB->NFieldAdd("usepoint",1,"N","最後點數","N");
					$DB->NFieldAdd("adddate","DATE","D","建立時間","N");
					$DB->Action="insert";
					$DB->Execute();        	         
				return  $msg;
				
			}
	}

}
?>
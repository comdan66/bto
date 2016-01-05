<?php include_once('include/STANDARD.php'); ?>
<?
PF_GETRequest();
$conn=PF_ConnString($DBString);
$XmlDoc=PF_LoadXmlDoc("Setup.xml");
$PAGE_NAME="產品檢測報告查詢系統";
$Config['FC_WebTitle']=$PAGE_NAME." | ".$Config['FC_WebTitle'];

PJ_HTMLHead();
?>
<script language=JavaScript>

 jQuery(document).ready(function () {
 	 
		    $('input:first').focus(); 
		    $('input').keyup(function(){
		        var inputs = $('input'); //先取得所有input元素
		        var maxlen = $(this).attr('maxlength');   //取得目前元素的maxlength
		        if( $(this).val().length == maxlen ){ //當滿足maxlength時...
		            inputs.eq( inputs.index($(this))+ 1 ).focus(); 
		        }
		    });
    
});

</script>


  <link href="css/aacd.css" rel="stylesheet" type="text/css" />
  </head>

  <body lang="zh-tw">
<script language=JavaScript>
function oForm_onsubmit(form){

    
    if (PF_FormMultiAll(form)==false){return false};
 PF_FieldDisabled(form)
   return true;
}
</script>
<form name="oForm" id="oForm"  method="post" language="javascript" action="aacddetail.php" onsubmit="return oForm_onsubmit(this);">


    <div id='TB'>
      <div id='TBT'>
        <h1><a href="#">禾場國際芳療學苑 - 產品檢測報告查詢系統 AACD, AromaHarvest Analysis Certificate Database</a></h1>
      </div>
    </div>

    <div id='CB'>
      <div class='CCBT'>
        <img src="images/ti.png" width="100%" height="auto" alt="">
      </div>

      <div class='CCBD'>
        <div class='BB'>
            <div class='BBC'>
              <p class="Btitle">查詢產品檢測報告</p>

              <p class="typo">請輸入完整產品序號<span class='typr'>*</span></p>

              <div class="Tab">
                <input class="contact" type="text" style="width:20%;" placeholder="2碼" name="product_no1" required  title="產品序號"  requiredclass="required[1,TEXT]" maxlength="2"/>
                -
                <input class="contact" type="text" style="width:20%;" placeholder="3碼" name="product_no2"  required  title="產品序號"  requiredclass="required[1,TEXT]" maxlength="3"/>
                /
                <input class="contact" type="text" style="width:31%;" placeholder="5碼" name="product_no3" required  title="產品序號"  requiredclass="required[1,TEXT]" maxlength="5"/>
              </div>

              <p class='BTN'><a href="#" onclick="if (oForm_onsubmit(document.forms['oForm'])){document.forms['oForm'].submit()};return false;">確認查詢</a></p>

              <div class="BBT">
                <p class="typt">共<span class="typtr"><?=PF_GETField($conn,"select count(*) from aacd");?></span> 筆</p>
              </div>
              <div class="BBTD">
<?foreach ($XmlDoc->xpath("//參數設定檔/產品種類/KIND") as $v) {?>            
              	  
                <div class="nbn">
                  <p class="tp"><?=$v->簡述?></p>
                  <p class="tpn"><?=PF_GETField($conn,"select count(*) from aacd where product_category=".PF_ReSqlCmd(strval($v->傳回值),"N"));?></p>
                </div>
<?}?>   
					              </div>

            </div>
        </div>
      </div>
    </div>
    			
</form>

<?php include('library/aacddown.php'); ?>

  </body>

</html>
<script type="text/javascript">
  $(document).ready(function(){
         $.when(
            $.getScript( "https://www.onlinepayment.com.my/MOLPay/API/seamless/3.10/js/MOLPay_seamless.deco.js" )          
         )
	 .done(function(){
            $('input[name=payment_options]').on('click', function(){
              if( $(".warning").length > 0 ) $(".warning").remove();
              $.post('index.php?route=payment/molpayseamless/validate_payment',
                  {'amount': $("input[name=amount]").val(), 'payment_options': $(this).val(), 'agree':$("input[name=agree_topay]").is(":checked")  },
                    function(data) {
                      var objc = jQuery.parseJSON(data);
                      if(objc.error_warning != ''){
                        $('<div class="warning"></div>').insertBefore('form#payment');
                        $('.warning').html(objc.error_warning);
                      }
                      else{
                        $("form#payment").trigger('submit');
                      }
              });
            })
         });


  });
</script>
<style type="text/css">
    .buttons{
      overflow: hidden;
    }
    #channelList {
        width: 100%;
    }
    #channelList > li {
        float: left;
        height: 80px;
        list-style: outside none none;
        text-align: center;
        width: 180px;
    }
    #channelList > li:nth-child(5n+1) {
        clear: left;
    }
	
	/*!Timer */
	#counter {
		left: 1%;
		position: fixed;
		top: 30%;
		z-index: 999999;
		padding: 5px;
	}
	#counter .mpslabels {
		color: #fff;
		margin-bottom:10px;
	}

	#counter .mpsdelimeter {
		float: left;
		padding: 5px;
		font-size: 30px;
		color: #2d2d2d;
	}
	#counter .mpsminutes, #counter .mpsseconds {
		color: #fff;
		float: left;
		font-size: 40px;
		padding: 5px 12px;
		text-align: center;
		background: #333;
		-moz-border-radius: 6px;
		-webkit-border-radius: 6px;
		border-radius: 6px;
		border: 0;
	}
	#counter .mpsseconds.red {
		color: #FF0000;
	}
	#counter small {
		font-size: 15px;
	}
</style>
<form action="<?php echo $action; ?>" method="post" id="payment" role="molpayseamless">

  <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
  <input type="hidden" name="orderid" value="<?php echo $orderid; ?>" />
  <input type="hidden" name="bill_name" value="<?php echo $bill_name; ?>" />
  <input type="hidden" name="bill_email" value="<?php echo $bill_email; ?>" />
  <input type="hidden" name="bill_mobile" value="<?php echo $bill_mobile; ?>" />
  <input type="hidden" name="country" value="<?php echo $country; ?>" />
  <input type="hidden" name="currency" value="<?php echo $currency;?>" />
  <input type="hidden" name="vcode" value="<?php echo $vcode?>">

  <input type="hidden" name="bill_desc" value="<?php echo implode("\n",$prod_desc);?>" />
  
	<?php if( $this->data['molpay_set_timer'] != 0 ) {?>
	<input type="hidden" name="molpay_set_timer" value="<?php echo $molpay_set_timer?>">
	<?php } ?>
  <div class="buttons">
    <?php if ($text_agree) { ?>
    <div>
      <div class="right" style="margin: 0px 0 25px 0px; width:100%;">
        <?php if ($agree) { ?>
        <input type="checkbox" name="agree_topay" value="1" checked="checked" />
        <?php } else { ?>
        <input type="checkbox" name="agree_topay" value="1" />
        <?php } ?>
        <?php echo $text_agree; ?>
      </div>
    </div>  
    <?php } ?>
    <div id="molpay_channel_list">
      <ul id="channelList">
      <?php 
          foreach($merchant_channels as $k => $v){

            $channel = strtolower($k);
            $channel_fulltext = ($k == 'fpx')? 'FPX' : $v[0];
            $img_code = $v[5];
      ?>
        <li>
          <label class="hand" for="payment<?php echo $channel?>"><img src="catalog/view/theme/default/image/molpay/<?php echo $img_code?>" alt="<?php echo $channel_fulltext?>" /></label>
          <br/>
          <input type="radio" name="payment_options" id="payment<?php echo $channel?>" value="<?php echo $channel?>"></input>
        </li>
      <?php } ?>
      </ul>
    </div>
  </div>
</form>
<?php if( $this->data['molpay_set_timer'] != 0 ) {?>
<div id="counter"></div>
<?php } ?>

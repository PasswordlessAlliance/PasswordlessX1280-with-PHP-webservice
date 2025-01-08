<?php
	session_start();
	if (isset($_SESSION['id'])) {
		header("Location: ../main.php");
		exit();
	}
	include '..\common\Include\headTOP.php';
?>
<!DOCTYPE html>
<html>
<head>
<script>
$(document).ready(function() {
	$("#id").focus();
	$("#pw").attr("disabled", false);
	$("#pw").attr("placeholder", "PASSWORD");
})

function trim(stringToTrim) {
    return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function join() {
	id = $("#id").val();
	pw = $("#pw").val();
	pw_re = $("#pw_re").val();
	email = $("#email").val();
	
	id = trim(id);
	pw = trim(pw);
	pw_re = trim(pw_re);
	email = trim(email);

	$("#id").val(id);
	$("#pw").val(pw);
	$("#pw_re").val(pw_re);
	$("#email").val(email);

	if(id == "") {
		alert(getTranslatedText("016"));	
		$("#id").focus();
		return false;
	}

	if(pw == "") {
		alert(getTranslatedText("017"));	
		$("#pw").focus();
		return false;
	}

	if(pw_re == "") {
		alert(getTranslatedText("023"));	
		$("#pw_re").focus();
		return false;
	}
	
	if(pw != pw_re) {
		alert(getTranslatedText("025"));
		$("#pw_re").val("");
		$("#pw_re").focus();
		return false;
	}

	if(email == "") {
		alert(getTranslatedText("024"));
		$("#email").focus();
		return false;
	}
	
	$.ajax({
        url : baseUrl,
        type : "post",
        data : {
        	"id" : id,
            "pw" : pw,
            "email" : email,
            "action" : "createUserInfo"
        },
        success : function(res) {
            console.log(res);
        	if(res.result == "OK") {
        		alert("Sign up is complete.");
            	location.href = "./login.php";
        	}
            else {
            	alert(getTranslatedText(res.result).replace('@@@', id));
            }
        },
        error : function(res) {
            alert(res.msg);
        },
        complete : function(res) {
        }
    });
}
</script>
</head>
<body>
<div class=" main_container">
	<div class=" main_container">
		<div class="modal">
			<div style="width:100%; text-align:right;">
				<div class="select_lang">
					<select id="lang" name="lang" onchange="selLang(this);">
						<option value="en">EN</option>
						<option value="ko">KR</option>
					</select>
				</div>
			</div>
			<div class="login_article">
				<div class="title"><em style="width:100%; text-align:center;" data-translate="010">></em></div>
				<div class="content">
					<div>
						<form>
							<div class="input_group">
								<input type="text" id="id" placeholder="ID" />
							</div>
							<div class="input_group">
								<input type="password" id="pw" placeholder="PASSWORD" />
							</div>
							<div class="input_group">
								<input type="password" id="pw_re" placeholder="Confirmation PASSWORD" />
							</div>
							<div class="input_group">
								<input type="text" id="email" placeholder="Email" />
							</div>
						</form>
					</div>
					<div class="btn_zone">
						<a href="javascript:join();" class="btn active_btn" data-translate="010">></a>
						&nbsp;
						<a href="/login/login.php" class="btn active_btn" data-translate="011">></a>
					</div>           
				</div>
			</div>
		</div>
		<div class="modal_bg"></div>
	</div>
</div>
</body>
</html>

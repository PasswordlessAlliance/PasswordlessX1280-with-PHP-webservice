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
			$("#pw").attr("placeholder", "PASSWORD");
			$("#pw").attr("disabled", false);
		})

		function trim(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}

		function changepw() {
			id = $("#id").val();
			pw = $("#pw").val();
			pw2 = $("#pw2").val();
			
			id = trim(id);
			pw = trim(pw);
			pw2 = trim(pw2);
			
			$("#id").val(id);
			$("#pw").val(pw);
			$("#pw2").val(pw2);
			
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

			if(pw2 == "") {
				alert(getTranslatedText("023"));	
				$("#pw2").focus();
				return false;
			}
			
			if(pw != pw2) {
				alert(getTranslatedText("025"));
				$("#pw2").focus();
				return false;
			}

			$.ajax({
				url : baseUrl,
				type : "post",
				data : {
					"id" : id,
					"pw" : pw,
					"action" : "changepw"
				},
				success : function(res) {
					if(res.result == "OK") {
						alert("Password changing complete.");
						location.href = "./login.php";
					}
					else {
						alert(getTranslatedText(res.result).replace('@@@', id));
					}
				},
				error : function(res) {
					alert(res.msg);
				},
				complete : function() {
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
					<select id="lang" name="lang" onchange="selLang();">
						<option value="en">EN</option>
						<option value="ko">KR</option>
					</select>
				</div>
			</div>
			<div class="login_article">
				<div class="title"><em style="width:100%; text-align:center;" data-translate="012"></em></div>
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
								<input type="password" id="pw2" placeholder="Confirmation PASSWORD" />
							</div>
						</form>
					</div>
					<div class="btn_zone">
						<a href="javascript:changepw();" class="btn active_btn" data-translate="013"></a>
						&nbsp;
						<a href="/login/login.php" class="btn active_btn" data-translate="011"></a>
					</div>           
				</div>
			</div>
		</div>
		<div class="modal_bg"></div>
	</div>
</div>
</body>
</html>

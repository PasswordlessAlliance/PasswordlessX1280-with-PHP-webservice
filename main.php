<?php
    include 'common\Include\headTOP.php';
?>
<!DOCTYPE html>
<html>
<head>
	<style>
		.sample_site{ padding: 2em;  text-align: center; width: 80%;  margin: 0 0 0 0;}
		.sample_site li p{width: 500px; text-decoration: none; color: #333333; padding: 30px 30px 30px; border: 1px solid #e1e1e1; box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.15); border-radius: 10px; }
		.sample_site li a{width: 500px; text-decoration: none; color: #333333; padding: 0px 0px 0px; border: 1px solid #e1e1e1; box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.15); border-radius: 10px; }
		.sample_site li .btn{line-height: 20px; font-size: 17px; padding: 17px 40px; font-weight: 500; display: inline-block; margin: 0 auto; background: #4ea1ff; margin: 15px 0 0 0; border-radius: 8px; color: #ffffff;}
	</style>
	<script>
		$("#btn_logout").click(function(e){
			console.log("Logout");
		});

		function logout() {
			$.ajax({
				url : baseUrl,
				type : "post",
				data : {
					"action" : "logout"
				},
				success : function(res) {
					location.href = "/";
				},
				error : function(res) {
					alert(res.msg);
				},
				complete : function() {
				}
			});
		}

		function withdraw() {
			if(confirm(getTranslatedText("048"))) {
				$.ajax({
					url : baseUrl,
					type : "post",
					data : {
						"action" : "withdraw"
					},
					success : function(res) {
						console.log(res);
						if(res.result == "OK")
							alert("Membership withdrawal has been completed.");
						
						location.href = "/";
					},
					error : function(res) {
						alert(res.msg);
					},
					complete : function() {
					}
				});
			}
		}
	</script>
</head>
<body>
<div class=" main_container">
	<div class="modal">
		<div class="sample_site" sylte="margin: -150px 0 0 0;">
			<div style="width:100%; text-align:right; margin:20px 45px;">
				<div class="select_lang">
					<select id="lang" name="lang" onchange="selLang(this);">
						<option value="en">EN</option>
						<option value="ko">KR</option>
					</select>
				</div>
			</div>
			<div style="width:100%;">
				<ul>
					<li>
						<p style="background-color:#ffffff;">
							<strong data-translate="001"></strong>
							<span data-translate="002"></span>
							<a href="javascript:logout();"><em class="btn" id="btn_logout" data-translate="018"></em></a>
							&nbsp;
							<a href="javascript:withdraw();"><em class="btn" id="btn_delete" data-translate="019"></em></a>
						</p>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
</body>
</html>
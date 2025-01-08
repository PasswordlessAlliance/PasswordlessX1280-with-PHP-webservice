<?php
	session_start();
	if (isset($_SESSION['id'])) {
		header("Location: ../main.php");
		exit();
	}
	include 'common\Include\headTOP.php';
?>
<!DOCTYPE html>
<html>
<head>
	<style>
		.sample_site{ padding: 2em;  text-align: center; width: 80%;  margin: 0 0 0 0;}
	</style>
	<script>
		
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
						<a href="/login/login.php" style="background-color:#ffffff;"><img src="/assets/image/pl_logo.png">
							<strong data-translate="001">></strong>
							<span data-translate="002">></span>
							<em class="btn" data-translate="003">></em>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
</body>
</html>
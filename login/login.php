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
</head>
<body>
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
			<div class="title"><em style="width:100%; text-align:center;" id="login_title" name="login_title"></em></div>
			<div class="content">
				<div id="login_content">
					<form id="frm">
						<div class="input_group">
							<input type="text" id="id" name="id" placeholder="ID" />
						</div>
						<div class="input_group" id="pw_group">
							<input type="password" id="pw" name="pw" placeholder="PASSWORD" />
						</div>
					</form>
					<div class="input_group" id="bar_group" style="display:none;">
						<div class="timer" id="bar_content" name="bar_content" style="position: relative; background: url('/assets/image/timerBG.png') no-repeat center right; border-radius: 8px; background-size: cover;">
							<div class="pbar" id="passwordless_bar" style="background: rgb(55 138 239 / 70%); height: 50px;width: 100%;border-radius: 8px; animation-duration: 0ms; width:0%;"></div>
							<div class="OTP_num" id="passwordless_num" name="passwordless_num" style="text-shadow:2px 2px 3px rgba(0,0,0,0.7); top: 0; position: absolute; font-size: 22px; color: #ffffff; text-align: center; height:50px; width: 100%; line-height: 50px; font-weight: 800; letter-spacing: 1px;">
								--- ---
							</div>
						</div>
					</div>
					
					<div id="passwordlessSelButton" style="height:30px;margin-top:10px;margin-bottom:10px;">
						<div style="text-align: center;">
							<span style="display:inline-block; padding: 6px 10px 16px 10px; text-align:right;">
								<label for"selLogin1" style="margin:0;padding:0;font-family: 'Noto Sans KR', sans-serif;font-weight:300;font-size:medium;">
									<input type="radio" id="selLogin1" name="selLogin" value="1" onchange="selPassword(1);" checked>
									Password
								</label>
							</span>
							<span style="display:inline-block; padding: 6px 10px 16px 10px; text-align:right;">
								<label for"selLogin2" style="margin:0;padding:0;font-family: 'Noto Sans KR', sans-serif;font-weight:300;font-size:medium;">
									<input class="radio_btn" type="radio" id="selLogin2" name="selLogin" value="2" onchange="selPassword(2);">
									Passwordless
								</label>
							</span>
							<span style="display:inline-block; padding: 6px 10px 16px 10px; text-align:right;">
								<a href="javascript:show_help();" class="cbtn_ball"><img src="/assets/image/help_bubble.png" style="width:16px; height:16px; border:0;"></a>
							</span>
						</div>
					</div>
					
					<div class="pwless_info">
						<a href="javascript:hide_help();" class="cbtn_ball"><img src="/assets/image/ic_fiicls.png" height="20" alt=""></a>
						<p data-translate="021">
							<p style="width:100%;text-align:center;font-size:140%;font-weight:800;">
								<font color="#5555FF">Passwordless X1280 Mobile App</font>
								<br>
								<br>
								<a href="https://apps.apple.com/us/app/autootp/id1290713471" target="_new_app_popup"><img src="/assets/image/app_apple_icon.png" style="width:45%;"></a>
								&nbsp;
								<a href="https://play.google.com/store/apps/details?id=com.estorm.autopassword" target="_new_app_popup"><img src="/assets/image/app_google_icon.png" style="width:45%;"></a>
								<br>
								<img src="/assets/image/app_apple_qr.png" style="width:45%;">
								&nbsp;
								<img src="/assets/image/app_google_qr.png" style="width:45%;">
							</p>
							<br>
							<p data-translate="022"></p>
						</p>
					</div>
					
					<div id="passwordlessNotice" style="display:none;">
						<div style="text-align: center;line-height:24px;" data-translate="015">
						</div>
					</div>
					
					<div class="btn_zone">
						<a href="javascript:login();" class="btn active_btn" id="btn_login" data-translate="005"> ></a>
					</div>
					<div class="btn_zone" id="login_mobile_check" name="login_mobile_check" style="display:none;">
						<a href="javascript:mobileCheck();" class="btn active_btn" data-translate="041"></a>
					</div>
					
					<div class="menbership" id="login_bottom1" name="login_bottom" style="text-align:center;">
						<a href="./join.php" data-translate="006"></a>
						<a href="./changepw.php" data-translate="007"></a>
					</div>
					<div class="menbership" id="login_bottom2" name="login_bottom" style="text-align:center;">
						<a href="./join.php" data-translate="006">
						<a href="javascript:moveManagePasswordless();"><font style="font-weight:800;" data-translate="009"></font></a>
					</div>
					<div class="menbership" id="manage_bottom" name="manage_bottom" style="display:none;text-align:center;">
						<a href="./changepw.php" data-translate="007"></a>
						<a href="javascript:cancelManage();"><font style="font-weight:800;" data-translate="005"></font></a>
					</div>
				</div>
				
				<div id="passwordless_reg_content" style="display:none;">
					<div style="text-align:center;">
						<span data-translate="039" style="width:100%; text-align:center; font-weight:500; font-size:24px;">
							<br>
						</span>
						<br>
						<img id="qr" name="qr" src="" width="300px" height="300px" style="display:inline-block;margin-top:10px;">
						<p data-translate="040" style="width:100%; padding:0% 0%; font-weight:500; font-size:16px; line-height:24px;">
						</p>
						<br>
						<span style="display:inline-block; width:100%; font-size:18px; padding:10px; margin-bottom:20px;">
							<div style="gap: 10px;display: flex; justify-content: center; margin:8px 0; font-size:13px;">
								<div style="width:88%; text-align:left;">
									<span style="width:30%;" data-translate="044" ></span>
									<span id="server_url" name="server_url" style="font-weight:800;"></span></div>
								<div style="width:10%;"><img src="/assets/image/ic-copy.png" onclick="javascripit:copyTxt1();"></div>
							</div>
							<div style="gap: 10px;display: flex; justify-content: center; margin:8px 0; font-size:13px;">
								<div style="width:88%; text-align:left;">
									<span style="width:30%;" data-translate="045" ></span>
									<span id="register_key" name="register_key" style="font-weight:800;"></span></div>
								<div style="width:10%;"><img src="/assets/image/ic-copy.png" onclick="javascripit:copyTxt2();"></div>
							</div>
							<br>
							<b><span id="rest_time" style="font-size:24px;text-shadow:1px 1px 2px rgba(0,0,0,0.9);color:#afafaf;"></span></b>
						</span>
					</div>
					<div class="btn_zone">
						<a href="javascript:cancelManage();" class="btn active_btn" id="btn_login" data-translate="011"></a>
					</div>
					<div class="btn_zone" id="reg_mobile_check" name="reg_mobile_check" style="display:none;">
						<a href="javascript:mobileCheck();" class="btn active_btn" data-translate="041"> ></a>
					</div>
				</div>
				<input type="hidden" id="passwordlessToken" name="passwordlessToken" value="">
				<div id="passwordless_unreg_content"  style="display:none;width:100%; text-align:center; font-weight:500; font-size:24px; line-height:35px;">
					<br>
					<br>
					<div class="passwordless_unregist">
						<div style="padding: 0px;">
							<button type="button" id="btn_unregist" name="btn_unregist" data-translate="036" style="height:120px; border-radius:4px; color:#FFFFFF; background:#3C9BEE; border-color:#3090E0; padding: 4px 20px; font-size: 20px; line-height:40px;">
							</button>
						</div>
						<div>
							&nbsp;
							<br>
							<p data-translate="037" style="width:100%; padding:0% 0%; font-weight:500; font-size:16px; line-height:24px;">
							</p>
						</div>
						<br>
						<div class="btn_zone">
							<a href="javascript:cancelManage();" class="btn active_btn" id="btn_login" data-translate="011"></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
	<script>
		var input = document.getElementById("id");
		input.addEventListener("keyup", function (event) {
			if (event.keyCode === 13) {
				event.preventDefault();
				login();
			}
		});

		var input = document.getElementById("pw");
		input.addEventListener("keyup", function (event) {
			if (event.keyCode === 13) {
				event.preventDefault();
				login();
			}
		});

		$("#btn_unregist").on("click", function(){
			if(confirm(getTranslatedText("034"))) {
				unregPasswordless();
			}
		});
	</script>
</html>

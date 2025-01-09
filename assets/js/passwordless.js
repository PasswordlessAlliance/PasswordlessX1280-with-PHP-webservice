var selPasswordNo = 1;	// 1:password, 2:passwordless, 3:passwordless manage
var timeoutId = null;
var check_millisec = 0;
var passwordless_terms = 0;
var passwordless_milisec = 0;
var pushConnectorUrl = "";
var pushConnectorToken = "";
var sessionId = "";
var loginStatus = false;
var checkType = "";	// : "LOGIN", QR: "QR"

var str_login = "";
var str_cancel = "";
var str_title_password = "";
var str_title_passwordless = "";
var str_passwordless_regunreg = "";
var str_passwordress_notreg = "";
var str_input_id = "";
var str_input_password = "";
var str_passwordless_blocked = "";
var str_login_expired = "";
var str_login_refused = "";
var str_qrreg_expired = "";
var str_passwordless_unreg = "";
var str_try = "";
const baseUrl = "/api/commonApi.php";
const selectedLang = localStorage.getItem('lang') || 'ko';

$(document).ready(function() {
	
	passwordless = window.localStorage.getItem('passwordless');
	
	if(passwordless != "Y")
		selPassword(1);
	else
		selPassword(2);
	
	$("#id").focus();
})

function trim(stringToTrim) {
	if(stringToTrim != "")
    	return stringToTrim.replaceAll(/^\s+|\s+$/g,"");
    else
    	return stringToTrim;
}

function copyTxt1() {
	var tmpVal = $("#server_url").html();
	tmpVal = tmpVal.replaceAll(" ", "");
	var tempInput = document.createElement("input")
	tempInput.value = tmpVal;
	document.body.appendChild(tempInput);
	tempInput.select();
	document.execCommand("copy");
	document.body.removeChild(tempInput);
	alert(getTranslatedText("042"));
}

function copyTxt2() {
	var tmpVal = $("#register_key").html();
	tmpVal = tmpVal.replaceAll(" ", "");
	var tempInput = document.createElement("input")
	tempInput.value = tmpVal;
	document.body.appendChild(tempInput);
	tempInput.select();
	document.execCommand("copy");
	document.body.removeChild(tempInput);
	alert(getTranslatedText("043"));
}

function selPassword(sel) {
	if(sel == 1) {
		if(loginStatus == true) {
			cancelLogin();
		}
		
		selPasswordNo = 1;
		$("#login_title").attr("data-translate", "004");
		let newTranslateKey = $("#login_title").attr("data-translate");
		$("#login_title").text(langData[newTranslateKey]);
		$("#btn_login").attr("data-translate", "005");
		newTranslateKey = $("#btn_login").attr("data-translate");
		$("#btn_login").text(langData[newTranslateKey]);
		$("#selLogin1").prop("checked", true);
		$("#selLogin2").prop("checked", false);
		$("#pw").attr("placeholder", "PASSWORD");
		$("#pw").attr("disabled", false);
		$("#pw_group").show();
		$("#bar_group").hide();
		$("#login_bottom1").show();
		$("#login_bottom2").hide();
		
		window.localStorage.removeItem('passwordless');
	}
	else if(sel == 2) {
		selPasswordNo = 2;
		$("#login_title").attr("data-translate", "008");
		let newTranslateKey = $("#login_title").attr("data-translate");
		$("#login_title").text(langData[newTranslateKey]);
		$("#btn_login").attr("data-translate", "008");
		newTranslateKey = $("#btn_login").attr("data-translate");
		$("#btn_login").text(langData[newTranslateKey]);
		$("#selLogin1").prop("checked", false);
		$("#selLogin2").prop("checked", true);
		$("#pw").val("");
		$("#pw").attr("placeholder", "");
		$("#pw").attr("disabled", true);
		$("#pw_group").hide();
		$("#bar_group").show();
		$("#login_bottom1").hide();
		$("#login_bottom2").show();

		window.localStorage.setItem('passwordless', 'Y');
	}
	
	$("#passwordlessSelButton").show();
	$("#manage_bottom").hide();
	$("#passwordlessNotice").hide();
}

function getTranslatedText(key) {
    const text = langData[key];

    if (!text) {
        return key;
    }
 
    return text.replace(/\\n/g, "\n");
}

function login() {
	id = $("#id").val();
	pw = $("#pw").val();
	
	id = trim(id);
	pw = trim(pw);
	
	$("#id").val(id);
	$("#pw").val(pw);
	
	if(id == "") {
		alert(getTranslatedText("016"));
		$("#id").focus();
		return false;
	}

	if(selPasswordNo == 1) {
		if(pw == "") {
			alert(getTranslatedText("017"));
			$("#pw").focus();
			return false;
		}
		$.ajax({
	        url : baseUrl,
	        type : "post",
	        data : {
	        	"id" : $('#id').val().trim(),
	            "pw" : $('#pw').val().trim(), 
				"action" : "loginCheck"
	        },
	        success : function(res) {
	        	if(res.result == "OK") {
	            	location.href = "/main.php";
	        	}
	            else {
	            	alert(getTranslatedText(res.result));
	            	$("#pw").val("");
	            }
	        },
	        error : function(res) {
	            alert(res);
	        },
	        complete : function(res) {
	        }
	    });
	}
	else if(selPasswordNo == 2) {
		if(loginStatus == true)
			cancelLogin();
		else
			loginPasswordless();
	}
	// Passwordless manage
	else if(selPasswordNo == 3) {
		managePasswordless();
	}
}

// ------------------------------------------------ Passwordless ------------------------------------------------

function callApi(data) {
	var api_url = baseUrl;
	var ret_val = "";
	data.action = "passwordlessCallApi";
	data.sort = true;
	$.ajax({
		url: api_url,
		method: 'POST',
		dataType: 'json',
		data: data,
		async: false,
		success: function(data) {
			//console.log(data);
			ret_val = data;
		},
		error: function(xhr, status, error) {
			console.log("[ERROR] code: " + xhr.status + ", message: " + xhr.responseText + ", status: " + status + ", ERROR: " + error);
		},
		complete: function(data) {
		}
	});
	
	return ret_val;
}

function loginPasswordless() {
	checkType = "LOGIN";
	
	var existId = passwordlessCheckID("");
	console.log("existId=" + existId);
	
	if(existId == "T") {
		var token = getTokenForOneTime();
		
		if(token != "") {
			loginStatus = true;
			$("#btn_login").attr("data-translate", "011");
			const newTranslateKey = $("#btn_login").attr("data-translate");
			$("#btn_login").text(langData[newTranslateKey]);
			loginPasswordlessStart(token);
		}
	}
	else if(existId == "F") {
		alert(getTranslatedText("046"));
	}
	else {
		alert(getTranslatedText("027").replace('@@@', id));
	}
}

function passwordlessCheckID(QRReg) {
	var id = $("#id").val();
	var ret_val = "";
	var data = {
		url: "isApUrl",
		params: "userId=" + id + "&QRReg=" + QRReg
	}

	
	var result = callApi(data);
	//console.log(result);
	
	var strResult = result.result;
	if(strResult == "OK") {
		var resultData = result.data;
		var jsonData = JSON.parse(resultData);
		var msg = jsonData.msg;
		var code = jsonData.code;
		
		//console.log("result=" + strResult);
		//console.log("data=" + data);
		//console.log("msg [" + msg + "] code [" + code + "]");
		
		if(code == "000" || code == "000.0") {
			var exist = jsonData.data.exist;
			if(exist)	ret_val = "T";
			else		ret_val = "F";
		}
		else {
			ret_val = msg;
		}	
	}
	else {
		ret_val = strResult;
	}
	
	return ret_val;
}

// onetime token 
function getTokenForOneTime() {

	var id = $("#id").val();
	var ret_val = "";
	var data = {
		url: "getTokenForOneTimeUrl",
		params: "userId=" + id
	}
	
	var result = callApi(data);
	var resultData = result.data;
	var jsonData = JSON.parse(resultData);
	var msg = jsonData.msg;
	var code = jsonData.code;
	
	console.log("msg [" + msg + "] code [" + code + "]");
	
	if(code == "000" || code == "000.0") {
		var oneTimeToken = result.oneTimeToken;
		ret_val = oneTimeToken;
	}
	else {
		alert("Onetime Token Request error : [" + code + "] " + msg);
	}

	return ret_val;
}

function loginPasswordlessStart(token) {
	
	var id = $("#id").val();
	var data = {
		url: "getSpUrl",
		params: "userId=" + id + "&token=" + token
	}
	
	var result = callApi(data);
	var resultData = result.data;
	var jsonData = JSON.parse(resultData);
	var msg = jsonData.msg;
	var code = jsonData.code;
	
	console.log("msg [" + msg + "] code [" + code + "]");
	console.log(jsonData.data);
	
	if(code == "000" || code == "000.0") {
		term = jsonData.data.term;
		servicePassword = jsonData.data.servicePassword;
		pushConnectorUrl = jsonData.data.pushConnectorUrl;
		pushConnectorToken = jsonData.data.pushConnectorToken;
		sessionId = result.sessionId;
		
		window.localStorage.setItem('session_id', sessionId);
		
		var today = new Date();
		passwordless_milisec = today.getTime();
		console.log(term);
		passwordless_terms = parseInt(term - 1);
		console.log("term=" + term + ", servicePassword=" + servicePassword);
		
		connWebSocket();
		drawPasswordlessLogin();
	}
	else if(code == "200.6") {
		sessionId = window.localStorage.getItem('session_id');
		//console.log("Already request authentication --> send [cancel], sessionId=" + sessionId);
		
		if(sessionId !== undefined && sessionId != null && sessionId != "") {
			var data = {
				url: "cancelUrl",
				params: "userId=" + id + "&sessionId=" + sessionId
			}
			
			var result = callApi(data);
			var resultData = result.data;
			var jsonData = JSON.parse(resultData);
			var msg = jsonData.msg;
			var code = jsonData.code;
		
			if(code == "000" || code == "000.0") {
				window.localStorage.removeItem('session_id');
				setTimeout(() => loginPasswordlessStart(token), 500);
			}
			else {
				cancelLogin();
				alert(getTranslatedText("030"));
			}
		}
		else {
			cancelLogin();
			alert(getTranslatedText("030"));	// Try again later.
		}
	}
	else if(code == "200.7") {
		cancelLogin();
		alert(getTranslatedText("030"));
	}
}

function drawPasswordlessLogin() {
	//console.log("----- drawPasswordlessLogin -----");

	var id = $("#id").val();
	var today = new Date();
	var gap_second = Math.ceil((today.getTime() - passwordless_milisec) / 1000);
	
	if(loginStatus == true) {
		if(gap_second < passwordless_terms) {
		
			var today = new Date();
			var now_millisec = today.getTime();
			var gap_millisec = now_millisec - check_millisec;

			console.log(gap_millisec);
			if(gap_millisec > 1500) {
				check_millisec = today.getTime();
				// loginPasswordlessCheck(); polling
			}
	
			gap_millisec = now_millisec - passwordless_milisec;
			var ratio = 100 - (gap_millisec / passwordless_terms / 1000) * 100 - 1;
			if(ratio > 0) {
				var tmpPassword = servicePassword;
				if(tmpPassword.length == 6)
					tmpPassword = tmpPassword.substr(0, 3) + " " + tmpPassword.substr(3, 6);
				
				if(loginStatus == true) {
					$("#passwordless_bar").css("width", ratio + "%");
					$("#passwordless_num").text(tmpPassword);
				}
			}
			
			timeoutId = setTimeout(drawPasswordlessLogin, 100);
		}
		else {
			clearTimeout(timeoutId);
			
			$("#rest_time").html("0 : 00");
			
			setTimeout(() => alert(getTranslatedText("047")), 100);
			setTimeout(() => cancelLogin(), 100);
		}
	}
}

function loginPasswordlessCheck() {
	//console.log("----- loginPasswordlessCheck -----");

	var today = new Date();
	var now_millisec = today.getTime();
	var gap_millisec = now_millisec - passwordless_milisec;
	
	if(gap_millisec < passwordless_terms * 1000 - 1000) {
		
		var id = $("#id").val();
		var data = {
			url: "resultUrl",
			params: "userId=" + id + "&sessionId=" + sessionId
		}
		
		var result = callApi(data);
		console.log(result);
		var resultData = result.data;
		var jsonData = JSON.parse(resultData);
		var msg = jsonData.msg;
		var code = jsonData.code;
		
		if(code == "000" || code == "000.0") {
			
			var auth = jsonData.data.auth;
			if(auth == "Y") {
				clearTimeout(timeoutId);
				window.localStorage.removeItem('session_id');
				
				//alert("Login OK");
				location.href = "/main.php";
			}
			else if(auth == "N") {
				cancelLogin();
				setTimeout(() => alert(getTranslatedText("031")), 100);
			}
			else {
				alert(getTranslatedText("050"));
			}
		}
	}
}

function cancelLogin() {
	loginStatus = false;

	if(timeoutId != null) {
		clearTimeout(timeoutId);
		timeoutId = null;
	}
	
	$("#btn_login").attr("data-translate", "008");
	newTranslateKey = $("#btn_login").attr("data-translate");
	$("#btn_login").text(langData[newTranslateKey]);
	$("#passwordless_bar").css("width", "0%");
	$("#passwordless_num").text("--- ---");
	$("#login_mobile_check").hide();
	sessionId = window.localStorage.getItem('session_id');
	
	var id = $("#id").val();
	var data = {
		url: "cancelUrl",
		params: "userId=" + id + "&sessionId=" + sessionId
	}
	
	var result = callApi(data);
	var resultData = result.data;
	var jsonData = JSON.parse(resultData);
	var msg = jsonData.msg;
	var code = jsonData.code;

	window.localStorage.removeItem('session_id');
	if (qrSocket && qrSocket.readyState === WebSocket.OPEN) {
		qrSocket.close();
	}
}

function moveManagePasswordless() {
	selPasswordNo = 3;
	$("#passwordlessSelButton").hide();
	$("#login_bottom1").hide();
	$("#login_bottom2").hide();
	$("#bar_group").hide();
	$("#pw_group").show();
	$("#manage_bottom").show();
	$("#passwordlessNotice").show();
	$("#login_title").attr("data-translate", "009");
	const newTranslateKey = $("#login_title").attr("data-translate");
	$("#login_title").text(langData[newTranslateKey]);
	$("#btn_login").attr("data-translate", "009");
	$("#btn_login").text(langData[newTranslateKey]);
	$("#pw").attr("placeholder", "PASSWORD");
	$("#pw").attr("disabled", false);
}

function managePasswordless() {
	
	id = $("#id").val();
	pw = $("#pw").val();
	
	id = trim(id);
	pw = trim(pw);
	
	$("#id").val(id);
	$("#pw").val(pw);
	
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
	
	var PasswordlessToken = "";
	
	$.ajax({
        url : baseUrl,
        type : "post",
        data : {
        	"id" : $('#id').val().trim(),
            "pw" : $('#pw').val().trim(),
			"action" : "passwordlessManageCheck"
        },
        async: false,
        success : function(res) {
        	if(res.result == "OK") {
            	PasswordlessToken = res.PasswordlessToken;
        	}
            else {
            	alert(getTranslatedText(res.result));
            	$("#pw").val("");
            }
        },
        error : function(res) {
            alert(res.msg);
        },
        complete : function() {
        }
    });
	console.log("PasswordlessToken=" + PasswordlessToken);
	$("#passwordlessToken").val(PasswordlessToken);
	
	if(PasswordlessToken != "") {
		var existId = passwordlessCheckID("");
		console.log("existId=" + existId);
		
		if(existId == "T") {
			$("#login_content").hide();
			$("#passwordless_unreg_content").show();
		}
		else {
			getPasswordlessQRinfo(PasswordlessToken);
		}
	}
}

// Passwordless  QR 
function getPasswordlessQRinfo(PasswordlessToken) {
	
	checkType = "QR";

	var id = $("#id").val();
	var data = {
		url: "joinApUrl",
		params: "userId=" + id + "&token=" + PasswordlessToken
	}
	
	var result = callApi(data);
	//console.log(result);
	var resultData = result.data;
	var jsonData = JSON.parse(resultData);
	var msg = jsonData.msg;
	var code = jsonData.code;
	
	console.log(data);
	console.log("msg [" + msg + "] code [" + code + "]");
	
	if(code == "000" || code == "000.0") {
		var data = jsonData.data;
		console.log("------------ info -----------");
		console.log(data);
		
		var data = jsonData.data;
		var qr = data.qr;
		var corpId = data.corpId;
		var registerKey = data.registerKey;
		var terms = data.terms;
		var serverUrl = data.serverUrl;
		var userId = data.userId;
		
		console.log("qr: " + qr);
		console.log("corpId: " + corpId);
		console.log("registerKey: " + registerKey);
		console.log("terms: " + terms);
		console.log("serverUrl: " + serverUrl);
		console.log("userId: " + userId);
		
		pushConnectorUrl = data.pushConnectorUrl;
		pushConnectorToken = data.pushConnectorToken;
		
		console.log("pushConnectorUrl: " + pushConnectorUrl);
		console.log("pushConnectorToken: " + pushConnectorToken);
		
		$("#login_content").hide();
		$("#passwordless_reg_content").show();
		
		var tmpRegisterKey = "";
		var tmpInterval = 4;
		for(var i=0; i<registerKey.length / tmpInterval; i++) {
			tmpRegisterKey = tmpRegisterKey + registerKey.substring(i*tmpInterval, i*tmpInterval + tmpInterval);
			if(registerKey.length > i*tmpInterval)
				tmpRegisterKey = tmpRegisterKey + " ";
		}
		registerKey = tmpRegisterKey;
		
		$("#qr").prop("src", qr);
		$("#user_id").html(userId);
		$("#server_url").html(serverUrl);
		$("#register_key").html(registerKey);
		
		var today = new Date();
		passwordless_milisec = today.getTime();
		passwordless_terms = parseInt(terms - 1);
		check_millisec = today.getTime();
		
		connWebSocket();
		drawPasswordlessReg();
	}
	else {
		alert("[" + code + "] " + msg);
	}
}

function drawPasswordlessReg() {

	var id = $("#userId").val();
	var today = new Date();
	var gap_second = Math.ceil((today.getTime() - passwordless_milisec) / 1000);
	
	if(gap_second < passwordless_terms) {
	
		var tmp_min = parseInt((passwordless_terms - gap_second) / 60);
		var tmp_sec = parseInt((passwordless_terms - gap_second) % 60);
		
		if(tmp_sec < 10)
			tmp_sec = "0" + tmp_sec;
		
		$("#rest_time").html(tmp_min + " : " + tmp_sec);
		
		timeoutId = setTimeout(drawPasswordlessReg, 300);
		
		var today = new Date();
		var now_millisec = today.getTime();
		var gap_millisec = now_millisec - check_millisec;
		console.log(gap_millisec);
		if(gap_millisec > 1500) {
			check_millisec = today.getTime();
			// regPasswordlessOK();	// polling   
		}
	}
	else {
		clearTimeout(timeoutId);
		
		$("#rest_time").html("0 : 00");
		
		$("#login_content").show();
		$("#passwordless_reg_content").hide();
		
		setTimeout(() => alert(getTranslatedText("032")), 100);
		setTimeout(() => cancelManage(), 200);
	}
}

// Passwordless   
function regPasswordlessOK() {
	var existId = passwordlessCheckID("T");
	console.log(existId);
	
	if(existId == "T") {
		clearTimeout(timeoutId);
		$("#login_content").hide();
		$("#passwordless_reg_content").show();
	
		cancelManage();
	}
	else{
		alert(getTranslatedText("050"));
	}
}

// Passwordless  
function unregPasswordless() {
	var passwordlessToken = $("#passwordlessToken").val();
	var id = $("#id").val();
	var data = {
		url: "withdrawalApUrl",
		params: "userId=" + id + "&token=" + passwordlessToken
	}
	
	var result = callApi(data);
	//console.log(result);
	var strResult = result.result;
	if(strResult == "OK") {
		var resultData = result.data;
		var jsonData = JSON.parse(resultData);
		var msg = jsonData.msg;
		var code = jsonData.code;
		
		//console.log("data=" + data);
		//console.log("msg [" + msg + "] code [" + code + "]");
		
		if(code == "000" || code == "000.0") {
			window.localStorage.removeItem('passwordless');
			alert(getTranslatedText("033"));
			selPassword(1);
			cancelManage();
		}
		else {
			cancelManage();
			alert("[" + code + "] " + msg);
		}
	}
	else {
		cancelManage();
		alert(getTranslatedText(resultData));
	}
}

//  
function cancelManage() {
	
	if(timeoutId != null) {
		clearTimeout(timeoutId);
		timeoutId = null;
	}

	$("#pw").val("");
	$("#login_content").show();
	$("#passwordless_reg_content").hide();
	$("#passwordless_unreg_content").hide();
	$("#reg_mobile_check").hide();
	
	passwordless = window.localStorage.getItem('passwordless');
	if (qrSocket && qrSocket.readyState === WebSocket.OPEN) {
		qrSocket.close();
	}
	
	if(passwordless != "Y")
		selPassword(1);
	else
		selPassword(2);
}

//
var showHelp = false;
function show_help() {
	if(showHelp == false) {
		$(".pwless_info").show();
		showHelp = true;
	}
	else {
		hide_help();
	}
}
function hide_help() {
	$(".pwless_info").hide();
	showHelp = false;
}

function mobileCheck() {
	if(checkType == "LOGIN")
		loginPasswordlessCheck();
	else if(checkType == "QR")
		regPasswordlessOK();
}

//-------------------------------------------------- WebSocket -------------------------------------------------

/*
	- WebSocket readyState
	  0 CONNECTING	     .
	  1 OPEN		    .
	  2 CLOSING		  .
	  3 CLOSED		 ,   .
*/

var qrSocket = null;
var result = null;

function connWebSocket() {

	qrSocket = new WebSocket(pushConnectorUrl);

	qrSocket.onopen = function(e) {
		console.log("######## WebSocket Connected ########");
		var send_msg = '{"type":"hand","pushConnectorToken":"' + pushConnectorToken + '"}';
		console.log("url [" + pushConnectorUrl + "]");
		console.log("send [" + send_msg + "]");
		qrSocket.send(send_msg);
	}

	qrSocket.onmessage = async function (event) {
		console.log("######## WebSocket Data received [" + qrSocket.readyState + "] ########");
		
		try {
			if (event !== null && event !== undefined) {
				result = await JSON.parse(event.data);
				console.log(result.type);
				if(result.type == "result") {
					console.log(checkType);
					if(checkType == "LOGIN")
						loginPasswordlessCheck();
					else if(checkType == "QR")
						regPasswordlessOK();
				}
			}
		} catch (err) {
			console.log(err);
		}
	}

	qrSocket.onclose = function(event) {
		if(event.wasClean)
			console.log("######## WebSocket Disconnected - OK !!! [" + qrSocket.readyState + "] ########");
		else
			console.log("######## WebSocket Disconnected - Error !!! [" + qrSocket.readyState + "] ########");

		console.log("=================================================");
		console.log(event);
		console.log("=================================================");
	}

	qrSocket.onerror = function(error) {
		console.log("######## WebSocket Error !!! [" + qrSocket.readyState + "] ########");
		console.log("=================================================");
		console.log(error);
		console.log("=================================================");

		$("#login_mobile_check").show();
		$("#reg_mobile_check").show();
	}
}
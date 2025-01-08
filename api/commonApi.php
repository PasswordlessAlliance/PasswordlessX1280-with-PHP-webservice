<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 86400");
    header('Content-Type: application/json; charset=utf-8');

    $servername = "localhost";
    $username = "pwless";
    $password = "qwer!@#$";
    $dbname = "passwordless";
    $port = 3306;   
 
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
         error_log("Database connection failed: " . $conn->connect_error);
         http_response_code(500);
        exit('500 Server Error');
    }

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === "loginCheck") {
            loginCheck();
        } elseif ($action === "logout") {
            logout();
        } elseif ($action === "createUserInfo") {
            createUserInfo();
        } elseif ($action === "changepw") {
            changepw();
        } elseif ($action === "passwordlessCallApi") {
            passwordlessCallApi($_POST['url'], $_POST['params'],$_POST['sort'] );
        } elseif ($action === "withdraw") {
            withdraw();
        } elseif ($action === "passwordlessManageCheck") {
            passwordlessManageCheck();
        }
        else {
            http_response_code(404);
            exit('404 Not Found');
        }
    }
    else {
        http_response_code(404);
        exit('404 Not Found');
    }


    function loginCheck() {
        global $conn;
        $id = $_POST['id'] ?? null;
        $pw = $_POST['pw'] ?? null;
        $result = "OK";
        $recommend = 1;

        http_response_code(200);
        if (!empty($id) || !empty($pw)) {
            $stmt = $conn->prepare("SELECT id, pw, email, regdate FROM userinfo WHERE id = ? AND pw = PASSWORD(?)");
            $stmt->bind_param("ss", $id, $pw);
            $stmt->execute();
            $stmtResult = $stmt->get_result();

            if ($stmtResult->num_rows > 0) {
                $modelMap = passwordlessCallApi("isApUrl", "userId=" . $id);
                if ($modelMap !== null) {
                    $result = $modelMap['result'] ?? null;
        
                    if ($result === "OK") {
                        $data = $modelMap['data'] ?? null;
        
                        if (!empty($data)) {
                            $jsonResponse = json_decode($data, true);
                            if (isset($jsonResponse['data'])) {
                                $jsonData = $jsonResponse['data'];
                                $exist = $jsonData['exist'] ?? false;
                            }
                        }
                    }
                }
                error_log("recommend=" . $recommend . ", exist66666=" . $exist);
                
                if ($recommend == 1 && $exist == 1) {
                    $result = "028";  
                } else {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['id'] = $id;
                }
            } else {
                $result = "020"; 
            }
            $stmt->close();
            $conn->close();
            $modelMap['result'] = $result;
            echo json_encode($modelMap);
        }
        exit;
    }

    function createUserInfo() {
        $modelMap = [];
        global $conn;
        $id = $_POST['id'] ?? null;
        $pw = $_POST['pw'] ?? null;
        $email = $_POST['email'] ?? null;
        $result = "OK";
        http_response_code(200);

        if (!empty($id) || !empty($pw) || !empty($email)) {
            $stmt = $conn->prepare("SELECT id, pw, email, regdate FROM userinfo WHERE id = ? AND pw = PASSWORD(?)");
            $stmt->bind_param("ss", $id, $pw);
            $stmt->execute();
            $stmResult = $stmt->get_result();
            error_log(print_r($result, true));
            if ($stmResult->num_rows > 0) {
                $result = "026";
            } else {
                $stmt = $conn->prepare("INSERT INTO userinfo (id, pw, email) VALUES(?, PASSWORD(?), ?)");
                $stmt->bind_param("sss", $id, $pw, $email);
                $stmt->execute();
            }
        }

        $stmt->close();
        $conn->close();
        $modelMap['result'] = $result;
        echo json_encode($modelMap);
        exit;
    }

    function passwordlessManageCheck() {
        global $conn;
        $modelMap = [];
        $id = $_POST['id'] ?? null;
        $pw = $_POST['pw'] ?? null;

        error_log("passwordlessManageCheck : id [" . $id . "] pw ["  . $pw . "]");

        $result = "OK";
        http_response_code(200);

        if (!empty($id) || !empty($pw) || !empty($email)) {
            $stmt = $conn->prepare("SELECT id, pw, email, regdate FROM userinfo WHERE id = ? AND pw = PASSWORD(?)");
            $stmt->bind_param("ss", $id, $pw);
            $stmt->execute();
            $stmResult = $stmt->get_result();

            if ($stmResult->num_rows > 0) {
                $tmpToken = uniqid('', true);

                $tmpTime = round(microtime(true) * 1000);

                error_log("passwordlessManageCheck : token [" . $tmpToken . "] time [" . $tmpTime . "]");

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['PasswordlessToken'] = $tmpToken;
                $_SESSION['PasswordlessTime'] = $tmpTime;
                $modelMap['PasswordlessToken'] = $tmpToken;

            } else {
                $result = "020";
            }
        }
        $stmt->close();
        $conn->close();
        $modelMap['result'] = $result;
        echo json_encode($modelMap);
        exit;
    }

    function passwordlessCallApi($url = null, $params = null, $sort = null) {
        global $conn;
        define("IS_AP_URL", "/ap/rest/auth/isAp");                      // Passwordless Registration REST API   
        define("JOIN_AP_URL", "/ap/rest/auth/joinAp");                  // Passwordless Deactivation REST API
        define("WITHDRAWAL_AP_URL", "/ap/rest/auth/withdrawalAp");      // Passwordless One-Time Token Request REST API
        define("GET_TOKEN_FOR_ONE_TIME_URL", "/ap/rest/auth/getTokenForOneTime"); // Passwordless Authentication Request REST API
        define("GET_SP_URL", "/ap/rest/auth/getSp");                    // Passwordless Authentication Result Request REST API
        define("RESULT_URL", "/ap/rest/auth/result");                   // Passwordless Authentication Cancellation REST API
        define("CANCEL_URL", "/ap/rest/auth/cancel");

        
        define("SERVER_KEY", "5be1fec6609a03d3");
        define("REST_CHECK_URL", "http://your-passwordlessX1280-domain:11040");
        define("PUSH_CONNECTOR_URL", "ws:your-passwordlessX1280-domain:15010");


        session_start();

        $modelMap = [];
        $result = "OK";

        if ($url === null) $url = "";
        if ($params === null) $params = "";

        $mapParams = getParamsKeyValue($params);

        error_log(print_r($mapParams, true));

        $userId = $mapParams['userId'] ?? '';
        $userToken = $mapParams['token'] ?? '';

        $sessionUserToken = $_SESSION['PasswordlessToken'] ?? '';
        $sessionTime = $_SESSION['PasswordlessTime'] ?? '';

        if ($sessionUserToken === null) $sessionUserToken = "";
        if ($sessionTime === null) $sessionTime = "";

        $nowTime = microtime(true) * 1000;
        $tokenTime = 0;
        $gapTime = 0;

        try {
            $tokenTime = (float)$sessionTime;
            $gapTime = (int)($nowTime - $tokenTime);
        } catch (Exception $e) {
            $gapTime = 99999999;
        }

        $matchToken = !empty($sessionUserToken) && $sessionUserToken === $userToken;

        if (($url === "joinApUrl" || $url === "withdrawalApUrl") && (!$matchToken || $gapTime > 5 * 60 * 1000)) {
            $result = "038";
        }

        if($url != "resultUrl"){
            error_log("passwordlessCallApi : url [" . $url . "] params [" . $params . "] userId [" . $userId . "]");
        }

        $stmt = $conn->prepare("SELECT id, pw, email, regdate FROM userinfo WHERE id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmResult = $stmt->get_result();
        if ($stmResult->num_rows == 0) {
            $result = "";
            $modelMap['result'] = $result;
            echo json_encode($modelMap);
            exit;
        }
        $apiUrl = "";

        if ($url === "isApUrl") {
            $apiUrl = IS_AP_URL;
        } elseif ($url === "joinApUrl") {
            $apiUrl = JOIN_AP_URL;
        } elseif ($url === "withdrawalApUrl") {
            $apiUrl = WITHDRAWAL_AP_URL;
        } elseif ($url === "getTokenForOneTimeUrl") {
            $apiUrl = GET_TOKEN_FOR_ONE_TIME_URL;
        } elseif ($url === "getSpUrl") {
            $apiUrl = GET_SP_URL;
            $params .= "&clientIp=" . $_SERVER['REMOTE_ADDR'] . "&sessionId=" . session_id(). "_sessionId" . "&random=" . session_id() . "&password=";
        } elseif ($url === "resultUrl") {
            $apiUrl = RESULT_URL;
        } elseif ($url === "cancelUrl") {
            $apiUrl = CANCEL_URL;
        }

        // REST API 
        if (!empty($apiUrl)) {
            try {
                $result = callApi("POST", REST_CHECK_URL . $apiUrl, $params);
            } catch (Exception $e) {
                if($url === "resultUrl"){
                    error_log(print_r($result, true));
                }
                error_log($e->getMessage());
            }
        }

        if ($url === "getTokenForOneTimeUrl") {
            // JSON 
            $jsonResponse = json_decode($result, true);
            $jsonData = $jsonResponse['data'] ?? [];
            $token = $jsonData['token'] ?? '';
            $oneTimeToken = getDecryptAES($token, SERVER_KEY);

            $modelMap['oneTimeToken'] = $oneTimeToken;
        }

        if ($url === "getSpUrl") {
            $modelMap['sessionId'] = session_id() ."_sessionId";
        }

        if ($url === "joinApUrl") {
            $modelMap['pushConnectorUrl'] = PUSH_CONNECTOR_URL;
        }

        if ($url === "isApUrl") {
            $isQRReg = $mapParams['QRReg'] ?? '';
            if ($isQRReg === "T") {
                $jsonResponse = json_decode($result, true);
                $jsonData = $jsonResponse['data'] ?? [];
                $exist = $jsonData['exist'] ?? false;

                if ($exist) {
                    $newPw = time() . ":" . $userId; 
                    $stmt = $conn->prepare("UPDATE userinfo SET pw = PASSWORD(?) WHERE id = ?");
                    $stmt->bind_param("ss", $newPw, $userId);
                    $stmt->execute();
                    error_log("changepw completed.");
                }
            }
        }

        if ($url === "resultUrl") {
            $jsonResponse = json_decode($result, true);
            $jsonData = $jsonResponse['data'] ?? [];

            if (!empty($jsonData)) {
                $auth = $jsonData['auth'] ?? '';

                if ($auth === "Y") {
                    $newPw = time() . ":" . $userId;  
                    $stmt = $conn->prepare("UPDATE userinfo SET pw = PASSWORD(?) WHERE id = ?");
                    $stmt->bind_param("ss", $newPw, $userId);
                    $stmt->execute();
                    error_log("changepw completed.");    
                    $_SESSION['id'] = $userId;
                }
            }
        }

        $modelMap['result'] = "OK";
        $modelMap['data'] = $result;

        if ($sort != null) {
            echo json_encode($modelMap);
            exit;
        }
        
        return $modelMap;
    }

    function callApi($type, $requestURL, $params) {
        $retVal = "";
        $mapParams = getParamsKeyValue($params);

        try {
            // URL  
            $queryParams = http_build_query($mapParams);
            $url = $requestURL;

            if (strtoupper($type) === "GET") {
                $url .= "?" . $queryParams;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if (strtoupper($type) === "POST") {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $queryParams);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/x-www-form-urlencoded",
                ]);
            } else {
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/x-www-form-urlencoded",
                ]);
            }
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }
            $retVal = $response;
            curl_close($ch);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return $retVal;
    }

    function getParamsKeyValue($params) {
        $arrParams = explode("&", $params);
        $map = [];

        foreach ($arrParams as $param) {
            $name = "";
            $value = "";

            $tmpArr = explode("=", $param);
            $name = $tmpArr[0] ?? "";

            if (count($tmpArr) === 2) {
                $value = $tmpArr[1];
            }

            $map[$name] = urldecode($value);
        }

        return $map;
    }

    function getDecryptAES($encrypted, $key) {
        $strRet = null;

        if ($key === null || strlen($key) === 0) {
            return null;
        }

        try {
            $iv = substr($key, 0, 16); // AES IV  16 
            $cipher = "aes-128-cbc"; // PHP AES CBC  

            // Base64 
            $byteStr = base64_decode($encrypted);

            //  
            $strRet = openssl_decrypt($byteStr, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return $strRet;
    }

    function logout() {
        session_start();
        $modelMap = [];
        $id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
        $_SESSION['id'] = null;
        session_unset();
        session_destroy();
        $modelMap['result'] = "OK";
        error_log("logout: [" . $id . "] completed.");
        echo json_encode($modelMap);
        exit;
    }

    function withdraw() {
        session_start();
        global $conn;
        $modelMap = [];
        $id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

        if($id != ""){
            $stmt = $conn->prepare("DELETE FROM userinfo WHERE id = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
        }
        session_unset();
        session_destroy();
        
        $modelMap['result'] = "OK";
        
        error_log("withRaw: [" . $id . "] completed.");
        echo json_encode($modelMap);
        exit;
    }

    function changepw() {
        global $conn;
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $pw = isset($_POST['pw']) ? $_POST['pw'] : '';
        $modelMap = [];
        $result = "OK";
        if (!empty($id) && !empty($pw)) {
            if (!empty($id) || !empty($pw) || !empty($email)) {
                $stmt = $conn->prepare("SELECT id, pw, email, regdate FROM userinfo WHERE id = ?");
                $stmt->bind_param("s", $id);
                $stmt->execute();
                $stmResult = $stmt->get_result();
                if ($stmResult->num_rows > 0) {
                    $stmt = $conn->prepare("UPDATE userinfo SET pw = PASSWORD(?) WHERE id = ?");
                    $stmt->bind_param("ss", $pw, $id);
                    $stmt->execute();
                    error_log("changepw completed.");
                } else {
                    error_log("changepw failed");
                    $result = "027";
                }
            } 
        }
        $stmt->close();
        $conn->close();
        $modelMap['result'] = $result;
        echo json_encode($modelMap);
        exit;
    }
?>

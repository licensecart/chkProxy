<?php
/**
Proxy/VPN Detection Hook for WHMCS
Version 1.4 by KuJoe (JMD.cc)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
**/

function chkProxy($vars) {
	// Set me!
	$license_key = 'MAXMIND_LICENSE_KEY'; # Set this to your Maxmind License Key (your account must have Proxy Detection queries).
	
	// Optional
	$max_score = '1.7'; # Likelihood of proxy (0.5 = 15%, 1.0 = 30%, 2.0 = 60%, 3.0+ = 90%)
	$error = 'You appear to be ordering from a proxy/VPN. Please logout of your proxy/VPN to continue ordering. If you believe that you received this error by mistake, please open a ticket with your IP address and we will investigate further. Thank you.';
	
	// No need to edit anything below this.
	$ipaddress = $_SERVER['REMOTE_ADDR'];
	$result = select_query("mod_chkproxy","",array("ipaddr"=>$ipaddress));
	if (mysql_num_rows($result) == 0) {
		$query = "https://minfraud.maxmind.com/app/ipauth_http?l=" . $license_key . "&i=" . $ipaddress;
		$query = file_get_contents($query);
		$score = substr($query, strpos($query, "=") + 1);
		if ($score != 'MAX_REQUESTS_REACHED') {
			if ($score >= $max_score) {
				global $errormessage;
				$errormessage .= $error;
			}
		}
	} else {
		$data = mysql_fetch_assoc($result);
		$score = $data['proxyscore'];
		if ($score >= $max_score) {
			global $errormessage;
			$errormessage .= $error;
		}
	}
	insert_query("mod_chkproxy", array("ipaddr"=>$ipaddress, "proxyscore"=>$score));
}

add_hook("ShoppingCartValidateCheckout",1,"chkProxy");
?>
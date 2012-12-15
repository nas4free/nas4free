--- ./tools/nut-scanner/nut-scanner.c.orig	2012-07-31 19:38:58.000000000 +0200
+++ ./tools/nut-scanner/nut-scanner.c	2012-12-06 13:22:08.000000000 +0100
@@ -35,7 +35,7 @@
 
 #define ERR_BAD_OPTION	(-1)
 
-const char optstring[] = "?ht:s:e:c:l:u:W:X:w:x:p:CUSMOAm:NPqIVa";
+const char optstring[] = "?ht:s:e:c:l:u:W:X:w:x:p:b:B:d:D:CUSMOAm:NPqIVa";
 
 #ifdef HAVE_GETOPT_LONG
 const struct option longopts[] =
@@ -50,6 +50,10 @@
 	{ "privPassword",required_argument,NULL,'X' },
 	{ "authProtocol",required_argument,NULL,'w' },
 	{ "privProtocol",required_argument,NULL,'x' },
+	{ "username",required_argument,NULL,'b' },
+	{ "password",required_argument,NULL,'B' },
+	{ "authType",required_argument,NULL,'d' },
+	{ "cipher_suite_id",required_argument,NULL,'D' },
 	{ "port",required_argument,NULL,'p' },
 	{ "complete_scan",no_argument,NULL,'C' },
 	{ "usb_scan",no_argument,NULL,'U' },
@@ -110,7 +114,9 @@
 }
 static void * run_ipmi(void * arg)
 {
-	dev[TYPE_IPMI] = nutscan_scan_ipmi();
+	nutscan_ipmi_t * sec = (nutscan_ipmi_t *)arg;
+
+	dev[TYPE_IPMI] = nutscan_scan_ipmi(start_ip,end_ip,sec);
 	return NULL;
 }
 #endif /* HAVE_PTHREAD */
@@ -133,6 +139,7 @@
 int main(int argc, char *argv[])
 {
 	nutscan_snmp_t snmp_sec;
+	nutscan_ipmi_t ipmi_sec;
 	int opt_ret;
 	char *	cidr = NULL;
 	int allow_all = 0;
@@ -147,6 +154,12 @@
 	int ret_code = EXIT_SUCCESS;
 
 	memset(&snmp_sec, 0, sizeof(snmp_sec));
+	memset(&ipmi_sec, 0, sizeof(ipmi_sec));
+	/* Set the default values for IPMI */
+	ipmi_sec.authentication_type = IPMI_AUTHENTICATION_TYPE_MD5;
+	ipmi_sec.ipmi_version = IPMI_1_5; /* default to IPMI 1.5, if not otherwise specified */
+	ipmi_sec.cipher_suite_id = 3; /* default to HMAC-SHA1; HMAC-SHA1-96; AES-CBC-128 */
+	ipmi_sec.privilege_level = IPMI_PRIVILEGE_LEVEL_ADMIN; /* should be sufficient */
 
 	nutscan_init();
 
@@ -220,6 +233,45 @@
 				}
 				allow_snmp = 1;
 				break;
+			case 'b':
+				if(!nutscan_avail_ipmi) {
+					goto display_help;
+				}
+				ipmi_sec.username = strdup(optarg);
+				break;
+			case 'B':
+				if(!nutscan_avail_ipmi) {
+					goto display_help;
+				}
+				ipmi_sec.password = strdup(optarg);
+				break;
+			case 'd':
+				if(!nutscan_avail_ipmi) {
+					goto display_help;
+				}
+				if (!strcmp(optarg, "NONE")) {
+					ipmi_sec.authentication_type = IPMI_AUTHENTICATION_TYPE_NONE;
+				}
+				else if (!strcmp(optarg, "STRAIGHT_PASSWORD_KEY")) {
+					ipmi_sec.authentication_type = IPMI_AUTHENTICATION_TYPE_STRAIGHT_PASSWORD_KEY;
+				}
+				else if (!strcmp(optarg, "MD2")) {
+					ipmi_sec.authentication_type = IPMI_AUTHENTICATION_TYPE_MD2;
+				}
+				else if (!strcmp(optarg, "MD5")) {
+					ipmi_sec.authentication_type = IPMI_AUTHENTICATION_TYPE_MD5;
+				}
+				else {
+					fprintf(stderr,"Unknown authentication type (%s). Defaulting to MD5\n", optarg);
+				}
+				break;
+			case 'D':
+				if(!nutscan_avail_ipmi) {
+					goto display_help;
+				}
+				ipmi_sec.cipher_suite_id = atoi(optarg);
+				/* Force IPMI 2.0! */
+				ipmi_sec.ipmi_version = IPMI_2_0;
 			case 'p':
 				port = strdup(optarg);
 				break;
@@ -307,6 +359,8 @@
 				if( nutscan_avail_ipmi ) {
 					printf("  -I, --ipmi_scan: Scan IPMI devices.\n");
 				}
+
+				printf("\nNetwork specific options:\n");
 				printf("  -t, --timeout <timeout in seconds>: network operation timeout (default %d).\n",DEFAULT_TIMEOUT);
 				printf("  -s, --start_ip <IP address>: First IP address to scan.\n");
 				printf("  -e, --end_ip <IP address>: Last IP address to scan.\n");
@@ -325,6 +379,18 @@
 					printf("  -X, --privPassword <privacy pass phrase>: Set the privacy pass phrase used for encrypted SNMPv3 messages (mandatory if you set secLevel to authPriv)\n");
 				}
 
+				if( nutscan_avail_ipmi ) {
+					printf("\nIPMI over LAN specific options:\n");
+					printf("  -b, --username <username>: Set the username used for authenticating IPMI over LAN connections (mandatory for IPMI over LAN. No default)\n");
+					/* Specify  the  username  to  use  when authenticating with the remote host.  If not specified, a null (i.e. anonymous) username is assumed. The user must have
+					 * at least ADMIN privileges in order for this tool to operate fully. */
+					printf("  -B, --password <password>: Specify the password to use when authenticationg with the remote host (mandatory for IPMI over LAN. No default)\n");
+					/* Specify the password to use when authenticationg with the remote host.  If not specified, a null password is assumed. Maximum password length is 16 for IPMI
+					 * 1.5 and 20 for IPMI 2.0. */
+					printf("  -d, --authType <authentication type>: Specify the IPMI 1.5 authentication type to use (NONE, STRAIGHT_PASSWORD_KEY, MD2, and MD5) with the remote host (default=MD5)\n");
+					printf("  -D, --cipher_suite_id <cipher suite id>: Specify the IPMI 2.0 cipher suite ID to use, for authentication, integrity, and confidentiality (default=3)\n");
+				}
+
 				printf("\nNUT specific options:\n");
 				printf("  -p, --port <port number>: Port number of remote NUT upsd\n");
 				printf("\ndisplay specific options:\n");
@@ -371,6 +437,7 @@
 	if( allow_snmp && nutscan_avail_snmp ) {
 		if( start_ip == NULL ) {
 			printq(quiet,"No start IP, skipping SNMP\n");
+			nutscan_avail_snmp = 0;
 		}
 		else {
 			printq(quiet,"Scanning SNMP bus.\n");
@@ -398,6 +465,7 @@
 	if( allow_oldnut && nutscan_avail_nut) {
 		if( start_ip == NULL ) {
 			printq(quiet,"No start IP, skipping NUT bus (old connect method)\n");
+			nutscan_avail_nut = 0;
 		}
 		else {
 			printq(quiet,"Scanning NUT bus (old connect method).\n");
@@ -425,11 +493,11 @@
 	if( allow_ipmi  && nutscan_avail_ipmi) {
 		printq(quiet,"Scanning IPMI bus.\n");
 #ifdef HAVE_PTHREAD
-		if(pthread_create(&thread[TYPE_IPMI],NULL,run_ipmi,NULL)) {
+		if(pthread_create(&thread[TYPE_IPMI],NULL,run_ipmi,&ipmi_sec)) {
 			nutscan_avail_ipmi = 0;
 		}
 #else
-		dev[TYPE_IPMI] = nutscan_scan_ipmi();
+		dev[TYPE_IPMI] = nutscan_scan_ipmi(start_ip,end_ip,&ipmi_sec);
 #endif /* HAVE_PTHREAD */
 	}
 

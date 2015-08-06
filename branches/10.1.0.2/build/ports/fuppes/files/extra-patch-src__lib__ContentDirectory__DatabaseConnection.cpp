--- src/lib/ContentDirectory/DatabaseConnection.cpp.orig	2011-09-22 17:45:44.000000000 +0900
+++ src/lib/ContentDirectory/DatabaseConnection.cpp	2014-12-30 06:51:55.000000000 +0900
@@ -166,7 +166,7 @@
 
 static CConnectionParams m_connectionParams;
 //static fuppesThreadMutex mutex;
-static fuppes::Mutex mutex;
+static fuppes::Mutex fmutex;
 
 bool CDatabase::connect(const CConnectionParams params) // static
 {
@@ -215,7 +215,7 @@
 
 ISQLQuery* CDatabase::query() // static
 {	
-	fuppes::MutexLocker locker(&mutex);
+	fuppes::MutexLocker locker(&fmutex);
 
   if(!m_connection)
 		return NULL;
@@ -226,7 +226,7 @@
 
 CDatabaseConnection* CDatabase::connection(bool create /*= false*/) // static
 {
-	fuppes::MutexLocker locker(&mutex);
+	fuppes::MutexLocker locker(&fmutex);
 	
 	if(!m_connection) {
 		return NULL;

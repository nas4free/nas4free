--- sys/net/rtsock.c.orig	2016-11-29 12:51:19.541256000 +0100
+++ sys/net/rtsock.c	2016-12-02 22:25:47.000000000 +0100
@@ -1566,8 +1566,8 @@
 }
 
 static int
-sysctl_iflist_ifml(struct ifnet *ifp, struct rt_addrinfo *info,
-    struct walkarg *w, int len)
+sysctl_iflist_ifml(struct ifnet *ifp, const struct if_data *src_ifd,
+    struct rt_addrinfo *info, struct walkarg *w, int len)
 {
 	struct if_msghdrl *ifm;
 	struct if_data *ifd;
@@ -1598,14 +1598,14 @@
 		ifd = &ifm->ifm_data;
 	}
 
-	if_data_copy(ifp, ifd);
+	memcpy(ifd, src_ifd, sizeof(*ifd));
 
 	return (SYSCTL_OUT(w->w_req, (caddr_t)ifm, len));
 }
 
 static int
-sysctl_iflist_ifm(struct ifnet *ifp, struct rt_addrinfo *info,
-    struct walkarg *w, int len)
+sysctl_iflist_ifm(struct ifnet *ifp, const struct if_data *src_ifd,
+    struct rt_addrinfo *info, struct walkarg *w, int len)
 {
 	struct if_msghdr *ifm;
 	struct if_data *ifd;
@@ -1630,7 +1630,7 @@
 		ifd = &ifm->ifm_data;
 	}
 
-	if_data_copy(ifp, ifd);
+	memcpy(ifd, src_ifd, sizeof(*ifd));
 
 	return (SYSCTL_OUT(w->w_req, (caddr_t)ifm, len));
 }
@@ -1705,15 +1705,18 @@
 {
 	struct ifnet *ifp;
 	struct ifaddr *ifa;
+	struct if_data ifd;
 	struct rt_addrinfo info;
 	int len, error = 0;
 	struct sockaddr_storage ss;
 
 	bzero((caddr_t)&info, sizeof(info));
+	bzero(&ifd, sizeof(ifd));
 	IFNET_RLOCK_NOSLEEP();
 	TAILQ_FOREACH(ifp, &V_ifnet, if_link) {
 		if (w->w_arg && w->w_arg != ifp->if_index)
 			continue;
+		if_data_copy(ifp, &ifd);
 		IF_ADDR_RLOCK(ifp);
 		ifa = ifp->if_addr;
 		info.rti_info[RTAX_IFP] = ifa->ifa_addr;
@@ -1723,9 +1726,11 @@
 		info.rti_info[RTAX_IFP] = NULL;
 		if (w->w_req && w->w_tmem) {
 			if (w->w_op == NET_RT_IFLISTL)
-				error = sysctl_iflist_ifml(ifp, &info, w, len);
+				error = sysctl_iflist_ifml(ifp, &ifd, &info, w,
+				    len);
 			else
-				error = sysctl_iflist_ifm(ifp, &info, w, len);
+				error = sysctl_iflist_ifm(ifp, &ifd, &info, w,
+				    len);
 			if (error)
 				goto done;
 		}

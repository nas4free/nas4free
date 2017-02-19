--- src/iperf_api.c.orig	2017-01-12 17:42:27.000000000 +0100
+++ src/iperf_api.c	2017-01-13 07:29:29.245990000 +0100
@@ -2674,7 +2674,7 @@
 	    if (test->json_output)
 		cJSON_AddItemToArray(json_interval_streams, iperf_json_printf("socket: %d  start: %f  end: %f  seconds: %f  bytes: %d  bits_per_second: %f  retransmits: %d  snd_cwnd:  %d  rtt:  %d  omitted: %b", (int64_t) sp->socket, (double) st, (double) et, (double) irp->interval_duration, (int64_t) irp->bytes_transferred, bandwidth * 8, (int64_t) irp->interval_retrans, (int64_t) irp->snd_cwnd, (int64_t) irp->rtt, irp->omitted));
 	    else {
-		unit_snprintf(cbuf, UNIT_LEN, irp->snd_cwnd, 'A');
+		unit_snprintf(cbuf, UNIT_LEN, (unsigned int)irp->snd_cwnd, 'A');
 		iprintf(test, report_bw_retrans_cwnd_format, sp->socket, st, et, ubuf, nbuf, irp->interval_retrans, cbuf, irp->omitted?report_omitted:"");
 	    }
 	} else {

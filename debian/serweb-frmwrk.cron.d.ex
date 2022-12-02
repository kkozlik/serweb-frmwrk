#
# Regular cron jobs for the serweb-frmwrk package
#
0 4	* * *	root	[ -x /usr/bin/serweb-frmwrk_maintenance ] && /usr/bin/serweb-frmwrk_maintenance

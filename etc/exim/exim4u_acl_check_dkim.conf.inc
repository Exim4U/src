acl_check_dkim:

# accept authenticated hosts
  accept authenticated = *
# exim4u: IP Whitelist
  accept hosts = +IPwhitelist : +backup_mx_hosts : +relay_from_hosts
# Accept From Local SMTP.
  accept hosts = :

# Modify this file according to your needs.  You have three options:
#
# 1) Greylist if DKIM fails and the sender is in testing mode.
# 2) Greylist if DKIM fails and the sender is not in testing mode.
# 3) Deny if DKIM fails and the sender is not in testing mode.
#
# You may enable either option 2 or 3 but obviously do not enable both simultaneously.
#
# 1) Greylist if DKIM fails and is in testing mode. Uncomment the following lines to greylist DKIM
# failures in testing mode.
#  warn dkim_status = fail
#  condition = ${if eq {$dkim_key_testing}{1}{yes}{no}}
#  set acl_m_greylistreasons = DKIM failure in testing mode: $dkim_verify_reason\n $acl_m_greylistreasons

# 2) Greylist if DKIM fails and is not in testing mode. Uncomment the following lines to greylist DKIM
# failures that are not in testing mode.
#  warn dkim_status = fail
#  condition = ${if eq {$dkim_key_testing}{1}{no}{yes}}
#  set acl_m_greylistreasons = DKIM failure not in testing mode: $dkim_verify_reason\n $acl_m_greylistreasons

## 3) Deny if DKIM fails and not in testing mode. Uncomment the following lines to reject DKIM failures
## that are not in testing mode.
#  deny dkim_status = fail
#  condition = ${if eq {$dkim_key_testing}{1} {no}{yes}}
#  message = Rejected for DKIM failure: $dkim_verify_reason 

# exim4u: Generate DKIM header for incoming mail that is signed with DKIM and not greylisted or denied above.
  warn	add_header = 	X-DKIM: Status on $acl_m_helo_data ($acl_m_interface) using Exim $version_number: dkim=$dkim_verify_status; \
			signing_identity="$dkim_cur_signer"
# Accept incoming mail that is signed with DKIM and not greylisted or denied above.
  accept

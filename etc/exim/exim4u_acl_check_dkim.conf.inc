acl_check_dkim:

# exim4u: First, accept all mail for which we do not want to do DKIM checks.

# Accept authenticated hosts
  accept authenticated = *

# Accept From Local SMTP.
  accept hosts = :

# Accept all mail from backup_mx_hosts which, by design, also uses exim4u.
# So, Exim4U on the backup_mx_host should have already generated the dkim header.
  accept hosts    = +backup_mx_hosts

# exim4u: Accept IP Whitelist
  accept hosts = +IPwhitelist

# exim4u: Accept hosts for which we are outgoing relays
  accept hosts = +relay_from_hosts 

# backup_mx_rl_hosts are not accepted by default since, by design, dkim verification
# should not have been done by the backup_mx_rl_ relay_hosts. If you want
# to avoid dkim checks for backup_mx_rl_hosts then uncomment the following line.
# accept backup_mx_rl_hosts

# exim4u: Greylist if DKIM verification status is "none" or "invalid" for known DKIM signers
# such as yahoo.com, gmail.com and paypal.com. This detects mail forgery from senders that
# are impersonating known DKIM signers using invalid or non-existant signatures. Add or
# remove domain names of known DKIM signers as necessary.
# Uncomment the following lines to greylist possible DKIM forgery for known DKIM signers.
 warn sender_domains = yahoo.com:gmail.com:paypal.com
 dkim_signers = yahoo.com:gmail.com:paypal.com
 condition = ${if match_domain{$sender_address_domain}{$dkim_cur_signer} {yes}{no}}
 dkim_status = none:invalid
 set acl_m_greylistreasons = Possible DKIM forgery (signature status: $dkim_verify_status from: $sender_address_domain)\n $acl_m_greylistreasons
#
# Greylist if DKIM fails and is in testing mode. Uncomment the following lines to greylist DKIM
# failures in testing mode.
  warn dkim_status = fail
  condition = ${if eq {$dkim_key_testing}{1}{yes}{no}}
  set acl_m_greylistreasons = DKIM failure in testing mode: $dkim_verify_reason\n $acl_m_greylistreasons

# Greylist if DKIM fails and is not in testing mode. Uncomment the following lines to greylist DKIM
# failures that are not in testing mode.
  warn dkim_status = fail
  condition = ${if eq {$dkim_key_testing}{1}{no}{yes}}
  set acl_m_greylistreasons = DKIM failure not in testing mode: $dkim_verify_reason\n $acl_m_greylistreasons

## Deny if DKIM fails and not in testing mode. Uncomment the following lines to reject DKIM failures
## that are not in testing mode.
#  deny dkim_status = fail
#  condition = ${if eq {$dkim_key_testing}{1} {no}{yes}}
#  message = Rejected for DKIM failure: $dkim_verify_reason 

# exim4u: Generate DKIM header for all other incoming mail
  warn	add_header = 	X-DKIM: Status on $acl_m_helo_data ($acl_m_interface) using Exim $version_number: dkim=$dkim_verify_status; \
			signing_identity="$dkim_cur_signer"
# !condition = ${if eq{$dkim_verify_status}{}{1}{0}}

  accept

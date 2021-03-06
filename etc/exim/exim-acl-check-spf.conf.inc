# exim-acl-check-spf.conf.inc
#
# SPF Auth test for Exim 4.xx
# Version 2.05 by david @ ols . es
#
# Features:
#
#    Full SPF support via spfd socket
#
# Warning:
#
#    Will use acl_m9, acl_m8, acl_m7 and acl_m6
#
# Requires
#
#    Mail::SPF::Query ver 1.9.1
#
# Usage instructions:
#
# 1. copy this file to your /usr/local/exim
#
# 2. add this line to your exim configuration file after your
#    begin acl:
#
#    .include /usr/local/exim/spf.acl
#
# 3. Grab a copy of Mail::SPF::Query from
#    http://spf.pobox.com/downloads.html and install it
#
# 4. Run spfd -path=/tmp/spfd as the same user as Exim runs
#
# 5. Now you can use the test on your RCPT/MAIL ACL this way:
#
#    deny    !acl        = spf_rcpt_acl
#
#    And on your DATA ACL:
#
#    deny    senders     = :
#            !acl        = spf_from_acl
#
#    now acl_m8 will hold 'pass','fail', 'unknown'
#    so you can take other decisions based on the result

spf_rcpt_acl:

    # Check envelope sender

    warn     set acl_m8  = $sender_address
    deny     !acl        = spf_check
    warn     message     = Received-SPF-Sender: $acl_m7
    accept

spf_from_acl:

# debugging log message
#   warn     log_message = SPF Sender pre-condition: $sender_address SPF From: ${address:$h_from:}
    # Check header From:
# exim4u MOD only check if From address differs from envelope sender (return path):
    accept   condition = ${if eq { $sender_address } { ${address:$h_from:} } }
    warn     set acl_m8	 = ${address:$h_from:}
    deny     !acl        = spf_check
    warn     message     = Received-SPF-From: $acl_m7
    accept

spf_check:

    warn     set acl_m9  = ${readsocket{/tmp/spfd}\
                           {ip=$sender_host_address\n\
			   helo=${if def:sender_helo_name\
			   {$sender_helo_name}{NOHELO}}\
                           \nsender=$acl_m8\n\n}{20s}{\n}{socket failure}} 

    # Defer on socket error

    #defer    condition   = ${if eq{$acl_m9}{socket failure}{yes}{no}}
    #         message     = Cannot connect to spfd

    # Prepare answer and get results

    warn     set acl_m9  = ${sg{$acl_m9}{\N=(.*)\n\N}{=\"\$1\" }}
             set acl_m8  = ${extract{result}{$acl_m9}{$value}{unknown}}
             set acl_m7  = ${extract{header_comment}{$acl_m9}{$value}{}}

    # Check for fail

    deny     condition   = ${if eq{$acl_m8}{fail}{yes}{no}}
             message     = $acl_m7
             log_message = Not authorized by SPF

    accept

# exim-mailinglist-router.conf.inc
#
# This router implements a Close Mailing List : only members can write to the mailing list.
#
# - non members that attempt to write to the list may receive a message via the rejection transport:
#   "Your message to $local_part@$domain is being returned because it is a closed mailing list."
# - a member can be out of the domain
# - only a member can write to the mailing list
# TODO : We could implement a flag to decide if only members in the domain can use the list.
#
virtual_mailing_lists:
    driver = redirect
    allow_fail
    senders = ${lookup mysql{ \
		select min(m.email) \
		from    ml m, domains d \
		where   d.enabled   = '1' \
                	and d.domain    = '${quote_mysql:$domain}' \
                	and d.domain_id = m.domain_id \
                	and m.enabled   = '1' \
                	and m.name      = '${quote_mysql:$local_part}' \
                	and m.email     = '${quote_mysql:$sender_address}' \
		}}
    data = ${lookup mysql{ \
            select  m.email \
            from    ml m, domains d \
            where   d.domain    = '${quote_mysql:$domain}' \
                and d.enabled   = '1' \
                and d.domain_id = m.domain_id \
                and m.enabled   = '1' \
                and m.name      = '${quote_mysql:$local_part}' }}
#    local_part_suffix = -*
#    local_part_suffix_optional
    retry_use_local_part
    reply_transport = address_reply
    pipe_transport = address_pipe
#
# Here is where we set the Reply-To Header
    headers_remove = Reply-To
    headers_add = ${if eq{m}{${lookup mysql{ \
                    select min(m.replyTo) \
                    from ml m, domains d \
                    where   d.enabled   = '1' \
                        and d.domain    = '${quote_mysql:$domain}' \
                        and d.domain_id = m.domain_id \
                        and m.enabled   = '1' \
                        and m.name      = '${quote_mysql:$local_part}' \
                        and m.email     = '${quote_mysql:$sender_address}' \
                    }}} \
                    {Reply-To: \
# For m.replyto == m (replies go to mailing list)
                         ${lookup mysql{ \
                         select min(m.name) \
                         from    ml m, domains d \
                         where   d.enabled   = '1' \
                             and d.domain    = '${quote_mysql:$domain}' \
                             and d.domain_id = m.domain_id \
                             and m.enabled   = '1' \
                             and m.name      = '${quote_mysql:$local_part}' \
                             and m.email     = '${quote_mysql:$sender_address}' \
                         }}@\
                         ${lookup mysql{ \
                         select min(d.domain) \
                         from    ml m, domains d \
                         where   d.enabled   = '1' \
                             and d.domain    = '${quote_mysql:$domain}' \
                             and d.domain_id = m.domain_id \
                             and m.enabled   = '1' \
                             and m.name      = '${quote_mysql:$local_part}' \
                             and m.email     = '${quote_mysql:$sender_address}' \
                         }}\n} \
# For m.replyTo == s (replies go to sender)
# The following line will cause the reply-to header to be expicitely designated as the
# sender's "From:" address. Otherwise, comment out this line and the reply-to header will
# be omitted so that replys default to sender as per the user's email client.
                   {Reply-To: $h_From:\n} \
# Endif
                   }
#
# Here is the router that manages senders that are not members of the mailing list
virtual_mailing_lists_closed:
	driver = accept
        domains = ${lookup mysql{ \
		select min(d.domain) \
        	from    ml m, domains d \
		where 	d.enabled = '1' \
			and d.domain    = '${quote_mysql:$domain}' \
                	and d.domain_id = m.domain_id \
                	and m.enabled   = '1' \
                	and m.name      = '${quote_mysql:$local_part}'  \
		}}
	transport = closed_mailing_list_rejection
	no_more

# The virtual_mailing_lists_closed router does not reject (fail) email sent
# to closed mailing lists when the sender is not a member of the list. Instead,
# this router accepts the mail but then uses the closed_mailing_list_rejection
# transport which sends a rejection notice to the sender that the list is a
# closed list and that the message was not processed to the list. This way,
# recipient and sender callouts to the mailing list will always work where they
# otherwise would not.


# exim4u_local_rl.conf
# exim4u: Increment ratelimit rates to stop runaway internal accounts from generating spam. This
# is directed at authenticated users and users on the local machine using webmail.
#
#
# Ratelimit local senders sent receipts (rcpt to:) to 250 per 3 hour (2,000 per day) per sender address.
# Only apply to local host via "hosts = :" since we are targeting webmail users here.
# defer ratelimit = 250 / 3h / leaky / per_rcpt / $sender_address

        defer
		condition       = ${if eq{SENDER_RL}{YES}}
		hosts		=  : localhost
                senders         = ! /etc/exim/exim4u_sender_rl_addr
		hosts           = ! /etc/exim/exim4u_sender_rl_dom
		ratelimit       = 250 / 3h / leaky / per_rcpt / $sender_address
                log_message     = Local senders sent mail ratelimit ( $sender_address ) exceeds $sender_rate per $sender_rate_period
                message         = Number of sent emails has exceeded our sender limit. Come back later.

# Ratelimit authenticated senders sent receipts (rcpt to:) to 250 per 3 hour (2,000 per day) per authenticated sender address.
# Only apply to authenticated sessions via "authenticated = *"
# defer ratelimit = 250 / 3h / leaky / per_rcpt / $authenticated_id
        defer
		condition       = ${if eq{SENDER_RL}{YES}}
		authenticated	= *
                senders         = ! /etc/exim/exim4u_sender_rl_addr
		hosts           = ! /etc/exim/exim4u_sender_rl_dom
                ratelimit       = 250 / 3h / leaky / per_rcpt / $authenticated_id
                log_message     = Authenticated senders sent mail ratelimit ( $authenticated_id ) exceeds $sender_rate per $sender_rate_period
                message         = Number of sent emails has exceeded our sender limit. Come back later.

# Remote IP ratelimit set to 10 per min (600 per hour)
# defer ratelimit = 10 / 1m / strict / per_mail / $sender_host_address
        defer
		condition       = ${if eq{SENDER_RL}{YES}}
		hosts		=  : localhost
		senders         = ! /etc/exim/exim4u_sender_rl_addr
                hosts		= ! /etc/exim/exim4u_sender_rl_dom
		ratelimit       = 10 / 1m / strict / per_mail / $sender_host_address
                log_message     = Local host ratelimit ( $sender_host_address ) exceeds $sender_rate per $sender_rate_period
                message		= Number of sent emails has exceeded our IP limit. Come back later.

# Remote IP ratelimit set to 10 per min (600 per hour) for authenticated users.
# defer ratelimit = 10 / 1m / strict / per_mail / $sender_host_address
        defer
		condition       = ${if eq{SENDER_RL}{YES}}
                authenticated	= *
		senders         = ! /etc/exim/exim4u_sender_rl_addr
                hosts           = ! /etc/exim/exim4u_sender_rl_dom
                ratelimit       = 10 / 1m / strict / per_mail / $sender_host_address
                log_message     = Authenticated remote IP ratelimit ( $sender_host_address ) exceeds $sender_rate per $sender_rate_period
                message         = Number of sent emails has exceeded our IP limit. Come back later.

# For some reason, the ratelimit statement for the $sender_host_address will only work properly when $sender_host_address has a valid
# reverse DNS lookup value.  So, if your exim machine is on a local network behind a router then you must setup reverse dns for the
# local network in order for this ratelimit statement to work for other sender machine IPs on the local network. That is, if the exim 
# machine is on a local network behind a router then reverse DNS must be setup for the router's IP address so that $sender_host_address
# will work properly in the ratelimit statement for the purpose of ratelimiting the other sender machines on the local network.

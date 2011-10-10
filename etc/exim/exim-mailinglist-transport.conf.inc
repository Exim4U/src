closed_mailing_list_rejection:
	driver = autoreply
	to = $sender_address
	subject = $local_part@$domain is a closed mailing list.
	text = Your message to $local_part@$domain is being returned because it is a closed mailing list.
# Uncomment the following line to include the original message in the autoreply
#	return_message


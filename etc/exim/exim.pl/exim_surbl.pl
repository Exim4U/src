# Copyright (c) 2006-2012 Erik Mugele.  All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
# IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
# NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
# THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
# THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

sub surblspamcheck
{

# Designed and written by Erik Mugele, 2004-2010,1http://www.teuton.org/~ejm
# Version 2.3-beta
#
# Please see the following website for details on usage of
# this script:  http://www.teuton.org/~ejm/exim_surbl

    # The following variable is the full path to the file containing the 
    # two-level top level domains (TLD).
    # ---------------------------------------------------------------------
    # THIS VARIABLE MUST BE SET TO THE FULL PATH AND NAME OF THE FILE 
    # CONTAINING THE TWO LEVEL TLD!
    # ---------------------------------------------------------------------
    my $twotld_file = "/etc/exim/exim.pl/two-level-tlds";    
    
    # The following variable is the full path to the file containing the 
    # three-level top level domains (TLD).
    # ---------------------------------------------------------------------
    # THIS VARIABLE MUST BE SET TO THE FULL PATH AND NAME OF THE FILE 
    # CONTAINING THE THREE LEVEL TLD!
    # ---------------------------------------------------------------------
    my $threetld_file = "/etc/exim/exim.pl/three-level-tlds";

    # The following variable is the full path to the file containing
    # whitelist entries.  
    # ---------------------------------------------------------------------
    # THIS VARIABLE MUST BE SET TO THE FULL PATH AND NAME OF THE FILE 
    # CONTAINING THE WHITELIST DOMAINS!
    # ---------------------------------------------------------------------
    my $whitelist_file = "/etc/exim/exim.pl/surbl_whitelist.txt";
    
    # This variable defines the maximum MIME file size that will be checked
    # if this script is called by the MIME ACL.  This is primarily to
    # keep the load down on the server.  Size is in bytes.
    my $max_file_size = 50000;
    
    # The following ariables enable or disable the SURBL, URIBL and DBL
    # lookups.  Set to 1 to enable and 0 to disable.
    my $surbl_enable = 1;
    my $uribl_enable = 1;
    my $dbl_enable = 1;
    
    # Check to see if a decode MIME attachment is being checked or 
    # just a plain old text message with no attachments
    my $exim_body = "";
    my $mime_filename = Exim::expand_string('$mime_decoded_filename');
    if ($mime_filename) {
        # DEBUG Statement
        #warn ("MIME FILENAME: $mime_filename\n");
        # If the MIME file is too large, skip it.
        if (-s $mime_filename <= $max_file_size) {
            open(fh,"<$mime_filename");
            binmode(fh);
            while (read(fh,$buff,1024)) {
                $exim_body .= $buff;
            }
            close (fh);
        } else {
            $exim_body = "";
        }
    } else {
        $exim_body = Exim::expand_string('$message_body');
    }
    
    sub surbllookup {
        # This subroutine does the actual DNS lookup and builds and returns
        # the return message for the SURBL lookup.
        my @params = @_;
        my $surbldomain = ".multi.surbl.org";
        @dnsbladdr=gethostbyname($params[0].$surbldomain);
        # If gethostbyname() returned anything, build a return message.
        $return_string = "";
        if (scalar(@dnsbladdr) != 0) {
            $return_string = "Blacklisted URL in message. (".$params[0].") in";
            @surblipaddr = unpack('C4',($dnsbladdr[4])[0]);
            if ($surblipaddr[3] & 64) {
                $return_string .= " [jp]";
            }
            if ($surblipaddr[3] & 32) {
                $return_string .= " [ab]";
            }
            if ($surblipaddr[3] & 16) {
                $return_string .= " [ob]";
            }
            if ($surblipaddr[3] & 8) {
                $return_string .= " [ph]";
            }
            if ($surblipaddr[3] & 4) {
                $return_string .= " [ws]";
            }
            if ($surblipaddr[3] & 2) {
                $return_string .= " [sc]";
            }
            $return_string .= ". See http://www.surbl.org/lists.html.";
        }
        return $return_string;
    }
    
    sub uribllookup {
        # This subroutine does the actual DNS lookup and builds and returns
        # the return message for the URIBL check.
        my @params = @_;
        my $uribldomain = ".black.uribl.com";
        @dnsbladdr=gethostbyname($params[0].$uribldomain);
        # If gethostbyname() returned anything, build a return message.
        $return_string = "";
        if (scalar(@dnsbladdr) != 0) {
            $return_string = "Blacklisted URL in message. (".$params[0].") in";
            @ipaddr = unpack('C4',($dnsbladdr[4])[0]);
            if ($ipaddr[3] & 8) {
                $return_string .= " [red]";
            }
            if ($ipaddr[3] & 4) {
                $return_string .= " [grey]";
            }
            if ($ipaddr[3] & 2) {
                $return_string .= " [black]";
            }
            $return_string .= ". See http://lookup.uribl.com.";
        }
        return $return_string;
    }

    sub dbllookup {
        # This subroutine does the actual DNS lookup and builds and returns
        # the return message for the Spamhaus DBL check.
        my @params = @_;
        my $dbldomain = ".dbl.spamhaus.org";
        @dnsbladdr=gethostbyname($params[0].$dbldomain);
        # If gethostbyname() returned anything, build a return message.
        $return_string = "";
        if (scalar(@dnsbladdr) != 0) {
            $return_string = "Blacklisted URL in message: ".$params[0];
            $return_string .= ". See http://www.spamhaus.org/lookup.lasso?dnsbl=domain.";
        }
        return $return_string;
    }

    sub mkaddress {
        # This subroutine takes a list of domain parts
        # (e.g. ["www","example","com"]) and a number (e.g. 2) and returns a 
        # the address of the given number of parts (e.g. example.com).
        my $numparts = @_[-1];
        pop(@_);
        my @domain = @_;
        my $address = $domain[-1];
        for (my $i=2; $i<=$numparts; $i++) {
            $address = $domain[-$i].".".$address;
        }
        return $address;
    }
    
    sub converthex {
        # This subroutine converts two hex characters to an ASCII character.
        # It is called when ASCII obfuscation or Printed-Quatable characters
        # are found (i.e. %AE or =AE).
        # It should return a converted/plain address after splitting off
        # everything that isn't part of the address portion of the URL.
        my @ob_parts = @_;
        my $address = $ob_parts[0];
        for (my $j=1; $j < scalar(@ob_parts); $j++) {
            $address .= chr(hex(substr($ob_parts[$j],0,2)));
            $address .= substr($ob_parts[$j],2,);
        }
        $address = (split(/[^A-Za-z0-9._\-]/,$address))[0];
        return $address
    }

    ################
    # Main Program #
    ################

    if ($exim_body) {
        # Find all the URLs in the message by finding the HTTP string
        @parts = split(/[hH][tT][tT][pP](:|=3[aA])(\/|=2[Ff])(\/|=2[Ff])/,$exim_body);
        if (scalar(@parts) > 1) {
            # Read the entries from the two-level TLD file.
            open (twotld_handle,$twotld_file) or die "Can't open $twotld_file.\n";
            while (<twotld_handle>) {
                next if (/^#/ || /^$/ || /^\s$/);
                push(@twotlds,$_);
            }
            close (twotld_handle) or die "Close: $!\n";
            # Read the entries from the three-level TLD file.
            open (threetld_handle,$threetld_file) or die "Can't open $threetld_file.\n";
            while (<threetld_handle>) {
                next if (/^#/ || /^$/ || /^\s$/);
                push(@threetlds,$_);
            }
            close (threetld_handle) or die "Close: $!\n";
            # Read the entries from the whitelist file.
            open (whitelist_handle,$whitelist_file) or die "Can't open $whitelist_file.\n";
            while (<whitelist_handle>) {
                next if (/^#/ || /^$/ || /^\s$/);
                push(@whitelist,$_);
            }
            close (whitelist_handle) or die "Close: $!\n";

            @surbl_list = ();
            @uribl_list = ();
            @dbl_list = ();

            # Go through each of the HTTP parts that were found in the message
            for ($i=1; $i < scalar(@parts); $i++) {
                # Special case of Quoted Printable EOL marker
                $parts[$i] =~ s/=\n//g;

                # Split the parts and find the address portion of the URL.
                # Address SHOULD be either a FQDN, IP address, or encoded address.
                $address = (split(/[^A-Za-z0-9\._\-%=]/,$parts[$i]))[0];
                
                # Check for an =.  If it exists, we assume the URL is doing 
                # Quoted-Printable.  Decode it and redefine $address
                if ($address =~ /=/) {
                    @ob_parts = split(/=/,$address);
                    $address = converthex(@ob_parts);
                }

                # Check for a %.  If it exists the URL is using % ASCII
                # obfuscation.  Decode it and redefine $address.
                if ($address =~ /%/) {
                    @ob_parts = split(/%/,$address);
                    $address = converthex(@ob_parts);
                }

                # Convert the address to lower case.
                $address = lc($address);

                # Split the the address into the elements separated by periods.
                @domain = split(/\./,$address);

                # Check the length of the domain name.  If less then two elements
                # at this point it is probably bogus or there is a bug in one of 
                # the decoding/converting routines above.
                if (scalar(@domain) >=2) {
                    $spamcheckdomain = "";

                    # DEBUG statement.
                    #warn ("FOUND DOMAIN: ".mkaddress(@domain,scalar(@domain))."\n");

                    # Domain has two or more than four elements.
                    if ((scalar(@domain) == 2) || (scalar(@domain) >=5)) {
                        # Add two elements of the domain to the list(s).
                        $spamcheckdomain=mkaddress(@domain,2);
                        # Check if $spamcheckdomain is not in the whitelist.
                        if (! grep(/^$spamcheckdomain$/i,@whitelist)) {
                            # If SURBL checks are enabled and the domain is
                            # not in the SURBL list, add it.
                            if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
                                ($surbl_enable == 1)) {
                                push(@surbl_list,$spamcheckdomain);
                            }
                            # If URIBL checks are enabled and the domain is
                            # not in the URIBL list, add it.
                            if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
                                ($uribl_enable == 1)) {
                                push(@uribl_list,$spamcheckdomain);
                            }
                            # If DBL checks are enabled and the domain is
                            # not in the DBL list, add it.
                            if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
                                ($dbl_enable == 1)) {
                                push(@dbl_list,$spamcheckdomain);
                            }
                        }
                    }

                    # Domain has three elements.
                    if (scalar(@domain) == 3) {
                        # Set $spamcheckdomain to two elements.
                        $spamcheckdomain = mkaddress(@domain,2);
                        $two_checkdomain = $spamcheckdomain;
                        if (grep(/^$spamcheckdomain$/i,@twotlds)) {
                            # $spamcheckdomain is in the two-level TLD list.
                            # Reset $spamcheckdomain to three elements.
                            $spamcheckdomain = mkaddress(@domain,3);
			    # Check if $spamcheckdomain is not in the whitelist.
                            if ((! grep(/^$spamcheckdomain$/i,@whitelist)) &&
                                (! grep(/^two_checkdomain$/i,@whitelist))) {
				# If SURBL checks are enabled and the domain is
				# not in the SURBL list, add it.
                                if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
                                    ($surbl_enable == 1)) {
                                    push(@surbl_list,$spamcheckdomain);
                                }
				# If URIBL checks are enabled and the domain is
				# not in the URIBL list, add it.
                                if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
                                    ($uribl_enable == 1)) {
                                    push(@uribl_list,$spamcheckdomain);
                                }
				# If DBL checks are enabled and the domain is
				# not in the DBL list, add it.
                                if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
                                    ($dbl_enable == 1)) {
                                    push(@dbl_list,$spamcheckdomain);
                                }
                            }
			} else {
                            # $spamcheckdomain is not in the two-level TLD list.
                            # $spamcheckdomain is still two elements.
			    # Check if $spamcheckdomain is not in the whitelist.
                            if (! grep(/^$spamcheckdomain$/i,@whitelist)) {
				# If SURBL checks are enabled and the domain is
				# not in the SURBL list, add it.
                                if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
                                    ($surbl_enable == 1)) {
                                    push(@surbl_list,$spamcheckdomain);
                                }
				# If URIBL checks are enabled and the domain is
				# not in the URIBL list, add it.
                                if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
                                    ($uribl_enable == 1)) {
                                    push(@uribl_list,$spamcheckdomain);
                                }
				# If DBL checks are enabled and the domain is
				# not in the DBL list, add it.
                                if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
                                    ($dbl_enable == 1)) {
                                    push(@dbl_list,$spamcheckdomain);
                                }
                            }
			    # Reset $spamcheckdomain to three elements.
			    $spamcheckdomain = mkaddress(@domain,3);
			    # Check if $spamcheckdomain is not in the whitelist.
			    if ((! grep(/^$spamcheckdomain$/i,@whitelist)) &&
                                (! grep(/^$two_checkdomain$/i,@whitelist))) {
				# If URIBL checks are enabled and the domain is
				# not in the URIBL list, add it.
				if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
				    ($uribl_enable == 1)) {
				    push(@uribl_list,$spamcheckdomain);
					
				}
				# If DBL checks are enabled and the domain is
				# not in the DBL list, add it.
				if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
				    ($dbl_enable == 1)) {
				    push(@dbl_list,$spamcheckdomain);
				}
			    }
			}
		    }
		    
                    # Domain has four elements.
                    if (scalar(@domain) == 4) {
                        if ($domain[-1] =~ /^(\d){1,3}$/) {
                            # Domain is an IP address
			    # Set $spamcheckdomain to the IP address in reverse.
                            $spamcheckdomain = $domain[3].".".$domain[2].
                                ".".$domain[1].".".$domain[0];

			    # Do NOT check IP addresses against the Spamhaus DBL list.

			    # If SURBL checks are enabled and the IP is
			    # not in the SURBL list, add it.
			    if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
				($surbl_enable == 1)) {
				push(@surbl_list,$spamcheckdomain);
			    }
			    # If URIBL checks are enabled and the IP is 
			    # not in the URIBL list, add it.
			    if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
				($uribl_enable == 1)) {
				push(@uribl_list,$spamcheckdomain);
			    }
			} else {
                            # Domain is not an IP address.
			    # Check if the last three elements of the domain are
			    # in the three-level TLD list.
                            $three_checkdomain = mkaddress(@domain,3);
                            $two_checkdomain = mkaddress(@domain,2);
			    if (grep(/^$three_checkdomain$/i,@threetlds)) {
				# Set $spamcheckdomain to four elements.
                                $spamcheckdomain = mkaddress(@domain,4);
				# Check if $spamcheckdomain is not in the whitelist.
                                if ((! grep(/^$spamcheckdomain$/i,@whitelist)) &&
                                    (! grep(/^three_checkdomain$/i,@whitelist)) &&
                                    (! grep(/^two_checkdomain$/i,@whitelist))) {
				    # If SURBL checks are enabled and the domain is
				    # not in the SURBL list, add it.
                                    if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
                                        ($surbl_enable == 1)) {
                                        push(@surbl_list,$spamcheckdomain);
                                    }
				    # If URIBL checks are enabled and the domain is
				    # not in the URIBL list, add it.
                                    if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
                                        ($uribl_enable == 1)) {
                                        push(@uribl_list,$spamcheckdomain);
                                    }
				    # If DBL checks are enabled and the domain is
				    # not in the DBL list, add it.
                                    if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
                                        ($dbl_enable == 1)) {
                                        push(@dbl_list,$spamcheckdomain);
                                    }
                                }
			    }

			    # Check if the last two elements of the domain are
			    # in the two-level TLD list.
			    elsif (grep(/^$two_checkdomain$/i,@twotlds)) {
				# Reset $spamcheckdomain to three elements.
				$spamcheckdomain = mkaddress(@domain,3);
				# Check if $spamcheckdomain is not in the whitelist.
				if ((! grep(/^$spamcheckdomain$/i,@whitelist)) &&
                                    (! grep(/^two_checkdomain$/i,@whitelist))) {
				    # If SURBL checks are enabled and the domain is
				    # not in the SURBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
					($surbl_enable == 1)) {
					push(@surbl_list,$spamcheckdomain);
				    }
                                    # If URIBL checks are enabled and the domain is
				    # not in the URIBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
					($uribl_enable == 1)) {
					push(@uribl_list,$spamcheckdomain);
				    }
				    # If DBL checks are enabled and the domain is
				    # not in the DBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
					($dbl_enable == 1)) {
					push(@dbl_list,$spamcheckdomain);
				    }
				}

				# Reset $spamcheckdomain to four elements.
				$spamcheckdomain = mkaddress(@domain,4);
				# Check if $spamcheckdomain is not in the whitelist.
				if ((! grep(/^$spamcheckdomain$/i,@whitelist)) &&
                                    (! grep(/^three_checkdomain$/i,@whitelist)) &&
                                    (! grep(/^two_checkdomain$/i,@whitelist))) {
				    # If SURBL checks are enabled and the domain is
				    # not in the SURBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
					($surbl_enable == 1)) {
					push(@surbl_list,$spamcheckdomain);
				    }
				    # If URIBL checks are enabled and the domain is
				    # not in the URIBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
					($uribl_enable == 1)) {
					push(@uribl_list,$spamcheckdomain);
				    }
				    # If DBL checks are enabled and the domain is
				    # not in the DBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
					($dbl_enable == 1)) {
					push(@dbl_list,$spamcheckdomain);
				    }
				}

			    } else {
				# Set $spamcheckdomain to two elements
				$spamcheckdomain = mkaddress(@domain,2);
				# Check if $spamcheckdomain is not in the whitelist.
				if (! grep(/^$spamcheckdomain$/i,@whitelist)) {
				    # If SURBL checks are enabled and the domain is
				    # not in the SURBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@surbl_list) &&
					($surbl_enable == 1)) {
					push(@surbl_list,$spamcheckdomain);
				    }
				    # If URIBL checks are enabled and the domain is
				    # not in the URIBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
					($uribl_enable == 1)) {
					push(@uribl_list,$spamcheckdomain);
				    }
				    # If DBL checks are enabled and the domain is
				    # not in the DBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
					($dbl_enable == 1)) {
					push(@dbl_list,$spamcheckdomain);
				    }
				}
				# Reset $spamcheckdomain to three elements
				$spamcheckdomain = mkaddress(@domain,3);
				# Check if $spamcheckdomain is not in the whitelist.
				if ((! grep(/^$spamcheckdomain$/i,@whitelist)) &&
                                    (! grep(/^$two_checkdomain$/i,@whitelist))) {
				    # If URIBL checks are enabled and the domain is
				    # not in the URIBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
					($uribl_enable == 1)) {
					push(@uribl_list,$spamcheckdomain);
				    }
				    # If DBL checks are enabled and the domain is
				    # not in the DBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
					($dbl_enable == 1)) {
					push(@dbl_list,$spamcheckdomain);
				    }
				}
				# Set $spamcheckdomain to four elements
				$spamcheckdomain = mkaddress(@domain,4);
				# Check if $spamcheckdomain is not in the whitelist.
				if ((! grep(/^$spamcheckdomain$/i,@whitelist)) &&
                                    (! grep(/^$three_checkdomain$/i,@whitelist)) &&
                                    (! grep(/^$two_checkdomain$/i,@whitelist))) {
				    # If URIBL checks are enabled and the domain is
				    # not in the URIBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@uribl_list) &&
					($uribl_enable == 1)) {
					push(@uribl_list,$spamcheckdomain);
				    }
				    # If DBL checks are enabled and the domain is
				    # not in the DBL list, add it.
				    if (! grep(/^$spamcheckdomain$/i,@dbl_list) &&
					($dbl_enable == 1)) {
					push(@dbl_list,$spamcheckdomain);
				    }
				}
			    }
                        } # End: if ($domain[-1] =~ /^(\d){1,3}$/)
		    } # End: if (scalar(@domain) == 4)
                } # End: if (scalar(@domain) >=2)
            } # End: for ($i=1; $i < scalar(@parts); $i++)

	    # If there are items in the SURBL list and the SURBL check
	    # is enabled then perform lookups on them.
	    if ((scalar(@surbl_list) > 0) && 
		($surbl_enable == 1)) {
		foreach $i (@surbl_list) {
		    # DEBUG statement.
                    #warn ("CHECKING DOMAIN ($mime_filename): $i in SURBL list.\n");
		    $return_result = surbllookup($i);
                    if ($return_result ne "") {
                        return $return_result;
                    }
		}
	    }

	    # If there are items in the URIBL list and the URIBL check
	    # is enabled and the previous lookup did not return a result
	    # then perform lookups on them.
	    if ((scalar(@uribl_list) > 0) && 
		($uribl_enable == 1) &&
		($return_result eq "")) {
		foreach $i (@uribl_list) {
		    # DEBUG statement.
                    #warn ("CHECKING DOMAIN ($mime_filename): $i in URIBL list.\n");
		    $return_result = uribllookup($i);
                    if ($return_result ne "") {
                        return $return_result;
                    }
		}
	    }

	    # If there are items in the DBL list and the DBL check
	    # is enabled and the previous lookups did not return a result
	    # then perform lookups on them.
	    if ((scalar(@dbl_list) > 0) && 
		($dbl_enable == 1) &&
		($return_result eq "")) {
		foreach $i (@dbl_list) {
		    # DEBUG statement.
                    #warn ("CHECKING DOMAIN ($mime_filename): $i in DBL list.\n");
		    $return_result = dbllookup($i);
                    if ($return_result ne "") {
                        return $return_result;
                    }
		}
	    }
        } # End: if (scalar(@parts) > 1)
    } # End: if ($exim_body)

    # No URLs were found or the URLs that were found were not
    # listed in any list so return false.
    return false;

} # End Main: - sub surblspamcheck

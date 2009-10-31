#
# Copyright (c) 2006-2007 Erik Mugele.  All rights reserved.
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
#
# NOTES
# -----
#
# 1. This script makes use of the Country Code Top Level 
# Domains (ccTLD) provided by the SURBL group at
# http://spamcheck.freeapp.net/two-level-tlds  
# THE VARIABLE $cctld_file MUST BE SET TO THE FULL PATH AND 
# NAME OF THE FILE CONTAINING THE CCTLD LIST!  (see below)
#
# 2. This script makes use of whitelisting of popular domains.  The 
# source of the list can be found here: 
# http://spamassassin.apache.org/full/3.1.x/dist/rules/25_uribl.cf
# These are domains that are whitelisted by the SURBL group so it
# doesn't make sense to waste resources doing lookups on them.
# THE VARIABLE $whitelist_file MUST BE SET TO THE FULL PATH AND
# NAME OF THE FILE CONTAINING THE WHITE LIST!  (see below)          
# 
# 3. Per the guidelines at http://www.surbl.org, if your site processes
# more than 100,000 messages per day, you should NOT be using the 
# public SURBL name servers but should be rsync-ing from them and 
# running your own.  See http://www3.surbl.org/rsync-signup.html
#
sub surblspamcheck
{
# Designed and written by Erik Mugele, 2004-2006
# http://www.teuton.org/~ejm
# Version 2.0

    # The following variable is the full path to the file containing the 
    # list of Country Code Top Level Domains (ccTLD).
    # ---------------------------------------------------------------------
    # THIS VARIABLE MUST BE SET TO THE FULL PATH AND NAME OF THE FILE 
    # CONTAINING THE CCTLD LIST!
    # ---------------------------------------------------------------------
    my $cctld_file = "/etc/exim/exim.pl/ccTLD.txt";    
    
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
    my $max_file_size = 75000;
    
    # The following two variables enable or disable the SURBL and URIBL
    # lookups.  Set to 1 to enable and 0 to disable.
    my $surbl_enable = 1;
    my $uribl_enable = 1;
    
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
        my $surbldomain = ".black.uribl.com";
        @dnsbladdr=gethostbyname($params[0].$surbldomain);
        # If gethostbyname() returned anything, build a return message.
        $return_string = "";
        if (scalar(@dnsbladdr) != 0) {
            $return_string = "Blacklisted URL in message. (".$params[0].") in";
            @surblipaddr = unpack('C4',($dnsbladdr[4])[0]);
            if ($surblipaddr[3] & 8) {
                $return_string .= " [red]";
            }
            if ($surblipaddr[3] & 4) {
                $return_string .= " [grey]";
            }
            if ($surblipaddr[3] & 2) {
                $return_string .= " [black]";
            }
            $return_string .= ". See http://lookup.uribl.com.";
        }
        return $return_string;
    }
    
    sub converthex {
        # This subroutin converts two hex characters to an ASCII character.
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
        @parts = split /[hH][tT][tT][pP]:\/\//,$exim_body;
        if (scalar(@parts) > 1) {
            # Read the entries from the ccTLD file.
            open (cctld_handle,$cctld_file) or die "Can't open $cctld_file.\n";
            while (<cctld_handle>) {
                next if (/^#/ || /^$/ || /^\s$/);
                push(@cctlds,$_);
            }
            close (cctld_handle) or die "Close: $!\n";
            # Read the entries from the whitelist file.
            open (whitelist_handle,$whitelist_file) or die "Can't open $whitelist_file.\n";
            while (<whitelist_handle>) {
                next if (/^#/ || /^$/ || /^\s$/);
                push(@whitelist,$_);
            }
            close (whitelist_handle) or die "Close: $!\n";
            # Go through each of the HTTP parts that were found in the message
            for ($i=1; $i < scalar(@parts); $i++) {
                # Special case of Quoted Printable EOL marker
                $parts[$i] =~ s/=\n//g;
                    # Split the parts and find the address portion of the URL.
                # Address SHOULD be either a FQDN, IP address, or encoded address.
                $address = (split(/[^A-Za-z0-9\._\-%=]/,$parts[$i]))[0];
                # Check for an =.  If it exists, we assume the URL is doing 
                # Quoted-Printable.  Decode it and redine $address
                if ($address =~ /=/) {
                    @ob_parts = split /=/,$address;
                    $address = converthex(@ob_parts);
                }
                # Check for a %.  If it exists the URL is using % ASCII
                # obfuscation.  Decode it and redefine $address.
                if ($address =~ /%/) {
                    @ob_parts = split /%/,$address;
                    $address = converthex(@ob_parts);
                }
                # Split the the address into the elements separated by periods.
                @domain = split /\./,$address;
                # Check the length of the domain name.  If less then two elements
                # at this point it is probably bogus or there is a bug in one of 
                # the decoding/converting routines above.
                if (scalar(@domain) >= 2) {
                    $return_result="";
                    # By default, assume that the domain check is on a 
                    # "standard" two level domain
                    $spamcheckdomain=$domain[-2].".".$domain[-1];
                    # Check for a two level domain
                    if (((scalar(@domain) == 2) || (scalar(@domain) >= 5))  && 
                        (grep(/^$spamcheckdomain$/i,@cctlds))) {
                        $return_result="cctld";
                    }
                    # Check for a three level domain
                    if (scalar(@domain) == 3) {
                        if (grep(/^$spamcheckdomain$/i,@cctlds)) {
                            $spamcheckdomain=$domain[-3].".".$spamcheckdomain;
                            if (grep(/^$spamcheckdomain$/,@cctlds)) {
                                $return_result="cctld";
                            }
                        }
                    }
                    # Check for a four level domain
                    if (scalar(@domain) == 4) {
                        # Check to see if the domain is an IP address
                        if ($domain[-1] =~ /[a-zA-Z]/) {
                            if (grep(/^$spamcheckdomain$/i,@cctlds)) {
                                $spamcheckdomain=$domain[-3].".".$spamcheckdomain;
                                if (grep(/^$spamcheckdomain$/i,@cctlds)) {
                                    $spamcheckdomain=$domain[-4].".".$spamcheckdomain;
                                }
                            }
                        } else {
                            # Domain is an IP address
                            $spamcheckdomain=$domain[3].".".$domain[2].
                                ".".$domain[1].".".$domain[0];
                        }
                    }
                    # DEBUG statement
                    #warn ("FOUND DOMAIN ($mime_filename): $spamcheckdomain\n");
                    # If whitelisting is enabled check domain against the 
                    # whitelist.
                    if ($whitelist_file ne "") {
                        foreach $whitelist_entry (@whitelist) {
                            chomp($whitelist_entry);
                            if ($spamcheckdomain =~ m/^$whitelist_entry$/i) {
                                $return_result="whitelisted";
                                last;
                            }
                        }
                    }
                    # If the domain is whitelisted or in the cctld skip adding
                    # it to the lookup list.
                    if ($return_result eq "") {
                        if (scalar(@lookupdomains) > 0) {
                            # Check so see if the domain already is in the list.
                            if (not grep(/^$spamcheckdomain$/i,@lookupdomains)) {
                                    push(@lookupdomains,$spamcheckdomain);
                            }
                        } else {
                            push(@lookupdomains,$spamcheckdomain);
                        }
                    }
                }
            }
            # If there are items in the lookupdomains list then
            # perform lookups on them.  If there are not, something is wrong
            # and just return false.  There should always be something in the list.
            if (scalar(@lookupdomains) > 0) {
                foreach $i (@lookupdomains) {
                    # DEBUG statement.
                    #warn ("CHECKING DOMAIN ($mime_filename): $i\n");
                    # If SURBL lookups are enabled do an SURBL lookup
                    if ($surbl_enable == 1) {
                        $return_result = surbllookup($i);
                    }
                    # If URIBL lookups are enabled and the SURBL lookup failed
                    # do a URIBL lookup
                    if (($uribl_enable == 1) && ($return_result eq "")) {
                        $return_result = uribllookup($i);
                    }
                    # If we got a hit return the result to Exim
                    if ($return_result ne "") {
                        return $return_result;
                    }
                }
            }
        }
    }
    # We didn't find any URLs or the URLs we did find were not
    # listed so return false.
    return false;
}
